<?php

namespace App\Http\Controllers;

use App\Models\FichaRespuesta;
use App\Models\FichaSeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FichaInscripcionController extends Controller
{
    public function edit(): View
    {
        $alumno = Auth::user()->persona;

        $secciones = FichaSeccion::where('activo', true)
            ->with(['campos' => fn ($q) => $q->where('activo', true)])
            ->orderBy('orden')
            ->get();

        $respuestas = FichaRespuesta::where('id_persona_alumno', $alumno->id_persona)
            ->get()
            ->keyBy('id_campo');

        return view('ficha-inscripcion.edit', [
            'secciones' => $secciones,
            'respuestas' => $respuestas,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $alumno = Auth::user()->persona;

        $campos = FichaSeccion::where('activo', true)
            ->with(['campos' => fn ($q) => $q->where('activo', true)])
            ->orderBy('orden')
            ->get()
            ->pluck('campos')
            ->flatten();

        $reglas = [];
        foreach ($campos as $campo) {
            $nombre = "campo_{$campo->id_campo}";
            $reglas[$nombre] = [$campo->obligatorio ? 'required' : 'nullable'];

            $reglas[$nombre][] = match ($campo->tipo) {
                'numero' => 'numeric',
                'fecha' => 'date',
                'seleccion' => 'in:' . implode(',', $campo->opciones ?? []),
                'si_no' => 'in:Sí,No',
                default => 'string',
            };
        }

        $data = $request->validate($reglas);

        foreach ($campos as $campo) {
            $valor = $data["campo_{$campo->id_campo}"] ?? null;

            FichaRespuesta::updateOrCreate(
                ['id_persona_alumno' => $alumno->id_persona, 'id_campo' => $campo->id_campo],
                ['valor' => $valor !== '' ? $valor : null]
            );
        }

        return redirect()->route('ficha-inscripcion.edit')->with('status', 'Tu ficha de inscripción se guardó correctamente.');
    }
}
