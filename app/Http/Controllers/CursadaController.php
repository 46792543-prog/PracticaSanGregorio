<?php

namespace App\Http\Controllers;

use App\Models\AnioLectivo;
use App\Models\CondicionAlumno;
use App\Models\HistorialAlumno;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CursadaController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::user()->persona;
        $inscripcionCarrera = $alumno->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();

        $materias = collect();

        if ($inscripcionCarrera) {
            // Un alumno solo ve las materias de SU año del plan, y las de
            // 2do cuatrimestre recién aparecen a mitad de año — antes de eso
            // ni siquiera se muestran como opción.
            $segundoCuatrimestreVisible = now()->month >= 8;

            $materias = $inscripcionCarrera->carrera->materias()
                ->where('id_anio_cursada', $inscripcionCarrera->id_anio_cursada)
                ->where('activa', true)
                ->whereDoesntHave('historial', fn ($q) => $q->where('id_persona_alumno', $alumno->id_persona))
                ->whereHas('periodo', fn ($q) => $q->where('nombre_periodo', '!=', '2do Cuatrimestre')
                    ->when($segundoCuatrimestreVisible, fn ($qq) => $qq->orWhere('nombre_periodo', '2do Cuatrimestre')))
                ->with(['nombreMateria', 'periodo', 'requisitos'])
                ->get()
                ->sortBy('nombre')
                ->map(fn (Materia $materia) => [
                    'materia' => $materia,
                    'bloqueo' => $materia->bloqueoParaCursar($alumno),
                ]);
        }

        return view('cursada.index', [
            'inscripcionCarrera' => $inscripcionCarrera,
            'materias' => $materias,
        ]);
    }

    public function store(Request $request, Materia $materia): RedirectResponse
    {
        $alumno = Auth::user()->persona;
        $inscripcionCarrera = $alumno->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();

        abort_unless($inscripcionCarrera && $materia->id_carrera === $inscripcionCarrera->id_carrera, 403);
        abort_unless($materia->id_anio_cursada === $inscripcionCarrera->id_anio_cursada, 403);

        $yaTieneHistorial = $alumno->historialAlumno()->where('id_materia', $materia->id_materia)->exists();
        abort_if($yaTieneHistorial, 409, 'Ya estás inscripto a esta materia.');

        if ($bloqueo = $materia->bloqueoParaCursar($alumno)) {
            return back()->withErrors(['materia' => $bloqueo]);
        }

        $anioLectivoActivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))->first();
        abort_unless($anioLectivoActivo, 500, 'No hay un año lectivo activo configurado.');

        HistorialAlumno::create([
            'id_persona_alumno' => $alumno->id_persona,
            'id_materia' => $materia->id_materia,
            'id_anio_lectivo' => $anioLectivoActivo->id_anio_lectivo,
            'id_condicion' => CondicionAlumno::where('nombre_condicion', 'Cursando')->value('id_condicion'),
            'fecha_ultima_modificacion' => now(),
        ]);

        return back()->with('status', "Te inscribiste a {$materia->nombre} correctamente.");
    }
}
