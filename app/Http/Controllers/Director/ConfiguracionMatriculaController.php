<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\ConfiguracionMatricula;
use App\Models\InscripcionCarrera;
use App\Models\MatriculaAlumno;
use App\Models\Mes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Configuración económica de la matrícula (monto, mes desde el que se
 * habilita el pago y día límite) por período lectivo. Al guardarla, el
 * sistema genera automáticamente la matrícula (una por alumno, no por mes)
 * para todos los alumnos con inscripción activa en ese año.
 */
class ConfiguracionMatriculaController extends Controller
{
    public function index(): View
    {
        $anioActivo = AnioLectivo::activo();

        return view('director.configuracion-matricula.index', [
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'anioActivoId' => $anioActivo?->id_anio_lectivo,
            // Clave "año-carrera" ('todas' si es la general).
            'configuraciones' => ConfiguracionMatricula::with('anioLectivo', 'carrera')->get()->keyBy(fn ($c) => "{$c->id_anio_lectivo}-" . ($c->id_carrera ?? 'todas')),
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'meses' => Mes::orderBy('id_mes')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_carrera' => ['nullable', 'exists:carrera,id_carrera'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'mes_habilitacion' => ['required', 'integer', 'min:1', 'max:12'],
            'dia_limite' => ['required', 'integer', 'min:1', 'max:28'],
            'concepto' => ['nullable', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
        ], [
            'monto.required' => 'Ingresá el monto de la matrícula.',
            'monto.numeric' => 'El monto tiene que ser un número.',
            'monto.min' => 'El monto tiene que ser mayor a $0.',
            'monto.max' => 'El monto no puede tener más de 7 dígitos.',
            'dia_limite.required' => 'Ingresá el día límite de pago.',
            'dia_limite.integer' => 'El día límite tiene que ser un número entero.',
            'dia_limite.min' => 'El día límite tiene que ser entre 1 y 28.',
            'dia_limite.max' => 'El día límite tiene que ser entre 1 y 28.',
            'concepto.regex' => 'El concepto solo puede tener letras.',
            'concepto.max' => 'El concepto puede tener como máximo 50 letras.',
        ]);

        $idCarrera = ($data['id_carrera'] ?? null) ?: null;

        $configuracion = ConfiguracionMatricula::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo'], 'id_carrera' => $idCarrera],
            [
                'monto' => round((float) $data['monto'], 2),
                'mes_habilitacion' => $data['mes_habilitacion'],
                'dia_limite' => $data['dia_limite'],
                'concepto' => $data['concepto'] ?: null,
            ]
        );

        $configuracion->load('anioLectivo', 'carrera');

        $creadas = $this->generarMatriculasAutomaticas($configuracion);

        $alcance = $configuracion->carrera ? $configuracion->carrera->nombre_carrera : 'todas las carreras';
        $mensaje = "Se guardó la configuración de matrícula de {$alcance} para el año {$configuracion->anioLectivo->anio}.";
        $mensaje .= $creadas > 0
            ? " Se generó la matrícula para {$creadas} alumno(s) con inscripción activa."
            : ' No se generaron matrículas nuevas: ya estaban todas generadas.';

        return redirect()->route('director.configuracion-matricula.index')->with('status', $mensaje);
    }

    // Elimina una configuración y las matrículas impagas que le corresponden.
    // No se puede si algún alumno ya pagó la matrícula de ese año y carrera(s).
    public function destroy(ConfiguracionMatricula $configuracion): RedirectResponse
    {
        $matriculas = MatriculaAlumno::where('id_anio_lectivo', $configuracion->id_anio_lectivo)
            ->when(
                $configuracion->id_carrera,
                fn ($q) => $q->where('id_carrera', $configuracion->id_carrera),
                // La general abarca las carreras que no tienen configuración propia ese año.
                fn ($q) => $q->whereNotIn('id_carrera', ConfiguracionMatricula::where('id_anio_lectivo', $configuracion->id_anio_lectivo)->whereNotNull('id_carrera')->pluck('id_carrera'))
            );

        if ((clone $matriculas)->where('pagado', true)->exists()) {
            return back()->withErrors(['configuracion' => 'No se puede eliminar: hay alumnos que ya pagaron esa matrícula.']);
        }

        $alcance = $configuracion->carrera?->nombre_carrera ?? 'todas las carreras';
        $anio = $configuracion->anioLectivo?->anio;

        $borradas = DB::transaction(function () use ($matriculas, $configuracion) {
            $borradas = $matriculas->delete(); // solo impagas (ya se verificó arriba)
            $configuracion->delete();

            return $borradas;
        });

        return redirect()->route('director.configuracion-matricula.index')
            ->with('status', "Se eliminó la configuración de matrícula de {$alcance} {$anio} y {$borradas} matrícula(s) impaga(s).");
    }

    /**
     * Una matrícula por carrera para cada alumno con inscripción activa en
     * ese año (si cursa dos carreras, paga dos matrículas). No duplica las
     * que ya existan. Si la configuración es general (sin carrera), no se
     * aplica a las carreras que ya tengan su propia configuración para ese
     * año: la específica manda sobre la general.
     */
    private function generarMatriculasAutomaticas(ConfiguracionMatricula $configuracion): int
    {
        $idAnio = $configuracion->id_anio_lectivo;

        $carrerasConConfiguracionPropia = ConfiguracionMatricula::where('id_anio_lectivo', $idAnio)
            ->whereNotNull('id_carrera')
            ->pluck('id_carrera');

        // Los de "Cursada completa" no se incluyen: pagan la matrícula solo
        // del año en que vuelven a rendir, y se les genera a mano desde Cuotas.
        $alumnos = InscripcionCarrera::where('id_anio_lectivo', $idAnio)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->whereDoesntHave('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::CURSADA_COMPLETA))
            ->when(
                $configuracion->id_carrera,
                fn ($q) => $q->where('id_carrera', $configuracion->id_carrera),
                fn ($q) => $q->whereNotIn('id_carrera', $carrerasConConfiguracionPropia)
            )
            ->get(['id_persona_alumno', 'id_carrera']);

        $creadas = 0;

        DB::transaction(function () use ($alumnos, $idAnio, $configuracion, &$creadas) {
            foreach ($alumnos as $inscripcion) {
                $matricula = MatriculaAlumno::firstOrNew([
                    'id_persona_alumno' => $inscripcion->id_persona_alumno,
                    'id_anio_lectivo' => $idAnio,
                    'id_carrera' => $inscripcion->id_carrera,
                ]);

                if ($matricula->exists) {
                    continue;
                }

                $matricula->monto = $configuracion->monto;
                $matricula->pagado = false;
                $matricula->save();

                $creadas++;
            }
        });

        return $creadas;
    }
}
