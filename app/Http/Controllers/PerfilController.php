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
        $usuario = Auth::user();
        $alumno = $usuario->persona;
        $alumno->setRelation('usuario', $usuario);
        $carreraId = $alumno->inscripcionesCarrera()->value('id_carrera');

        $documentos = DocumentoRequisito::orderBy('nombre_documento')
            ->where(fn ($q) => $q->whereNull('id_carrera')->orWhere('id_carrera', $carreraId))
            ->with(['controlDocumentacion' => fn ($q) => $q->where('id_persona_alumno', $alumno->id_persona)])
            ->get()
            ->map(function (DocumentoRequisito $documento) {
                $documento->miEntrega = $documento->controlDocumentacion->first();
                $documento->estado_alumno = strtolower($documento->miEntrega?->estadoDocumento?->nombre_estado ?? 'pendiente');

                return $documento;
            });

        $primerIngreso = $documentos->where('es_obligatorio', true)
            ->contains(fn ($d) => in_array($d->estado_alumno, ['pendiente', 'rechazado']));

        return view('mis-datos.index', [
            'alumno' => $alumno,
            'documentos' => $documentos,
            'primerIngreso' => $primerIngreso,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $alumno = $usuario->persona;

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'dni' => ['required', 'digits:8', Rule::unique('persona', 'dni')->ignore($alumno->id_persona, 'id_persona')],
            'fecha_nacimiento' => ['nullable', 'date'],
            'telefono' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:35', 'regex:/^[\pL0-9\s]+$/u'],
            'email' => ['required', 'email', 'max:25', Rule::unique('usuario', 'email')->ignore($usuario->id_usuario, 'id_usuario')],
        ]);

        $email = $datos['email'];
        unset($datos['email']);

        $alumno->update($datos);
        $usuario->update(['email' => $email]);

        return redirect()->route('mis-datos.edit')->with('status', 'Tus datos se guardaron correctamente.');
    }
}
