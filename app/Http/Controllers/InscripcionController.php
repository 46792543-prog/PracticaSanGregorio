<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InscripcionController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user();

        $inscripciones = $alumno->inscripcionesMesa()
            ->with('mesaExamen.materia')
            ->orderByDesc('fecha_inscripcion')
            ->get();

        $seleccionadaId = $request->query('ver', $inscripciones->firstWhere('estado', 'en_proceso')?->id ?? $inscripciones->first()?->id);
        $seleccionada = $inscripciones->firstWhere('id', (int) $seleccionadaId);

        return view('inscripciones.index', [
            'inscripciones' => $inscripciones,
            'seleccionada' => $seleccionada,
        ]);
    }
}
