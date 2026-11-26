<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plan de estudios de una carrera, con vigencia:
 *  - anio_desde: desde qué año lectivo rige.
 *  - anio_cierre_inscripcion: último año en que entran alumnos nuevos (vacío = sigue abierto).
 *  - anio_caducidad: año en que caduca; hasta ahí siguen los alumnos que ya estaban (vacío = sin fecha).
 */
class PlanEstudio extends Model
{
    protected $table = 'plan_estudio';
    protected $primaryKey = 'id_plan_estudio';

    protected $fillable = [
        'id_carrera', 'nombre', 'resolucion_ministerial', 'duracion_anos',
        'anio_desde', 'anio_cierre_inscripcion', 'anio_caducidad',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'id_plan_estudio', 'id_plan_estudio');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_plan_estudio', 'id_plan_estudio');
    }

    // ¿Puede entrar un alumno nuevo en ese año lectivo?
    public function aceptaInscripciones(int $anio): bool
    {
        return $this->anio_desde <= $anio
            && ($this->anio_cierre_inscripcion === null || $anio <= $this->anio_cierre_inscripcion)
            && ($this->anio_caducidad === null || $anio <= $this->anio_caducidad);
    }

    // Estado hoy: Próximo, Vigente, En extinción (ya no entran nuevos) o Caducado.
    public function estado(): string
    {
        $hoy = (int) now()->year;

        return match (true) {
            $this->anio_desde > $hoy => 'Próximo',
            $this->anio_caducidad !== null && $hoy > $this->anio_caducidad => 'Caducado',
            $this->anio_cierre_inscripcion !== null && $hoy > $this->anio_cierre_inscripcion => 'En extinción',
            default => 'Vigente',
        };
    }

    // Con alumnos inscriptos, sus materias y correlativas ya no se tocan: se crea un plan nuevo.
    public function tieneAlumnos(): bool
    {
        return $this->inscripciones()->exists();
    }
}
