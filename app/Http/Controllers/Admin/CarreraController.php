<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\EstadoCarrera;
use App\Models\Materia;
use App\Models\NombreMateria;
use App\Models\PlanEstudio;
use App\Models\PeriodoDictado;
use App\Models\RegimenAprobacion;
use App\Models\TurnoCursada;
use App\Support\CambioDePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CarreraController extends Controller
{
    public function index(Request $request): View
    {
        // La tarjeta muestra las materias del plan vigente de cada carrera.
        $idsVigentes = PlanEstudio::all()->filter(fn ($p) => $p->estado() === 'Vigente')->pluck('id_plan_estudio');
        $carreras = Carrera::withCount(['materias' => fn ($q) => $q->whereIn('id_plan_estudio', $idsVigentes)])->with('estadoCarrera', 'planes', 'turnos')
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
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
            'estados' => EstadoCarrera::orderBy('id_estado_carrera')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->asegurarEstadosCarrera();

        $data = $request->validate([
            'nombre_carrera' => ['required', 'string', 'max:40', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/', 'unique:carrera,nombre_carrera'],
            'familia_profesional' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/'],
            'resolucion_ministerial' => ['nullable', 'string', 'max:25'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:7'],
            'id_estado_carrera' => ['required', 'exists:estado_carrera,id_estado_carrera'],
            'turnos' => ['required', 'array', 'min:1'],
            'turnos.*' => ['exists:turno_cursada,id_turno_cursada'],
        ], ['turnos.required' => 'Elegí al menos un turno en que se dicta la carrera.']);

        $carrera = Carrera::create(collect($data)->except('turnos')->all());
        $carrera->turnos()->sync($data['turnos']);
        $carrera->planes()->create([
            'nombre' => 'Plan ' . now()->year,
            'resolucion_ministerial' => $carrera->resolucion_ministerial,
            'duracion_anos' => $carrera->duracion_anos,
            'anio_desde' => now()->year,
        ]);

        return redirect()->route('admin.carreras.materias', $carrera)
            ->with('status', 'Carrera creada. Ahora sumá las materias de su plan de estudio.');
    }

    public function edit(Carrera $carrera): View
    {
        $this->asegurarEstadosCarrera();

        return view('admin.carreras.edit', [
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
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
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:7'],
            'id_estado_carrera' => ['required', 'exists:estado_carrera,id_estado_carrera'],
            'turnos' => ['required', 'array', 'min:1'],
            'turnos.*' => ['exists:turno_cursada,id_turno_cursada'],
        ], ['turnos.required' => 'Elegí al menos un turno en que se dicta la carrera.']);

        $carrera->update(collect($data)->except('turnos')->all());
        $carrera->turnos()->sync($data['turnos']);

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

    public function materias(Request $request, Carrera $carrera): View
    {
        $this->asegurarCatalogosPlan();
        $plan = $this->planElegido($request, $carrera);

        return view('admin.carreras.materias', [
            'carrera' => $carrera,
            'plan' => $plan,
            'materias' => $plan->materias()->with('nombreMateria', 'anioCursada', 'periodo', 'regimen')->orderBy('numero_orden')->get(),
            'aniosCursada' => AnioCursada::orderBy('id_anio_cursada')->get(),
            'periodos' => PeriodoDictado::orderBy('id_periodo')->get(),
            'regimenes' => RegimenAprobacion::orderBy('id_regimen')->get(),
        ]);
    }

    public function storeMateria(Request $request, Carrera $carrera): RedirectResponse
    {
        $this->asegurarCatalogosPlan();

        $plan = $this->planElegido($request, $carrera);
        $this->exigirPlanEditable($plan);

        $data = $request->validate([
            'numero_orden' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+$/'],
            'id_anio_cursada' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'id_periodo' => ['required', 'exists:periodo_dictado,id_periodo'],
            'id_regimen' => ['required', 'exists:regimen_aprobacion,id_regimen'],
            // Horas cátedra semanales (1 h cátedra = 40 min), CUPOF y si es práctica.
            'horas_catedra' => ['required', 'integer', 'min:1', 'max:30'],
            'cupof' => [
                'required',
                'digits_between:1,8',
                Rule::unique('materia', 'cupof')->where('id_plan_estudio', $plan->id_plan_estudio),
            ],
            'es_taller' => ['nullable', 'boolean'],
        ], [
            'horas_catedra.required' => 'Cargá las horas cátedra semanales de la materia.',
            'horas_catedra.max' => 'Una materia no puede tener más de 30 horas cátedra semanales.',
            'cupof.required' => 'Cargá el CUPOF de la materia.',
            'cupof.digits_between' => 'El CUPOF tiene que tener solo números (hasta 8).',
            'cupof.unique' => 'Ese CUPOF ya está usado por otra materia de este plan.',
        ]);

        $nombreMateria = NombreMateria::firstOrCreate(['nombre' => $data['nombre']]);

        $carrera->materias()->create([
            'id_plan_estudio' => $plan->id_plan_estudio,
            'numero_orden' => $data['numero_orden'],
            'id_nombre_materia' => $nombreMateria->id_nombre_materia,
            'id_anio_cursada' => $data['id_anio_cursada'],
            'id_periodo' => $data['id_periodo'],
            'id_regimen' => $data['id_regimen'],
            'horas_catedra' => $data['horas_catedra'],
            'cupof' => $data['cupof'] ?? null,
            'es_taller' => $request->boolean('es_taller'),
            'version_plan' => (string) $plan->anio_desde,
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
            // Horas cátedra semanales (1 h cátedra = 40 min), CUPOF y si es práctica.
            'horas_catedra' => ['required', 'integer', 'min:1', 'max:30'],
            'cupof' => [
                'required',
                'digits_between:1,8',
                Rule::unique('materia', 'cupof')
                    ->where('id_plan_estudio', $materia->id_plan_estudio)
                    ->ignore($materia->id_materia, 'id_materia'),
            ],
            'es_taller' => ['nullable', 'boolean'],
        ], [
            'horas_catedra.required' => 'Cargá las horas cátedra semanales de la materia.',
            'horas_catedra.max' => 'Una materia no puede tener más de 30 horas cátedra semanales.',
            'cupof.required' => 'Cargá el CUPOF de la materia.',
            'cupof.digits_between' => 'El CUPOF tiene que tener solo números (hasta 8).',
            'cupof.unique' => 'Ese CUPOF ya está usado por otra materia de este plan.',
        ]);

        // Editar los datos de una materia se puede siempre, aunque el plan tenga alumnos.
        $nombreMateria = NombreMateria::firstOrCreate(['nombre' => $data['nombre']]);

        $materia->update([
            'numero_orden' => $data['numero_orden'],
            'id_nombre_materia' => $nombreMateria->id_nombre_materia,
            'id_anio_cursada' => $data['id_anio_cursada'],
            'id_periodo' => $data['id_periodo'],
            'id_regimen' => $data['id_regimen'],
            'horas_catedra' => $data['horas_catedra'],
            'cupof' => $data['cupof'] ?? null,
            'es_taller' => $request->boolean('es_taller'),
        ]);

        return back()->with('status', 'Materia actualizada correctamente.');
    }

    public function bajaMateria(Carrera $carrera, Materia $materia): RedirectResponse
    {
        $this->exigirPlanEditable($materia->plan);
        $materia->update(['activa' => false]);

        return back()->with('status', 'Materia dada de baja.');
    }

    public function reactivarMateria(Carrera $carrera, Materia $materia): RedirectResponse
    {
        $this->exigirPlanEditable($materia->plan);
        $materia->update(['activa' => true]);

        return back()->with('status', 'Materia reactivada.');
    }

    public function correlativas(Request $request, Carrera $carrera): View
    {
        return $this->vistaCorrelativas($this->planElegido($request, $carrera));
    }

    public function editGrupoCorrelativas(Request $request, Carrera $carrera, int $principal): View
    {
        $this->buscarPrincipalDeCarrera($carrera, $principal);

        return $this->vistaCorrelativas($this->planElegido($request, $carrera), $principal);
    }

    /**
     * Una sola pantalla para crear y editar: el formulario arma la lista completa de
     * materias requeridas de una materia destino (agregar, quitar y marcar para
     * cursar/rendir) y la matriz lleva los datos para editar sin recargar la página.
     */
    private function vistaCorrelativas(PlanEstudio $plan, ?int $principalEditar = null): View
    {
        $carrera = $plan->carrera;
        $materias = $plan->materias()->with('nombreMateria')->orderBy('numero_orden')->get();

        $correlativas = Correlativa::whereIn('id_materia_principal', $materias->pluck('id_materia'))
            ->get()
            ->sortBy(fn ($correlativa) => $materias->firstWhere('id_materia', $correlativa->id_materia_requisito)?->numero_orden)
            ->groupBy('id_materia_principal')
            ->sortBy(fn ($grupo, $idPrincipal) => $materias->firstWhere('id_materia', $idPrincipal)?->numero_orden);

        abort_if($principalEditar && ! $correlativas->has($principalEditar), 404);

        return view('admin.carreras.correlativas', [
            'carrera' => $carrera,
            'plan' => $plan,
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
        $plan = $this->planElegido($request, $carrera);
        $this->exigirPlanEditable($plan);
        $idsMaterias = $plan->materias()->pluck('id_materia')->all();

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
        $this->exigirPlanEditable($this->buscarPrincipalDeCarrera($carrera, $principal)->plan);

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

    public function plan(Request $request, Carrera $carrera): View
    {
        $plan = $this->planElegido($request, $carrera);
        $materias = $plan->materias()
            ->with(['requisitos.nombreMateria', 'nombreMateria', 'anioCursada', 'periodo', 'regimen'])
            ->orderBy('numero_orden')
            ->get()
            ->groupBy(fn ($materia) => $materia->anioCursada->nombre_anio);

        return view('admin.carreras.plan', [
            'carrera' => $carrera,
            'plan' => $plan,
            'materiasPorAnio' => $materias,
        ]);
    }

    // ---------- Planes de estudio de la carrera (historial y vigencia) ----------

    public function planes(Carrera $carrera): View
    {
        return view('admin.carreras.planes', [
            'carrera' => $carrera,
            'planes' => $carrera->planes()->withCount(['materias', 'inscripciones'])->get(),
        ]);
    }

    // Nuevo plan: copia las materias y correlativas del plan elegido, y le
    // cierra las inscripciones al plan anterior el año antes de que empiece el nuevo.
    public function storePlan(Request $request, Carrera $carrera): RedirectResponse
    {
        $data = $this->validarPlan($request, $carrera) + $request->validate([
            'copiar_de' => ['nullable', Rule::exists('plan_estudio', 'id_plan_estudio')->where('id_carrera', $carrera->id_carrera)],
        ]);

        $nuevo = DB::transaction(function () use ($data, $carrera) {
            $nuevo = $carrera->planes()->create($data);
            $base = $data['copiar_de'] ? PlanEstudio::find($data['copiar_de']) : null;

            if ($base) {
                // Copia materias (id viejo => id nuevo) y después las correlativas entre ellas.
                $mapa = [];
                foreach ($base->materias as $materia) {
                    $copia = $materia->replicate();
                    $copia->id_plan_estudio = $nuevo->id_plan_estudio;
                    $copia->version_plan = (string) $nuevo->anio_desde;
                    $copia->save();
                    $mapa[$materia->id_materia] = $copia->id_materia;
                }

                foreach (Correlativa::whereIn('id_materia_principal', array_keys($mapa))->get() as $c) {
                    Correlativa::create([
                        'id_materia_principal' => $mapa[$c->id_materia_principal],
                        'id_materia_requisito' => $mapa[$c->id_materia_requisito] ?? $c->id_materia_requisito,
                        'id_tipo_correlativa' => $c->id_tipo_correlativa,
                        'requiere_regularizada' => $c->requiere_regularizada,
                        'requiere_aprobada' => $c->requiere_aprobada,
                    ]);
                }

                // (solo si ese año queda después del inicio del plan viejo: nunca el mismo año)
                if ($base->anio_cierre_inscripcion === null && $base->anio_desde < $nuevo->anio_desde - 1) {
                    $base->update(['anio_cierre_inscripcion' => $nuevo->anio_desde - 1]);
                }
            }

            return $nuevo;
        });

        session(["plan_carrera.{$carrera->id_carrera}" => $nuevo->id_plan_estudio]);

        return redirect()->route('admin.carreras.planes', $carrera)
            ->with('status', "Se creó el {$nuevo->nombre}. Ya podés editar sus materias y correlativas. Acordate de cargar en el plan anterior hasta qué año sigue vigente (caducidad).");
    }

    // Cambiar nombre, resolución, duración o vigencia (no toca materias, se puede siempre).
    public function updatePlan(Request $request, Carrera $carrera, PlanEstudio $plan): RedirectResponse
    {
        abort_unless($plan->id_carrera === $carrera->id_carrera, 404);
        $plan->update($this->validarPlan($request, $carrera, $plan));

        return back()->with('status', "Se actualizó el {$plan->nombre}.");
    }

    // Pasa a todos los alumnos de un plan a otro (ej. cuando el plan caduca).
    // Los que están cursando alguna materia se saltean hasta que cierren la cursada.
    public function pasarAlumnos(Request $request, Carrera $carrera, PlanEstudio $plan): RedirectResponse
    {
        abort_unless($plan->id_carrera === $carrera->id_carrera, 404);
        $destino = PlanEstudio::where('id_carrera', $carrera->id_carrera)->findOrFail($request->integer('id_plan_destino'));

        $pasados = 0;
        $salteados = [];

        foreach ($plan->inscripciones()->with('personaAlumno', 'plan')->get() as $inscripcion) {
            if ($motivo = CambioDePlan::impedimento($inscripcion, $destino)) {
                $salteados[] = "{$inscripcion->personaAlumno->apellido}, {$inscripcion->personaAlumno->nombre} ({$motivo})";
                continue;
            }

            CambioDePlan::aplicar($inscripcion, $destino);
            $pasados++;
        }

        $respuesta = back()->with('status', "Se pasaron {$pasados} alumno(s) del {$plan->nombre} al {$destino->nombre}.");

        return $salteados ? $respuesta->withErrors(['plan' => 'No se pasaron: ' . implode(' · ', $salteados)]) : $respuesta;
    }

    public function destroyPlan(Carrera $carrera, PlanEstudio $plan): RedirectResponse
    {
        abort_unless($plan->id_carrera === $carrera->id_carrera, 404);

        if ($plan->tieneAlumnos() || $carrera->planes()->count() === 1) {
            return back()->withErrors(['plan' => 'No se puede eliminar un plan con alumnos inscriptos, ni el único plan de la carrera.']);
        }

        DB::transaction(function () use ($plan) {
            $ids = $plan->materias()->pluck('id_materia');
            Correlativa::whereIn('id_materia_principal', $ids)->orWhereIn('id_materia_requisito', $ids)->get()->each->delete();
            $plan->materias()->delete();
            $plan->delete();
        });

        session()->forget("plan_carrera.{$carrera->id_carrera}");

        return back()->with('status', 'Plan eliminado.');
    }

    private function validarPlan(Request $request, Carrera $carrera, ?PlanEstudio $plan = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:40',
                Rule::unique('plan_estudio', 'nombre')->where('id_carrera', $carrera->id_carrera)->ignore($plan?->id_plan_estudio, 'id_plan_estudio')],
            'resolucion_ministerial' => ['nullable', 'string', 'max:25'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:7'],
            'anio_desde' => ['required', 'integer', 'between:2000,2100'],
            // Inscripciones hasta: un año POSTERIOR al de inicio (no el mismo).
            'anio_cierre_inscripcion' => ['nullable', 'integer', 'gt:anio_desde', 'max:2100'],
            'anio_caducidad' => ['nullable', 'integer', 'gte:anio_desde', 'max:2100'],
        ], [
            'nombre.unique' => 'Ya hay un plan con ese nombre en esta carrera.',
            'anio_cierre_inscripcion.gt' => '"Inscripciones hasta" tiene que ser un año posterior a "Desde" (no puede ser el mismo año).',
            'anio_caducidad.gte' => 'La caducidad no puede ser antes del año en que empieza.',
        ]);
    }

    // Plan que se está viendo en Materias / Correlativas / Ver plan (?plan=, o el
    // último elegido, o el vigente). Se recuerda en la sesión.
    private function planElegido(Request $request, Carrera $carrera): PlanEstudio
    {
        $planes = $carrera->planes()->get();
        $id = $request->integer('plan') ?: session("plan_carrera.{$carrera->id_carrera}");
        $plan = $planes->firstWhere('id_plan_estudio', $id)
            ?? $planes->first(fn ($p) => $p->estado() === 'Vigente')
            ?? $planes->first();

        abort_unless($plan, 404, 'La carrera no tiene planes de estudio.');
        session(["plan_carrera.{$carrera->id_carrera}" => $plan->id_plan_estudio]);
        view()->share('planesCarrera', $planes);

        return $plan;
    }

    // Un plan con alumnos no se modifica: para cambiar materias/correlativas se crea un plan nuevo.
    private function exigirPlanEditable(?PlanEstudio $plan): void
    {
        if ($plan?->tieneAlumnos()) {
            throw ValidationException::withMessages(['plan' => "El {$plan->nombre} ya tiene alumnos inscriptos: sus materias y correlativas no se pueden cambiar. Creá un plan nuevo desde \"Planes\" (se copia este como base)."]);
        }
    }
}
