<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioCelda extends Model
{
    protected $table = 'horario_celda';
    protected $primaryKey = 'id_horario_celda';

    protected $fillable = [
        'id_horario_modulo',
        'dia_semana',
        'contenido',
    ];

    public function horarioModulo(): BelongsTo
    {
        return $this->belongsTo(HorarioModulo::class, 'id_horario_modulo', 'id_horario_modulo');
    }
}
