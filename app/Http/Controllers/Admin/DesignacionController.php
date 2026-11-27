<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria as Designacion;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\TurnoCursada;
use App\Support\CorteActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Designaciones de profesores en materias (etapa 3, tomado de SIGEDOC):
 *  - Materia no práctica: un solo docente, con todas las horas de la materia.
 *  - Materia práctica: varios docentes, repartiendo las horas sin pasarse.
 *  - Tipo (Titular / Interino / Suplente), alta, baja prevista y resolución.
 *  - "Por vencer": la fecha de baja cae en los próximos 30 días.
 * Los horarios por día de cada designación se cargan en la etapa 4.
 */
class DesignacionController extends Controller
{
    public function index(Request $request): View
    {
        $anio = AnioLectivo::find($request->integer('anio')) ?? CorteActivo::actual() ?? AnioLectivo::orderByDesc('anio')->first();
        $estado = $request->query('estado', 'activas');

        $designaciones = Designacion::with('profesor.persona', 'materia.nombreMateria', 'materia.carrera', 'materia.anioCursada', 'horarios', 'turnoCursada')
            ->where('id_anio_lectivo', $anio?->id_anio_lectivo)
            ->when($request->integer('profesor'), fn ($q, $p) => $q->where('id_profesor', $p))
            ->when($request->integer('carrera'), fn ($q, $c) => $q->whereHas('materia', fn ($m) => $m->where('id_carrera', $c)))
            ->orderByDesc('id_asignacion')
            ->get()
            ->filter(fn ($d) => match ($estado) {
                'activas' => in_array($d->estado, ['Activa', 'Por vencer'], true),
                'por_vencer' => $d->estado === 'Por vencer',
                'finalizadas' => in_array($d->estado, ['Finalizada', 'Vencida'], true),
                default => true,
            })
            ->values();

        // Materias que se pueden designar: activas y de planes que no caducaron.
        $materias = Materia::with('nombreMateria', 'carrera.turnos', 'anioCursada', 'plan')
            ->where('activa', true)
            ->orderBy('id_carrera')->orderBy('id_anio_cursada')->orderBy('numero_orden')
            ->get()
            ->filter(fn ($m) => $m->plan?->estado() !== 'Caducado');

        // Horas ya ocupadas por materia y turno en el año (designaciones vigentes).
        $ocupadas = Designacion::vigentes()->where('id_anio_lectivo', $anio?->id_anio_lectivo)
            ->get()->groupBy(fn ($d) => "{$d->id_materia}-{$d->id_turno_cursada}")->map->sum('horas_catedra');

        // Sin docente: cada materia en cada turno en que se dicta su carrera
        // (o práctica que todavía tiene horas sin repartir en ese turno).
        $sinDocente = $materias->flatMap(fn ($m) => $m->carrera->turnos->map(fn ($t) => ['materia' => $m, 'turno' => $t]))
            ->filter(function ($item) use ($ocupadas) {
                $clave = "{$item['materia']->id_materia}-{$item['turno']->id_turno_cursada}";

                return ! $ocupadas->has($clave)
                    || ($item['materia']->es_taller && $item['materia']->horas_catedra && $ocupadas[$clave] < $item['materia']->horas_catedra);
            })->values();

        return view('admin.designaciones.index', [
            'anio' => $anio,
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'estado' => $estado,
            'filtros' => $request->only('profesor', 'carrera'),
            'designaciones' => $designaciones,
            'sinDocente' => $sinDocente,
            'materiasPorCarrera' => $materias->groupBy(fn ($m) => $m->carrera->nombre_carrera),
            'datosMaterias' => $materias->mapWithKeys(fn ($m) => [$m->id_materia => [
                'horas' => $m->horas_catedra, 'practica' => $m->es_taller, 'ocupadas' => (int) ($ocupadas[$m->id_materia] ?? 0),
            ]]),
            'profesores' => Profesor::with('persona')->where('activo', true)->get()->sortBy('apellido'),
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
            // Quién dicta cada materia este año (para el recuadro y las horas libres).
            'vigentes' => Designacion::vigentes()->with('profesor.persona')->where('id_anio_lectivo', $anio?->id_anio_lectivo)->get()
                ->map(fn ($d) => ['id' => $d->id_asignacion, 'id_materia' => $d->id_materia, 'id_turno' => $d->id_turno_cursada,
                    'docente' => "{$d->profesor->apellido}, {$d->profesor->nombre}", 'horas' => $d->horas_catedra, 'tipo' => $d->tipo])->values(),
            'carreras' => Carrera::with('turnos')->orderBy('nombre_carrera')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->guardar($request, new Designacion());
    }

    public function update(Request $request, Designacion $designacion): RedirectResponse
    {
        return $this->guardar($request, $designacion);
    }

    // Termina la designación (queda en el historial como Finalizada).
    public function finalizar(Designacion $designacion): RedirectResponse
    {
        $designacion->update(['activa' => false, 'fecha_baja' => $designacion->fecha_baja && $designacion->fecha_baja->lt(today()) ? $designacion->fecha_baja : today()]);

        return back()->with('status', 'Designación finalizada.');
    }

    public function destroy(Designacion $designacion): RedirectResponse
    {
        DB::transaction(function () use ($designacion) {
            $designacion->horarios()->delete();
            $designacion->delete();
        });

        return back()->with('status', 'Designación eliminada.');
    }

    private function guardar(Request $request, Designacion $designacion): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', Rule::exists('profesor', 'id_profesor')->where('activo', true)],
            'id_materia' => ['required', Rule::exists('materia', 'id_materia')->where('activa', true)],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_turno_cursada' => ['required', 'exists:turno_cursada,id_turno_cursada'],
            'tipo' => ['required', Rule::in(Designacion::TIPOS)],
            'activa' => ['required', 'boolean'],
            'horas_catedra' => ['nullable', 'integer', 'min:1', 'max:30'],
            'fecha_alta' => ['required', 'date'],
            'fecha_baja' => ['nullable', 'date', 'after:fecha_alta'],
            // Solo el número: el año lectivo se agrega solo (123 → "123/2026").
            'resolucion_numero' => ['nullable', 'digits_between:1,6'],
            'aula' => ['nullable', 'string', 'max:20'],
        ], [
            'id_profesor.exists' => 'El profesor no existe o está dado de baja.',
            'fecha_baja.after' => 'La fecha de baja tiene que ser posterior a la de alta.',
            'id_turno_cursada.required' => 'Elegí el turno.',
            'resolucion_numero.digits_between' => 'El número de resolución tiene que tener solo números (hasta 6).',
        ]);

