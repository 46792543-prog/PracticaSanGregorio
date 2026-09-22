<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\EstadoInscripcion;
use App\Models\EstadoUsuario;
use App\Models\HistorialAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Materia;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TurnoCursada;
use App\Models\Usuario;
use App\Support\CorteActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $seguimientos = $persona->seguimientos()->with('autor')->latest()->get();
        $condiciones = CondicionAlumno::whereIn('nombre_condicion', ['Pendiente', 'Cursando', 'Regular', 'Aprobada'])->orderBy('id_condicion')->get();

        return view('admin.alumnos.show', ['alumno' => $persona, 'seguimientos' => $seguimientos, 'condiciones' => $condiciones]);
    }

    public function create(): View
    {
        return view('admin.alumnos.create', ['datos' => session(self::SESSION_KEY . '.personales', [])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'dni' => ['required', 'digits:8', Rule::unique('persona', 'dni')],
            'apellido' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'nombre' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:' . now()->subYears(17)->toDateString()],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')],
            'localidad' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
        ], [
            'fecha_nacimiento.before_or_equal' => 'El alumno debe tener al menos 17 años.',
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
            'turnos' => TurnoCursada::orderBy('id_turno_cursada')->get(),
            'condiciones' => CondicionAlumno::whereIn('nombre_condicion', ['Regular', 'Promoción', 'Libre'])->orderBy('id_condicion')->get(),
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
            'turno_cursada_id' => ['required', 'exists:turno_cursada,id_turno_cursada'],
            'condicion_id' => ['required', 'exists:condicion_alumno,id_condicion'],
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
            'fecha_nacimiento' => $personales['fecha_nacimiento'],
            'telefono' => $personales['telefono'] ?? null,
            'direccion' => $personales['direccion'] ?? null,
            'localidad' => $personales['localidad'] ?? null,
        ]);

        Usuario::create([
            'id_persona' => $persona->id_persona,
            'email' => $personales['email'],
            'password' => Hash::make($clave),
            'id_rol' => Rol::where('nombre_rol', 'Alumno')->value('id_rol'),
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
        ]);

        InscripcionCarrera::create([
            'id_persona_alumno' => $persona->id_persona,
            'id_carrera' => $academicos['carrera_id'],
            'id_anio_cursada' => $academicos['anio_cursada_id'],
            'id_anio_lectivo' => $academicos['anio_lectivo_id'],
            'id_turno_cursada' => $academicos['turno_cursada_id'],
            'id_condicion' => $academicos['condicion_id'],
            'id_estado_inscripcion' => EstadoInscripcion::where('nombre_estado', 'Activo')->value('id_estado_inscripcion'),
            'id_secretario_registra' => Auth::user()->id_persona,
        ]);

        session()->forget(self::SESSION_KEY);

        return redirect()->route('admin.alumnos.show', $persona)
            ->with('status', "Alumno registrado correctamente. Clave inicial: {$clave}");
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

        return back()->with('status', "Se dio de alta nuevamente a {$persona->nombre} {$persona->apellido}.");
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

        $historial->update([
            'id_condicion' => $data['id_condicion'],
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'fecha_ultima_modificacion' => now(),
        ]);

        return back()->with('status', 'Condición académica actualizada correctamente.');
    }

    public function storeSeguimiento(Request $request, Persona $persona): RedirectResponse
    {
        $data = $request->validate([
            'texto' => ['required', 'string', 'max:1000'],
        ]);

        $persona->seguimientos()->create([
            'id_persona_autor' => Auth::user()->id_persona,
            'texto' => $data['texto'],
        ]);

        return back()->with('status', 'Nota de seguimiento agregada.');
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
