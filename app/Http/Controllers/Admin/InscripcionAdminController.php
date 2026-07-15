<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InscripcionMesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InscripcionAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->query('estado', 'en_proceso');

        $inscripciones = InscripcionMesa::with('user', 'mesaExamen.materia.carrera')
            ->when($filtro !== 'todas', fn ($q) => $q->where('estado', $filtro))
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
            'estado' => ['required', Rule::in(['aceptada', 'rechazada'])],
        ]);

        $inscripcionMesa->update($data);

        return back()->with('status', 'Inscripción actualizada correctamente.');
    }
}
