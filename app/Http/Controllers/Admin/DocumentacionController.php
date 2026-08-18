<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\ControlDocumentacion;
use App\Models\DocumentoRequisito;
use App\Models\EstadoDocumento;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentacionController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->query('filtro', 'todos');
        $busqueda = trim((string) $request->query('q'));

        $alumnos = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->with(['documentacion.documentoRequisito', 'documentacion.estadoDocumento', 'inscripcionesCarrera.carrera', 'inscripcionesCarrera.anioCursada'])
            ->when($busqueda, fn ($q) => $q->where(fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")))
            ->orderBy('apellido')
            ->get()
            ->map(function (Persona $alumno) {
                $obligatorios = $alumno->documentacion->filter(fn ($d) => $d->documentoRequisito->es_obligatorio);
                $estados = $obligatorios->map(fn ($d) => $d->estadoDocumento->nombre_estado ?? null);

                $alumno->estado_documentacion = match (true) {
                    $obligatorios->isEmpty() => 'sin_enviar',
                    $estados->contains('Rechazado') => 'pendiente',
                    $estados->contains('Pendiente') => 'sin_enviar',
                    $estados->contains('Entregado') => 'revision',
                    default => 'completo',
                };

                return $alumno;
            });

        if ($filtro === 'pendientes') {
            $alumnos = $alumnos->whereIn('estado_documentacion', ['pendiente', 'revision', 'sin_enviar']);
        } elseif ($filtro === 'completos') {
            $alumnos = $alumnos->where('estado_documentacion', 'completo');
        }

        return view('admin.documentacion.index', [
            'alumnos' => $alumnos,
            'filtro' => $filtro,
            'busqueda' => $busqueda,
            'totales' => [
                'total' => Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))->count(),
                'en_revision' => ControlDocumentacion::whereHas('estadoDocumento', fn ($q) => $q->where('nombre_estado', 'Entregado'))
                    ->distinct('id_persona_alumno')->count('id_persona_alumno'),
                'completos' => Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
                    ->whereDoesntHave('documentacion.estadoDocumento', fn ($q) => $q->whereIn('nombre_estado', ['Pendiente', 'Entregado', 'Rechazado']))
                    ->count(),
            ],
        ]);
    }

    public function show(Persona $persona): View
    {
        $carreraId = $persona->inscripcionesCarrera()->value('id_carrera');

        $documentos = DocumentoRequisito::orderBy('nombre_documento')
            ->where(fn ($q) => $q->whereNull('id_carrera')->orWhere('id_carrera', $carreraId))
            ->with(['controlDocumentacion' => fn ($q) => $q->where('id_persona_alumno', $persona->id_persona)->with('estadoDocumento')])
            ->get();

        return view('admin.documentacion.show', [
            'alumno' => $persona,
            'documentos' => $documentos,
            'carrera' => Carrera::find($carreraId),
        ]);
    }

    public function actualizar(Request $request, ControlDocumentacion $controlDocumentacion): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:Entregado,Aprobado,Rechazado'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $estado = EstadoDocumento::where('nombre_estado', $data['estado'])->firstOrFail();

        $controlDocumentacion->update([
            'id_estado_documento' => $estado->id_estado_documento,
            'observaciones' => $data['observaciones'] ?? null,
            'fecha_aprobacion' => $data['estado'] === 'Aprobado' ? now() : null,
            'id_secretario_recibe' => Auth::user()->id_persona,
        ]);

        return back()->with('status', 'Documento actualizado correctamente.');
    }

    public function descargar(ControlDocumentacion $controlDocumentacion): StreamedResponse
    {
        abort_unless($controlDocumentacion->archivo_path, 404);

        return Storage::disk('local')->download($controlDocumentacion->archivo_path, $controlDocumentacion->archivo_nombre_original);
    }

    public function requisitos(Request $request): View
    {
        $requisitos = DocumentoRequisito::with('carrera')
            ->when($request->query('q'), fn ($q, $busqueda) => $q->where('nombre_documento', 'like', "%{$busqueda}%"))
            ->orderBy('nombre_documento')
            ->get();

        return view('admin.documentacion.requisitos', [
            'requisitos' => $requisitos,
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'busqueda' => $request->query('q'),
        ]);
    }

    public function storeRequisito(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_documento' => ['required', 'string', 'max:150', 'unique:documento_requisito,nombre_documento'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'es_obligatorio' => ['required', 'in:1,0'],
            'id_carrera' => ['nullable', 'exists:carrera,id_carrera'],
        ]);

        DocumentoRequisito::create($data);

        return back()->with('status', 'Requisito agregado correctamente.');
    }

    public function destroyRequisito(DocumentoRequisito $documentoRequisito): RedirectResponse
    {
        $documentoRequisito->delete();

        return back()->with('status', 'Requisito eliminado.');
    }
}
