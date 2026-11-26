<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\InscripcionCarrera;
use App\Models\ConfiguracionCuota;
use App\Models\Materia;
use App\Models\TarifaRecursante;
use App\Support\GeneradorCuotas;
use App\Support\RecalculadorCuotas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Cuotas de Recursantes: precio por cantidad de materias, por año lectivo y
 * carrera. Al guardar, se generan y actualizan las cuotas de los recursantes
 * de esa carrera (el sistema cuenta cuántas materias cursa cada uno).
 */
class TarifaRecursanteController extends Controller
{
    public function index(Request $request): View
    {
        $anioActivoId = AnioLectivo::activo()?->id_anio_lectivo;

        // Búsqueda de recursantes: nombre/DNI, carrera y año lectivo de las materias que cursa.
        $filtros = [
            'q' => trim((string) $request->query('q')),
            'carrera' => $request->integer('carrera') ?: null,
            'anio' => $request->integer('anio') ?: $anioActivoId,
        ];

        $recursantes = InscripcionCarrera::with(['personaAlumno', 'carrera'])
            ->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::RECURSANTE))
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->when($filtros['carrera'], fn ($q, $c) => $q->where('id_carrera', $c))
            ->when($filtros['q'], fn ($q, $texto) => $q->whereHas('personaAlumno', fn ($p) => $p
                ->where('nombre', 'like', "%{$texto}%")->orWhere('apellido', 'like', "%{$texto}%")->orWhere('dni', 'like', "%{$texto}%")))
            ->get()
            ->map(function (InscripcionCarrera $inscripcion) use ($filtros) {
                // Materias que está cursando en esa carrera y año, y la cuota que le corresponde.
                $materias = $inscripcion->personaAlumno->historialAlumno()
                    ->with('materia.nombreMateria')
                    ->where('id_anio_lectivo', $filtros['anio'])
                    ->whereHas('materia', fn ($q) => $q->where('id_carrera', $inscripcion->id_carrera))
                    ->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::RECURSANDO))
                    ->get()->pluck('materia');

                return [
                    'alumno' => $inscripcion->personaAlumno,
                    'carrera' => $inscripcion->carrera,
                    'materias' => $materias,
                    'monto' => $filtros['anio'] ? TarifaRecursante::montoPara($inscripcion->personaAlumno, $filtros['anio'], $inscripcion->id_carrera) : null,
                ];
            })
            ->sortBy(fn ($r) => $r['alumno']->apellido)
            ->values();

        return view('director.tarifas-recursantes.index', [
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'anioActivoId' => $anioActivoId,
            'recursantes' => $recursantes,
            'filtros' => $filtros,
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'tarifas' => TarifaRecursante::with('anioLectivo', 'carrera')->get()
                ->sortBy(fn ($t) => [-$t->anioLectivo->anio, $t->carrera->nombre_carrera, $t->cantidad_materias]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->guardar($request, new TarifaRecursante());
    }

    // Lápiz: cambia la cantidad de materias o el monto.
    public function update(Request $request, TarifaRecursante $tarifa): RedirectResponse
    {
        return $this->guardar($request, $tarifa);
    }

    public function destroy(TarifaRecursante $tarifa): RedirectResponse
    {
        $tarifa->delete();
        $this->actualizarCuotas($tarifa->id_anio_lectivo, $tarifa->id_carrera);

        return back()->with('status', 'Se eliminó el precio para ' . $tarifa->cantidad_materias . ' materia(s).');
    }

    // Valida y guarda un precio (nuevo o editado) y actualiza las cuotas de los recursantes.
    private function guardar(Request $request, TarifaRecursante $tarifa): RedirectResponse
    {
        $idCarrera = $request->integer('id_carrera') ?: $tarifa->id_carrera;
        $maxMaterias = max(1, Materia::where('id_carrera', $idCarrera)->where('activa', true)->count());

        $data = $request->validate([
            'id_anio_lectivo' => [$tarifa->exists ? 'sometimes' : 'required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_carrera' => [$tarifa->exists ? 'sometimes' : 'required', 'exists:carrera,id_carrera'],
            'cantidad_materias' => ['required', 'integer', 'min:1', "max:{$maxMaterias}"],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
        ], [
            'cantidad_materias.max' => "La carrera tiene {$maxMaterias} materia(s) activa(s): no puede ser más que eso.",
            'monto.max' => 'El monto no puede superar $1.000.000.',
        ]);

        $tarifa->fill($data + ['id_anio_lectivo' => $tarifa->id_anio_lectivo, 'id_carrera' => $idCarrera]);
        $tarifa->monto = round((float) $data['monto'], 2);

        // Las demás filas de la misma carrera y año (sin contar la que se edita).
        $otras = TarifaRecursante::where('id_anio_lectivo', $tarifa->id_anio_lectivo)->where('id_carrera', $tarifa->id_carrera)
            ->whereKeyNot($tarifa->id_tarifa_recursante)->get();

        // Validación 1: una sola fila por cantidad de materias.
        if ($otras->contains('cantidad_materias', $tarifa->cantidad_materias)) {
            throw ValidationException::withMessages(['cantidad_materias' => "Ya hay un precio cargado para {$tarifa->cantidad_materias} materia(s). Editalo con el lápiz."]);
        }

        // Validación 2: más materias, más caro (y menos materias, más barato).
        $menor = $otras->where('cantidad_materias', '<', $tarifa->cantidad_materias)->max('monto');
        $mayor = $otras->where('cantidad_materias', '>', $tarifa->cantidad_materias)->min('monto');
        if (($menor !== null && $tarifa->monto <= (float) $menor) || ($mayor !== null && $tarifa->monto >= (float) $mayor)) {
            throw ValidationException::withMessages(['monto' => 'El monto tiene que ser mayor al de menos materias'
                . ($menor !== null ? ' ($' . number_format((float) $menor, 0, ',', '.') . ')' : '')
                . ' y menor al de más materias' . ($mayor !== null ? ' ($' . number_format((float) $mayor, 0, ',', '.') . ')' : '') . '.']);
        }

        $tarifa->save();
        $mensaje = "Se guardó el precio para {$tarifa->cantidad_materias} materia(s): $" . number_format($tarifa->monto, 0, ',', '.') . '.';

        return back()->with('status', $mensaje . $this->actualizarCuotas($tarifa->id_anio_lectivo, $tarifa->id_carrera));
    }

    // Genera las cuotas que falten y pone al día las impagas de los recursantes de esa carrera.
    private function actualizarCuotas(int $idAnio, int $idCarrera): string
    {
        $configuracion = ConfiguracionCuota::de($idAnio, $idCarrera);

        if (! $configuracion) {
            return ' Para que se generen las cuotas, cargá también la Configuración de Cuotas de esa carrera y año (período y vencimiento).';
        }

        $creadas = GeneradorCuotas::generar($configuracion);
        $actualizadas = RecalculadorCuotas::aplicar($configuracion);

        return " Cuotas nuevas: {$creadas}. Cuotas impagas actualizadas: {$actualizadas}.";
    }
}
