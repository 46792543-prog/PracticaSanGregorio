<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\HorarioCelda;
use App\Models\HorarioModulo;
use App\Models\TurnoCursada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $modulos = HorarioModulo::with('celdas')
            ->where('id_carrera', $idCarrera)
            ->where('id_turno_cursada', $idTurno)
            ->orderBy('hora_inicio')
            ->orderBy('modulo')
            ->get()
            ->map(function (HorarioModulo $modulo) {
                $modulo->celdasPorDia = $modulo->celdas->keyBy('dia_semana');

                return $modulo;
            });

        return view('admin.horarios.index', [
            'carreras' => $carreras,
            'turnos' => $turnos,
            'idCarrera' => $idCarrera,
            'idTurno' => $idTurno,
            'modulos' => $modulos,
            'dias' => self::DIAS,
        ]);
    }

    public function storeModulo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_turno_cursada' => ['required', 'exists:turno_cursada,id_turno_cursada'],
            'modulo' => [
                'required', 'integer', 'min:1', 'max:20',
                Rule::unique('horario_modulo', 'modulo')->where(fn ($q) => $q
                    ->where('id_carrera', $request->id_carrera)
                    ->where('id_turno_cursada', $request->id_turno_cursada)),
            ],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'modulo.unique' => 'Ya existe un módulo con ese número para esta carrera y turno.',
        ]);

        HorarioModulo::create($data);

        return redirect()->route('admin.horarios.index', ['id_carrera' => $data['id_carrera'], 'id_turno_cursada' => $data['id_turno_cursada']])
            ->with('status', 'Se agregó el módulo correctamente.');
    }

    public function updateModulo(Request $request, HorarioModulo $horarioModulo): RedirectResponse
    {
        $data = $request->validate([
            'modulo' => [
                'required', 'integer', 'min:1', 'max:20',
                Rule::unique('horario_modulo', 'modulo')
                    ->where(fn ($q) => $q
                        ->where('id_carrera', $horarioModulo->id_carrera)
                        ->where('id_turno_cursada', $horarioModulo->id_turno_cursada))
                    ->ignore($horarioModulo->id_horario_modulo, 'id_horario_modulo'),
            ],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'modulo.unique' => 'Ya existe un módulo con ese número para esta carrera y turno.',
        ]);

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
        ]);

        HorarioCelda::updateOrCreate(
            ['id_horario_modulo' => $horarioModulo->id_horario_modulo, 'dia_semana' => $data['dia_semana']],
            ['contenido' => $data['contenido'] ?: null]
        );

        return redirect()->route('admin.horarios.index', ['id_carrera' => $horarioModulo->id_carrera, 'id_turno_cursada' => $horarioModulo->id_turno_cursada])
            ->with('status', 'Se guardó el contenido correctamente.');
    }
}
