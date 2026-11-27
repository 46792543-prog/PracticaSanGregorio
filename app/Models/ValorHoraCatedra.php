<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Valor de la hora cátedra por carrera y tipo de designación, desde un mes en adelante.
class ValorHoraCatedra extends Model
{
    protected $table = 'valor_hora_catedra';
    protected $primaryKey = 'id_valor_hora_catedra';

    protected $fillable = ['id_carrera', 'tipo', 'monto', 'vigente_desde'];

    protected $casts = [
        'monto' => 'decimal:2',
        'vigente_desde' => 'date',
    ];

    public const TOPE_POR_DEFECTO = 80000;

    // Máximo que se puede cargar por hora cátedra (configurable en Liquidación de horas).
    public static function tope(): float
    {
        return (float) (ConfiguracionInstitucion::first()?->tope_valor_hora_catedra ?? self::TOPE_POR_DEFECTO);
    }

    // Valor que rige para esa carrera y tipo en ese mes (el último cargado desde ese mes o antes).
    public static function para(int $idCarrera, string $tipo, Carbon $periodo): ?self
    {
        return self::where('id_carrera', $idCarrera)->where('tipo', $tipo)
            ->whereDate('vigente_desde', '<=', $periodo->copy()->startOfMonth())
            ->orderByDesc('vigente_desde')
            ->first();
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }
}
