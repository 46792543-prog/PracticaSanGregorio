<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionMesa extends Model
{
    protected $table = 'inscripcion_mesa';
    protected $primaryKey = 'id_inscripcion';

    protected $fillable = [
        'id_mesa',
        'id_persona_alumno',
        'fecha_inscripcion',
        'id_estado_inscripcion',
        'nota_examen',
        'resultado',
        'id_secretario_baja',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_baja' => 'datetime',
        'nota_examen' => 'decimal:2',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class, 'id_mesa', 'id_mesa');
    }

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
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
