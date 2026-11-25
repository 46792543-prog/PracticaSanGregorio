<?php

namespace App\Support;

use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;

/**
 * Recalcula desde cero cada cuota IMPAGA con la configuración de hoy:
 *   monto   = precio vigente según los aumentos (ConfiguracionCuota::montoACobrar)
 *   recargo = si ya venció, monto x % de mora (una sola vez); si no, 0
 * Así, si sube la cuota o cambia el % de mora, se actualizan todas las
 * impagas. Las pagadas no se tocan. Se llama al guardar la configuración o un
 * aumento, al abrir/cobrar cuotas y en la tarea diaria.
 */
class RecalculadorCuotas
{
    public static function aplicar(ConfiguracionCuota $configuracion): int
    {
        $configuracion->loadMissing('aumentos', 'anioLectivo');
        $actualizadas = 0;

        $impagas = CuotaAlumno::with('personaAlumno')
            ->where('id_anio_lectivo', $configuracion->id_anio_lectivo)
            ->where('pagado', false)
            ->get();

        foreach ($impagas as $cuota) {
            // Al recursante el monto se lo pone el director a mano: se respeta, solo se le calcula la mora.
            $monto = $cuota->personaAlumno?->condicionInscripcion() === CondicionAlumno::RECURSANTE
                ? (float) $cuota->monto
                : $configuracion->montoACobrar((int) $cuota->id_mes);

            $vencida = $cuota->fecha_vencimiento?->lt(today()) ?? false;
            $recargo = $vencida ? round($monto * $configuracion->porcentaje_interes_mora / 100, 2) : 0.0;

            if ((float) $cuota->monto !== $monto || (float) $cuota->recargo !== $recargo) {
                $cuota->update(['monto' => $monto, 'recargo' => $recargo, 'mora_aplicada' => $vencida]);
                $actualizadas++;
            }
        }

        return $actualizadas;
    }

    // Lo mismo para todos los años lectivos con configuración cargada.
    public static function aplicarATodos(): int
    {
        return ConfiguracionCuota::all()->sum(fn (ConfiguracionCuota $c) => self::aplicar($c));
    }
}
