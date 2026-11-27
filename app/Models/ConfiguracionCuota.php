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
        'id_carrera', // cada carrera tiene su propia configuración
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

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    // Configuración de un año lectivo para una carrera.
    public static function de(?int $idAnioLectivo, ?int $idCarrera): ?self
    {
        return self::with('aumentos', 'anioLectivo')
            ->where('id_anio_lectivo', $idAnioLectivo)
            ->where('id_carrera', $idCarrera)
            ->first();
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

    // Lo que se cobra por la cuota de un mes: el precio de ESE mes. Un aumento
    // rige desde su mes en adelante (si subió en agosto, marzo a julio siguen
    // al precio anterior aunque se paguen después).
    public function montoACobrar(int $mes): float
    {
        return $this->montoParaMes($mes);
    }

    // Fecha de vencimiento de un mes. Si el mes tiene menos días que el
    // elegido (ej. día 31 en febrero o abril), vence el último día del mes.
    public static function fechaVencimiento(int $anio, int $mes, int $dia): string
    {
        $primero = \Carbon\Carbon::create($anio, $mes, 1);

        return $primero->day(min($dia, $primero->daysInMonth))->toDateString();
    }

    // Precio que rige hoy (el del mes actual). Para años ya terminados cuenta como diciembre; para años futuros, enero.
    public function montoVigente(): float
    {
        $anio = (int) $this->anioLectivo?->anio;

        return $this->montoParaMes($anio === now()->year ? now()->month : ($anio < now()->year ? 12 : 1));
    }
}
