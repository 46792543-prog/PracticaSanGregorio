<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\HistorialAlumno;
use App\Models\Materia;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HistorialPrevioController extends Controller
{
    private const TOPE_VECES_RENDIDA = 3;
    private const CONDICIONES_DISPONIBLES = ['Cursando', 'Regular', 'Promocionada', 'Examen Final', 'Equivalencia', 'Libre'];

    // Condiciones "en curso": todavía no hay una nota final que cargar.
    private const SIN_NOTA = ['Cursando', 'Regular'];

    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('busqueda'));
        $alumno = null;

        if ($busqueda) {
            $alumno = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
                ->whereHas('inscripcionesCarrera')
                ->where(fn ($q) => $q->where('apellido', 'like', "%{$busqueda}%")
                    ->orWhere('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('dni', 'like', "%{$busqueda}%"))
                ->first();
        }

        $historial = $alumno
            ? HistorialAlumno::where('id_persona_alumno', $alumno->id_persona)
                ->with(['materia.nombreMateria', 'materia.carrera', 'materia.anioCursada', 'anioLectivo', 'condicion'])
                ->orderByDesc('id_historial')
                ->get()
            : collect();

        $anioIngreso = $alumno?->inscripcionesCarrera->sortBy('id_inscripcion_carrera')->first()?->anioLectivo?->anio;

        // Carrera que está cursando (inscripción activa más reciente, o la
        // última que tuvo): se deja preseleccionada en "Cargar una materia".
        $carreraAlumnoId = $alumno?->inscripcionCarreraActiva()?->id_carrera
            ?? $alumno?->inscripcionesCarrera->sortByDesc('id_inscripcion_carrera')->first()?->id_carrera;

        return view('admin.alumnos.historial-cargar', [
            'busqueda' => $busqueda,
            'alumno' => $alumno,
            'anioIngreso' => $anioIngreso,
            'carreraAlumnoId' => $carreraAlumnoId,
            'carreras' => Carrera::with(['materias' => fn ($q) => $q->where('activa', true)->with(['nombreMateria', 'anioCursada'])])
                ->orderBy('nombre_carrera')
                ->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'condiciones' => CondicionAlumno::whereIn('nombre_condicion', self::CONDICIONES_DISPONIBLES)->orderBy('id_condicion')->get(),
            'historial' => $historial,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $condicionesPermitidas = CondicionAlumno::whereIn('nombre_condicion', self::CONDICIONES_DISPONIBLES)->pluck('id_condicion');

        $data = $request->validate([
            'id_persona_alumno' => ['required', 'exists:persona,id_persona'],
            'id_materia' => [
                'required',
                'exists:materia,id_materia',
                Rule::unique('historial_alumno', 'id_materia')->where(fn ($q) => $q
                    ->where('id_persona_alumno', $request->id_persona_alumno)
                    ->where('id_anio_lectivo', $request->id_anio_lectivo)),
            ],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_condicion' => ['required', Rule::in($condicionesPermitidas)],
            'nota_cursada' => ['nullable', 'integer', 'between:1,10'],
            'veces_rendida' => ['nullable', 'integer', 'between:1,10'],
            'institucion_equivalencia' => ['nullable', 'string', 'max:255'],
            'resolucion_equivalencia' => ['nullable', 'string', 'max:255'],
            'fecha_ultima_modificacion' => ['required', 'date'],
            'busqueda_actual' => ['nullable', 'string'],
        ], [
            'id_materia.unique' => 'Esta materia ya está cargada para este alumno en ese año lectivo.',
            'nota_cursada.integer' => 'La nota tiene que ser un número entero del 1 al 10, sin decimales.',
            'veces_rendida.integer' => 'Las veces rendida tienen que ser un número entero del 1 al 10, sin decimales.',
            'veces_rendida.between' => 'Las veces rendida tienen que ser un número entero del 1 al 10.',
        ]);

        [$idCondicion, $sePasoALibre] = $this->resolverCondicion((int) $data['id_condicion'], $data['veces_rendida'] ?? null);
        $condicionNombre = CondicionAlumno::find($idCondicion)?->nombre_condicion;

        if ($error = $this->validarReglasNota($condicionNombre, $data['nota_cursada'] ?? null)) {
            return back()->withErrors(['nota_cursada' => $error])->withInput();
        }

        if ($error = $this->validarCamposEquivalencia($condicionNombre, $data['institucion_equivalencia'] ?? null, $data['resolucion_equivalencia'] ?? null)) {
            return back()->withErrors(['institucion_equivalencia' => $error])->withInput();
        }

        HistorialAlumno::create([
            'id_persona_alumno' => $data['id_persona_alumno'],
            'id_materia' => $data['id_materia'],
            'id_anio_lectivo' => $data['id_anio_lectivo'],
            'id_condicion' => $idCondicion,
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'veces_rendida' => $condicionNombre === 'Equivalencia' ? null : ($data['veces_rendida'] ?? null),
            'institucion_equivalencia' => $condicionNombre === 'Equivalencia' ? ($data['institucion_equivalencia'] ?? null) : null,
            'resolucion_equivalencia' => $condicionNombre === 'Equivalencia' ? ($data['resolucion_equivalencia'] ?? null) : null,
            'fecha_ultima_modificacion' => $data['fecha_ultima_modificacion'],
        ]);

        $status = $sePasoALibre
            ? 'Se cargó la materia, pero como superó las 3 veces rendidas se marcó automáticamente como Libre.'
            : 'Se cargó la materia correctamente.';

        return redirect()->route('admin.alumnos.historial-previo.index', ['busqueda' => $data['busqueda_actual'] ?? null])
            ->with('status', $status);
    }

    /**
     * Carga varias materias de una vez (por ejemplo, todo Primer Año): cada
     * fila trae su año lectivo, condición, fecha, nota, etc. Se aplican las
     * mismas reglas que al cargar una materia suelta. Si alguna fila tiene
     * un error no se guarda ninguna (todo o nada), y se avisa qué materia
     * falló para corregirla.
     */
    public function storeLote(Request $request): RedirectResponse
    {
        $request->validate([
            'id_persona_alumno' => ['required', 'exists:persona,id_persona'],
            'busqueda_actual' => ['nullable', 'string'],
            'filas' => ['required', 'array'],
        ], [
            'filas.required' => 'Elegí un año de la carrera y tildá al menos una materia.',
        ]);

        $filas = collect($request->input('filas'))->filter(fn ($fila) => ! empty($fila['incluir']));

        if ($filas->isEmpty()) {
            return back()->withErrors(['filas' => 'Tildá al menos una materia para guardar.'])->withInput();
        }

        $idPersona = (int) $request->input('id_persona_alumno');
        $condicionesPermitidas = CondicionAlumno::whereIn('nombre_condicion', self::CONDICIONES_DISPONIBLES)->pluck('id_condicion')->all();
        $materias = Materia::with('nombreMateria')->whereIn('id_materia', $filas->keys())->get()->keyBy('id_materia');

        $errores = [];
        $aGuardar = [];
        $pasadasALibre = 0;

        foreach ($filas as $idMateria => $fila) {
            $materia = $materias->get((int) $idMateria);

            if (! $materia) {
                $errores[] = "La materia #{$idMateria} no existe.";

                continue;
            }

            $nombre = $materia->nombre;

            $validador = Validator::make($fila, [
                'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
                'id_condicion' => ['required', Rule::in($condicionesPermitidas)],
                'nota_cursada' => ['nullable', 'integer', 'between:1,10'],
                'veces_rendida' => ['nullable', 'integer', 'between:1,10'],
                'institucion_equivalencia' => ['nullable', 'string', 'max:255'],
                'resolucion_equivalencia' => ['nullable', 'string', 'max:255'],
                'fecha_ultima_modificacion' => ['required', 'date'],
            ], [
                'nota_cursada.integer' => 'la nota tiene que ser un número entero del 1 al 10.',
                'nota_cursada.between' => 'la nota tiene que ser del 1 al 10.',
                'veces_rendida.integer' => 'las veces rendida tienen que ser un número entero del 1 al 10.',
                'veces_rendida.between' => 'las veces rendida tienen que ser del 1 al 10.',
                'fecha_ultima_modificacion.required' => 'falta la fecha.',
            ]);

            if ($validador->fails()) {
                $errores[] = "{$nombre}: " . $validador->errors()->first();

                continue;
            }

            $datos = $validador->validated();
            $nota = isset($datos['nota_cursada']) ? (int) $datos['nota_cursada'] : null;
            $veces = isset($datos['veces_rendida']) ? (int) $datos['veces_rendida'] : null;

            $yaCargada = HistorialAlumno::where('id_persona_alumno', $idPersona)
                ->where('id_materia', $materia->id_materia)
                ->where('id_anio_lectivo', $datos['id_anio_lectivo'])
                ->exists();

            if ($yaCargada) {
                $errores[] = "{$nombre}: ya está cargada para ese año lectivo.";

                continue;
            }

            [$idCondicion, $sePasoALibre] = $this->resolverCondicion((int) $datos['id_condicion'], $veces);
            $condicionNombre = CondicionAlumno::find($idCondicion)?->nombre_condicion;

            $error = $this->validarReglasNota($condicionNombre, $nota)
                ?? $this->validarCamposEquivalencia($condicionNombre, $datos['institucion_equivalencia'] ?? null, $datos['resolucion_equivalencia'] ?? null);

            if ($error) {
                $errores[] = "{$nombre}: {$error}";

                continue;
            }

            $esEquivalencia = $condicionNombre === 'Equivalencia';

            $aGuardar[] = [
                'id_persona_alumno' => $idPersona,
                'id_materia' => $materia->id_materia,
                'id_anio_lectivo' => $datos['id_anio_lectivo'],
                'id_condicion' => $idCondicion,
                'nota_cursada' => $nota,
                'veces_rendida' => $esEquivalencia ? null : $veces,
                'institucion_equivalencia' => $esEquivalencia ? ($datos['institucion_equivalencia'] ?? null) : null,
                'resolucion_equivalencia' => $esEquivalencia ? ($datos['resolucion_equivalencia'] ?? null) : null,
                'fecha_ultima_modificacion' => $datos['fecha_ultima_modificacion'],
            ];

            if ($sePasoALibre) {
                $pasadasALibre++;
            }
        }

        if ($errores) {
            array_unshift($errores, 'No se guardó ninguna materia. Corregí estas y volvé a tocar "Guardar todas":');

            return back()->withErrors($errores)->withInput();
        }

        DB::transaction(function () use ($aGuardar) {
            foreach ($aGuardar as $datos) {
                HistorialAlumno::create($datos);
            }
        });

        $status = 'Se cargaron ' . count($aGuardar) . ' materia(s) correctamente.';

        if ($pasadasALibre > 0) {
            $status .= " {$pasadasALibre} se marcaron como Libre porque superaron las 3 veces rendidas.";
        }

        return redirect()->route('admin.alumnos.historial-previo.index', ['busqueda' => $request->input('busqueda_actual')])
            ->with('status', $status);
    }

    public function update(Request $request, HistorialAlumno $historial): RedirectResponse
    {
        $condicionesPermitidas = CondicionAlumno::whereIn('nombre_condicion', self::CONDICIONES_DISPONIBLES)->pluck('id_condicion');

        $data = $request->validate([
            'id_anio_lectivo' => [
                'required',
                'exists:anio_lectivo,id_anio_lectivo',
                Rule::unique('historial_alumno', 'id_anio_lectivo')
                    ->where(fn ($q) => $q
                        ->where('id_persona_alumno', $historial->id_persona_alumno)
                        ->where('id_materia', $historial->id_materia))
                    ->ignore($historial->id_historial, 'id_historial'),
            ],
            'id_condicion' => ['required', Rule::in($condicionesPermitidas)],
            'nota_cursada' => ['nullable', 'integer', 'between:1,10'],
            'veces_rendida' => ['nullable', 'integer', 'between:1,10'],
            'institucion_equivalencia' => ['nullable', 'string', 'max:255'],
            'resolucion_equivalencia' => ['nullable', 'string', 'max:255'],
            'fecha_ultima_modificacion' => ['required', 'date'],
            'busqueda_actual' => ['nullable', 'string'],
        ], [
            'id_anio_lectivo.unique' => 'Esta materia ya está cargada para este alumno en ese año lectivo.',
            'nota_cursada.integer' => 'La nota tiene que ser un número entero del 1 al 10, sin decimales.',
            'veces_rendida.integer' => 'Las veces rendida tienen que ser un número entero del 1 al 10, sin decimales.',
            'veces_rendida.between' => 'Las veces rendida tienen que ser un número entero del 1 al 10.',
        ]);

        [$idCondicion, $sePasoALibre] = $this->resolverCondicion((int) $data['id_condicion'], $data['veces_rendida'] ?? null);
        $condicionNombre = CondicionAlumno::find($idCondicion)?->nombre_condicion;

        if ($error = $this->validarReglasNota($condicionNombre, $data['nota_cursada'] ?? null)) {
            return back()->withErrors(['nota_cursada' => $error])->withInput();
        }

        if ($error = $this->validarCamposEquivalencia($condicionNombre, $data['institucion_equivalencia'] ?? null, $data['resolucion_equivalencia'] ?? null)) {
            return back()->withErrors(['institucion_equivalencia' => $error])->withInput();
        }

        $historial->update([
            'id_anio_lectivo' => $data['id_anio_lectivo'],
            'id_condicion' => $idCondicion,
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'veces_rendida' => $condicionNombre === 'Equivalencia' ? null : ($data['veces_rendida'] ?? null),
            'institucion_equivalencia' => $condicionNombre === 'Equivalencia' ? ($data['institucion_equivalencia'] ?? null) : null,
            'resolucion_equivalencia' => $condicionNombre === 'Equivalencia' ? ($data['resolucion_equivalencia'] ?? null) : null,
            'fecha_ultima_modificacion' => $data['fecha_ultima_modificacion'],
        ]);

        $status = $sePasoALibre
            ? 'Se actualizó la materia, pero como superó las 3 veces rendidas se marcó automáticamente como Libre.'
            : 'Se actualizó la materia correctamente.';

        return redirect()->route('admin.alumnos.historial-previo.index', ['busqueda' => $data['busqueda_actual'] ?? null])
            ->with('status', $status);
    }

    public function destroy(Request $request, HistorialAlumno $historial): RedirectResponse
    {
        $historial->delete();

        return redirect()->route('admin.alumnos.historial-previo.index', ['busqueda' => $request->query('busqueda')])
            ->with('status', 'Se eliminó la materia del historial.');
    }

    /**
     * Con más de 3 veces rendidas, la materia queda libre automáticamente
     * sin importar qué condición haya tildado secretaría.
     *
     * @return array{0: int, 1: bool} [id_condicion final, si se forzó a Libre]
     */
    private function resolverCondicion(int $idCondicion, ?int $vecesRendida): array
    {
        if ((int) $vecesRendida > self::TOPE_VECES_RENDIDA) {
            $idLibre = CondicionAlumno::where('nombre_condicion', 'Libre')->value('id_condicion');

            if ($idLibre && $idCondicion !== $idLibre) {
                return [$idLibre, true];
            }
        }

        return [$idCondicion, false];
    }

    /**
     * Devuelve el mensaje de error si la nota no corresponde a la condición
     * final, o null si está todo bien.
     */
    private function validarReglasNota(?string $condicionNombre, ?int $nota): ?string
    {
        if (in_array($condicionNombre, self::SIN_NOTA, true) && $nota !== null) {
            return "Una materia en condición \"{$condicionNombre}\" no puede llevar nota final. Si el alumno ya aprobó el examen, cambiá la condición a \"Examen Final\".";
        }

        if (CondicionAlumno::esAprobada($condicionNombre)) {
            if ($nota === null) {
                return "Para guardar la materia como \"{$condicionNombre}\" hace falta completar la nota.";
            }

            if ($nota < 4) {
                return 'No se puede cargar la materia como aprobada (Promocionada, Examen Final o Equivalencia) con una nota inferior a 4.';
            }
        }

        if ($condicionNombre === 'Libre' && $nota !== null && $nota >= 4) {
            return 'No se puede cargar la materia como Libre con una nota de aprobación (4 o más).';
        }

        return null;
    }

    /**
     * Una equivalencia tiene que decir de qué institución viene y con qué
     * resolución se reconoció, además de la nota (ya validada aparte).
     */
    private function validarCamposEquivalencia(?string $condicionNombre, ?string $institucion, ?string $resolucion): ?string
    {
        if ($condicionNombre === 'Equivalencia' && (! $institucion || ! $resolucion)) {
            return 'Para guardar la materia como "Equivalencia" hace falta indicar la institución de origen y el número de resolución.';
        }

        return null;
    }
}
