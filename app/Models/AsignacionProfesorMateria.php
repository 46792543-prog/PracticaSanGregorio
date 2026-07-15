<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionProfesorMateria extends Model
{
    protected $table = 'asignaciones_profesor_materia';

    protected $fillable = [
        'profesor_id',
        'materia_id',
        'anio_lectivo_id',
        'hora_inicio',
        'hora_fin',
        'aula',
        'turno',
        'dias_cursada',
    ];

    protected $casts = [
        'dias_cursada' => 'array',
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }
}
