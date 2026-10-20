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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HistorialPrevioController extends Controller
{
    private const TOPE_VECES_RENDIDA = 3;
    private const CONDICIONES_DISPONIBLES = ['Pendiente', 'Cursando', 'Regular', 'Aprobada', 'Libre'];

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

        return view('admin.alumnos.historial-cargar', [
            'busqueda' => $busqueda,
            'alumno' => $alumno,
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
            'nota_cursada' => ['nullable', 'numeric', 'between:1,10'],
            'veces_rendida' => ['nullable', 'integer', 'min:0', 'max:50'],
            'fecha_ultima_modificacion' => ['required', 'date'],
            'busqueda_actual' => ['nullable', 'string'],
        ], [
            'id_materia.unique' => 'Esta materia ya está cargada para este alumno en ese año lectivo.',
        ]);

        $idCondicion = (int) $data['id_condicion'];
        $sePasoALibre = false;

        // Con más de 3 veces rendidas, la materia queda libre automáticamente
        // sin importar qué condición haya tildado secretaría.
        if ((int) ($data['veces_rendida'] ?? 0) > self::TOPE_VECES_RENDIDA) {
            $idLibre = CondicionAlumno::where('nombre_condicion', 'Libre')->value('id_condicion');

            if ($idLibre && $idCondicion !== $idLibre) {
                $idCondicion = $idLibre;
                $sePasoALibre = true;
            }
        }

        $condicionNombre = CondicionAlumno::find($idCondicion)?->nombre_condicion;

        if ($condicionNombre === 'Aprobada' && (float) ($data['nota_cursada'] ?? 0) < 4) {
            return back()->withErrors([
                'nota_cursada' => 'No se puede cargar la materia como Aprobada con una nota inferior a 4.',
            ])->withInput();
        }

        if ($condicionNombre === 'Libre' && $data['nota_cursada'] !== null && (float) $data['nota_cursada'] >= 4) {
            return back()->withErrors([
                'nota_cursada' => 'No se puede cargar la materia como Libre con una nota de aprobación (4 o más).',
            ])->withInput();
        }

        HistorialAlumno::create([
            'id_persona_alumno' => $data['id_persona_alumno'],
            'id_materia' => $data['id_materia'],
            'id_anio_lectivo' => $data['id_anio_lectivo'],
            'id_condicion' => $idCondicion,
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'veces_rendida' => $data['veces_rendida'] ?? null,
            'fecha_ultima_modificacion' => $data['fecha_ultima_modificacion'],
        ]);

        $status = $sePasoALibre
            ? 'Se cargó la materia, pero como superó las 3 veces rendidas se marcó automáticamente como Libre.'
            : 'Se cargó la materia correctamente.';

        return redirect()->route('admin.alumnos.historial-previo.index', ['busqueda' => $data['busqueda_actual'] ?? null])
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
            'nota_cursada' => ['nullable', 'numeric', 'between:1,10'],
            'veces_rendida' => ['nullable', 'integer', 'min:0', 'max:50'],
            'fecha_ultima_modificacion' => ['required', 'date'],
            'busqueda_actual' => ['nullable', 'string'],
        ], [
            'id_anio_lectivo.unique' => 'Esta materia ya está cargada para este alumno en ese año lectivo.',
        ]);

        $idCondicion = (int) $data['id_condicion'];
        $sePasoALibre = false;

        if ((int) ($data['veces_rendida'] ?? 0) > self::TOPE_VECES_RENDIDA) {
            $idLibre = CondicionAlumno::where('nombre_condicion', 'Libre')->value('id_condicion');

            if ($idLibre && $idCondicion !== $idLibre) {
                $idCondicion = $idLibre;
                $sePasoALibre = true;
            }
        }

        $condicionNombre = CondicionAlumno::find($idCondicion)?->nombre_condicion;

        if ($condicionNombre === 'Aprobada' && (float) ($data['nota_cursada'] ?? 0) < 4) {
            return back()->withErrors([
                'nota_cursada' => 'No se puede guardar la materia como Aprobada con una nota inferior a 4.',
            ])->withInput();
        }

        if ($condicionNombre === 'Libre' && $data['nota_cursada'] !== null && (float) $data['nota_cursada'] >= 4) {
            return back()->withErrors([
                'nota_cursada' => 'No se puede guardar la materia como Libre con una nota de aprobación (4 o más).',
            ])->withInput();
        }

        $historial->update([
            'id_anio_lectivo' => $data['id_anio_lectivo'],
            'id_condicion' => $idCondicion,
            'nota_cursada' => $data['nota_cursada'] ?? null,
            'veces_rendida' => $data['veces_rendida'] ?? null,
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
}
