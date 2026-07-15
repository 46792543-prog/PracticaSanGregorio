<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionCarrera extends Model
{
    protected $table = 'inscripciones_carrera';

    protected $fillable = [
        'user_id',
        'carrera_id',
        'anio_lectivo_id',
        'anio_actual',
        'turno',
        'condicion',
        'estado',
        'fecha_inscripcion',
        'secretario_registra_id',
        'secretario_baja_id',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_baja' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }

    public function secretarioRegistra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretario_registra_id');
    }

    public function secretarioBaja(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretario_baja_id');
    }
}
