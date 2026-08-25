<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
        'anios_plazo_regularidad',
    ];

    protected $casts = [
        'nota_cursada' => 'decimal:2',
        'fecha_ultima_modificacion' => 'date',
        'anios_plazo_regularidad' => 'integer',
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

    /**
     * El plazo se cuenta en años desde el año lectivo en que el alumno
     * ingresó a la carrera (no desde el año de esta materia puntual),
     * porque así lo define la secretaría.
     */
    public function getFechaLimiteCalculadaAttribute(): ?Carbon
    {
        if (! $this->anios_plazo_regularidad) {
            return null;
        }

        $anioIngreso = $this->personaAlumno?->inscripcionesCarrera?->first()?->anioLectivo?->anio;

        if (! $anioIngreso) {
            return null;
        }

        return Carbon::create($anioIngreso + $this->anios_plazo_regularidad, 12, 31);
    }

    public function getRegularidadVencidaAttribute(): bool
    {
        return $this->condicion?->nombre_condicion === 'Regular'
            && $this->fecha_limite_calculada !== null
            && $this->fecha_limite_calculada->isPast();
    }
}
