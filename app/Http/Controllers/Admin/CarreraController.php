<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CarreraController extends Controller
{
    public function index(Request $request): View
    {
        $carreras = Carrera::withCount('materias')
            ->when($request->query('q'), fn ($q, $busqueda) => $q->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->get();

        return view('admin.carreras.index', [
            'carreras' => $carreras,
            'busqueda' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.carreras.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:carreras,nombre'],
            'familia_profesional' => ['nullable', 'string', 'max:100'],
            'resolucion_ministerial' => ['nullable', 'string', 'max:25'],
            'duracion_anios' => ['required', 'integer', 'min:1', 'max:6'],
            'estado' => ['required', Rule::in(['activa', 'inactiva', 'en_espera'])],
        ]);

        $carrera = Carrera::create($data);

        return redirect()->route('admin.carreras.materias', $carrera)
            ->with('status', 'Carrera creada. Ahora sumá las materias de su plan de estudio.');
    }

    public function materias(Carrera $carrera): View
    {
        return view('admin.carreras.materias', [
            'carrera' => $carrera,
            'materias' => $carrera->materias()->orderBy('numero_orden')->get(),
        ]);
    }

    public function storeMateria(Request $request, Carrera $carrera): RedirectResponse
    {
        $data = $request->validate([
            'numero_orden' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:150'],
            'anio_cursada' => ['required', 'integer', 'min:1', 'max:6'],
            'cuatrimestre' => ['required', Rule::in(['anual', '1er_cuatrimestre', '2do_cuatrimestre'])],
            'regimen' => ['required', Rule::in(['solo_promocion', 'solo_examen_final', 'promocion_o_examen_final'])],
        ]);

        $carrera->materias()->create($data + ['version_plan' => (string) now()->year]);

        return back()->with('status', 'Materia agregada al plan de estudio.');
    }

    public function correlativas(Carrera $carrera): View
    {
        return view('admin.carreras.correlativas', [
            'carrera' => $carrera,
            'materias' => $carrera->materias()->orderBy('numero_orden')->get(),
            'correlativas' => Correlativa::whereIn('materia_id', $carrera->materias()->pluck('id'))
                ->with('materia', 'materiaRequisito')
                ->get(),
        ]);
    }

    public function storeCorrelativa(Request $request, Carrera $carrera): RedirectResponse
    {
        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'materia_requisito_id' => ['required', 'exists:materias,id', 'different:materia_id'],
            'requiere_regularizada' => ['nullable', 'boolean'],
            'requiere_aprobada' => ['nullable', 'boolean'],
        ]);

        Correlativa::updateOrCreate(
            ['materia_id' => $data['materia_id'], 'materia_requisito_id' => $data['materia_requisito_id']],
            [
                'requiere_regularizada' => $request->boolean('requiere_regularizada'),
                'requiere_aprobada' => $request->boolean('requiere_aprobada'),
            ]
        );

        return back()->with('status', 'Correlativa guardada.');
    }

    public function destroyCorrelativa(Correlativa $correlativa): RedirectResponse
    {
        $carrera = $correlativa->materia->carrera;
        $correlativa->delete();

        return redirect()->route('admin.carreras.correlativas', $carrera)->with('status', 'Correlativa eliminada.');
    }

    public function plan(Carrera $carrera): View
    {
        $materias = $carrera->materias()
            ->with(['requisitos' => fn ($q) => $q->orderBy('numero_orden')])
            ->orderBy('numero_orden')
            ->get()
            ->groupBy('anio_cursada');

        return view('admin.carreras.plan', [
            'carrera' => $carrera,
            'materiasPorAnio' => $materias,
        ]);
    }
}
