<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\EstadoCarrera;
use App\Models\Materia;
use App\Models\NombreMateria;
use App\Models\PeriodoDictado;
use App\Models\RegimenAprobacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $this->asegurarEstadosCarrera();

        return view('admin.carreras.create', [
            'estados' => EstadoCarrera::orderBy('id_estado_carrera')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->asegurarEstadosCarrera();

        $data = $request->validate([
            'nombre_carrera' => ['required', 'string', 'max:40', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/', 'unique:carrera,nombre_carrera'],
            'familia_profesional' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/'],
            'resolucion_ministerial' => ['nullable', 'digits_between:1,4'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:6'],
            'id_estado_carrera' => ['required', 'exists:estado_carrera,id_estado_carrera'],
        ]);

        $carrera = Carrera::create($data);

        return redirect()->route('admin.carreras.materias', $carrera)
            ->with('status', 'Carrera creada. Ahora sumá las materias de su plan de estudio.');
    }

    public function edit(Carrera $carrera): View
    {
        $this->asegurarEstadosCarrera();

        return view('admin.carreras.edit', [
            'carrera' => $carrera,
            'estados' => EstadoCarrera::orderBy('id_estado_carrera')->get(),
        ]);
    }

    public function update(Request $request, Carrera $carrera): RedirectResponse
    {
        $this->asegurarEstadosCarrera();

        $data = $request->validate([
            'nombre_carrera' => ['required', 'string', 'max:50', 'unique:carrera,nombre_carrera,' . $carrera->id_carrera . ',id_carrera'],
            'familia_profesional' => ['nullable', 'string', 'max:100'],
            'resolucion_ministerial' => ['nullable', 'string', 'max:25'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:6'],
            'id_estado_carrera' => ['required', 'exists:estado_carrera,id_estado_carrera'],
        ]);

        $carrera->update($data);

        return redirect()->route('admin.carreras.index')->with('status', 'Carrera actualizada correctamente.');
    }

    private function asegurarEstadosCarrera(): void
    {
        foreach (['Activa', 'Inactiva', 'En espera'] as $nombre) {
            EstadoCarrera::firstOrCreate(['nombre_estado' => $nombre]);
        }
    }

    private function asegurarCatalogosPlan(): void
    {
        foreach (['Primer Año', 'Segundo Año', 'Tercer Año'] as $nombre) {
            AnioCursada::firstOrCreate(['nombre_anio' => $nombre]);
        }

        foreach (['Anual', '1er Cuatrimestre', '2do Cuatrimestre'] as $nombre) {
            PeriodoDictado::firstOrCreate(['nombre_periodo' => $nombre]);
        }

        foreach (['Solo Promoción', 'Solo Examen Final', 'Promoción o Examen Final'] as $nombre) {
            RegimenAprobacion::firstOrCreate(['nombre_regimen' => $nombre]);
        }
    }

    public function materias(Carrera $carrera): View
    {
        $this->asegurarCatalogosPlan();

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
        $this->asegurarCatalogosPlan();

        $data = $request->validate([
            'numero_orden' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/'],
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

    public function updateMateria(Request $request, Carrera $carrera, Materia $materia): RedirectResponse
    {
        $this->asegurarCatalogosPlan();

        $data = $request->validate([
            'numero_orden' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/'],
            'id_anio_cursada' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'id_periodo' => ['required', 'exists:periodo_dictado,id_periodo'],
            'id_regimen' => ['required', 'exists:regimen_aprobacion,id_regimen'],
        ]);

        $nombreMateria = NombreMateria::firstOrCreate(['nombre' => $data['nombre']]);

        $materia->update([
            'numero_orden' => $data['numero_orden'],
            'id_nombre_materia' => $nombreMateria->id_nombre_materia,
            'id_anio_cursada' => $data['id_anio_cursada'],
            'id_periodo' => $data['id_periodo'],
            'id_regimen' => $data['id_regimen'],
        ]);

        return back()->with('status', 'Materia actualizada correctamente.');
    }

    public function bajaMateria(Carrera $carrera, Materia $materia): RedirectResponse
    {
        $materia->update(['activa' => false]);

        return back()->with('status', 'Materia dada de baja.');
    }

    public function reactivarMateria(Carrera $carrera, Materia $materia): RedirectResponse
    {
        $materia->update(['activa' => true]);

        return back()->with('status', 'Materia reactivada.');
    }

    public function correlativas(Carrera $carrera): View
    {
        return $this->vistaCorrelativas($carrera);
    }

    public function editGrupoCorrelativas(Carrera $carrera, int $principal): View
    {
        $this->buscarPrincipalDeCarrera($carrera, $principal);

        return $this->vistaCorrelativas($carrera, $principal);
    }

    /**
     * Una sola pantalla para crear y editar: el formulario arma la lista completa de
     * materias requeridas de una materia destino (agregar, quitar y marcar para
     * cursar/rendir) y la matriz lleva los datos para editar sin recargar la página.
     */
    private function vistaCorrelativas(Carrera $carrera, ?int $principalEditar = null): View
    {
        $materias = $carrera->materias()->with('nombreMateria')->orderBy('numero_orden')->get();

        $correlativas = Correlativa::whereIn('id_materia_principal', $materias->pluck('id_materia'))
            ->get()
            ->sortBy(fn ($correlativa) => $materias->firstWhere('id_materia', $correlativa->id_materia_requisito)?->numero_orden)
            ->groupBy('id_materia_principal')
            ->sortBy(fn ($grupo, $idPrincipal) => $materias->firstWhere('id_materia', $idPrincipal)?->numero_orden);

        abort_if($principalEditar && ! $correlativas->has($principalEditar), 404);

        return view('admin.carreras.correlativas', [
            'carrera' => $carrera,
            'materias' => $materias->keyBy('id_materia'),
            'correlativas' => $correlativas,
            'principalEditar' => $principalEditar,
        ]);
    }

    /**
     * Guarda la lista completa de correlativas de una materia destino: agrega las
     * nuevas, actualiza las existentes y borra las que se quitaron del formulario.
     */
    public function storeCorrelativa(Request $request, Carrera $carrera): RedirectResponse
    {
        $idsMaterias = $carrera->materias()->pluck('id_materia')->all();

        $data = $request->validate([
            'id_materia_principal' => ['required', 'integer', Rule::in($idsMaterias)],
            'requisitos' => ['required', 'array', 'min:1'],
            'requisitos.*.id_materia' => ['required', 'integer', 'distinct', Rule::in($idsMaterias), 'different:id_materia_principal'],
            'requisitos.*.para_cursar' => ['nullable', 'boolean'],
            'requisitos.*.para_rendir' => ['nullable', 'boolean'],
        ], [
            'requisitos.required' => 'Agregá al menos una materia requerida.',
            'requisitos.*.id_materia.different' => 'Una materia no puede ser correlativa de sí misma.',
        ], [
            'id_materia_principal' => 'materia destino',
            'requisitos.*.id_materia' => 'materia requerida',
        ]);

        $principal = (int) $data['id_materia_principal'];
        $requisitos = collect($data['requisitos'])->map(fn ($fila) => [
            'id' => (int) $fila['id_materia'],
            'cursar' => (bool) ($fila['para_cursar'] ?? false),
            'rendir' => (bool) ($fila['para_rendir'] ?? false),
        ]);

        if ($requisitos->contains(fn ($fila) => ! $fila['cursar'] && ! $fila['rendir'])) {
            return back()->withInput()->withErrors(['requisitos' => 'Cada materia requerida tiene que estar marcada "Para cursar", "Para rendir" o ambas.']);
        }

        // Evita el ciclo directo: si B ya es correlativa de A, A no puede ser correlativa de B.
        $ciclo = Correlativa::where('id_materia_requisito', $principal)
            ->whereIn('id_materia_principal', $requisitos->pluck('id'))
            ->first();
        if ($ciclo) {
            $nombre = Materia::with('nombreMateria')->find($ciclo->id_materia_principal)?->nombre;

            return back()->withInput()->withErrors(['requisitos' => "\"{$nombre}\" ya tiene como correlativa a esta materia destino; no pueden requerirse mutuamente."]);
        }

        $existiaGrupo = Correlativa::where('id_materia_principal', $principal)->exists();

        // Una sola transacción: MySQL confirma todo junto en vez de una escritura por fila.
        DB::transaction(function () use ($principal, $requisitos): void {
            Correlativa::where('id_materia_principal', $principal)
                ->whereNotIn('id_materia_requisito', $requisitos->pluck('id'))
                ->get()
                ->each->delete();

            foreach ($requisitos as $fila) {
                Correlativa::updateOrCreate(
                    ['id_materia_principal' => $principal, 'id_materia_requisito' => $fila['id']],
                    ['requiere_regularizada' => $fila['cursar'], 'requiere_aprobada' => $fila['rendir']]
                );
            }
        });

        return redirect()->route('admin.carreras.correlativas', $carrera)
            ->with('status', $existiaGrupo ? 'Correlativas actualizadas correctamente.' : 'Correlativas guardadas.');
    }

    public function destroyGrupoCorrelativas(Carrera $carrera, int $principal): RedirectResponse
    {
        $this->buscarPrincipalDeCarrera($carrera, $principal);

        DB::transaction(fn () => Correlativa::where('id_materia_principal', $principal)->get()->each->delete());

        return redirect()->route('admin.carreras.correlativas', $carrera)
            ->with('status', 'Correlativas de la materia eliminadas.');
    }

    private function buscarPrincipalDeCarrera(Carrera $carrera, int $principal): Materia
    {
        return Materia::where('id_carrera', $carrera->id_carrera)
            ->where('id_materia', $principal)
            ->firstOrFail();
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
