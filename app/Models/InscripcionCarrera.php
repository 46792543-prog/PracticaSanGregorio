<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionCarrera extends Model
{
    protected $table = 'inscripcion_carrera';
    protected $primaryKey = 'id_inscripcion_carrera';

    protected $fillable = [
        'id_persona_alumno',
        'id_carrera',
        'id_anio_cursada',
        'id_anio_lectivo',
        'id_turno_cursada',
        'id_condicion',
        'fecha_inscripcion',
        'id_secretario_registra',
        'id_estado_inscripcion',
        'id_secretario_baja',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_baja' => 'datetime',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function anioCursada(): BelongsTo
    {
        return $this->belongsTo(AnioCursada::class, 'id_anio_cursada', 'id_anio_cursada');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function turnoCursada(): BelongsTo
    {
        return $this->belongsTo(TurnoCursada::class, 'id_turno_cursada', 'id_turno_cursada');
    }

    public function condicion(): BelongsTo
    {
        return $this->belongsTo(CondicionAlumno::class, 'id_condicion', 'id_condicion');
    }

    public function secretarioRegistra(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_registra', 'id_persona');
    }

    public function estadoInscripcion(): BelongsTo
    {
        return $this->belongsTo(EstadoInscripcion::class, 'id_estado_inscripcion', 'id_estado_inscripcion');
    }

    public function secretarioBaja(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_baja', 'id_persona');
    }
}
