<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\DocumentoAlumno;
use App\Models\DocumentoRequisito;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentacionController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->query('filtro', 'todos');
        $busqueda = trim((string) $request->query('q'));

        $alumnos = User::where('rol', 'alumno')
            ->with(['documentosAlumno.documentoRequisito', 'inscripcionesCarrera.carrera'])
            ->when($busqueda, fn ($q) => $q->where(fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")))
            ->orderBy('apellido')
            ->get()
            ->map(function (User $alumno) {
                $obligatorios = $alumno->documentosAlumno->filter(fn ($d) => $d->documentoRequisito->obligatorio);
                $alumno->estado_documentacion = match (true) {
                    $obligatorios->isEmpty() => 'sin_enviar',
                    $obligatorios->contains(fn ($d) => $d->estado === 'rechazado') => 'pendiente',
                    $obligatorios->contains(fn ($d) => $d->estado === 'pendiente') => 'sin_enviar',
                    $obligatorios->contains(fn ($d) => $d->estado === 'entregado') => 'revision',
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
                'total' => User::where('rol', 'alumno')->count(),
                'en_revision' => DocumentoAlumno::where('estado', 'entregado')->distinct('user_id')->count('user_id'),
                'completos' => User::where('rol', 'alumno')->whereDoesntHave('documentosAlumno', fn ($q) => $q->whereIn('estado', ['pendiente', 'entregado', 'rechazado']))->count(),
            ],
        ]);
    }

    public function show(User $user): View
    {
        $carreraId = $user->inscripcionesCarrera()->value('carrera_id');

        $documentos = DocumentoRequisito::orderBy('nombre')
            ->where(fn ($q) => $q->whereNull('carrera_id')->orWhere('carrera_id', $carreraId))
            ->with(['documentosAlumno' => fn ($q) => $q->where('user_id', $user->id)])
            ->get();

        return view('admin.documentacion.show', [
            'alumno' => $user,
            'documentos' => $documentos,
            'carrera' => Carrera::find($carreraId),
        ]);
    }

    public function actualizar(Request $request, DocumentoAlumno $documentoAlumno): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:entregado,aprobado,rechazado'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $documentoAlumno->update([
            'estado' => $data['estado'],
            'observaciones' => $data['observaciones'] ?? null,
            'fecha_aprobacion' => $data['estado'] === 'aprobado' ? now() : null,
            'secretario_revisa_id' => Auth::id(),
        ]);

        return back()->with('status', 'Documento actualizado correctamente.');
    }

    public function requisitos(Request $request): View
    {
        $requisitos = DocumentoRequisito::with('carrera')
            ->when($request->query('q'), fn ($q, $busqueda) => $q->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->get();

        return view('admin.documentacion.requisitos', [
            'requisitos' => $requisitos,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'busqueda' => $request->query('q'),
        ]);
    }

    public function storeRequisito(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:documento_requisitos,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'obligatorio' => ['required', 'in:1,0'],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
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
