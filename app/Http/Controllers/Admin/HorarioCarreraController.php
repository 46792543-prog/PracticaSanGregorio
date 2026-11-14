<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\HorarioCelda;
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
    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

    public function index(Request $request): View
    {
        $carreras = Carrera::orderBy('nombre_carrera')->get();
        $turnos = TurnoCursada::orderBy('id_turno_cursada')->get();

        $idCarrera = (int) ($request->query('id_carrera') ?: $carreras->first()?->id_carrera);
        $idTurno = (int) ($request->query('id_turno_cursada') ?: $turnos->first()?->id_turno_cursada);

        $modulos = $this->modulosDelCuadro($idCarrera, $idTurno);

        return view('admin.horarios.index', [
            'carreras' => $carreras,
            'turnos' => $turnos,
            'idCarrera' => $idCarrera,
            'idTurno' => $idTurno,
            'modulos' => $modulos,
            'dias' => self::DIAS,
            'asignaciones' => $this->asignacionesDisponibles($idCarrera),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $carrera = Carrera::findOrFail($request->query('id_carrera'));
        $turno = TurnoCursada::findOrFail($request->query('id_turno_cursada'));

        $pdf = Pdf::loadView('admin.horarios.pdf', [
            'carrera' => $carrera,
            'turno' => $turno,
            'modulos' => $this->modulosDelCuadro($carrera->id_carrera, $turno->id_turno_cursada),
            'dias' => self::DIAS,
        ])->setPaper('a4', 'landscape');

        $archivo = Str::slug("horario {$carrera->nombre_carrera} {$turno->nombre_turno}");

        return $pdf->download("{$archivo}.pdf");
    }

    public function storeModulo(Request $request): RedirectResponse
    {
        $cuadro = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_turno_cursada' => ['required', 'exists:turno_cursada,id_turno_cursada'],
        ], [
            'id_carrera.required' => 'Seleccioná una carrera.',
            'id_carrera.exists' => 'La carrera seleccionada no existe.',
            'id_turno_cursada.required' => 'Seleccioná un turno.',
            'id_turno_cursada.exists' => 'El turno seleccionado no existe.',
        ]);

        $data = $this->validarModulo($request, (int) $cuadro['id_carrera'], (int) $cuadro['id_turno_cursada']);

        HorarioModulo::create($cuadro + $data);

        return redirect()->route('admin.horarios.index', $cuadro)
            ->with('status', 'Se agregó el módulo correctamente.');
    }

    public function updateModulo(Request $request, HorarioModulo $horarioModulo): RedirectResponse
    {
        $data = $this->validarModulo($request, $horarioModulo->id_carrera, $horarioModulo->id_turno_cursada, $horarioModulo);

        $horarioModulo->update($data);

        return redirect()->route('admin.horarios.index', ['id_carrera' => $horarioModulo->id_carrera, 'id_turno_cursada' => $horarioModulo->id_turno_cursada])
            ->with('status', 'Se actualizó el módulo correctamente.');
    }

    public function destroyModulo(HorarioModulo $horarioModulo): RedirectResponse
    {
        $idCarrera = $horarioModulo->id_carrera;
        $idTurno = $horarioModulo->id_turno_cursada;

        $horarioModulo->delete();

        return redirect()->route('admin.horarios.index', ['id_carrera' => $idCarrera, 'id_turno_cursada' => $idTurno])
            ->with('status', 'Se eliminó el módulo correctamente.');
    }

    public function storeCelda(Request $request, HorarioModulo $horarioModulo): RedirectResponse
    {
        $data = $request->validate([
            'dia_semana' => ['required', Rule::in(self::DIAS)],
            'contenido' => ['nullable', 'string', 'max:150'],
        ], [
            'dia_semana.required' => 'Seleccioná un día de la semana.',
            'dia_semana.in' => 'El día seleccionado no es válido.',
            'contenido.max' => 'El contenido no puede superar los 150 caracteres.',
        ]);

        HorarioCelda::updateOrCreate(
            ['id_horario_modulo' => $horarioModulo->id_horario_modulo, 'dia_semana' => $data['dia_semana']],
            ['contenido' => $data['contenido'] ?: null]
        );

        return redirect()->route('admin.horarios.index', ['id_carrera' => $horarioModulo->id_carrera, 'id_turno_cursada' => $horarioModulo->id_turno_cursada])
            ->with('status', 'Se guardó el contenido correctamente.');
    }

    /**
     * "Materia — Apellido, Nombre" de cada profesor ya asignado a una materia
     * de esta carrera en el año lectivo activo, para elegir en el modal de
     * carga en vez de escribirlo a mano (evita typos/inconsistencias con lo
     * ya cargado en "Asignación de Materias y Horarios"). No se filtra por
     * turno: esa pantalla hoy no pide el turno al asignar un profesor, así
     * que la mayoría de las asignaciones existentes no lo tienen guardado.
     */
    private function asignacionesDisponibles(int $idCarrera): Collection
    {
        $anioLectivo = CorteActivo::actual();

        return AsignacionProfesorMateria::with(['materia.nombreMateria', 'profesor.persona'])
            ->whereHas('materia', fn ($q) => $q->where('id_carrera', $idCarrera))
            ->when($anioLectivo, fn ($q) => $q->where('id_anio_lectivo', $anioLectivo->id_anio_lectivo))
            ->get()
            ->map(fn (AsignacionProfesorMateria $a) => mb_strtoupper("{$a->materia->nombre} — {$a->profesor->apellido}, {$a->profesor->nombre}"))
            ->unique()
            ->sort()
            ->values();
    }

    private function modulosDelCuadro(int $idCarrera, int $idTurno): Collection
    {
        return HorarioModulo::with('celdas')
            ->where('id_carrera', $idCarrera)
            ->where('id_turno_cursada', $idTurno)
            ->orderBy('hora_inicio')
            ->orderBy('modulo')
            ->get()
            ->map(function (HorarioModulo $modulo) {
                $modulo->celdasPorDia = $modulo->celdas->keyBy('dia_semana');

                return $modulo;
            });
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
