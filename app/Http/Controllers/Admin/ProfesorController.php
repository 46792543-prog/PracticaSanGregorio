<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\EspecialidadProfesor;
use App\Models\HorarioAsignacion;
use App\Models\Materia;
use App\Models\Persona;
use App\Models\Profesor;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\Titulo;
use App\Support\Cuil;
use App\Support\CorteActivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfesorController extends Controller
{
    public function index(Request $request): View
    {
        $anioLectivo = CorteActivo::actual() ?? AnioLectivo::orderByDesc('anio')->first();
        $sinAsignar = $request->boolean('sin_asignar');

        $asignaciones = AsignacionProfesorMateria::with('profesor.persona', 'profesor.especialidades', 'materia.carrera', 'materia.nombreMateria', 'horarios')
            ->when($anioLectivo, fn ($q) => $q->where('id_anio_lectivo', $anioLectivo->id_anio_lectivo))
            ->when($request->query('profesor'), fn ($q, $p) => $q->where('id_profesor', $p))
            ->when($request->query('dia'), fn ($q, $dia) => $q->whereHas('horarios', fn ($w) => $w->where('dia_semana', ucfirst($dia))))
            ->when($request->query('materia'), fn ($q, $m) => $q->whereHas('materia.nombreMateria', fn ($w) => $w->where('nombre', 'like', "%{$m}%")))
            ->orderByDesc('id_asignacion')
            ->paginate(15, ['*'], 'pagina_asignaciones')
            ->withQueryString();

        $materiasSinAsignar = Materia::with('carrera', 'nombreMateria', 'anioCursada')
            ->where('activa', true)
            ->when($anioLectivo, fn ($q) => $q->whereDoesntHave('asignaciones', fn ($w) => $w->where('id_anio_lectivo', $anioLectivo->id_anio_lectivo)))
            ->when($request->query('materia'), fn ($q, $m) => $q->whereHas('nombreMateria', fn ($w) => $w->where('nombre', 'like', "%{$m}%")))
            ->orderBy('id_carrera')
            ->orderBy('id_anio_cursada')
            ->paginate(15, ['*'], 'pagina_asignaciones')
            ->withQueryString();

        // Días ya tomados de cada materia, para ocultarla del combo cuando otro profesor la dicta ese día.
        $materiasOcupadas = HorarioAsignacion::with('asignacion.profesor.persona')
            ->get()
            ->map(fn ($h) => [
                'id_materia' => $h->asignacion->id_materia,
                'id_anio_lectivo' => $h->asignacion->id_anio_lectivo,
                'id_profesor' => $h->asignacion->id_profesor,
                'profesor' => $h->asignacion->profesor->apellido . ', ' . $h->asignacion->profesor->nombre,
                'dia' => $h->dia_semana,
            ])
            ->values();

        $profesores = Profesor::with('persona', 'especialidades')->get()->sortBy('apellido');

        $profesoresPaginados = Profesor::with('persona.localidadDomicilio', 'especialidades', 'titulos')
            ->join('persona', 'persona.id_persona', '=', 'profesor.id_persona')
            ->orderBy('persona.apellido')
            ->select('profesor.*')
            ->paginate(15, ['*'], 'pagina_profesores')
            ->withQueryString();

        return view('admin.profesores.index', [
            'profesores' => $profesores,
            'profesoresPaginados' => $profesoresPaginados,
            'profesoresActivos' => $profesores->where('activo', true),
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'anioLectivo' => $anioLectivo,
            'asignaciones' => $asignaciones,
            'materiasSinAsignar' => $materiasSinAsignar,
            'materiasOcupadas' => $materiasOcupadas,
            'sinAsignar' => $sinAsignar,
            'especialidades' => EspecialidadProfesor::withCount('profesores')->orderBy('nombre_especialidad')->get(),
            'titulosCatalogo' => Titulo::orderBy('nombre')->get(),
            'provincias' => Provincia::with('localidades')->orderBy('nombre')->get(),
            'filtros' => $request->only('profesor', 'dia', 'materia'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8', 'unique:persona,dni'],
            'nombre' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido' => ['required', 'string', 'max:25', 'regex:/^[\pL\s\'-]+$/u'],
            'email' => ['nullable', 'email', 'max:25', 'unique:profesor,email'],
            'id_especialidad' => ['required', 'array', 'min:1'],
            'id_especialidad.*' => ['exists:especialidad_profesor,id_especialidad'],
        ] + $this->reglasFicha(null), $this->mensajesFicha());
        $this->validarCuil($data);

        $persona = Persona::create([
            'dni' => $data['dni'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
        ] + $this->datosPersona($data));

        $profesor = Profesor::create([
            'id_persona' => $persona->id_persona,
            'email' => $data['email'] ?? null,
        ] + $this->datosFicha($data));

        $profesor->especialidades()->sync($data['id_especialidad']);
        $this->guardarTitulos($profesor, $data);

        return back()->with('status', 'Profesor agregado correctamente.');
    }

    public function update(Request $request, Profesor $profesor): RedirectResponse
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8', 'unique:persona,dni,' . $profesor->id_persona . ',id_persona'],
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'email' => ['nullable', 'email', 'max:100', 'unique:profesor,email,' . $profesor->id_profesor . ',id_profesor'],
            'id_especialidad' => ['required', 'array', 'min:1'],
            'id_especialidad.*' => ['exists:especialidad_profesor,id_especialidad'],
        ] + $this->reglasFicha($profesor ?? null), $this->mensajesFicha());
        $this->validarCuil($data);

        $profesor->persona->update([
            'dni' => $data['dni'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
        ] + $this->datosPersona($data));

        $profesor->update($this->datosFicha($data));

        $profesor->especialidades()->sync($data['id_especialidad']);
        $this->guardarTitulos($profesor, $data);

        return back()->with('status', 'Profesor actualizado correctamente.');
    }

    // ---------- Ficha del docente: CUIL, sexo, fecha de ingreso y títulos ----------

    private function reglasFicha(?Profesor $profesor): array
    {
        return [
            'cuil' => ['nullable', 'digits:11', Rule::unique('profesor', 'cuil')->ignore($profesor?->id_profesor, 'id_profesor')],
            'sexo' => ['required', Rule::in(array_keys(Profesor::SEXOS))],
            'fecha_ingreso' => ['nullable', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'fecha_nacimiento' => ['required', 'date', 'before:-20 years', 'after:-75 years'],
            'telefono' => ['nullable', 'regex:/^\d{7,15}$/'],
            'direccion' => ['nullable', 'string', 'max:40', 'regex:/^[\pL0-9\s]+$/u'],
            'id_localidad' => ['required', 'exists:localidad,id_localidad'],
            'titulos' => ['required', 'array', 'min:1', 'max:10'],
            'titulos.*' => ['exists:titulo,id_titulo'],
        ];
    }

    private function mensajesFicha(): array
    {
        return [
            'cuil.digits' => 'El CUIL tiene que tener 11 números, sin guiones.',
            'cuil.unique' => 'Ese CUIL ya está cargado en otro docente.',
            'sexo.required' => 'Elegí el sexo.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'fecha_nacimiento.required' => 'Cargá la fecha de nacimiento.',
            'fecha_nacimiento.before' => 'Revisá la fecha de nacimiento: tiene que tener al menos 20 años.',
            'fecha_nacimiento.after' => 'Revisá la fecha de nacimiento: no puede tener más de 75 años.',
            'direccion.max' => 'El domicilio puede tener hasta 40 caracteres.',
            'direccion.regex' => 'El domicilio solo puede tener letras, números y espacios.',
            'telefono.regex' => 'El teléfono tiene que tener entre 7 y 15 números, sin espacios ni guiones.',
            'id_localidad.required' => 'Elegí la provincia y la localidad del domicilio.',
            'titulos.required' => 'Agregá al menos un título.',
        ];
    }

    // El CUIL tiene que corresponder al DNI y tener bien el dígito verificador.
    private function validarCuil(array $data): void
    {
        if (! empty($data['cuil']) && ($error = Cuil::error($data['cuil'], $data['dni']))) {
            throw ValidationException::withMessages(['cuil' => $error]);
        }
    }

    private function datosFicha(array $data): array
    {
        return [
            'cuil' => $data['cuil'] ?? null,
            'sexo' => $data['sexo'] ?? null,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
        ];
    }

    // Títulos elegidos del catálogo (reemplaza los anteriores).
    private function guardarTitulos(Profesor $profesor, array $data): void
    {
        $profesor->titulos()->sync($data['titulos'] ?? []);
    }

    // Contacto y domicilio de la persona (la localidad también se guarda como texto).
    private function datosPersona(array $data): array
    {
        return [
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'id_localidad' => $data['id_localidad'],
            'localidad' => Localidad::find($data['id_localidad'])?->nombre,
        ];
    }

    // ---------- Botones "+" del formulario: crean sin salir de la pantalla ----------

    public function storeTitulo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:titulo,nombre'],
        ], ['nombre.unique' => 'Ese título ya existe: elegilo de la lista.']);

        return response()->json(['titulo' => Titulo::create(['nombre' => trim($data['nombre'])])]);
    }

    public function updateTitulo(Request $request, Titulo $titulo): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('titulo', 'nombre')->ignore($titulo->id_titulo, 'id_titulo')],
        ], ['nombre.unique' => 'Ese título ya existe: elegilo de la lista.']);

        $titulo->update(['nombre' => trim($data['nombre'])]);

        return response()->json(['titulo' => $titulo]);
    }

    public function destroyTitulo(Titulo $titulo): JsonResponse
    {
        if ($titulo->profesores()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: hay profesores con este título cargado. Sacáselo primero a esos profesores.'], 422);
        }

        $titulo->delete();

        return response()->json(['status' => 'Título eliminado.']);
    }

    public function storeProvincia(Request $request): JsonResponse
    {
        $data = $request->validate(['nombre' => ['required', 'string', 'max:60', 'unique:provincia,nombre']],
            ['nombre.unique' => 'Esa provincia ya existe.']);

        return response()->json(['provincia' => Provincia::create(['nombre' => trim($data['nombre'])])]);
    }

    public function updateProvincia(Request $request, Provincia $provincia): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:60', Rule::unique('provincia', 'nombre')->ignore($provincia->id_provincia, 'id_provincia')],
        ], ['nombre.unique' => 'Esa provincia ya existe.']);

        $provincia->update(['nombre' => trim($data['nombre'])]);

        return response()->json(['provincia' => $provincia]);
    }

    public function storeLocalidad(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_provincia' => ['required', 'exists:provincia,id_provincia'],
            'nombre' => ['required', 'string', 'max:80',
                Rule::unique('localidad', 'nombre')->where('id_provincia', $request->input('id_provincia'))],
        ], ['nombre.unique' => 'Esa localidad ya existe en la provincia.']);

        return response()->json(['localidad' => Localidad::create(['id_provincia' => $data['id_provincia'], 'nombre' => trim($data['nombre'])])]);
    }

    public function updateLocalidad(Request $request, Localidad $localidad): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80',
                Rule::unique('localidad', 'nombre')->where('id_provincia', $localidad->id_provincia)->ignore($localidad->id_localidad, 'id_localidad')],
        ], ['nombre.unique' => 'Esa localidad ya existe en la provincia.']);

        $localidad->update(['nombre' => trim($data['nombre'])]);

        return response()->json(['localidad' => $localidad]);
    }

    public function baja(Profesor $profesor): RedirectResponse
    {
        $profesor->update([
            'activo' => false,
            'id_secretario_baja' => Auth::user()->id_persona,
            'fecha_baja' => now(),
        ]);

        return back()->with('status', 'Profesor dado de baja.');
    }

    public function reactivar(Profesor $profesor): RedirectResponse
    {
        $profesor->update([
            'activo' => true,
            'id_secretario_reactiva' => Auth::user()->id_persona,
            'fecha_reactivacion' => now(),
        ]);

        return back()->with('status', 'Profesor reactivado.');
    }

    public function storeEspecialidad(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nombre_especialidad' => ['required', 'string', 'max:120', 'unique:especialidad_profesor,nombre_especialidad'],
        ]);

        $especialidad = EspecialidadProfesor::create($data);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'Especialidad agregada correctamente.', 'especialidad' => $especialidad]);
        }

        return back()->with('status', 'Especialidad agregada correctamente.');
    }

    public function updateEspecialidad(Request $request, EspecialidadProfesor $especialidad): RedirectResponse|JsonResponse
    {
        if ($especialidad->profesores()->exists()) {
            $mensaje = 'No se puede editar: hay profesores cargados con esta especialidad. Cambiales la especialidad primero.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje], 422);
            }

            return back()->withErrors(['nombre_especialidad' => $mensaje]);
        }

        $data = $request->validate([
            'nombre_especialidad' => ['required', 'string', 'max:120', 'unique:especialidad_profesor,nombre_especialidad,' . $especialidad->id_especialidad . ',id_especialidad'],
        ]);

        $especialidad->update($data);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'Especialidad actualizada correctamente.', 'especialidad' => $especialidad]);
        }

        return back()->with('status', 'Especialidad actualizada correctamente.');
    }

    public function destroyEspecialidad(Request $request, EspecialidadProfesor $especialidad): RedirectResponse|JsonResponse
    {
        if ($especialidad->profesores()->exists()) {
            $mensaje = 'No se puede eliminar: hay profesores cargados con esta especialidad. Cambiales la especialidad primero.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje], 422);
            }

            return back()->withErrors(['nombre_especialidad' => $mensaje]);
        }

        $especialidad->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'Especialidad eliminada.']);
        }

        return back()->with('status', 'Especialidad eliminada.');
    }

    public function storeAsignacion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'id_materia' => ['required', 'exists:materia,id_materia'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'aula' => ['nullable', 'string', 'max:20'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required', 'after:hora_inicio'],
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['in:Lunes,Martes,Miercoles,Jueves,Viernes'],
        ]);

        $idAsignacionActual = AsignacionProfesorMateria::where([
            'id_profesor' => $data['id_profesor'],
            'id_materia' => $data['id_materia'],
            'id_anio_lectivo' => $data['id_anio_lectivo'],
        ])->value('id_asignacion');

        // Una materia ya asignada a otro profesor en el mismo año lectivo no se puede volver a asignar.
        $materiaOcupada = AsignacionProfesorMateria::where('id_materia', $data['id_materia'])
            ->where('id_anio_lectivo', $data['id_anio_lectivo'])
            ->where('id_profesor', '!=', $data['id_profesor'])
            ->with('profesor.persona', 'materia')
            ->first();

        if ($materiaOcupada) {
            $otroProfesor = $materiaOcupada->profesor;

            return back()->withErrors([
                'id_materia' => "\"{$materiaOcupada->materia->nombre}\" ya está asignada a {$otroProfesor->apellido}, {$otroProfesor->nombre} en este año lectivo. Elegí otra materia.",
            ])->withInput();
        }

        // Un profesor no puede dar dos materias el mismo día en horarios que se pisan.
        $conflicto = HorarioAsignacion::whereIn('dia_semana', $data['dias'])
            ->where('hora_desde', '<', $data['hora_fin'])
            ->where('hora_fin', '>', $data['hora_inicio'])
            ->whereHas('asignacion', function ($q) use ($data, $idAsignacionActual) {
                $q->where('id_profesor', $data['id_profesor'])
                    ->where('id_anio_lectivo', $data['id_anio_lectivo']);

                if ($idAsignacionActual) {
                    $q->where('id_asignacion', '!=', $idAsignacionActual);
                }
            })
            ->with('asignacion.materia')
            ->first();

        if ($conflicto) {
            $materiaConflicto = $conflicto->asignacion->materia->nombre;

            return back()->withErrors([
                'hora_inicio' => "El profesor ya tiene asignada \"{$materiaConflicto}\" el {$conflicto->dia_semana} de {$conflicto->hora_desde} a {$conflicto->hora_fin}, y se superpone con este horario.",
            ])->withInput();
        }

        $asignacion = AsignacionProfesorMateria::updateOrCreate(
            [
                'id_profesor' => $data['id_profesor'],
                'id_materia' => $data['id_materia'],
                'id_anio_lectivo' => $data['id_anio_lectivo'],
            ],
            [
                'fecha_asignacion' => now(),
                'aula' => $data['aula'] ?? null,
            ]
        );

        // Solo se reemplazan los horarios de los días que vienen en este envío
        // — así se puede cargar un día con un horario y, en otro envío
        // aparte, agregarle otro día con un horario distinto sin perder el
        // primero (es el mismo profesor+materia+año, una sola asignación con
        // varios bloques de día/hora).
        $asignacion->horarios()->whereIn('dia_semana', $data['dias'])->delete();

        foreach ($data['dias'] as $dia) {
            HorarioAsignacion::create([
                'id_asignacion' => $asignacion->id_asignacion,
                'dia_semana' => $dia,
                'hora_desde' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
            ]);
        }

        return back()->with('status', 'Asignación guardada correctamente.');
    }

    public function destroyAsignacion(AsignacionProfesorMateria $asignacion): RedirectResponse
    {
        $asignacion->horarios()->delete();
        $asignacion->delete();

        return back()->with('status', 'Asignación eliminada.');
    }

    /**
     * Quita un solo bloque de día/horario de una asignación (ej. solo el
     * Viernes, dejando el Miércoles intacto). Si era el último que le
     * quedaba, no tiene sentido una asignación sin ningún horario, así que
     * se borra también.
     */
    public function destroyHorario(AsignacionProfesorMateria $asignacion, HorarioAsignacion $horario): RedirectResponse
    {
        abort_unless($horario->id_asignacion === $asignacion->id_asignacion, 404);

        $horario->delete();

        if ($asignacion->horarios()->doesntExist()) {
            $asignacion->delete();

            return back()->with('status', 'Se quitó el horario y, al no quedarle ninguno, se eliminó la asignación.');
        }

        return back()->with('status', 'Se quitó ese día de la asignación.');
    }
}
