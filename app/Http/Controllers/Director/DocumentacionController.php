<?php

namespace App\Http\Controllers;

use App\Models\ControlDocumentacion;
use App\Models\DocumentoRequisito;
use App\Models\EstadoDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentacionController extends Controller
{
    public function store(Request $request, DocumentoRequisito $documentoRequisito): RedirectResponse
    {
        $alumno = Auth::user()->persona;

        $carreraId = $alumno->inscripcionesCarrera()->value('id_carrera');
        abort_unless($documentoRequisito->id_carrera === null || $documentoRequisito->id_carrera === $carreraId, 403);

        $data = $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $archivo = $data['archivo'];
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreArchivo = Str::uuid() . '.' . $archivo->extension();
        $path = $archivo->storeAs("documentacion/{$alumno->id_persona}", $nombreArchivo, 'local');

        $anterior = ControlDocumentacion::where('id_persona_alumno', $alumno->id_persona)
            ->where('id_requisito', $documentoRequisito->id_requisito)
            ->first();

        // Si ya había un archivo anterior (por ejemplo tras un rechazo), se borra
        // del disco al reemplazarlo para no acumular archivos huérfanos.
        if ($anterior?->archivo_path) {
            Storage::disk('local')->delete($anterior->archivo_path);
        }

        $estadoEntregado = EstadoDocumento::where('nombre_estado', 'Entregado')->firstOrFail();

        ControlDocumentacion::updateOrCreate(
            ['id_persona_alumno' => $alumno->id_persona, 'id_requisito' => $documentoRequisito->id_requisito],
            [
                'fecha_entrega' => now(),
                'id_estado_documento' => $estadoEntregado->id_estado_documento,
                'fecha_aprobacion' => null,
                'observaciones' => null,
                'archivo_path' => $path,
                'archivo_nombre_original' => $nombreOriginal,
            ]
        );

        return back()->with('status', 'Documento enviado correctamente. Quedó pendiente de revisión por secretaría.');
    }

    public function descargar(ControlDocumentacion $controlDocumentacion): StreamedResponse
    {
        // Un alumno solo puede descargar su propio documento, nunca el de otro.
        abort_unless($controlDocumentacion->id_persona_alumno === Auth::user()->persona->id_persona, 403);
        abort_unless($controlDocumentacion->archivo_path, 404);

        return Storage::disk('local')->download($controlDocumentacion->archivo_path, $controlDocumentacion->archivo_nombre_original);
    }
}
