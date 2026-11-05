<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use App\Models\InscripcionCarrera;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Configuración económica de cuotas (monto mensual, día de vencimiento e
 * interés por mora) por período lectivo. Accede quien entra al panel de
 * dirección (Director o Secretario Administrativo, ver middleware
 * EnsureDirector): al guardarla, el sistema genera automáticamente la
 * cuota correspondiente a todos los alumnos con inscripción activa en ese
 * año, sin cargarlas una por una.
 */
class ConfiguracionCuotaController extends Controller
{
    public function index(): View
    {
        $anioActivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))->first();

        return view('director.configuracion-cuotas.index', [
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'anioActivoId' => $anioActivo?->id_anio_lectivo,
            'configuraciones' => ConfiguracionCuota::with('anioLectivo')->get()->keyBy('id_anio_lectivo'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'monto_mensual' => ['required', 'numeric', 'min:0.01'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:28'],
            'porcentaje_interes_mora' => ['required', 'numeric', 'min:0', 'max:100'],
            'concepto' => ['nullable', 'string', 'max:20', 'regex:/^[\pL0-9\s]+$/u'],
        ], [
            'monto_mensual.required' => 'Ingresá el monto de la cuota mensual.',
            'monto_mensual.numeric' => 'El monto tiene que ser un número.',
            'monto_mensual.min' => 'El monto tiene que ser mayor a $0.',
            'porcentaje_interes_mora.max' => 'El interés por mora no puede superar el 100%.',
        ]);

        $configuracion = ConfiguracionCuota::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo']],
            [
                'monto_mensual' => round((float) $data['monto_mensual'], 2),
                'dia_vencimiento' => $data['dia_vencimiento'],
                'porcentaje_interes_mora' => round((float) $data['porcentaje_interes_mora'], 2),
                'concepto' => $data['concepto'] ?: null,
            ]
        );

        $creadas = $this->generarCuotasAutomaticas($configuracion);

        $mensaje = "Se guardó la configuración de cuotas para el año {$configuracion->anioLectivo->anio}.";
        $mensaje .= $creadas > 0
            ? " Se generaron {$creadas} cuota(s) nueva(s) para los alumnos con inscripción activa."
            : ' No se generaron cuotas nuevas: ya estaban todas generadas.';

        return redirect()->route('director.configuracion-cuotas.index')->with('status', $mensaje);
    }

    public function calcularMora(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
        ]);

        $configuracion = ConfiguracionCuota::where('id_anio_lectivo', $data['id_anio_lectivo'])->first();

        if (! $configuracion) {
            return back()->withErrors(['id_anio_lectivo' => 'Todavía no hay una configuración de cuotas guardada para ese año lectivo.']);
        }

        $vencidas = CuotaAlumno::where('id_anio_lectivo', $data['id_anio_lectivo'])
            ->where('pagado', false)
            ->where('mora_aplicada', false)
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->get();

        $afectadas = 0;

        DB::transaction(function () use ($vencidas, $configuracion, &$afectadas) {
            foreach ($vencidas as $cuota) {
                $cuota->update([
                    'recargo' => round((float) $cuota->monto * ((float) $configuracion->porcentaje_interes_mora / 100), 2),
                    'mora_aplicada' => true,
                ]);

                $afectadas++;
            }
        });

        $mensaje = $afectadas > 0
            ? "Se calculó el interés por mora en {$afectadas} cuota(s) vencida(s)."
            : 'No había cuotas vencidas pendientes de aplicarles mora.';

        return redirect()->route('director.configuracion-cuotas.index')->with('status', $mensaje);
    }

    /**
     * Una cuota por mes (desde que arranca el ciclo lectivo del alumno hasta
     * diciembre) para cada alumno con inscripción activa en ese año —
     * mismo criterio de "inicio de ciclo" que Persona::estadoMensualCuotas().
     * No duplica cuotas que ya existan.
     */
    private function generarCuotasAutomaticas(ConfiguracionCuota $configuracion): int
    {
        $anio = $configuracion->anioLectivo;

        $alumnos = InscripcionCarrera::where('id_anio_lectivo', $anio->id_anio_lectivo)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->selectRaw('id_persona_alumno, MIN(fecha_inscripcion) as fecha_inscripcion')
            ->groupBy('id_persona_alumno')
            ->get();

        $creadas = 0;

        DB::transaction(function () use ($alumnos, $anio, $configuracion, &$creadas) {
            foreach ($alumnos as $alumno) {
                $inicioCiclo = max(3, Carbon::parse($alumno->fecha_inscripcion)->month);

                for ($mes = $inicioCiclo; $mes <= 12; $mes++) {
                    $cuota = CuotaAlumno::firstOrNew([
                        'id_persona_alumno' => $alumno->id_persona_alumno,
                        'id_anio_lectivo' => $anio->id_anio_lectivo,
                        'id_mes' => $mes,
                    ]);

                    if ($cuota->exists) {
                        continue;
                    }

                    $cuota->concepto = $configuracion->concepto;
                    $cuota->fecha_vencimiento = sprintf('%04d-%02d-%02d', $anio->anio, $mes, $configuracion->dia_vencimiento);
                    $cuota->monto = $configuracion->monto_mensual;
                    $cuota->recargo = 0;
                    $cuota->mora_aplicada = false;
                    $cuota->pagado = false;
                    $cuota->save();

                    $creadas++;
                }
            }
        });

        return $creadas;
    }
}