        $materia = Materia::with('nombreMateria', 'carrera')->find($data['id_materia']);

        // 1. La materia tiene que tener sus horas cátedra cargadas (etapa 2).
        if (! $materia->horas_catedra) {
            throw ValidationException::withMessages(['id_materia' => "«{$materia->nombre}» todavía no tiene horas cátedra: cargalas en Carreras → Materias."]);
        }

        // 1b. La carrera de la materia se tiene que dictar en ese turno.
        if (! $materia->carrera->ofreceTurno($data['id_turno_cursada'])) {
            throw ValidationException::withMessages(['id_turno_cursada' => "«{$materia->carrera->nombre_carrera}» no se dicta en ese turno (los turnos se configuran en Carreras → Editar)."]);
        }

        // Las otras designaciones vigentes de esa materia en el año (sin contar esta).
        $otras = Designacion::vigentes()->with('profesor.persona')
            ->where('id_materia', $materia->id_materia)
            ->where('id_anio_lectivo', $data['id_anio_lectivo'])
            ->where('id_turno_cursada', $data['id_turno_cursada'])
            ->when($designacion->exists, fn ($q) => $q->whereKeyNot($designacion->id_asignacion))
            ->get();

        // Una designación inactiva no ocupa horas: no choca con las demás.
        if (! $data['activa']) {
            $otras = collect();
        }

        // 2. El mismo docente no puede estar dos veces en la misma materia.
        if ($otras->contains('id_profesor', (int) $data['id_profesor'])) {
            throw ValidationException::withMessages(['id_profesor' => 'Ese profesor ya tiene una designación en esta materia, en este turno y año.']);
        }

        if ($materia->es_taller) {
            // 3a. Práctica: se reparten las horas sin pasarse de las de la materia.
            $libres = $materia->horas_catedra - $otras->sum('horas_catedra');
            $horas = (int) ($data['horas_catedra'] ?? 0);

            if ($horas < 1 || $horas > $libres) {
                throw ValidationException::withMessages(['horas_catedra' => $libres > 0
                    ? "«{$materia->nombre}» tiene {$materia->horas_catedra} h cátedra y le quedan {$libres} h para repartir: elegí entre 1 y {$libres}."
                    : "«{$materia->nombre}» ya tiene todas sus horas repartidas. Bajale horas a otro docente o aumentá la carga en Materias."]);
            }
        } else {
            // 3b. No práctica: un solo docente, con todas las horas de la materia.
            if ($otro = $otras->first()) {
                throw ValidationException::withMessages(['id_materia' => "«{$materia->nombre}» ya está designada en este turno a {$otro->profesor->apellido}, {$otro->profesor->nombre}. Finalizá esa designación primero, o marcá la materia como práctica para repartir las horas."]);
            }
            $horas = $materia->horas_catedra;
        }

        $designacion->fill([
            'id_profesor' => $data['id_profesor'],
            'id_materia' => $materia->id_materia,
            'id_anio_lectivo' => $data['id_anio_lectivo'],
            'tipo' => $data['tipo'],
            'horas_catedra' => $horas,
            'fecha_alta' => $data['fecha_alta'],
            'fecha_baja' => $data['fecha_baja'] ?? null,
            'resolucion' => ! empty($data['resolucion_numero']) ? $data['resolucion_numero'] . '/' . AnioLectivo::find($data['id_anio_lectivo'])->anio : null,
            'id_turno_cursada' => $data['id_turno_cursada'],
            'aula' => $data['aula'] ?? null,
            'id_anio_cursada' => $materia->id_anio_cursada,
            'fecha_asignacion' => $designacion->fecha_asignacion ?? now(),
            'activa' => (bool) $data['activa'],
        ])->save();

        return redirect()->route('admin.designaciones.index', ['anio' => $data['id_anio_lectivo']])
            ->with('status', $designacion->wasRecentlyCreated ? 'Designación guardada.' : 'Designación actualizada.');
    }
}
