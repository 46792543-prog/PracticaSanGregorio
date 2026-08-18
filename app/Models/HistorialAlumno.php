<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialAlumno extends Model
{
    protected $table = 'historial_alumno';
    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_persona_alumno',
        'id_materia',
        'id_anio_lectivo',
        'id_condicion',
        'nota_cursada',
        'fecha_ultima_modificacion',
    ];

    protected $casts = [
        'nota_cursada' => 'decimal:2',
        'fecha_ultima_modificacion' => 'date',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function condicion(): BelongsTo
    {
        return $this->belongsTo(CondicionAlumno::class, 'id_condicion', 'id_condicion');
    }
}
