<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
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
            'configuraciones' => ConfiguracionMatricula::with('anioLectivo')->get()->keyBy('id_anio_lectivo'),
            'meses' => Mes::orderBy('id_mes')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'mes_habilitacion' => ['required', 'integer', 'min:1', 'max:12'],
            'dia_limite' => ['required', 'integer', 'min:1', 'max:28'],
            'concepto' => ['nullable', 'string', 'max:20', 'regex:/^[\pL\s\'-]+$/u'],
        ], [
            'monto.required' => 'Ingresá el monto de la matrícula.',
            'monto.numeric' => 'El monto tiene que ser un número.',
            'monto.min' => 'El monto tiene que ser mayor a $0.',
            'monto.max' => 'El monto no puede tener más de 7 dígitos.',
            'concepto.regex' => 'El concepto solo puede tener letras.',
        ]);

        $configuracion = ConfiguracionMatricula::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo']],
            [
                'monto' => round((float) $data['monto'], 2),
                'mes_habilitacion' => $data['mes_habilitacion'],
                'dia_limite' => $data['dia_limite'],
                'concepto' => $data['concepto'] ?: null,
            ]
        );

        $creadas = $this->generarMatriculasAutomaticas($configuracion);

        $mensaje = "Se guardó la configuración de matrícula para el año {$configuracion->anioLectivo->anio}.";
        $mensaje .= $creadas > 0
            ? " Se generó la matrícula para {$creadas} alumno(s) con inscripción activa."
            : ' No se generaron matrículas nuevas: ya estaban todas generadas.';

        return redirect()->route('director.configuracion-matricula.index')->with('status', $mensaje);
    }

    /**
     * Una sola matrícula por alumno con inscripción activa en ese año (no
     * se repite por mes, a diferencia de la cuota). No duplica las que ya
     * existan.
     */
    private function generarMatriculasAutomaticas(ConfiguracionMatricula $configuracion): int
    {
        $idAnio = $configuracion->id_anio_lectivo;

        // Los de "Cursada completa" no se incluyen: pagan la matrícula solo
        // del año en que vuelven a rendir, y se les genera a mano desde Cuotas.
        $alumnos = InscripcionCarrera::where('id_anio_lectivo', $idAnio)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->whereDoesntHave('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::CURSADA_COMPLETA))
            ->pluck('id_persona_alumno')
            ->unique();

        $creadas = 0;

        DB::transaction(function () use ($alumnos, $idAnio, $configuracion, &$creadas) {
            foreach ($alumnos as $idPersona) {
                $matricula = MatriculaAlumno::firstOrNew([
                    'id_persona_alumno' => $idPersona,
                    'id_anio_lectivo' => $idAnio,
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
