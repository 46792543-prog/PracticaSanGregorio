<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClaveInicialMail;
use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\ConfiguracionCuota;
use App\Models\CondicionAlumno;
use App\Models\DetalleActa;
use App\Models\EstadoInscripcion;
use App\Models\EstadoUsuario;
use App\Models\FichaSeccion;
use App\Models\HistorialAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Materia;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TurnoCursada;
use App\Models\Usuario;
use App\Support\Auditor;
use App\Support\CambioDePlan;
use App\Support\CorteActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlumnoController extends Controller
{
    private const SESSION_KEY = 'alta_alumno';

    public function index(Request $request): View
    {
        $carreraId = $request->query('carrera');
        $estado = $request->query('estado');
        $busqueda = trim((string) $request->query('q'));

        $query = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->with([
                'inscripcionesCarrera' => fn ($q) => $q->with('carrera', 'estadoInscripcion', 'anioCursada')->latest('id_inscripcion_carrera'),
                'cuotas',
            ]);

        if ($busqueda) {
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")
                ->orWhere('dni', 'like', "%{$busqueda}%"));
        }

        if ($carreraId) {
            $query->whereHas('inscripcionesCarrera', fn ($q) => $q->where('id_carrera', $carreraId));
        }

        $corteActivo = CorteActivo::actual();

        if ($corteActivo) {
            $query->whereHas('inscripcionesCarrera', fn ($q) => $q->where('id_anio_lectivo', $corteActivo->id_anio_lectivo));
        }

        if ($estado === 'baja') {
            $query->whereHas('inscripcionesCarrera.estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Baja'));
        } elseif ($estado === 'activo') {
            $query->whereHas('inscripcionesCarrera')
                ->whereDoesntHave('inscripcionesCarrera.estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Baja'));
        }

        $alumnos = $query->orderBy('apellido')->paginate(8)->withQueryString();

        $carreras = Carrera::orderBy('nombre_carrera')->get();

        $inscripcionesCarrera = InscripcionCarrera::when($carreraId, fn ($q) => $q->where('id_carrera', $carreraId))
            ->when($corteActivo, fn ($q) => $q->where('id_anio_lectivo', $corteActivo->id_anio_lectivo));
        $totalEnCarrera = (clone $inscripcionesCarrera)->count();
        $activos = (clone $inscripcionesCarrera)->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))->count();
        $docPendiente = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->whereHas('documentacion.estadoDocumento', fn ($q) => $q->whereIn('nombre_estado', ['Pendiente', 'Rechazado']))
            ->count();
        $conDeuda = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->whereHas('cuotas', fn ($q) => $q->where('pagado', false)
                ->when($corteActivo, fn ($qq) => $qq->where('id_anio_lectivo', $corteActivo->id_anio_lectivo)))
            ->count();

        return view('admin.alumnos.index', [
            'alumnos' => $alumnos,
            'carreras' => $carreras,
            'carreraId' => $carreraId,
            'estado' => $estado,
            'busqueda' => $busqueda,
            'totalEnCarrera' => $totalEnCarrera,
            'activos' => $activos,
            'docPendiente' => $docPendiente,
            'conDeuda' => $conDeuda,
            'corteActivo' => $corteActivo,
        ]);
    }

    public function show(Request $request, Persona $persona): View
    {
        $persona->load([
            'usuario',
            'inscripcionesCarrera.carrera',
            'inscripcionesCarrera.anioLectivo',
            'inscripcionesCarrera.anioCursada',
            'inscripcionesCarrera.turnoCursada',
            'inscripcionesCarrera.condicion',
            'inscripcionesCarrera.estadoInscripcion',
            'historialAlumno.materia.nombreMateria',
            'historialAlumno.materia.anioCursada',
            'historialAlumno.materia.regimen',
            'historialAlumno.condicion',
            'cuotas.mes',
            'cuotas.anioLectivo',
            'documentacion.documentoRequisito',
        ]);

        // Carrera que se está viendo. La elegida va primera en la lista (la vista
        // usa la primera inscripción) y el historial y las cuotas se filtran por ella.
        $carreraPedida = $request->integer('carrera') ?: null;
        $inscripcion = $this->inscripcionDe($persona, $carreraPedida) ?? $this->inscripcionDe($persona, $carreraPedida, null);
        $idCarrera = $inscripcion?->id_carrera;
        $carrerasAlumno = $persona->inscripcionesCarrera->pluck('carrera')->filter()->unique('id_carrera')->values();
        $persona->setRelation('inscripcionesCarrera', $persona->inscripcionesCarrera->sortByDesc(fn ($i) => $i->id_carrera === $idCarrera)->values());
        $persona->setRelation('historialAlumno', $persona->historialAlumno->filter(fn ($h) => $h->materia?->id_carrera === $idCarrera)->values());
        $persona->setRelation('cuotas', $persona->cuotas->where('id_carrera', $idCarrera)->values());

        $fichaSecciones = FichaSeccion::where('activo', true)
            ->with(['campos' => fn ($q) => $q->where('activo', true)])
            ->orderBy('orden')
            ->get();
        $fichaRespuestas = $persona->fichaRespuestas()->get()->keyBy('id_campo');

        $progresoPromocion = $this->progresoPromocion($persona, $idCarrera);

        // Historial académico: datos para mostrarlo como un analítico —
        // solo consulta, la carga/corrección se hace desde "Cargar historial
        // previo" o mientras el alumno cursa/rinde.
        $inscripcionOriginal = $persona->inscripcionesCarrera->sortBy('id_inscripcion_carrera')->first();
        $anioIngreso = $persona->anioIngresoCarrera();

        $actasPorMateria = DetalleActa::where('id_persona_alumno', $persona->id_persona)
            ->whereHas('acta', fn ($q) => $q->where('estado', 'generada'))
            ->with('acta.mesaExamen')
            ->get()
            ->keyBy(fn (DetalleActa $d) => $d->acta->mesaExamen->id_materia);

        $inscripcionActiva = $persona->inscripcionesCarrera->first();
        $totalMateriasPlan = $inscripcionActiva
            ? $inscripcionActiva->materiasDelPlan()->where('activa', true)->count()
            : 0;
        $materiasAprobadasTotal = $persona->historialAlumno
            ->filter(fn (HistorialAlumno $h) => CondicionAlumno::esAprobada($h->condicion?->nombre_condicion))
            ->pluck('id_materia')
            ->unique()
            ->count();
        $porcentajeAvance = $totalMateriasPlan > 0 ? (int) round($materiasAprobadasTotal / $totalMateriasPlan * 100) : 0;

        $montoActualCuota = ConfiguracionCuota::de(CorteActivo::id(), $idCarrera)?->montoVigente();

        return view('admin.alumnos.show', [
            'alumno' => $persona,
            'fichaSecciones' => $fichaSecciones,
            'fichaRespuestas' => $fichaRespuestas,
            'progresoPromocion' => $progresoPromocion,
            'anioIngreso' => $anioIngreso,
            'actasPorMateria' => $actasPorMateria,
            'totalMateriasPlan' => $totalMateriasPlan,
            'materiasAprobadasTotal' => $materiasAprobadasTotal,
            'porcentajeAvance' => $porcentajeAvance,
            'montoActualCuota' => $montoActualCuota,
            'carrerasAlumno' => $carrerasAlumno,
            'idCarrera' => $idCarrera,
            // Para "Inscribir en otra carrera": solo las carreras activas en las que todavía no está.
            'carrerasDisponibles' => Carrera::with('turnos')->whereHas('estadoCarrera', fn ($q) => $q->where('nombre_estado', 'Activa'))
                ->whereNotIn('id_carrera', $carrerasAlumno->pluck('id_carrera'))->orderBy('nombre_carrera')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'aniosCursada' => AnioCursada::orderBy('id_anio_cursada')->get(),
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
            'corteActivoId' => CorteActivo::id(),
        ] + $this->datosCambioDePlan($request, $inscripcion));
    }

    // Para el panel "Pasar a otro plan": planes destino, el elegido y la vista previa.
    private function datosCambioDePlan(Request $request, ?InscripcionCarrera $inscripcion): array
    {
        $destinos = $inscripcion
            ? $inscripcion->carrera->planes()->get()->filter(fn ($p) => $p->estado() === 'Vigente' && $p->id_plan_estudio !== $inscripcion->id_plan_estudio)->values()
            : collect();
        $destino = $destinos->firstWhere('id_plan_estudio', $request->integer('plan_destino')) ?? $destinos->first();

        return [
            'planActual' => $inscripcion?->plan,
            'planesDestino' => $destinos,
            'planDestino' => $destino,
            'vistaPreviaPlan' => $destino ? CambioDePlan::vistaPrevia($inscripcion, $destino) : null,
            'impedimentoPlan' => $destino ? CambioDePlan::impedimento($inscripcion, $destino) : null,
        ];
    }

    public function cambiarPlan(Request $request, Persona $persona): RedirectResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_plan_destino' => ['required', 'exists:plan_estudio,id_plan_estudio'],
        ]);

        $inscripcion = $this->inscripcionDe($persona, (int) $data['id_carrera']);
        $destino = \App\Models\PlanEstudio::findOrFail($data['id_plan_destino']);
        abort_unless($inscripcion, 404);

        if ($motivo = CambioDePlan::impedimento($inscripcion, $destino)) {
            return back()->withErrors(['plan' => $motivo]);
        }

        $copiadas = CambioDePlan::aplicar($inscripcion, $destino);

        return back()->with('status', "Se pasó a {$persona->nombre} {$persona->apellido} al {$destino->nombre}. Se le reconocieron {$copiadas} registro(s) de su historial.");
    }

    // Inscribe a un alumno que ya existe en otra carrera. Desde ahí, cada
    // carrera tiene sus cuotas, su matrícula y su bloqueo por deuda.
    public function inscribirCarrera(Request $request, Persona $persona): RedirectResponse
    {
        $data = $request->validate([
            'carrera_id' => ['required', 'exists:carrera,id_carrera',
                Rule::unique('inscripcion_carrera', 'id_carrera')->where('id_persona_alumno', $persona->id_persona)],
            'anio_cursada_id' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'anio_lectivo_id' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'turno_cursada_id' => ['required', 'exists:turno_cursada,id_turno_cursada'],
        ], ['carrera_id.unique' => 'El alumno ya está inscripto en esa carrera.']);

        $plan = $this->planParaInscribir($data['carrera_id'], $data['anio_lectivo_id']);
        $this->validarTurnoDeCarrera($data['carrera_id'], $data['turno_cursada_id']);

        InscripcionCarrera::create([
            'id_persona_alumno' => $persona->id_persona,
            'id_carrera' => $data['carrera_id'],
            'id_plan_estudio' => $plan->id_plan_estudio,
            'id_anio_cursada' => $data['anio_cursada_id'],
            'id_anio_lectivo' => $data['anio_lectivo_id'],
            'id_turno_cursada' => $data['turno_cursada_id'],
            'id_condicion' => CondicionAlumno::where('nombre_condicion', 'Regular')->value('id_condicion'),
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Activo')->value('id_estado_inscripcion'),
            'id_secretario_registra' => Auth::user()->id_persona,
        ]);

        return redirect()->route('admin.alumnos.show', [$persona, 'carrera' => $data['carrera_id']])
            ->with('status', 'Se inscribió al alumno en ' . Carrera::find($data['carrera_id'])->nombre_carrera
                . '. Generale la matrícula y las cuotas de esta carrera desde Registrar Cuotas.');
    }

    // El turno elegido tiene que ser uno de los que ofrece la carrera.
    private function validarTurnoDeCarrera(int|string $idCarrera, int|string $idTurno): void
    {
        if (! Carrera::find($idCarrera)?->ofreceTurno($idTurno)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['turno_cursada_id' => 'La carrera no se dicta en ese turno. Elegí uno de sus turnos (se configuran en Carreras → Editar).']);
        }
    }

    // Plan al que entra un alumno nuevo en esa carrera y año lectivo. Si ninguno
    // acepta inscripciones (cerrado o caducado), no se lo puede inscribir.
    private function planParaInscribir(int|string $idCarrera, int|string $idAnioLectivo): \App\Models\PlanEstudio
    {
        $anio = (int) AnioLectivo::find($idAnioLectivo)?->anio;
        $plan = Carrera::find($idCarrera)?->planParaInscribir($anio);

        if (! $plan) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'carrera_id' => "La carrera no tiene un plan de estudios que acepte inscripciones en {$anio}. Revisá la vigencia en Carreras → Planes.",
            ]);
        }

        return $plan;
    }

    // Inscripción del alumno en una carrera (o la más reciente si no se indica),
    // filtrando por estado ('Activo', 'Baja' o null = cualquiera).
    private function inscripcionDe(Persona $persona, ?int $idCarrera, ?string $estado = 'Activo'): ?InscripcionCarrera
    {
        return $persona->inscripcionesCarrera()
            ->when($idCarrera, fn ($q) => $q->where('id_carrera', $idCarrera))
            ->when($estado, fn ($q) => $q->whereHas('estadoInscripcion', fn ($e) => $e->where('nombre_estado', $estado)))
            ->latest('id_inscripcion_carrera')
            ->first();
    }

    public function create(): View
    {
        return view('admin.alumnos.create', ['datos' => session(self::SESSION_KEY . '.personales', [])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'dni' => ['required', 'digits:8', Rule::unique('persona', 'dni')],
            'apellido' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            // email:rfc,dns → además del formato, verifica que el dominio
            // (lo que va después de la @) exista y reciba correo. Frena
            // errores de tipeo como "gmial.com" o dominios inventados.
            'email' => ['required', 'email:rfc,dns', 'max:100', Rule::unique('usuario', 'email')],
        ], [
            'email.email' => 'El correo no es válido o su dominio no existe (revisá lo que va después de la @).',
        ]);

        session([self::SESSION_KEY . '.personales' => $datos]);

        return redirect()->route('admin.alumnos.academico');
    }

    public function academicoForm(): View|RedirectResponse
    {
        if (! session(self::SESSION_KEY . '.personales')) {
            return redirect()->route('admin.alumnos.create');
        }

        return view('admin.alumnos.academico', [
            'personales' => session(self::SESSION_KEY . '.personales'),
            'academicos' => session(self::SESSION_KEY . '.academicos', []),
            'carreras' => Carrera::with('turnos')->whereHas('estadoCarrera', fn ($q) => $q->where('nombre_estado', 'Activa'))->orderBy('nombre_carrera')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'corteActivoId' => CorteActivo::id(),
            'aniosCursada' => AnioCursada::orderBy('id_anio_cursada')->get(),
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
        ]);
    }

    public function academicoStore(Request $request): RedirectResponse
    {
        abort_unless(session(self::SESSION_KEY . '.personales'), 419);

        $datos = $request->validate([
            'carrera_id' => ['required', 'exists:carrera,id_carrera'],
            'anio_cursada_id' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'anio_lectivo_id' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'turno_cursada_id' => ['required', 'exists:turno_cursada,id_turno_cursada'],
        ]);

        $this->planParaInscribir($datos['carrera_id'], $datos['anio_lectivo_id']); // que haya un plan abierto ese año
        $this->validarTurnoDeCarrera($datos['carrera_id'], $datos['turno_cursada_id']);
        session([self::SESSION_KEY . '.academicos' => $datos]);

        return redirect()->route('admin.alumnos.acceso');
    }

    public function acceso(): View|RedirectResponse
    {
        if (! session(self::SESSION_KEY . '.personales') || ! session(self::SESSION_KEY . '.academicos')) {
            return redirect()->route('admin.alumnos.create');
        }

        $personales = session(self::SESSION_KEY . '.personales');

        if (! session(self::SESSION_KEY . '.clave')) {
            session([self::SESSION_KEY . '.clave' => $this->generarClave($personales['dni'])]);
        }

        return view('admin.alumnos.acceso', [
            'personales' => $personales,
            'clave' => session(self::SESSION_KEY . '.clave'),
        ]);
    }

    public function confirmar(): RedirectResponse
    {
        $personales = session(self::SESSION_KEY . '.personales');
        $academicos = session(self::SESSION_KEY . '.academicos');
        $clave = session(self::SESSION_KEY . '.clave');

        // Si se perdieron los datos de los pasos anteriores (se tocó
        // "Registrar" dos veces y el primer clic ya lo registró, o se cerró la
        // sesión en otra pestaña) no se muestra el error 419 en blanco: se
        // vuelve al listado con un aviso para revisar si quedó registrado.
        if (! $personales || ! $academicos || ! $clave) {
            return redirect()->route('admin.alumnos.index')->withErrors([
                'registro' => 'No se encontraron los datos del registro (puede que ya se haya registrado con el primer clic, o que la sesión se haya cerrado). Buscá al alumno en el listado y, si no aparece, registralo de nuevo.',
            ]);
        }

        $persona = Persona::create([
            'dni' => $personales['dni'],
            'nombre' => $personales['nombre'],
            'apellido' => $personales['apellido'],
        ]);

        Usuario::create([
            'id_persona' => $persona->id_persona,
            'email' => $personales['email'],
            'password' => Hash::make($clave),
            'debe_cambiar_clave' => true,
            'id_rol' => Rol::where('nombre_rol', 'Alumno')->value('id_rol'),
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
        ]);

        InscripcionCarrera::create([
            'id_persona_alumno' => $persona->id_persona,
            'id_carrera' => $academicos['carrera_id'],
            'id_plan_estudio' => $this->planParaInscribir($academicos['carrera_id'], $academicos['anio_lectivo_id'])->id_plan_estudio,
            'id_anio_cursada' => $academicos['anio_cursada_id'],
            'id_anio_lectivo' => $academicos['anio_lectivo_id'],
            'id_turno_cursada' => $academicos['turno_cursada_id'],
            'id_condicion' => CondicionAlumno::where('nombre_condicion', 'Regular')->value('id_condicion'),
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Activo')->value('id_estado_inscripcion'),
            'id_secretario_registra' => Auth::user()->id_persona,
        ]);

        session()->forget(self::SESSION_KEY);

        // Si el correo falla (ej. servidor de mail caído) el alumno igual queda
        // registrado: la secretaría ve la clave en pantalla y se la entrega.
        try {
            Mail::to($personales['email'])->send(new ClaveInicialMail($persona->nombre, $personales['email'], $clave));
            $aviso = "Se envió la clave inicial a {$personales['email']}.";
        } catch (\Throwable $e) {
            report($e);
            $aviso = 'No se pudo enviar el mail: entregale la clave al alumno.';
        }

        return redirect()->route('admin.alumnos.show', $persona)
            ->with('status', "Alumno registrado correctamente. Clave inicial: {$clave}. {$aviso}");
    }
