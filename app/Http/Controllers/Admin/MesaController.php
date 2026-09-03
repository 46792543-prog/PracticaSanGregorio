<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\EstadoMesa;
use App\Models\LlamadoExamen;
use App\Models\MesaExamen;
use App\Models\Profesor;
use App\Models\RolTribunal;
use App\Models\TribunalMesa;
use App\Models\TurnoExamen;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function index(Request $request): View
    {
        $turnos = TurnoExamen::orderBy('id_turno')->get();
        $turnoId = (int) $request->query('turno', $turnos->firstWhere('nombre_turno', 'Turno Julio')?->id_turno ?? $turnos->first()?->id_turno);

        $mesas = MesaExamen::with('materia.carrera', 'materia.nombreMateria', 'tribunal.profesor.persona', 'tribunal.rolTribunal', 'llamadoExamen', 'inscripciones', 'estadoMesa')
            ->where('id_turno', $turnoId)
            ->when($request->query('q'), fn ($q, $busqueda) => $q->whereHas('materia.nombreMateria', fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")))
            ->orderBy('fecha_examen')
            ->get();

        return view('admin.mesas.index', [
            'mesas' => $mesas,
            'turno' => $turnoId,
            'turnos' => $turnos,
            'busqueda' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.mesas.create', [
            'carreras' => Carrera::with(['materias' => fn ($q) => $q->with('nombreMateria')->orderBy('numero_orden')])->orderBy('nombre_carrera')->get(),
            'profesores' => Profesor::with('persona')->where('activo', true)->get()->sortBy('apellido'),
            'turnos' => TurnoExamen::orderBy('id_turno')->get(),
            'llamados' => LlamadoExamen::orderBy('id_llamado')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validarDatosMesa($request);

        $anioLectivo = AnioLectivo::orderByDesc('anio')->firstOrFail();

        $mesa = MesaExamen::create([
            'id_materia' => $data['id_materia'],
            'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
            'id_turno' => $data['id_turno'],
            'id_llamado' => $data['id_llamado'],
            'fecha_examen' => $data['fecha_examen'],
            'fecha_inicio_inscripcion' => $data['fecha_inicio_inscripcion'],
            'fecha_fin_inscripcion' => $data['fecha_fin_inscripcion'],
            'id_estado_mesa' => EstadoMesa::where('nombre_estado', 'Programada')->value('id_estado_mesa'),
            'cupo_maximo' => $data['cupo_maximo'] ?? null,
        ]);

        $this->guardarTribunal($mesa, $data);

        return redirect()->route('admin.mesas.index', ['turno' => $data['id_turno']])->with('status', 'Mesa de examen creada correctamente.');
    }

    public function edit(MesaExamen $mesa): View
    {
        abort_unless($mesa->estadoMesa->nombre_estado === 'Programada', 403, 'Solo se pueden editar mesas programadas.');

        $tribunal = TribunalMesa::with('rolTribunal')->where('id_mesa', $mesa->id_mesa)->get()
            ->mapWithKeys(fn ($t) => [$t->rolTribunal->nombre_rol => $t->id_profesor]);

        $profesores = Profesor::with('persona')
            ->where(fn ($q) => $q->where('activo', true)->orWhereIn('id_profesor', $tribunal->values()))
            ->get()->sortBy('apellido');

        return view('admin.mesas.edit', [
            'mesa' => $mesa,
            'carreras' => Carrera::with(['materias' => fn ($q) => $q->with('nombreMateria')->orderBy('numero_orden')])->orderBy('nombre_carrera')->get(),
            'profesores' => $profesores,
            'turnos' => TurnoExamen::orderBy('id_turno')->get(),
            'llamados' => LlamadoExamen::orderBy('id_llamado')->get(),
            'presidenteId' => $tribunal['Presidente'] ?? null,
            'vocal1Id' => $tribunal['Vocal 1'] ?? null,
            'vocal2Id' => $tribunal['Vocal 2'] ?? null,
        ]);
    }

    public function update(Request $request, MesaExamen $mesa): RedirectResponse
    {
        abort_unless($mesa->estadoMesa->nombre_estado === 'Programada', 403, 'Solo se pueden editar mesas programadas.');

        $data = $this->validarDatosMesa($request);

        $mesa->update([
            'id_materia' => $data['id_materia'],
            'id_turno' => $data['id_turno'],
            'id_llamado' => $data['id_llamado'],
            'fecha_examen' => $data['fecha_examen'],
            'fecha_inicio_inscripcion' => $data['fecha_inicio_inscripcion'],
            'fecha_fin_inscripcion' => $data['fecha_fin_inscripcion'],
            'cupo_maximo' => $data['cupo_maximo'] ?? null,
        ]);

        TribunalMesa::where('id_mesa', $mesa->id_mesa)->delete();
        $this->guardarTribunal($mesa, $data);

        return redirect()->route('admin.mesas.index', ['turno' => $data['id_turno']])->with('status', 'Mesa de examen actualizada correctamente.');
    }

    public function destroy(MesaExamen $mesa): RedirectResponse
    {
        $mesa->update(['id_estado_mesa' => EstadoMesa::where('nombre_estado', 'Cancelada')->value('id_estado_mesa')]);

        return back()->with('status', 'Mesa cancelada.');
    }

    private function validarDatosMesa(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'id_materia' => ['required', 'exists:materia,id_materia'],
            'fecha_inicio_inscripcion' => ['required', 'date'],
            'fecha_fin_inscripcion' => ['required', 'date', 'after_or_equal:fecha_inicio_inscripcion'],
            'fecha_examen' => ['required', 'date', 'after_or_equal:fecha_fin_inscripcion'],
            'id_turno' => ['required', 'exists:turno_examen,id_turno'],
            'id_llamado' => ['required', 'exists:llamado_examen,id_llamado'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'presidente_id' => ['nullable', 'exists:profesor,id_profesor'],
            'vocal1_id' => ['nullable', 'exists:profesor,id_profesor'],
            'vocal2_id' => ['nullable', 'exists:profesor,id_profesor'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->fecha_inicio_inscripcion && $request->fecha_examen) {
                $apertura = Carbon::parse($request->fecha_inicio_inscripcion);
                $examen = Carbon::parse($request->fecha_examen);

                if ($examen->lt($apertura->copy()->addWeek())) {
                    $validator->errors()->add('fecha_examen', 'La fecha del examen debe ser al menos una semana después de la apertura de inscripción.');
                }
            }

            $docentes = array_filter([$request->presidente_id, $request->vocal1_id, $request->vocal2_id]);
            if (count($docentes) !== count(array_unique($docentes))) {
                $validator->errors()->add('presidente_id', 'No se puede asignar el mismo docente en más de un rol del tribunal.');
            }
        });

        return $validator->validate();
    }

    private function guardarTribunal(MesaExamen $mesa, array $data): void
    {
        $roles = RolTribunal::pluck('id_rol_tribunal', 'nombre_rol');

        foreach (['Presidente' => 'presidente_id', 'Vocal 1' => 'vocal1_id', 'Vocal 2' => 'vocal2_id'] as $rol => $campo) {
            if (! empty($data[$campo]) && $roles->has($rol)) {
                TribunalMesa::create([
                    'id_mesa' => $mesa->id_mesa,
                    'id_profesor' => $data[$campo],
                    'id_rol_tribunal' => $roles[$rol],
                ]);
            }
        }
    }

    public function storeTurno(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_turno' => ['required', 'string', 'max:100', 'unique:turno_examen,nombre_turno'],
            'mes_desde' => ['required', 'integer', 'between:1,12'],
            'mes_hasta' => ['required', 'integer', 'between:1,12'],
        ]);

        TurnoExamen::create($data);

        return back()->with('status', 'Turno de examen agregado correctamente.');
    }

    public function updateTurno(Request $request, TurnoExamen $turno): RedirectResponse
    {
        $data = $request->validate([
            'nombre_turno' => ['required', 'string', 'max:100', 'unique:turno_examen,nombre_turno,' . $turno->id_turno . ',id_turno'],
            'mes_desde' => ['required', 'integer', 'between:1,12'],
            'mes_hasta' => ['required', 'integer', 'between:1,12'],
        ]);

        $turno->update($data);

        return back()->with('status', 'Turno de examen actualizado correctamente.');
    }

    public function destroyTurno(TurnoExamen $turno): RedirectResponse
    {
        if (MesaExamen::where('id_turno', $turno->id_turno)->exists()) {
            return back()->withErrors(['nombre_turno' => 'No se puede eliminar: hay mesas de examen que usan este turno.']);
        }

        $turno->delete();

        return back()->with('status', 'Turno de examen eliminado.');
    }

    public function storeLlamado(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_llamado' => ['required', 'string', 'max:50', 'unique:llamado_examen,nombre_llamado'],
        ]);

        LlamadoExamen::create($data);

        return back()->with('status', 'Llamado agregado correctamente.');
    }

    public function updateLlamado(Request $request, LlamadoExamen $llamado): RedirectResponse
    {
        $data = $request->validate([
            'nombre_llamado' => ['required', 'string', 'max:50', 'unique:llamado_examen,nombre_llamado,' . $llamado->id_llamado . ',id_llamado'],
        ]);

        $llamado->update($data);

        return back()->with('status', 'Llamado actualizado correctamente.');
    }

    public function destroyLlamado(LlamadoExamen $llamado): RedirectResponse
    {
        if (MesaExamen::where('id_llamado', $llamado->id_llamado)->exists()) {
            return back()->withErrors(['nombre_llamado' => 'No se puede eliminar: hay mesas de examen que usan este llamado.']);
        }

        $llamado->delete();

        return back()->with('status', 'Llamado eliminado.');
    }
}
