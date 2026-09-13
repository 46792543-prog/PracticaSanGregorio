<?php

namespace App\Http\Controllers;

use App\Models\AnioLectivo;
use App\Models\CondicionAlumno;
use App\Models\HistorialAlumno;
use App\Models\Materia;
use App\Models\PeriodoInscripcionCursada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CursadaController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::user()->persona;
        $anioLectivo = $this->anioLectivoActivo();

        $inscripcionCarrera = $alumno->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->first();

        $periodosAbiertos = $anioLectivo
            ? PeriodoInscripcionCursada::where('id_anio_lectivo', $anioLectivo->id_anio_lectivo)
                ->where('abierto', true)
                ->pluck('id_periodo')
            : collect();

        $materias = collect();

        if ($inscripcionCarrera && $periodosAbiertos->isNotEmpty()) {
            $materias = Materia::with('nombreMateria', 'periodo', 'requisitos')
                ->where('id_carrera', $inscripcionCarrera->id_carrera)
                ->where('activa', true)
                ->whereIn('id_periodo', $periodosAbiertos)
                ->orderBy('numero_orden')
                ->get()
                ->reject(function (Materia $materia) use ($alumno, $anioLectivo) {
                    $historial = $materia->historialDe($alumno);

                    return $historial && $historial->id_anio_lectivo === $anioLectivo->id_anio_lectivo
                        && in_array($historial->condicion?->nombre_condicion, ['Cursando', 'Regular', 'Aprobada'], true);
                })
                ->map(function (Materia $materia) use ($alumno) {
                    $materia->bloqueo = $materia->correlativaFaltanteParaCursar($alumno);

                    return $materia;
                });
        }

        return view('cursada.index', [
            'materias' => $materias,
            'anioLectivo' => $anioLectivo,
        ]);
    }

    public function inscribir(Request $request, Materia $materia): RedirectResponse
    {
        $alumno = Auth::user()->persona;
        $anioLectivo = $this->anioLectivoActivo();

        abort_unless($anioLectivo, 422, 'No hay un año lectivo activo.');

        $periodoAbierto = PeriodoInscripcionCursada::where('id_anio_lectivo', $anioLectivo->id_anio_lectivo)
            ->where('id_periodo', $materia->id_periodo)
            ->where('abierto', true)
            ->exists();

        if (! $periodoAbierto) {
            return back()->withErrors(['materia' => 'La inscripción a cursada de este período no está habilitada.']);
        }

        if ($materia->correlativaFaltanteParaCursar($alumno)) {
            return back()->withErrors(['materia' => 'No cumplís las correlativas necesarias para cursar esta materia.']);
        }

        $condicionCursando = CondicionAlumno::where('nombre_condicion', 'Cursando')->firstOrFail();

        HistorialAlumno::firstOrCreate(
            [
                'id_persona_alumno' => $alumno->id_persona,
                'id_materia' => $materia->id_materia,
                'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
            ],
            [
                'id_condicion' => $condicionCursando->id_condicion,
                'fecha_ultima_modificacion' => now(),
            ]
        );

        return redirect()->route('cursada.index')
            ->with('status', 'Tu inscripción a cursar ' . $materia->nombre . ' quedó registrada.');
    }

    private function anioLectivoActivo(): ?AnioLectivo
    {
        return AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderByDesc('anio')
            ->first();
    }
}
