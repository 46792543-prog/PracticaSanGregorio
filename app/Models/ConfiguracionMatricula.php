<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionMatricula extends Model
{
    protected $table = 'configuracion_matricula';
    protected $primaryKey = 'id_configuracion_matricula';

    protected $fillable = [
        'id_anio_lectivo',
        'monto',
        'mes_habilitacion',
        'dia_limite',
        'concepto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }
}
