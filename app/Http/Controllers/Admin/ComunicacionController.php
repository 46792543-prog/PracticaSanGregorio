<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AvisoAlumnoMail;
use App\Models\ComunicacionAlumno;
use App\Models\Persona;
use App\Support\AvisoDeuda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Correos MANUALES a alumnos desde la Central de Alertas. Nada se envía solo:
 * Secretaría prepara la plantilla, la edita, ve la vista previa, confirma y envía.
 * Cada envío es individual (un destinatario por correo) y queda en el historial.
 */
class ComunicacionController extends Controller
{
    // Plantilla lista para editar (datos de la deuda del alumno).
    public function preparar(Persona $persona): JsonResponse
    {
        return response()->json(AvisoDeuda::plantilla($persona->load('usuario')));
    }

    // Cómo se va a ver el correo (mismo diseño que el que se envía).
    public function vistaPrevia(Request $request): JsonResponse
    {
        $data = $this->validar($request);

        return response()->json(['html' => view('emails.aviso-alumno', ['mensaje' => $data['mensaje']])->render()]);
    }

    // Envía el correo a ese alumno solo. Siempre queda registrado, salga bien o mal.
    public function enviar(Request $request, Persona $persona): JsonResponse
    {
        $data = $this->validar($request);
        $email = trim((string) $persona->load('usuario')->usuario?->email);

        if (! AvisoDeuda::emailValido($email)) {
            return response()->json(['ok' => false, 'mensaje' => "{$persona->apellido}, {$persona->nombre} no tiene un correo válido cargado: no se envió nada. Corregilo en su ficha."], 422);
        }

        try {
            Mail::to($email)->send(new AvisoAlumnoMail($data['asunto'], $data['mensaje'])); // un destinatario por correo
            $error = null;
        } catch (Throwable $e) {
            $error = mb_substr($e->getMessage(), 0, 500);
        }

        ComunicacionAlumno::create([
            'id_persona_alumno' => $persona->id_persona,
            'destinatario' => $email,
            'asunto' => $data['asunto'],
            'mensaje' => $data['mensaje'],
            'enviado' => $error === null,
            'error' => $error,
            'id_persona_envia' => Auth::user()->id_persona,
        ]);

        return response()->json($error === null
            ? ['ok' => true, 'mensaje' => "Correo enviado a {$persona->apellido}, {$persona->nombre} ({$email})."]
            : ['ok' => false, 'mensaje' => "No se pudo enviar el correo a {$email}. Quedó registrado en el historial con el error."], $error === null ? 200 : 500);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'asunto' => ['required', 'string', 'min:5', 'max:150'],
            'mensaje' => ['required', 'string', 'min:20', 'max:5000'],
        ], [
            'asunto.required' => 'Escribí el asunto del correo.',
            'asunto.min' => 'El asunto es muy corto.',
            'mensaje.required' => 'Escribí el mensaje del correo.',
            'mensaje.min' => 'El mensaje es muy corto.',
            'mensaje.max' => 'El mensaje puede tener hasta 5000 caracteres.',
        ]);
    }
}
