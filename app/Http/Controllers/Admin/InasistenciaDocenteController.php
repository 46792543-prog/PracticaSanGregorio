<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProfesorMateria as Designacion;
use App\Models\InasistenciaDocente;
use App\Models\LicenciaDocente;
use App\Models\Materia;
use App\Models\PagoDocente;
use App\Models\Profesor;
use App\Models\TipoLicencia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Inasistencias docentes como licencias (panel de Secretaría):
 *  docente + desde/hasta + tipo de licencia + documentación,
 *  y por cada clase que pierde en esas fechas (según el Horario) sus horas cátedra.
 * Descuentan del pago las sin goce y las con goce con documentación Pendiente.
 */
class InasistenciaDocenteController extends Controller
{
    // Carbon: 1 = lunes … 7 = domingo (igual que dia_semana del Horario).
    private const DIAS = [1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    private const MAX_DIAS_LICENCIA = 90;

    public function index(Request $request): View
    {
        $periodo = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('periodo')) ? Carbon::parse($request->query('periodo') . '-01') : now()->startOfMonth();

        // Licencias que tocan el mes elegido.
        $licencias = LicenciaDocente::with('profesor.persona', 'tipo', 'clases.asignacion.materia.nombreMateria')
            ->whereDate('desde', '<=', $periodo->copy()->endOfMonth())->whereDate('hasta', '>=', $periodo)
            ->when($request->integer('profesor'), fn ($q, $p) => $q->where('id_profesor', $p))
            ->orderByDesc('desde')->get();

        // Designaciones con horas, y sus horas de clase por día (para armar las clases que pierde).
        $designaciones = Designacion::with('materia.nombreMateria', 'materia.carrera', 'horarios', 'turnoCursada')
            ->whereNotNull('horas_catedra')
            ->where(fn ($q) => $q->where('activa', true)->orWhereNotNull('fecha_baja'))
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id_asignacion,
                'profesor' => $d->id_profesor,
                'materia' => $d->materia->nombre,
                'detalle' => "{$d->materia->carrera?->nombre_carrera} · {$d->tipo} · {$d->turnoCursada?->nombre_turno}",
                'horas' => $d->horas_catedra,
                'alta' => $d->fecha_alta?->toDateString(),
                'baja' => $d->fecha_baja?->toDateString(),
                'dias' => $this->horasPorDia($d),
            ])->values();

        $profesores = Profesor::with('persona')->whereIn('id_profesor', $designaciones->pluck('profesor'))->get()->sortBy(fn ($p) => $p->apellido)->values();

        return view('admin.inasistencias.index', [
            'periodo' => $periodo,
            'licencias' => $licencias,
            'filtroProfesor' => $request->integer('profesor') ?: null,
            'profesores' => $profesores,
            'docentesJs' => $profesores->map(fn ($p) => ['id' => $p->id_profesor, 'nombre' => "{$p->apellido}, {$p->nombre}", 'dni' => $p->dni]),
            'designaciones' => $designaciones,
            'tipos' => TipoLicencia::orderBy('nombre')->get(),
            // Días ya usados por docente y tipo ("prof-tipo-2026" y "prof-tipo-2026-11"), para mostrar los topes.
            'diasUsados' => $this->diasUsados(),
            // Clases que ya tienen una inasistencia cargada ("idAsignacion|fecha").
            'ocupadas' => InasistenciaDocente::get(['id_asignacion', 'fecha'])->map(fn ($f) => $f->id_asignacion . '|' . $f->fecha->toDateString())->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'desde' => ['required', 'date', 'before_or_equal:' . today()->addYear()->toDateString()],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'id_tipo_licencia' => ['required', 'exists:tipo_licencia,id_tipo_licencia'],
            'documentacion' => ['nullable', Rule::in(LicenciaDocente::DOCUMENTACION)],
            'fecha_presentacion' => ['nullable', 'required_if:documentacion,Presentada', 'date', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:255'],
            'clases' => ['required', 'array', 'min:1'],
            'clases.*.id_asignacion' => ['required', 'integer'],
            'clases.*.fecha' => ['required', 'date'],
            'clases.*.horas' => ['required', 'integer', 'min:1', 'max:30'],
        ], [
            'id_profesor.required' => 'Elegí el docente.',
            'desde.required' => 'Elegí desde qué fecha.',
            'desde.before_or_equal' => 'La fecha no puede ser de más de un año adelante.',
            'hasta.after_or_equal' => '"Hasta" no puede ser anterior a "Desde".',
            'id_tipo_licencia.required' => 'Elegí el tipo de licencia.',
            'fecha_presentacion.required_if' => 'Indicá la fecha en que presentó la documentación.',
            'fecha_presentacion.before_or_equal' => 'La fecha de presentación no puede ser futura.',
            'clases.required' => 'En esas fechas el docente no tiene clases según el Horario: no hay horas cátedra para registrar.',
        ]);

        $desde = Carbon::parse($data['desde']);
        $hasta = Carbon::parse($data['hasta'] ?? $data['desde']);
        $tipo = TipoLicencia::findOrFail($data['id_tipo_licencia']);
        // Sin goce: no lleva documentación. Con goce: Presentada o Pendiente (pendiente descuenta hasta que la presente).
        $data['documentacion'] = $tipo->con_goce ? ($data['documentacion'] ?? null) : 'No corresponde';
        $this->fallar(! $data['documentacion'], 'documentacion', 'Indicá si presentó la documentación o si está pendiente.');
        $this->fallar($desde->diffInDays($hasta) >= self::MAX_DIAS_LICENCIA, 'hasta', 'La licencia no puede abarcar más de ' . self::MAX_DIAS_LICENCIA . ' días.');

        // Cada clase: del docente, dentro de las fechas y de la designación, con clase ese día y sin pasarse de horas.
        $designaciones = Designacion::with('horarios', 'materia.nombreMateria')->where('id_profesor', $data['id_profesor'])->get()->keyBy('id_asignacion');
        $clases = collect($data['clases'])->map(function ($c) use ($designaciones, $desde, $hasta) {
            $d = $designaciones->get($c['id_asignacion']);
            $this->fallar(! $d, 'clases', 'Una de las clases no es de este docente.');
            $fecha = Carbon::parse($c['fecha']);
            $deClase = $this->horasPorDia($d)[self::DIAS[$fecha->dayOfWeekIso]] ?? 0;
            $cuando = $fecha->format('d/m');
            $this->fallar($fecha->lt($desde) || $fecha->gt($hasta), 'clases', "La clase del {$cuando} está fuera de las fechas de la licencia.");
            $this->fallar(($d->fecha_alta && $fecha->lt($d->fecha_alta)) || ($d->fecha_baja && $fecha->gt($d->fecha_baja)), 'clases', "El {$cuando} {$d->materia->nombre} no estaba designada.");
            $this->fallar($deClase === 0, 'clases', "Según el Horario, {$d->materia->nombre} no tiene clase el {$cuando}.");
            $this->fallar($c['horas'] > $deClase, 'clases', "El {$cuando} {$d->materia->nombre} tiene {$deClase} h cátedra: no puede faltar más horas.");
            $this->fallar(InasistenciaDocente::where('id_asignacion', $d->id_asignacion)->whereDate('fecha', $fecha)->exists(), 'clases', "El {$cuando} {$d->materia->nombre} ya tiene una inasistencia cargada.");

            return ['id_asignacion' => $d->id_asignacion, 'fecha' => $fecha->toDateString(), 'horas_catedra' => (int) $c['horas']];
        });
        $this->fallar($clases->unique(fn ($c) => $c['id_asignacion'] . $c['fecha'])->count() < $clases->count(), 'clases', 'Hay una clase repetida.');

        // Topes del tipo: días distintos con clase, por año y por mes.
        $usados = $this->diasUsados();
        $dias = $clases->pluck('fecha')->unique();
        foreach ([['tope_anual', 'Y', 'en el año'], ['tope_mensual', 'Y-m', 'en el mes']] as [$campo, $formato, $texto]) {
            if (! $tipo->$campo) {
                continue;
            }
            foreach ($dias->groupBy(fn ($f) => Carbon::parse($f)->format($formato)) as $clave => $nuevos) {
                $total = ($usados["{$data['id_profesor']}-{$tipo->id_tipo_licencia}-{$clave}"] ?? 0) + $nuevos->count();
                $this->fallar($total > $tipo->$campo, 'id_tipo_licencia', "Supera el tope de \"{$tipo->nombre}\": {$tipo->$campo} días {$texto} ({$clave}); con esta licencia serían {$total}.");
            }
        }

        // Si algún mes ya se pagó, no se carga (cambiaría un pago ya hecho).
        foreach ($dias as $f) {
            $this->fallar($this->mesPagado($data['id_profesor'], Carbon::parse($f)), 'desde', 'El docente ya cobró ' . Carbon::parse($f)->translatedFormat('F Y') . ': primero eliminá ese pago.');
        }

        $licencia = DB::transaction(function () use ($data, $hasta, $clases) {
            $licencia = LicenciaDocente::create([
                'hasta' => $hasta->toDateString(),
                'fecha_presentacion' => $data['documentacion'] === 'Presentada' ? $data['fecha_presentacion'] : null,
                'id_persona_registra' => Auth::user()->id_persona,
            ] + collect($data)->only('id_profesor', 'id_tipo_licencia', 'desde', 'documentacion', 'observaciones')->all());
            $licencia->clases()->createMany($clases->all());

            return $licencia;
        });

        return redirect()->route('admin.inasistencias.index', ['periodo' => $desde->format('Y-m')])
            ->with('status', "Se registró la inasistencia ({$tipo->nombre}): {$clases->count()} clase(s), {$clases->sum('horas_catedra')} h cátedra. "
                . ($licencia->descuenta ? 'Se descuenta del pago.' : 'No se descuenta.'));
    }

    // Actualizar la documentación (ej: trajo el certificado después).
    public function documentacion(Request $request, LicenciaDocente $licencia): RedirectResponse
    {
        $data = $request->validate([
            'documentacion' => ['required', Rule::in(LicenciaDocente::DOCUMENTACION)],
            'fecha_presentacion' => ['nullable', 'required_if:documentacion,Presentada', 'date', 'before_or_equal:today'],
        ], [
            'fecha_presentacion.required_if' => 'Indicá la fecha en que presentó la documentación.',
            'fecha_presentacion.before_or_equal' => 'La fecha de presentación no puede ser futura.',
        ]);

        $this->fallar(! $licencia->tipo->con_goce, 'documentacion', 'Las licencias sin goce de sueldo no llevan documentación.');
        $descontaba = $licencia->descuenta;
        $licencia->fill([
            'documentacion' => $data['documentacion'],
            'fecha_presentacion' => $data['documentacion'] === 'Presentada' ? $data['fecha_presentacion'] : null,
        ]);
        // Si cambia si descuenta o no, el mes no tiene que estar pagado.
        if ($licencia->descuenta !== $descontaba) {
            $this->asegurarSinPagos($licencia);
        }
        $licencia->save();

        return back()->with('status', 'Se actualizó la documentación. ' . ($licencia->descuenta ? 'La licencia se descuenta del pago.' : 'La licencia no se descuenta.'));
    }

    public function destroy(LicenciaDocente $licencia): RedirectResponse
    {
        $this->asegurarSinPagos($licencia);
        $licencia->delete(); // sus clases se borran en cascada

        return back()->with('status', 'Se eliminó la inasistencia.');
    }

    // Nuevo tipo de licencia (desde el "+" del modal). Responde JSON.
    public function storeTipo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:tipo_licencia,nombre'],
            'articulo' => ['nullable', 'string', 'max:30'],
            'tope_anual' => ['nullable', 'integer', 'min:1', 'max:365'],
            'tope_mensual' => ['nullable', 'integer', 'min:1', 'max:31'],
            'con_goce' => ['required', 'boolean'],
        ], [
            'nombre.required' => 'Escribí el nombre del tipo de licencia.',
            'nombre.unique' => 'Ya existe un tipo de licencia con ese nombre.',
        ]);
        $this->fallar(($data['tope_anual'] ?? null) && ($data['tope_mensual'] ?? 0) > $data['tope_anual'], 'tope_mensual', 'El máximo por mes no puede superar el tope anual.');

        $tipo = TipoLicencia::create($data);

        return response()->json(['id' => $tipo->id_tipo_licencia, 'nombre' => $tipo->nombre, 'resumen' => $tipo->resumen, 'con_goce' => $tipo->con_goce, 'tope_anual' => $tipo->tope_anual, 'tope_mensual' => $tipo->tope_mensual]);
    }

    // Horas cátedra de clase por día de la semana, según las franjas del Horario.
    private function horasPorDia(Designacion $d): array
    {
        return $d->horarios->groupBy('dia_semana')->map(fn ($f) => intdiv($f->sum('minutos'), Materia::MINUTOS_HORA_CATEDRA))->all();
    }

    // Días distintos usados por docente y tipo: claves "prof-tipo-AAAA" y "prof-tipo-AAAA-MM".
    private function diasUsados(): Collection
    {
        return InasistenciaDocente::with('licencia:id_licencia,id_profesor,id_tipo_licencia')->get()
            ->unique(fn ($f) => $f->licencia->id_profesor . '-' . $f->licencia->id_tipo_licencia . '-' . $f->fecha->toDateString())
            ->flatMap(fn ($f) => ["{$f->licencia->id_profesor}-{$f->licencia->id_tipo_licencia}-{$f->fecha->format('Y')}", "{$f->licencia->id_profesor}-{$f->licencia->id_tipo_licencia}-{$f->fecha->format('Y-m')}"])
            ->countBy();
    }

    private function mesPagado(int $idProfesor, Carbon $fecha): bool
    {
        return PagoDocente::where('id_profesor', $idProfesor)->whereDate('periodo', $fecha->copy()->startOfMonth())->exists();
    }

    // Si algún mes de la licencia ya se pagó, no se puede cambiar (alteraría ese pago).
    private function asegurarSinPagos(LicenciaDocente $licencia): void
    {
        foreach ($licencia->clases as $clase) {
            $this->fallar($this->mesPagado($licencia->id_profesor, $clase->fecha), 'licencia', 'El docente ya cobró ' . $clase->fecha->translatedFormat('F Y') . ': no se puede modificar. Primero eliminá ese pago.');
        }
    }

    private function fallar(bool $condicion, string $campo, string $mensaje): void
    {
        if ($condicion) {
            throw ValidationException::withMessages([$campo => $mensaje]);
        }
    }
}
