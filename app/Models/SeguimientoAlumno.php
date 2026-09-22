<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoAlumno extends Model
{
    protected $table = 'seguimiento_alumno';
    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'id_persona_alumno',
        'id_persona_autor',
        'texto',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_autor', 'id_persona');
    }
}