//baja del alumno y reactivacion de la inscripcion a la carrera, con la baja del usuario si lo tuviera
    public function baja(Request $request, Persona $persona): RedirectResponse  
    {
        $inscripcion = $this->inscripcionDe($persona, $request->integer('id_carrera') ?: null);

        abort_unless($inscripcion, 404);

        $inscripcion->update([
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Baja')->value('id_estado_inscripcion'),
            'fecha_baja' => now(),
            'id_secretario_baja' => Auth::user()->id_persona,
        ]);

        // Las cuotas se frenan: se borran las impagas de los meses siguientes a la
        // baja. Las anteriores y la de este mes quedan (las tiene que pagar).
        $persona->cuotas()->where('id_carrera', $inscripcion->id_carrera)->where('pagado', false)
            ->whereDate('fecha_vencimiento', '>', now()->endOfMonth())->delete();

        // Si el alumno tiene usuario para entrar al sistema, se lo desactiva
        // también: dar de baja la inscripción no debía impedir el login por sí
        // solo, y ahora sí lo hace (ver LoginController::login).
        // Si sigue activo en otra carrera, el usuario no se desactiva.
        if (! $this->inscripcionDe($persona, null)) {
            $persona->usuario?->update([
                'id_estado' => EstadoUsuario::where('nombre_estado', 'Inactivo')->value('id_estado'),
            ]);
        }

        return back()->with('status', "Se dio de baja a {$persona->nombre} {$persona->apellido} en {$inscripcion->carrera->nombre_carrera}.");
    }

    public function alta(Request $request, Persona $persona): RedirectResponse
    {
        $inscripcion = $this->inscripcionDe($persona, $request->integer('id_carrera') ?: null, 'Baja');

        abort_unless($inscripcion, 404);

        $inscripcion->update([
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Activo')->value('id_estado_inscripcion'),
            'fecha_baja' => null,
            'id_secretario_baja' => null,
        ]);

        // Si el alumno tenía usuario para entrar al sistema, se lo reactiva
        // también (ver AlumnoController::baja y LoginController::login).
        $persona->usuario?->update([
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
        ]);

        return back()->with('status', "Se dio de alta nuevamente a {$persona->nombre} {$persona->apellido}.");
    }

    public function promoverAnio(Request $request, Persona $persona): RedirectResponse
    {
        $progreso = $this->progresoPromocion($persona, $request->integer('id_carrera') ?: null);

        abort_unless($progreso, 404, 'Este alumno no tiene una inscripción activa, o ya está en el último año del plan.');

        if (! $progreso['puede']) {
            return back()->withErrors([
                'promocion' => "No se puede promover: tiene {$progreso['aprobadas']} de {$progreso['total']} materias de su año aprobadas (hace falta la mayoría).",
            ]);
        }

        $progreso['inscripcion']->update(['id_anio_cursada' => $progreso['proximoAnio']->id_anio_cursada]);

        return back()->with('status', "Se promovió a {$persona->nombre} {$persona->apellido} a {$progreso['proximoAnio']->nombre_anio}.");
    }

    public function actualizarPlazoRegularidad(Request $request, Persona $persona, HistorialAlumno $historial): RedirectResponse
    {
        abort_unless($historial->id_persona_alumno === $persona->id_persona, 404);

        $data = $request->validate([
            'anios_plazo_regularidad' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $historial->update($data);

        return back()->with('status', 'Plazo de regularidad actualizado correctamente.');
    }

    public function materias(Request $request, Persona $persona): View
    {
        $inscripcion = $this->inscripcionDe($persona, $request->integer('carrera') ?: null);

        abort_unless($inscripcion, 404, 'El alumno no tiene una inscripción a carrera activa.');

        // A diferencia del autoservicio del alumno, acá se listan TODAS las
        // materias de la carrera (no solo las del año/cuatrimestre actual):
        // secretaría necesita poder inscribir en casos excepcionales
        // (recursada, materias de otro año, etc.), por eso el bloqueo se
        // muestra solo como aviso, no impide tildar la materia.
        $materias = $inscripcion->materiasDelPlan()
            ->where('activa', true)
            ->with(['nombreMateria', 'anioCursada', 'periodo', 'requisitos'])
            ->get()
            ->sortBy(['id_anio_cursada', 'nombre'])
            ->map(fn (Materia $materia) => [
                'materia' => $materia,
                'bloqueo' => $materia->bloqueoParaCursar($persona),
                'yaCursando' => $persona->historialAlumno->firstWhere('id_materia', $materia->id_materia),
            ]);

        return view('admin.alumnos.materias', [
            'alumno' => $persona,
            'inscripcion' => $inscripcion,
            'materias' => $materias,
        ]);
    }

    public function materiasStore(Request $request, Persona $persona): RedirectResponse
    {
        $inscripcion = $this->inscripcionDe($persona, $request->integer('id_carrera') ?: null);

        abort_unless($inscripcion, 404);

        $data = $request->validate([
            'materias' => ['required', 'array', 'min:1'],
            'materias.*' => ['integer', Rule::exists('materia', 'id_materia')->where('id_plan_estudio', $inscripcion->id_plan_estudio)],
        ]);

        $anioLectivoActivo = AnioLectivo::activo();
        abort_unless($anioLectivoActivo, 500, 'No hay un año lectivo activo configurado.');

        $idCondicionCursando = CondicionAlumno::where('nombre_condicion', 'Cursando')->value('id_condicion');
        $yaInscriptas = $persona->historialAlumno()->pluck('id_materia');

        $inscriptas = 0;
        foreach (array_diff($data['materias'], $yaInscriptas->all()) as $idMateria) {
            HistorialAlumno::create([
                'id_persona_alumno' => $persona->id_persona,
                'id_materia' => $idMateria,
                'id_anio_lectivo' => $anioLectivoActivo->id_anio_lectivo,
                'id_condicion' => $idCondicionCursando,
                'fecha_ultima_modificacion' => now(),
            ]);
            $inscriptas++;
        }

        return back()->with('status', "Se inscribió al alumno a {$inscriptas} materia(s).");
    }

    public function actualizarCondicionHistorial(Request $request, Persona $persona, HistorialAlumno $historial): RedirectResponse
    {
        abort_unless($historial->id_persona_alumno === $persona->id_persona, 404);

        // Una materia ya cargada en el historial queda bloqueada: solo
        // Dirección la puede rectificar, y con motivo (ver HistorialPrevioController::update).
        abort_unless($request->user()->accedeADireccion(), 403, 'Esta materia ya está cargada: solo Dirección puede rectificarla.');

        $data = $request->validate([
            'id_condicion' => ['required', 'exists:condicion_alumno,id_condicion'],
            'nota_cursada' => ['nullable', 'integer', 'between:1,10'],
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'nota_cursada.integer' => 'La nota tiene que ser un número entero del 1 al 10, sin decimales.',
            'motivo.required' => 'Para rectificar una materia ya cargada hace falta indicar el motivo.',
            'motivo.min' => 'El motivo es muy corto: contá brevemente por qué se rectifica.',
        ]);

        $condicionNombre = CondicionAlumno::find($data['id_condicion'])?->nombre_condicion;

        // Mismos rangos que al cargar el historial: Promocionada de 7 a 10,
        // Final Aprobado y Equivalencia de 4 a 10, Final Desaprobado de 1 a 3.
        if ($rango = CondicionAlumno::RANGO_NOTA[$condicionNombre] ?? null) {
            $nota = (float) ($data['nota_cursada'] ?? 0);

            if ($nota < $rango[0] || $nota > $rango[1]) {
                return back()->withErrors(['nota_cursada' => 'Para "' . CondicionAlumno::etiqueta($condicionNombre) . "\" la nota tiene que ser de {$rango[0]} a {$rango[1]}."]);
            }
        }

        if ($condicionNombre === 'Libre' && $data['nota_cursada'] !== null && (float) $data['nota_cursada'] >= 4) {
            return back()->withErrors(['nota_cursada' => 'No se puede guardar la materia como Libre con una nota de aprobación (4 o más).']);
        }

        Auditor::conMotivo($data['motivo']);

        $historial->update([
            'id_condicion' => $data['id_condicion'],
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'fecha_ultima_modificacion' => now(),
        ]);

        return back()->with('status', 'Condición académica actualizada correctamente.');
    }

    /**
     * Cuántas materias de SU año (de su carrera) tiene aprobadas un alumno,
     * y si eso le alcanza (mayoría) para promoverlo al año siguiente del plan.
     * Null = no tiene inscripción activa, o ya está en el último año (no hay
     * a dónde promoverlo).
     */
    private function progresoPromocion(Persona $persona, ?int $idCarrera = null): ?array
    {
        $inscripcion = $this->inscripcionDe($persona, $idCarrera);

        // Sin año de cursada cargado no hay desde dónde promover (inscripciones viejas lo tienen vacío).
        if (! $inscripcion || ! $inscripcion->id_anio_cursada) {
            return null;
        }

        $proximoAnio = AnioCursada::where('id_anio_cursada', '>', $inscripcion->id_anio_cursada)
            ->orderBy('id_anio_cursada')
            ->first();

        if (! $proximoAnio) {
            return null;
        }

        $total = $inscripcion->materiasDelPlan()
            ->where('id_anio_cursada', $inscripcion->id_anio_cursada)
            ->where('activa', true)
            ->count();

        $idsAprobada = CondicionAlumno::whereIn('nombre_condicion', CondicionAlumno::APROBADA)->pluck('id_condicion');

        $aprobadas = HistorialAlumno::where('id_persona_alumno', $persona->id_persona)
            ->whereIn('id_condicion', $idsAprobada)
            ->whereHas('materia', fn ($q) => $q
                ->where('id_carrera', $inscripcion->id_carrera)
                ->where('id_anio_cursada', $inscripcion->id_anio_cursada))
            ->distinct('id_materia')
            ->count('id_materia');

        return [
            'inscripcion' => $inscripcion,
            'proximoAnio' => $proximoAnio,
            'total' => $total,
            'aprobadas' => $aprobadas,
            'puede' => $total > 0 && $aprobadas > ($total / 2),
        ];
    }

    private function generarClave(string $dni): string
    {
        $ultimosCuatro = substr($dni, -4);
        // 8 caracteres alfanuméricos random (~2×10^12 combinaciones) en vez de 3,
        // para que la clave inicial no sea fuerza-bruteable en combinación con el DNI.
        $codigo = strtoupper(Str::random(8));

        return "ISG-{$ultimosCuatro}-{$codigo}";
    }
}
