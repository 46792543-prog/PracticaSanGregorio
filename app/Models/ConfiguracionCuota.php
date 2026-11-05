<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionCuota extends Model
{
    protected $table = 'configuracion_cuota';
    protected $primaryKey = 'id_configuracion_cuota';

    protected $fillable = [
        'id_anio_lectivo',
        'monto_mensual',
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
}
