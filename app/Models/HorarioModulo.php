<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioModulo extends Model
{
    protected $table = 'horario_modulo';
    protected $primaryKey = 'id_horario_modulo';

    protected $fillable = [
        'id_carrera',
        'id_turno_cursada',
        'modulo',
        'hora_inicio',
        'hora_fin',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function turnoCursada(): BelongsTo
    {
        return $this->belongsTo(TurnoCursada::class, 'id_turno_cursada', 'id_turno_cursada');
    }

    public function celdas(): HasMany
    {
        return $this->hasMany(HorarioCelda::class, 'id_horario_modulo', 'id_horario_modulo');
    }
}
