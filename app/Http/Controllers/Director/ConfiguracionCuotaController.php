<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Mes;
use App\Support\CalculadoraMora;
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
        $anioActivo = AnioLectivo::activo();

        return view('director.configuracion-cuotas.index', [
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'anioActivoId' => $anioActivo?->id_anio_lectivo,
            'configuraciones' => ConfiguracionCuota::with('anioLectivo')->get()->keyBy('id_anio_lectivo'),
            'meses' => Mes::orderBy('id_mes')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'monto_mensual' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'mes_desde' => ['required', 'integer', 'between:1,12'],
            'mes_hasta' => ['required', 'integer', 'between:1,12', 'gte:mes_desde'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:28'],
            'porcentaje_interes_mora' => ['required', 'numeric', 'min:0', 'max:100'],
            'concepto' => ['nullable', 'string', 'max:120', 'regex:/^[\pL\s\'-]+$/u'],
        ], [
            'monto_mensual.required' => 'Ingresá el monto de la cuota mensual.',
            'monto_mensual.numeric' => 'El monto tiene que ser un número.',
            'monto_mensual.min' => 'El monto tiene que ser mayor a $0.',
            'monto_mensual.max' => 'El monto no puede tener más de 7 dígitos.',
            'mes_hasta.gte' => 'El mes hasta tiene que ser igual o posterior al mes desde.',
            'porcentaje_interes_mora.max' => 'El interés por mora no puede superar el 100%.',
            'concepto.regex' => 'El concepto solo puede tener letras.',
        ]);

        $configuracion = ConfiguracionCuota::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo']],
            [
                'monto_mensual' => round((float) $data['monto_mensual'], 2),
                'mes_desde' => $data['mes_desde'],
                'mes_hasta' => $data['mes_hasta'],
                'dia_vencimiento' => $data['dia_vencimiento'],
                'porcentaje_interes_mora' => round((float) $data['porcentaje_interes_mora'], 2),
                'concepto' => $data['concepto'] ?: null,
            ]
        );

        $creadas = $this->generarCuotasAutomaticas($configuracion);
        $actualizadas = $this->actualizarCuotasImpagas($configuracion);

        $mensaje = "Se guardó la configuración de cuotas para el año {$configuracion->anioLectivo->anio}.";
        $mensaje .= $creadas > 0
            ? " Se generaron {$creadas} cuota(s) nueva(s) para los alumnos con inscripción activa."
            : ' No se generaron cuotas nuevas: ya estaban todas generadas.';

        if ($actualizadas > 0) {
            $mensaje .= " Se actualizó el monto de {$actualizadas} cuota(s) impaga(s) al valor nuevo.";
        }

        return redirect()->route('director.configuracion-cuotas.index')->with('status', $mensaje);
    }

    public function calcularMora(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
        ]);

        if (! ConfiguracionCuota::where('id_anio_lectivo', $data['id_anio_lectivo'])->exists()) {
            return back()->withErrors(['id_anio_lectivo' => 'Todavía no hay una configuración de cuotas guardada para ese año lectivo.']);
        }

        $afectadas = CalculadoraMora::aplicarParaAnio($data['id_anio_lectivo']);

        $mensaje = $afectadas > 0
            ? "Se calculó el interés por mora en {$afectadas} cuota(s) vencida(s)."
            : 'No había cuotas vencidas pendientes de aplicarles mora.';

        return redirect()->route('director.configuracion-cuotas.index')->with('status', $mensaje);
    }

    /**
     * Una cuota por mes, dentro del período de cobro configurado (mes_desde
     * a mes_hasta), para cada alumno con inscripción activa en ese año —
     * mismo criterio que Persona::estadoMensualCuotas().
     * No duplica cuotas que ya existan.
     *
     * Según la condición de la inscripción, no se les genera nada a:
     *  - Recursante: su cuota depende de cuántas materias cursa; el director
     *    se la genera desde Registrar Cuotas con el monto que corresponda.
     *  - Cursada completa: solo paga la cuota del mes en que rinde, que
     *    también se carga a mano desde Registrar Cuotas.
     */
    private function generarCuotasAutomaticas(ConfiguracionCuota $configuracion): int
    {
        $anio = $configuracion->anioLectivo;

        $alumnos = InscripcionCarrera::where('id_anio_lectivo', $anio->id_anio_lectivo)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->select('id_persona_alumno')
            ->distinct()
            ->get();

        $condiciones = $this->condicionesPorAlumno($anio->id_anio_lectivo);

        $creadas = 0;

        DB::transaction(function () use ($alumnos, $anio, $configuracion, &$creadas) {
            foreach ($alumnos as $alumno) {
                $condicion = $condiciones[$alumno->id_persona_alumno] ?? null;

                if (in_array($condicion, [CondicionAlumno::RECURSANTE, CondicionAlumno::CURSADA_COMPLETA], true)) {
                    continue;
                }

                for ($mes = $configuracion->mes_desde; $mes <= $configuracion->mes_hasta; $mes++) {
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

    /**
     * Si la cuota sube (o baja), las cuotas de ese año que todavía no se
     * pagaron pasan al monto nuevo — también las de meses anteriores. Las
     * pagadas no se tocan: se cobraron al valor de ese momento.
     *
     * Las cuotas de los recursantes no se tocan: su monto lo puso el
     * director a mano según cuántas materias cursa. Si la cuota ya tenía
     * aplicada la mora, el recargo se recalcula sobre el monto nuevo con el
     * porcentaje configurado.
     */
    private function actualizarCuotasImpagas(ConfiguracionCuota $configuracion): int
    {
        $impagas = CuotaAlumno::with('personaAlumno')
            ->where('id_anio_lectivo', $configuracion->id_anio_lectivo)
            ->where('pagado', false)
            ->get();

        $condiciones = [];
        $actualizadas = 0;

        DB::transaction(function () use ($impagas, $configuracion, &$condiciones, &$actualizadas) {
            foreach ($impagas as $cuota) {
                $idPersona = $cuota->id_persona_alumno;
                $condiciones[$idPersona] ??= $cuota->personaAlumno?->condicionInscripcion() ?? CondicionAlumno::REGULAR;

                if ($condiciones[$idPersona] === CondicionAlumno::RECURSANTE) {
                    continue;
                }

                $montoNuevo = round((float) $configuracion->monto_mensual, 2);
                $recargoNuevo = $cuota->mora_aplicada
                    ? round($montoNuevo * ((float) $configuracion->porcentaje_interes_mora / 100), 2)
                    : (float) $cuota->recargo;

                if ((float) $cuota->monto === $montoNuevo && (float) $cuota->recargo === $recargoNuevo) {
                    continue;
                }

                $cuota->update(['monto' => $montoNuevo, 'recargo' => $recargoNuevo]);
                $actualizadas++;
            }
        });

        return $actualizadas;
    }

    /**
     * [id_persona_alumno => nombre de la condición] de la inscripción activa
     * más reciente de cada alumno en ese año lectivo.
     */
    private function condicionesPorAlumno(int $idAnioLectivo): array
    {
        return InscripcionCarrera::with('condicion')
            ->where('id_anio_lectivo', $idAnioLectivo)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderBy('id_inscripcion_carrera')
            ->get()
            ->mapWithKeys(fn (InscripcionCarrera $i) => [$i->id_persona_alumno => $i->condicion?->nombre_condicion])
            ->all();
    }
}
