<?php

namespace App\Http\Controllers;

use App\Models\DocumentoRequisito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function edit(): View
    {
        $alumno = Auth::user();
        $carreraId = $alumno->inscripcionesCarrera()->value('carrera_id');

        $documentos = DocumentoRequisito::orderBy('nombre')
            ->where(fn ($q) => $q->whereNull('carrera_id')->orWhere('carrera_id', $carreraId))
            ->with(['documentosAlumno' => fn ($q) => $q->where('user_id', $alumno->id)])
            ->get()
            ->map(function (DocumentoRequisito $documento) {
                $documento->estado_alumno = $documento->documentosAlumno->first()->estado ?? 'pendiente';

                return $documento;
            });

        $primerIngreso = $documentos->where('obligatorio', true)
            ->contains(fn ($d) => in_array($d->estado_alumno, ['pendiente', 'rechazado']));

        return view('mis-datos.index', [
            'alumno' => $alumno,
            'documentos' => $documentos,
            'primerIngreso' => $primerIngreso,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $alumno = Auth::user();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($alumno->id)],
            'fecha_nacimiento' => ['nullable', 'date'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($alumno->id)],
        ]);

        $alumno->update($datos);

        return redirect()->route('mis-datos.edit')->with('status', 'Tus datos se guardaron correctamente.');
    }
}
