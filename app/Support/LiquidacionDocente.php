<?php

namespace App\Support;

use App\Models\AsignacionProfesorMateria;
use App\Models\ValorHoraCatedra;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Cuánto se le paga a un docente en un mes, según las horas cátedra que tiene
 * designadas (no la asistencia):
 *
 *   por cada designación vigente en el mes:
 *     horas del mes = horas cátedra semanales × 4
 *     bruto         = horas del mes × valor de la hora cátedra de su carrera y tipo
 *     descuento     = horas de licencias que descuentan en el mes × ese valor
 *     subtotal      = bruto − descuento (nunca menos de 0)
 *   total = suma de los subtotales
 * Descuentan las licencias sin goce, y las con goce con documentación Pendiente.
 *
 * La usan "Liquidación de horas" y "Pago de docentes" (el monto sale de acá).
 */
class LiquidacionDocente
{
    public const SEMANAS_POR_MES = 4;

    // Liquidación de un docente en un mes.
    public static function calcular(int $idProfesor, Carbon $periodo): array
    {
        $detalle = self::designacionesDelMes($periodo)
            ->where('id_profesor', $idProfesor)
            ->map(function (AsignacionProfesorMateria $d) use ($periodo) {
                $valor = ValorHoraCatedra::para((int) $d->materia->id_carrera, $d->tipo, $periodo);
                $horasMes = (int) $d->horas_catedra * self::SEMANAS_POR_MES;
                $faltas = $d->inasistencias->filter(fn ($f) => $f->fecha->isSameMonth($periodo));
                // No se descuentan más horas que las que cobra en el mes.
                $horasFalta = min($horasMes, (int) $faltas->filter(fn ($f) => $f->licencia->descuenta)->sum('horas_catedra'));
                $bruto = $valor ? round($horasMes * (float) $valor->monto, 2) : 0.0;
                $descuento = $valor ? round($horasFalta * (float) $valor->monto, 2) : 0.0;

                return [
                    'materia' => $d->materia->nombre,
                    'carrera' => $d->materia->carrera?->nombre_carrera,
                    'tipo' => $d->tipo,
                    'horas_semanales' => (int) $d->horas_catedra,
                    'horas_mes' => $horasMes,
                    'valor_hora' => $valor ? (float) $valor->monto : null,
                    'bruto' => $bruto,
                    'horas_falta' => $horasFalta,
                    'descuento' => $descuento,
                    'subtotal' => $bruto - $descuento,
                    'faltas' => $faltas->sortBy('fecha')->map(fn ($f) => ['fecha' => $f->fecha->format('d/m'), 'horas' => $f->horas_catedra, 'justificada' => ! $f->licencia->descuenta, 'tipo' => $f->licencia->tipo->nombre])->values(),
                ];
            })->values();

        return [
            'detalle' => $detalle,
            'horas_semanales' => $detalle->sum('horas_semanales'),
            'horas_mes' => $detalle->sum('horas_mes'),
            'horas_falta' => $detalle->sum('horas_falta'),
            'bruto' => round($detalle->sum('bruto'), 2),
            'descuento' => round($detalle->sum('descuento'), 2),
            'total' => round($detalle->sum('subtotal'), 2),
            // Carrera y tipo sin valor cargado para ese mes (no se puede liquidar hasta cargarlos). Ej: "Titular (Lic En enfermeria)"
            'tipos_sin_valor' => $detalle->whereNull('valor_hora')->map(fn ($d) => "{$d['tipo']} ({$d['carrera']})")->unique()->values(),
        ];
    }

    // Liquidación de todos los docentes con designaciones en el mes.
    public static function delMes(Carbon $periodo): Collection
    {
        return self::designacionesDelMes($periodo)
            ->groupBy('id_profesor')
            ->map(fn ($desig, $idProfesor) => ['profesor' => $desig->first()->profesor] + self::calcular((int) $idProfesor, $periodo))
            ->sortBy(fn ($fila) => $fila['profesor']->apellido)
            ->values();
    }

    // Designaciones que estuvieron vigentes algún día del mes: dadas de alta antes de
    // que termine el mes, y sin baja o con baja después de que empezó.
    private static function designacionesDelMes(Carbon $periodo): Collection
    {
        $inicio = $periodo->copy()->startOfMonth();
        $fin = $periodo->copy()->endOfMonth();

        return AsignacionProfesorMateria::with('materia.nombreMateria', 'materia.carrera', 'profesor.persona', 'inasistencias.licencia.tipo')
            ->whereNotNull('horas_catedra')
            ->whereDate('fecha_alta', '<=', $fin)
            ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhereDate('fecha_baja', '>=', $inicio))
            // Una finalizada sin fecha de baja no se cuenta.
            ->where(fn ($q) => $q->where('activa', true)->orWhereNotNull('fecha_baja'))
            ->get();
    }
}
