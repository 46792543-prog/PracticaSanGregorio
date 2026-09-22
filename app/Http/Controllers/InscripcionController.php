<?php

namespace App\Http\Controllers;

use App\Models\InscripcionMesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InscripcionController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user()->persona;

        $inscripciones = $alumno->inscripcionesMesa()
            ->with('mesaExamen.materia.nombreMateria', 'mesaExamen.turnoExamen', 'mesaExamen.llamadoExamen', 'estadoInscripcion')
            ->orderByDesc('fecha_inscripcion')
            ->get();

        $seleccionadaId = $request->query(
            'ver',
            $inscripciones->first(fn ($i) => $i->estadoInscripcion->nombre_estado === 'En proceso')?->id_inscripcion
                ?? $inscripciones->first()?->id_inscripcion
        );
        $seleccionada = $inscripciones->firstWhere('id_inscripcion', (int) $seleccionadaId);

        return view('inscripciones.index', [
            'inscripciones' => $inscripciones,
            'seleccionada' => $seleccionada,
        ]);
    }

    public function cancelar(InscripcionMesa $inscripcion): RedirectResponse
    {
        $alumno = Auth::user()->persona;

        abort_unless($inscripcion->id_persona_alumno === $alumno->id_persona, 403);

        // Solo se puede cancelar mientras está "En proceso" — una vez que
        // secretaría la aceptó o rechazó, ya no depende del alumno.
        abort_unless($inscripcion->estadoInscripcion->nombre_estado === 'En proceso', 403, 'Esta inscripción ya no se puede cancelar.');

        $inscripcion->delete();

        return redirect()->route('inscripciones.index')->with('status', 'Cancelaste tu inscripción correctamente.');
    }
}
