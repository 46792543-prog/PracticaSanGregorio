<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equivalencia extends Model
{
    protected $table = 'equivalencia';
    protected $primaryKey = 'id_equivalencia';

    protected $fillable = [
        'id_persona_alumno',
        'id_materia_destino',
        'id_institucion_origen',
        'materia_origen_nombre',
        'num_resolucion_interna',
        'fecha_aprobacion',
        'id_director_firmante',
    ];

    protected $casts = [
        'fecha_aprobacion' => 'date',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function materiaDestino(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia_destino', 'id_materia');
    }

    public function institucionOrigen(): BelongsTo
    {
        return $this->belongsTo(InstitucionOrigen::class, 'id_institucion_origen', 'id_institucion');
    }

    public function directorFirmante(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_director_firmante', 'id_persona');
    }
}
