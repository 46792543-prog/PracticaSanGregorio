<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClaveInicialMail;
use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\EstadoInscripcion;
use App\Models\EstadoUsuario;
use App\Models\FichaSeccion;
use App\Models\HistorialAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Materia;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
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

    public function show(Persona $persona): View
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
            'historialAlumno.condicion',
            'cuotas.mes',
            'documentacion.documentoRequisito',
        ]);

        $condiciones = CondicionAlumno::whereIn('nombre_condicion', ['Pendiente', 'Cursando', 'Regular', 'Aprobada'])->orderBy('id_condicion')->get();

        $fichaSecciones = FichaSeccion::where('activo', true)
            ->with(['campos' => fn ($q) => $q->where('activo', true)])
            ->orderBy('orden')
            ->get();
        $fichaRespuestas = $persona->fichaRespuestas()->get()->keyBy('id_campo');

        $progresoPromocion = $this->progresoPromocion($persona);

        return view('admin.alumnos.show', [
            'alumno' => $persona,
            'condiciones' => $condiciones,
            'fichaSecciones' => $fichaSecciones,
            'fichaRespuestas' => $fichaRespuestas,
            'progresoPromocion' => $progresoPromocion,
        ]);
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
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')],
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
            'carreras' => Carrera::whereHas('estadoCarrera', fn ($q) => $q->where('nombre_estado', 'Activa'))->orderBy('nombre_carrera')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'corteActivoId' => CorteActivo::id(),
            'aniosCursada' => AnioCursada::orderBy('id_anio_cursada')->get(),
        ]);
    }

    public function academicoStore(Request $request): RedirectResponse
    {
        abort_unless(session(self::SESSION_KEY . '.personales'), 419);

        $datos = $request->validate([
            'carrera_id' => ['required', 'exists:carrera,id_carrera'],
            'anio_cursada_id' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'anio_lectivo_id' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
        ]);

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

        abort_unless($personales && $academicos && $clave, 419);

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
            'id_anio_cursada' => $academicos['anio_cursada_id'],
            'id_anio_lectivo' => $academicos['anio_lectivo_id'],
            'id_turno_cursada' => null,
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

    public function baja(Request $request, Persona $persona): RedirectResponse
    {
        $inscripcion = $persona->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->first();

        abort_unless($inscripcion, 404);

        $inscripcion->update([
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Baja')->value('id_estado_inscripcion'),
            'fecha_baja' => now(),
            'id_secretario_baja' => Auth::user()->id_persona,
        ]);

        // Si el alumno tiene usuario para entrar al sistema, se lo desactiva
        // también: dar de baja la inscripción no debía impedir el login por sí
        // solo, y ahora sí lo hace (ver LoginController::login).
        $persona->usuario?->update([
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Inactivo')->value('id_estado'),
        ]);

        return back()->with('status', "Se dio de baja a {$persona->nombre} {$persona->apellido}.");
    }

    public function alta(Request $request, Persona $persona): RedirectResponse
    {
        $inscripcion = $persona->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Baja'))
            ->latest('id_inscripcion_carrera')
            ->first();

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
        $progreso = $this->progresoPromocion($persona);

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

    public function materias(Persona $persona): View
    {
        $inscripcion = $persona->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();

        abort_unless($inscripcion, 404, 'El alumno no tiene una inscripción a carrera activa.');

        // A diferencia del autoservicio del alumno, acá se listan TODAS las
        // materias de la carrera (no solo las del año/cuatrimestre actual):
        // secretaría necesita poder inscribir en casos excepcionales
        // (recursada, materias de otro año, etc.), por eso el bloqueo se
        // muestra solo como aviso, no impide tildar la materia.
        $materias = $inscripcion->carrera->materias()
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
        $inscripcion = $persona->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();

        abort_unless($inscripcion, 404);

        $data = $request->validate([
            'materias' => ['required', 'array', 'min:1'],
            'materias.*' => ['integer', Rule::exists('materia', 'id_materia')->where('id_carrera', $inscripcion->id_carrera)],
        ]);

        $anioLectivoActivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))->first();
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

        $data = $request->validate([
            'id_condicion' => ['required', 'exists:condicion_alumno,id_condicion'],
            'nota_cursada' => ['nullable', 'numeric', 'between:1,10'],
        ]);

        $condicionNombre = CondicionAlumno::find($data['id_condicion'])?->nombre_condicion;

        if ($condicionNombre === 'Aprobada' && (float) ($data['nota_cursada'] ?? 0) < 4) {
            return back()->withErrors(['nota_cursada' => 'No se puede guardar la materia como Aprobada con una nota inferior a 4.']);
        }

        if ($condicionNombre === 'Libre' && $data['nota_cursada'] !== null && (float) $data['nota_cursada'] >= 4) {
            return back()->withErrors(['nota_cursada' => 'No se puede guardar la materia como Libre con una nota de aprobación (4 o más).']);
        }

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
    private function progresoPromocion(Persona $persona): ?array
    {
        $inscripcion = $persona->inscripcionesCarrera()
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();

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

        $total = Materia::where('id_carrera', $inscripcion->id_carrera)
            ->where('id_anio_cursada', $inscripcion->id_anio_cursada)
            ->where('activa', true)
            ->count();

        $idAprobada = CondicionAlumno::where('nombre_condicion', 'Aprobada')->value('id_condicion');

        $aprobadas = HistorialAlumno::where('id_persona_alumno', $persona->id_persona)
            ->where('id_condicion', $idAprobada)
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
