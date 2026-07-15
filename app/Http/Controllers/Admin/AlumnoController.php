<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\InscripcionCarrera;
use App\Models\User;
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

        $query = User::where('rol', 'alumno')
            ->with(['inscripcionesCarrera' => fn ($q) => $q->with('carrera')->latest()]);

        if ($busqueda) {
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")
                ->orWhere('dni', 'like', "%{$busqueda}%"));
        }

        if ($carreraId) {
            $query->whereHas('inscripcionesCarrera', fn ($q) => $q->where('carrera_id', $carreraId));
        }

        $alumnos = $query->orderBy('apellido')->paginate(8)->withQueryString();

        $carreras = Carrera::orderBy('nombre')->get();

        $inscripcionesCarrera = InscripcionCarrera::when($carreraId, fn ($q) => $q->where('carrera_id', $carreraId));
        $totalEnCarrera = (clone $inscripcionesCarrera)->count();
        $activos = (clone $inscripcionesCarrera)->where('estado', 'activo')->count();
        $docPendiente = User::where('rol', 'alumno')->whereHas('documentosAlumno', fn ($q) => $q->whereIn('estado', ['pendiente', 'rechazado']))->count();
        $conDeuda = User::where('rol', 'alumno')->whereHas('cuotas', fn ($q) => $q->where('estado', 'vencido'))->count();

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

    public function show(User $user): View
    {
        $user->load([
            'inscripcionesCarrera.carrera',
            'historialMaterias.materia',
            'cuotas',
            'documentosAlumno.documentoRequisito',
        ]);

        return view('admin.alumnos.show', ['alumno' => $user]);
    }

    public function create(): View
    {
        return view('admin.alumnos.create', ['datos' => session(self::SESSION_KEY . '.personales', [])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'dni' => ['required', 'string', 'max:20', Rule::unique('users', 'dni')],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'localidad' => ['nullable', 'string', 'max:100'],
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
            'carreras' => Carrera::where('estado', 'activa')->orderBy('nombre')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
        ]);
    }

    public function academicoStore(Request $request): RedirectResponse
    {
        abort_unless(session(self::SESSION_KEY . '.personales'), 419);

        $datos = $request->validate([
            'carrera_id' => ['required', 'exists:carreras,id'],
            'anio_lectivo_id' => ['required', 'exists:anios_lectivos,id'],
            'anio_actual' => ['required', 'integer', 'min:1', 'max:5'],
            'turno' => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
            'condicion' => ['required', Rule::in(['regular', 'promocion', 'libre'])],
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

        $alumno = User::create([
            'dni' => $personales['dni'],
            'nombre' => $personales['nombre'],
            'apellido' => $personales['apellido'],
            'fecha_nacimiento' => $personales['fecha_nacimiento'],
            'telefono' => $personales['telefono'] ?? null,
            'direccion' => $personales['direccion'] ?? null,
            'localidad' => $personales['localidad'] ?? null,
            'email' => $personales['email'],
            'password' => Hash::make($clave),
            'rol' => 'alumno',
        ]);

        InscripcionCarrera::create([
            'user_id' => $alumno->id,
            'carrera_id' => $academicos['carrera_id'],
            'anio_lectivo_id' => $academicos['anio_lectivo_id'],
            'anio_actual' => $academicos['anio_actual'],
            'turno' => $academicos['turno'],
            'condicion' => $academicos['condicion'],
            'estado' => 'activo',
            'secretario_registra_id' => Auth::id(),
        ]);

        session()->forget(self::SESSION_KEY);

        return redirect()->route('admin.alumnos.show', $alumno)
            ->with('status', "Alumno registrado correctamente. Clave inicial: {$clave}");
    }

    public function baja(Request $request, User $user): RedirectResponse
    {
        $inscripcion = $user->inscripcionesCarrera()->where('estado', 'activo')->first();

        abort_unless($inscripcion, 404);

        $inscripcion->update([
            'estado' => 'baja',
            'fecha_baja' => now(),
            'secretario_baja_id' => Auth::id(),
        ]);

        return back()->with('status', "Se dio de baja a {$user->nombre} {$user->apellido}.");
    }

    private function generarClave(string $dni): string
    {
        $ultimosCuatro = substr($dni, -4);
        $codigo = strtoupper(Str::random(3));

        return "ISG-{$ultimosCuatro}-{$codigo}";
    }
}
