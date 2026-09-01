<?php

namespace App\Http\Controllers;

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
}
