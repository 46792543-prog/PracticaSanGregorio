<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria;
use App\Models\HorarioAsignacion;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\HorarioModulo;
use App\Models\TurnoCursada;
use App\Support\CorteActivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HorarioCarreraController extends Controller
{
    // Se cursa de lunes a viernes.
    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

    /**
     * Horarios: arriba el formulario "Franja horaria" para cargar clases, y
     * abajo el "Horario de cursada" para verlas por docente o por curso
     * (carrera · año · turno), en grilla semanal o en tabla.
     */
    public function index(Request $request): View
    {
        return view('admin.horarios.index', $this->datosHorario($request));
    }

    // PDF de la semana de un docente (?profesor=) o de un curso (?carrera=&anio=&turno=).
    public function pdf(Request $request): Response
    {
        $datos = $this->datosHorario($request);
        $franjas = collect($datos['franjas'])->filter(fn ($f) => $request->filled('profesor')
            ? $f['id_profesor'] === $request->integer('profesor')
            : $f['id_carrera'] === $request->integer('carrera') && $f['id_anio_cursada'] === $request->integer('anio') && $f['id_turno'] === $request->integer('turno'))
            ->values();
        abort_if($franjas->isEmpty(), 404, 'No hay franjas para descargar.');

        $titulo = $request->filled('profesor')
            ? 'Horario de ' . $franjas->first()['docente']
            : 'Horario de ' . $franjas->first()['carrera'] . ' · ' . $franjas->first()['anio_cursada'] . ' · Turno ' . $franjas->first()['turno'];

        // Filas: cada rango horario distinto; columnas: los días con clases (lunes a viernes siempre).
        $rangos = $franjas->map(fn ($f) => "{$f['desde']}-{$f['hasta']}")->unique()->sort()->values();
        $dias = collect(self::DIAS)->filter(fn ($d, $i) => $i < 5 || $franjas->contains('dia', $d))->values();

        $pdf = Pdf::loadView('admin.horarios.pdf', compact('franjas', 'titulo', 'rangos', 'dias') + ['anioLectivo' => $datos['anioLectivoActual']])
            ->setPaper('a4', 'landscape');

        return $pdf->download(Str::slug($titulo) . '.pdf');
    }

    // Celda de la grilla: una designación en un módulo y día.
    public function storeClase(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_asignacion' => ['required', 'exists:asignacion_profesor_materia,id_asignacion'],
            'id_horario_modulo' => ['required', 'exists:horario_modulo,id_horario_modulo'],
            'dia_semana' => ['required', Rule::in(self::DIAS)],
        ], ['id_asignacion.required' => 'Elegí la materia y el docente.']);

        $designacion = AsignacionProfesorMateria::with('materia.nombreMateria', 'profesor.persona')->find($data['id_asignacion']);
        $modulo = HorarioModulo::find($data['id_horario_modulo']);

        if ($designacion->materia->id_carrera !== $modulo->id_carrera || (int) $designacion->id_turno_cursada !== (int) $modulo->id_turno_cursada) {
            throw ValidationException::withMessages(['id_asignacion' => 'Esa designación no es de esta carrera y turno.']);
        }

        return $this->guardarClase($designacion, $data['dia_semana'], $modulo->hora_inicio->format('H:i'), $modulo->hora_fin->format('H:i'), $modulo->id_horario_modulo);
    }

    // Formulario "Franja horaria": docente + materia + turno + día + desde/hasta.
    public function storeFranja(Request $request): RedirectResponse
    {
        return $this->procesarFranja($request, null);
    }

    // Editar una franja ya cargada (lápiz en el Horario de cursada).
    public function updateFranja(Request $request, HorarioAsignacion $horario): RedirectResponse
    {
        return $this->procesarFranja($request, $horario);
    }

    private function procesarFranja(Request $request, ?HorarioAsignacion $actual): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'id_materia' => ['required', 'exists:materia,id_materia'],
            'id_turno_cursada' => ['required', 'exists:turno_cursada,id_turno_cursada'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'dia_semana' => ['required', Rule::in(self::DIAS)],
            'hora_desde' => ['required', 'date_format:H:i'],
            'hora_hasta' => ['required', 'date_format:H:i', 'after:hora_desde'],
        ], [
            'id_profesor.required' => 'Elegí el docente.',
            'id_materia.required' => 'Elegí la materia.',
            'id_turno_cursada.required' => 'Elegí el turno.',
            'dia_semana.required' => 'Elegí el día.',
            'dia_semana.in' => 'Elegí un día de lunes a viernes.',
            'hora_hasta.after' => 'La hora "hasta" tiene que ser después de "desde".',
        ]);

        // El docente tiene que tener una designación vigente en esa materia y turno.
        $designacion = AsignacionProfesorMateria::vigentes()->with('materia.nombreMateria', 'profesor.persona')
            ->where('id_profesor', $data['id_profesor'])->where('id_materia', $data['id_materia'])
            ->where('id_turno_cursada', $data['id_turno_cursada'])->where('id_anio_lectivo', $data['id_anio_lectivo'])
            ->first();

        if (! $designacion) {
            throw ValidationException::withMessages(['id_materia' => 'Ese docente no tiene una designación vigente en esa materia y turno (cargala en Designaciones).']);
        }

        // Si coincide justo con un módulo de la grilla, queda enganchada a él.
        $modulo = HorarioModulo::where('id_carrera', $designacion->materia->id_carrera)->where('id_turno_cursada', $designacion->id_turno_cursada)
            ->where('hora_inicio', $data['hora_desde'])->where('hora_fin', $data['hora_hasta'])->first();

        $this->guardarClase($designacion, $data['dia_semana'], $data['hora_desde'], $data['hora_hasta'], $modulo?->id_horario_modulo, $actual);

        // Vuelve a la grilla de esa carrera, turno y año para ver la franja cargada.
        return redirect()->route('admin.horarios.index', [
            'id_carrera' => $designacion->materia->id_carrera, 'id_turno_cursada' => $designacion->id_turno_cursada,
            'id_anio_cursada' => $designacion->materia->id_anio_cursada, 'id_anio_lectivo' => $designacion->id_anio_lectivo,
        ])->with('status', ($actual ? 'Se actualizó la franja: ' : 'Se cargó ') . "{$designacion->materia->nombre} el {$data['dia_semana']} de {$data['hora_desde']} a {$data['hora_hasta']}.");
    }

    /**
     * Valida y guarda una clase. Reglas:
     *  1. La designación tiene que estar vigente.
     *  2. La duración tiene que ser múltiplo de 40 min (horas cátedra enteras).
     *  3. El docente no puede tener otra clase a esa hora (en cualquier carrera).
     *  4. Ese año y turno de la carrera no puede tener otra materia a esa hora
     *     (en una práctica sí pueden estar varios docentes a la vez).
     *  5. Sin pasarse de las horas cátedra de la designación.
     * Las clases pegadas (una termina 19:20 y otra empieza 19:20) no chocan.
     */
    private function guardarClase(AsignacionProfesorMateria $designacion, string $dia, string $desde, string $hasta, ?int $idModulo, ?HorarioAsignacion $actual = null): RedirectResponse
    {
        $fallar = fn (string $m) => throw ValidationException::withMessages(['id_asignacion' => $m]);
        $aMinutos = fn (string $h) => (int) substr($h, 0, 2) * 60 + (int) substr($h, 3, 2);
        $minutos = $aMinutos($hasta) - $aMinutos($desde);
        $hc = Materia::MINUTOS_HORA_CATEDRA;

        if (! in_array($designacion->estado, ['Activa', 'Por vencer'], true)) {
            $fallar('Esa designación no está vigente.');
        }
        if ($minutos <= 0 || $minutos % $hc !== 0) {
            $fallar("La franja dura {$minutos} min: tiene que ser múltiplo de {$hc} min (horas cátedra enteras: 40, 80, 120…).");
        }

        $seSuperponen = fn ($q) => $q->where('dia_semana', $dia)->where('hora_desde', '<', $hasta)->where('hora_fin', '>', $desde)
            ->when($actual, fn ($w) => $w->whereKeyNot($actual->id_horario));

        $choqueDocente = HorarioAsignacion::with('asignacion.materia.nombreMateria')->tap($seSuperponen)
            ->whereHas('asignacion', fn ($q) => $q->vigentes()->where('id_profesor', $designacion->id_profesor)
                ->where('id_anio_lectivo', $designacion->id_anio_lectivo))
            ->first();
        if ($choqueDocente) {
            $fallar("{$designacion->profesor->apellido}, {$designacion->profesor->nombre} ya da «{$choqueDocente->asignacion->materia->nombre}» el {$dia} de "
                . substr($choqueDocente->hora_desde, 0, 5) . ' a ' . substr($choqueDocente->hora_fin, 0, 5) . '.');
        }

        $choqueCurso = HorarioAsignacion::with('asignacion.materia.nombreMateria')->tap($seSuperponen)
            ->whereHas('asignacion', fn ($q) => $q->vigentes()
                ->where('id_anio_lectivo', $designacion->id_anio_lectivo)
                ->where('id_turno_cursada', $designacion->id_turno_cursada)
                ->whereKeyNot($designacion->id_asignacion)
                ->whereHas('materia', fn ($m) => $m->where('id_carrera', $designacion->materia->id_carrera)
                    ->where('id_anio_cursada', $designacion->materia->id_anio_cursada)))
            ->get()
            ->first(fn ($h) => ! ($designacion->materia->es_taller && $h->asignacion->id_materia === $designacion->id_materia));
        if ($choqueCurso) {
            $fallar("El {$dia} de " . substr($choqueCurso->hora_desde, 0, 5) . ' a ' . substr($choqueCurso->hora_fin, 0, 5)
                . " ese año ya tiene «{$choqueCurso->asignacion->materia->nombre}».");
        }

        $cargados = $designacion->horarios()->when($actual, fn ($q) => $q->whereKeyNot($actual->id_horario))->get()->sum('minutos');
        $tope = (int) $designacion->horas_catedra * $hc;
        if ($cargados + $minutos > $tope) {
            $fallar("«{$designacion->materia->nombre}» tiene {$designacion->horas_catedra} h cátedra ({$tope} min por semana) y ya tiene {$cargados} min cargados: "
                . ($tope - $cargados > 0 ? 'le quedan ' . ($tope - $cargados) . ' min.' : 'ya está completa.'));
        }

        ($actual ?? new HorarioAsignacion())->fill([
            'id_asignacion' => $designacion->id_asignacion,
            'id_horario_modulo' => $idModulo,
            'dia_semana' => $dia,
            'hora_desde' => $desde,
            'hora_fin' => $hasta,
        ])->save();

        return back()->with('status', "Se cargó {$designacion->materia->nombre} el {$dia} de {$desde} a {$hasta}.");
    }

    public function destroyClase(HorarioAsignacion $horario): RedirectResponse
    {
        $horario->delete();

        return back()->with('status', 'Se quitó la clase del horario.');
    }

    // Todo lo que necesitan la pantalla y el PDF: datos del formulario y todas las franjas del año.
    private function datosHorario(Request $request): array
    {
        $aniosLectivos = AnioLectivo::orderByDesc('anio')->get();
        $anioLectivo = $aniosLectivos->firstWhere('id_anio_lectivo', $request->integer('id_anio_lectivo')) ?? CorteActivo::actual() ?? $aniosLectivos->first();

        $vigentes = AsignacionProfesorMateria::vigentes()
            ->with('materia.nombreMateria', 'materia.carrera', 'materia.anioCursada', 'profesor.persona', 'horarios', 'turnoCursada')
            ->where('id_anio_lectivo', $anioLectivo?->id_anio_lectivo)->get();

        // Formulario "Franja horaria": designaciones y clases para elegir y revisar superposiciones en vivo.
        $franjaDesignaciones = $vigentes->map(fn ($d) => [
            'id' => $d->id_asignacion, 'id_profesor' => $d->id_profesor, 'docente' => "{$d->profesor->apellido}, {$d->profesor->nombre}",
            'id_materia' => $d->id_materia, 'materia' => $d->materia->nombre, 'id_carrera' => $d->materia->id_carrera,
            'carrera' => $d->materia->carrera->nombre_carrera, 'id_anio_cursada' => $d->materia->id_anio_cursada,
            'anio_cursada' => $d->materia->anioCursada?->nombre_anio, 'id_turno' => $d->id_turno_cursada, 'turno' => $d->turnoCursada?->nombre_turno,
            'practica' => (bool) $d->materia->es_taller, 'horas' => (int) $d->horas_catedra,
            'tope' => (int) $d->horas_catedra * Materia::MINUTOS_HORA_CATEDRA, 'cargados' => $d->horarios->sum('minutos'),
        ])->values();

        // Todas las franjas del año, con todo lo que necesita el "Horario de cursada".
        $franjas = $vigentes->flatMap(fn ($d) => $d->horarios->map(fn ($h) => [
            'id' => $h->id_horario, 'id_asignacion' => $d->id_asignacion, 'dia' => $h->dia_semana,
            'desde' => substr($h->hora_desde, 0, 5), 'hasta' => substr($h->hora_fin, 0, 5),
            'id_profesor' => $d->id_profesor, 'docente' => "{$d->profesor->apellido}, {$d->profesor->nombre}", 'apellido' => $d->profesor->apellido,
            'id_materia' => $d->id_materia, 'materia' => $d->materia->nombre, 'id_carrera' => $d->materia->id_carrera,
            'carrera' => $d->materia->carrera->nombre_carrera, 'id_anio_cursada' => $d->materia->id_anio_cursada,
            'anio_cursada' => $d->materia->anioCursada?->nombre_anio, 'id_turno' => $d->id_turno_cursada, 'turno' => $d->turnoCursada?->nombre_turno,
        ]))->sortBy(fn ($f) => array_search($f['dia'], self::DIAS) . $f['desde'])->values();

        return [
            'aniosLectivos' => $aniosLectivos, 'anioLectivoActual' => $anioLectivo,
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'turnosTodos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
            'todosLosDias' => self::DIAS,
            'franjaDesignaciones' => $franjaDesignaciones,
            'franjaClases' => $franjas->map(fn ($f) => ['id' => $f['id'], 'id_asignacion' => $f['id_asignacion'], 'dia' => $f['dia'], 'desde' => $f['desde'], 'hasta' => $f['hasta']])->values(),
            'franjas' => $franjas,
        ];
    }

    /**
     * Valida número y horas de un módulo. $actual es el módulo en edición, que
     * se excluye de los chequeos de número repetido y de superposición.
     */
    private function validarModulo(Request $request, int $idCarrera, int $idTurno, ?HorarioModulo $actual = null): array
    {
        $numeroUnico = Rule::unique('horario_modulo', 'modulo')->where(fn ($q) => $q
            ->where('id_carrera', $idCarrera)
            ->where('id_turno_cursada', $idTurno));

        if ($actual) {
            $numeroUnico->ignore($actual->id_horario_modulo, 'id_horario_modulo');
        }

        $data = $request->validate([
            'modulo' => ['required', 'integer', 'min:1', 'max:20', $numeroUnico],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'modulo.required' => 'Ingresá el número de módulo.',
            'modulo.integer' => 'El número de módulo debe ser un número entero.',
            'modulo.min' => 'El número de módulo debe ser como mínimo 1.',
            'modulo.max' => 'El número de módulo no puede ser mayor a 20.',
            'modulo.unique' => 'Ya existe un módulo con ese número para esta carrera y turno.',
            'hora_inicio.required' => 'Ingresá la hora de inicio.',
            'hora_inicio.date_format' => 'La hora de inicio no tiene un formato válido (HH:MM).',
            'hora_fin.required' => 'Ingresá la hora de fin.',
            'hora_fin.date_format' => 'La hora de fin no tiene un formato válido (HH:MM).',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        // Dos módulos pueden ser contiguos (uno termina 09:00 y el otro empieza 09:00).
        $superpuesto = HorarioModulo::where('id_carrera', $idCarrera)
            ->where('id_turno_cursada', $idTurno)
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id_horario_modulo))
            ->where('hora_inicio', '<', $data['hora_fin'])
            ->where('hora_fin', '>', $data['hora_inicio'])
            ->orderBy('hora_inicio')
            ->first();

        if ($superpuesto) {
            throw ValidationException::withMessages([
                'hora_inicio' => "El horario se superpone con el módulo {$superpuesto->modulo}° ({$superpuesto->hora_inicio->format('H:i')} a {$superpuesto->hora_fin->format('H:i')}).",
            ]);
        }

        return $data;
    }
}
