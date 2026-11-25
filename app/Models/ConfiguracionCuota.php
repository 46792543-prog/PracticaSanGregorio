<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionCuota extends Model
{
    protected $table = 'configuracion_cuota';
    protected $primaryKey = 'id_configuracion_cuota';

    protected $fillable = [
        'id_anio_lectivo', /* */
        'monto_mensual',  /* monto base: rige desde mes_desde hasta el primer aumento */
        'mes_desde',
        'mes_hasta',
        'dia_vencimiento',
        'porcentaje_interes_mora',
        'concepto',
    ];

    protected $casts = [
        'monto_mensual' => 'decimal:2',
        'porcentaje_interes_mora' => 'decimal:2',
    ];

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    // Aumentos del año, ordenados por mes (ej: julio $110.000, octubre $120.000).
    public function aumentos(): HasMany
    {
        return $this->hasMany(AumentoCuota::class, 'id_configuracion_cuota', 'id_configuracion_cuota')->orderBy('mes_desde');
    }

    // Precio de un mes: el del último aumento que empezó ese mes o antes.
    // Si no hubo ninguno, el monto base. Ej: base $100.000 y aumento en
    // julio a $110.000 -> marzo = 100.000, agosto = 110.000.
    public function montoParaMes(int $mes): float
    {
        $aumento = $this->aumentos->where('mes_desde', '<=', $mes)->last();

        return round((float) ($aumento->monto ?? $this->monto_mensual), 2);
    }

    // Lo que se cobra HOY por la cuota de un mes. Si es un mes que ya pasó,
    // se cobra al precio de hoy (si marzo se debe y en julio subió, marzo
    // se paga a precio de julio). Si es un mes futuro, al precio de ese mes.
    // Para años ya terminados cuenta como diciembre; para años futuros, enero.
    public function montoACobrar(int $mes): float
    {
        $anio = (int) $this->anioLectivo?->anio;
        $mesHoy = $anio === now()->year ? now()->month : ($anio < now()->year ? 12 : 1);

        return $this->montoParaMes(max($mes, $mesHoy));
    }

    // Fecha de vencimiento de un mes. Si el mes tiene menos días que el
    // elegido (ej. día 31 en febrero o abril), vence el último día del mes.
    public static function fechaVencimiento(int $anio, int $mes, int $dia): string
    {
        $primero = \Carbon\Carbon::create($anio, $mes, 1);

        return $primero->day(min($dia, $primero->daysInMonth))->toDateString();
    }

    // Precio que rige hoy (el de un mes "pasado" cualquiera).
    public function montoVigente(): float
    {
        return $this->montoACobrar(1);
    }
}
