<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\EstadoCarrera;
use App\Models\NombreMateria;
use App\Models\PeriodoDictado;
use App\Models\RegimenAprobacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarreraController extends Controller
{
    public function index(Request $request): View
    {
        $carreras = Carrera::withCount('materias')->with('estadoCarrera')
            ->when($request->query('q'), fn ($q, $busqueda) => $q->where('nombre_carrera', 'like', "%{$busqueda}%"))
            ->orderBy('nombre_carrera')
            ->get();

        return view('admin.carreras.index', [
            'carreras' => $carreras,
            'busqueda' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.carreras.create', [
            'estados' => EstadoCarrera::orderBy('id_estado_carrera')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_carrera' => ['required', 'string', 'max:50', 'unique:carrera,nombre_carrera'],
            'familia_profesional' => ['nullable', 'string', 'max:100'],
            'resolucion_ministerial' => ['nullable', 'string', 'max:25'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:6'],
            'id_estado_carrera' => ['required', 'exists:estado_carrera,id_estado_carrera'],
        ]);

        $carrera = Carrera::create($data);

        return redirect()->route('admin.carreras.materias', $carrera)
            ->with('status', 'Carrera creada. Ahora sumá las materias de su plan de estudio.');
    }

    public function materias(Carrera $carrera): View
    {
        return view('admin.carreras.materias', [
            'carrera' => $carrera,
            'materias' => $carrera->materias()->with('nombreMateria', 'anioCursada', 'periodo', 'regimen')->orderBy('numero_orden')->get(),
            'aniosCursada' => AnioCursada::orderBy('id_anio_cursada')->get(),
            'periodos' => PeriodoDictado::orderBy('id_periodo')->get(),
            'regimenes' => RegimenAprobacion::orderBy('id_regimen')->get(),
        ]);
    }

    public function storeMateria(Request $request, Carrera $carrera): RedirectResponse
    {
        $data = $request->validate([
            'numero_orden' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:50'],
            'id_anio_cursada' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'id_periodo' => ['required', 'exists:periodo_dictado,id_periodo'],
            'id_regimen' => ['required', 'exists:regimen_aprobacion,id_regimen'],
        ]);

        $nombreMateria = NombreMateria::firstOrCreate(['nombre' => $data['nombre']]);

        $carrera->materias()->create([
            'numero_orden' => $data['numero_orden'],
            'id_nombre_materia' => $nombreMateria->id_nombre_materia,
            'id_anio_cursada' => $data['id_anio_cursada'],
            'id_periodo' => $data['id_periodo'],
            'id_regimen' => $data['id_regimen'],
            'version_plan' => (string) now()->year,
        ]);

        return back()->with('status', 'Materia agregada al plan de estudio.');
    }

    public function correlativas(Carrera $carrera): View
    {
        $materiaIds = $carrera->materias()->pluck('id_materia');

        return view('admin.carreras.correlativas', [
            'carrera' => $carrera,
            'materias' => $carrera->materias()->with('nombreMateria')->orderBy('numero_orden')->get(),
            'correlativas' => Correlativa::whereIn('id_materia_principal', $materiaIds)
                ->with('materiaPrincipal.nombreMateria', 'materiaRequisito.nombreMateria')
                ->get(),
        ]);
    }

    public function storeCorrelativa(Request $request, Carrera $carrera): RedirectResponse
    {
        $data = $request->validate([
            'id_materia_principal' => ['required', 'exists:materia,id_materia'],
            'id_materia_requisito' => ['required', 'exists:materia,id_materia', 'different:id_materia_principal'],
            'requiere_regularizada' => ['nullable', 'boolean'],
            'requiere_aprobada' => ['nullable', 'boolean'],
        ]);

        Correlativa::updateOrCreate(
            ['id_materia_principal' => $data['id_materia_principal'], 'id_materia_requisito' => $data['id_materia_requisito']],
            [
                'requiere_regularizada' => $request->boolean('requiere_regularizada'),
                'requiere_aprobada' => $request->boolean('requiere_aprobada'),
            ]
        );

        return back()->with('status', 'Correlativa guardada.');
    }

    public function destroyCorrelativa(int $principal, int $requisito): RedirectResponse
    {
        $correlativa = Correlativa::where('id_materia_principal', $principal)
            ->where('id_materia_requisito', $requisito)
            ->firstOrFail();

        $carrera = $correlativa->materiaPrincipal->carrera;
        $correlativa->delete();

        return redirect()->route('admin.carreras.correlativas', $carrera)->with('status', 'Correlativa eliminada.');
    }

    public function plan(Carrera $carrera): View
    {
        $materias = $carrera->materias()
            ->with(['requisitos.nombreMateria', 'nombreMateria', 'anioCursada', 'periodo', 'regimen'])
            ->orderBy('numero_orden')
            ->get()
            ->groupBy(fn ($materia) => $materia->anioCursada->nombre_anio);

        return view('admin.carreras.plan', [
            'carrera' => $carrera,
            'materiasPorAnio' => $materias,
        ]);
    }
}
