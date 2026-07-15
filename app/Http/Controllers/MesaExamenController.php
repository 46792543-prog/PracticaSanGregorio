<?php

namespace App\Http\Controllers;

use App\Models\InscripcionMesa;
use App\Models\MesaExamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MesaExamenController extends Controller
{
    private const TURNOS = [
        'febrero_marzo' => 'Turno Febrero/Marzo 2026',
        'julio' => 'Turno Julio 2026',
        'noviembre_diciembre' => 'Turno Noviembre/Diciembre 2026',
    ];

    public function index(Request $request): View
    {
        $alumno = Auth::user();
        $turno = $request->query('turno', 'julio');

        $mesas = MesaExamen::with('materia')
            ->where('turno', $turno)
            ->where('estado', 'programada')
            ->orderBy('fecha_examen')
            ->get()
            ->map(function (MesaExamen $mesa) use ($alumno) {
                $mesa->bloqueo = $mesa->materia->correlativaFaltante($alumno);
                $mesa->ya_inscripto = InscripcionMesa::where('mesa_examen_id', $mesa->id)
                    ->where('user_id', $alumno->id)
                    ->exists();

                return $mesa;
            });

        return view('mesas-examen.index', [
            'mesas' => $mesas,
            'turno' => $turno,
            'turnos' => self::TURNOS,
        ]);
    }

    public function inscribir(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $alumno = Auth::user();

        if ($mesa->materia->correlativaFaltante($alumno)) {
            return back()->withErrors(['mesa' => 'No cumplís las correlativas necesarias para esta mesa.']);
        }

        InscripcionMesa::firstOrCreate(
            ['mesa_examen_id' => $mesa->id, 'user_id' => $alumno->id],
            ['fecha_inscripcion' => now(), 'estado' => 'en_proceso']
        );

        return redirect()->route('inscripciones.index')
            ->with('status', 'Tu inscripción a ' . $mesa->materia->nombre . ' quedó registrada y pendiente de aprobación.');
    }
}
