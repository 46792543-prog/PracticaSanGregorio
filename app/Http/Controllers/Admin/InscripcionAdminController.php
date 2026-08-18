<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstadoInscripcion;
use App\Models\InscripcionMesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InscripcionAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->query('estado', 'En proceso');

        $inscripciones = InscripcionMesa::with('personaAlumno', 'mesaExamen.materia.carrera', 'mesaExamen.materia.nombreMateria', 'estadoInscripcion')
            ->when($filtro !== 'todas', fn ($q) => $q->whereHas('estadoInscripcion', fn ($e) => $e->where('nombre_estado', $filtro)))
            ->orderByDesc('fecha_inscripcion')
            ->get();

        return view('admin.inscripciones.index', [
            'inscripciones' => $inscripciones,
            'filtro' => $filtro,
        ]);
    }

    public function actualizar(Request $request, InscripcionMesa $inscripcionMesa): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['Aceptado', 'Rechazado'])],
            'nota_examen' => ['nullable', 'integer', 'between:1,10'],
            'resultado' => ['nullable', 'in:aprobado,desaprobado,ausente'],
        ]);

        $estado = EstadoInscripcion::where('nombre_estado', $data['estado'])->firstOrFail();

        $inscripcionMesa->update([
            'id_estado_inscripcion' => $estado->id_estado_inscripcion,
            'nota_examen' => $data['nota_examen'] ?? $inscripcionMesa->nota_examen,
            'resultado' => $data['resultado'] ?? $inscripcionMesa->resultado,
        ]);

        return back()->with('status', 'Inscripción actualizada correctamente.');
    }
}
