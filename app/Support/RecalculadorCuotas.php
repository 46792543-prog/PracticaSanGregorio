<?php

namespace App\Support;

use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use App\Models\TarifaRecursante;

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
        $porAlumno = []; // inscripción y precio de recursante, calculados una vez por alumno

        $impagas = CuotaAlumno::with('personaAlumno')
            ->where('id_anio_lectivo', $configuracion->id_anio_lectivo)
            ->where('id_carrera', $configuracion->id_carrera)
            ->where('pagado', false)
            ->get();

        foreach ($impagas as $cuota) {
            $alumno = $porAlumno[$cuota->id_persona_alumno] ??= (function () use ($cuota, $configuracion) {
                $inscripcion = $cuota->personaAlumno?->inscripcionCarreraActiva($configuracion->id_carrera);
                $esRecursante = $inscripcion?->condicion?->nombre_condicion === CondicionAlumno::RECURSANTE;

                return [
                    'activo' => (bool) $inscripcion,
                    'recursante' => $esRecursante,
                    'precio' => $esRecursante ? TarifaRecursante::montoPara($cuota->personaAlumno, $configuracion->id_anio_lectivo, $configuracion->id_carrera) : null,
                ];
            })();

            // Dado de baja en esta carrera: sus cuotas quedan congeladas como estaban.
            if (! $alumno['activo']) {
                continue;
            }

            // Recursante: precio de su tabla (los aumentos no lo afectan). Sin precio cargado, se deja el que tenía.
            $monto = $alumno['recursante']
                ? ($alumno['precio'] ?? (float) $cuota->monto)
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
