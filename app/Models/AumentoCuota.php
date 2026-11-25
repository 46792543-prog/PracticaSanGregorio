<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aumento de la cuota mensual: desde mes_desde (inclusive) en adelante la
 * cuota pasa a valer "monto", hasta el próximo aumento.
 */
class AumentoCuota extends Model
{
    protected $table = 'aumento_cuota';
    protected $primaryKey = 'id_aumento_cuota';

    protected $fillable = [
        'id_configuracion_cuota',
        'mes_desde',
        'monto',
    ];

    protected $casts = [
        'mes_desde' => 'integer',
        'monto' => 'decimal:2',
    ];

    public function configuracionCuota(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionCuota::class, 'id_configuracion_cuota', 'id_configuracion_cuota');
    }
}
