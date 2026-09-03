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
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TurnoCursada;
use App\Models\Usuario;
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

        $alumnos = $query->orderBy('apellido')->paginate(8)->withQueryString();

        $carreras = Carrera::orderBy('nombre_carrera')->get();

        $inscripcionesCarrera = InscripcionCarrera::when($carreraId, fn ($q) => $q->where('id_carrera', $carreraId));
        $totalEnCarrera = (clone $inscripcionesCarrera)->count();
        $activos = (clone $inscripcionesCarrera)->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))->count();
        $docPendiente = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->whereHas('documentacion.estadoDocumento', fn ($q) => $q->whereIn('nombre_estado', ['Pendiente', 'Rechazado']))
            ->count();
        $conDeuda = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->whereHas('cuotas', fn ($q) => $q->where('pagado', false))
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

        return view('admin.alumnos.show', ['alumno' => $persona]);
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
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')],
            'localidad' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
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

        return back()->with('status', "Se dio de baja a {$persona->nombre} {$persona->apellido}.");
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

    private function generarClave(string $dni): string
    {
        $ultimosCuatro = substr($dni, -4);
        // 8 caracteres alfanuméricos random (~2×10^12 combinaciones) en vez de 3,
        // para que la clave inicial no sea fuerza-bruteable en combinación con el DNI.
        $codigo = strtoupper(Str::random(8));

        return "ISG-{$ultimosCuatro}-{$codigo}";
    }
}
