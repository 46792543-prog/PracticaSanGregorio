<?php

namespace App\Support;

use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use Illuminate\Support\Facades\DB;

/**
 * Aplica el interés por mora configurado a las cuotas vencidas sin pagar.
 * La usan tanto el botón manual "Calcular moras del día" (un año lectivo
 * puntual) como la tarea programada que corre sola todos los días (todos
 * los años lectivos que tengan una configuración de cuotas cargada).
 */
class CalculadoraMora
{
    /**
     * Aplica la mora a un año lectivo puntual. Devuelve cuántas cuotas
     * se vieron afectadas.
     */
    public static function aplicarParaAnio(int $idAnioLectivo): int
    {
        $configuracion = ConfiguracionCuota::where('id_anio_lectivo', $idAnioLectivo)->first();

        if (! $configuracion) {
            return 0;
        }

        return self::aplicarConConfiguracion($configuracion);
    }

    /**
     * Aplica la mora en todos los años lectivos que tengan una
     * configuración de cuotas cargada (lo que corre la tarea programada
     * diaria). Devuelve el total de cuotas afectadas.
     */
    public static function aplicarATodos(): int
    {
        $afectadas = 0;

        foreach (ConfiguracionCuota::all() as $configuracion) {
            $afectadas += self::aplicarConConfiguracion($configuracion);
        }

        return $afectadas;
    }

    private static function aplicarConConfiguracion(ConfiguracionCuota $configuracion): int
    {
        $vencidas = CuotaAlumno::where('id_anio_lectivo', $configuracion->id_anio_lectivo)
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

        return $afectadas;
    }
}
