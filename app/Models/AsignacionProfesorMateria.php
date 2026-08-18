<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionProfesorMateria extends Model
{
    protected $table = 'asignacion_profesor_materia';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_profesor',
        'id_materia',
        'id_anio_lectivo',
        'fecha_asignacion',
        'id_anio_cursada',
        'id_turno_cursada',
        'aula',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function anioCursada(): BelongsTo
    {
        return $this->belongsTo(AnioCursada::class, 'id_anio_cursada', 'id_anio_cursada');
    }

    public function turnoCursada(): BelongsTo
    {
        return $this->belongsTo(TurnoCursada::class, 'id_turno_cursada', 'id_turno_cursada');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioAsignacion::class, 'id_asignacion', 'id_asignacion');
    }
}
