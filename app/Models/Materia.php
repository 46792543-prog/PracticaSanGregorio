<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materia';
    protected $primaryKey = 'id_materia';

    protected $fillable = [
        'id_carrera',
        'numero_orden',
        'id_anio_cursada',
        'id_periodo',
        'id_regimen',
        'id_nombre_materia',
        'version_plan',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function anioCursada(): BelongsTo
    {
        return $this->belongsTo(AnioCursada::class, 'id_anio_cursada', 'id_anio_cursada');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoDictado::class, 'id_periodo', 'id_periodo');
    }

    public function regimen(): BelongsTo
    {
        return $this->belongsTo(RegimenAprobacion::class, 'id_regimen', 'id_regimen');
    }

    public function nombreMateria(): BelongsTo
    {
        return $this->belongsTo(NombreMateria::class, 'id_nombre_materia', 'id_nombre_materia');
    }

    public function getNombreAttribute()
    {
        return $this->nombreMateria->nombre ?? null;
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialAlumno::class, 'id_materia', 'id_materia');
    }

    public function mesasExamen(): HasMany
    {
        return $this->hasMany(MesaExamen::class, 'id_materia', 'id_materia');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class, 'id_materia', 'id_materia');
    }

    /**
     * Materias que esta materia necesita como correlativa.
     */
    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativa',
            'id_materia_principal',
            'id_materia_requisito'
        )->withPivot('id_tipo_correlativa', 'requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Materias que tienen a esta materia como correlativa.
     */
    public function esRequisitoDe(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativa',
            'id_materia_requisito',
            'id_materia_principal'
        )->withPivot('id_tipo_correlativa', 'requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Devuelve la primera correlativa que el alumno todavía no cumple para
     * cursar/rendir esta materia, o null si tiene todas cumplidas.
     */
    public function correlativaFaltante(Persona $alumno): ?Materia
    {
        $historialPorMateria = $alumno->historialAlumno()
            ->whereIn('id_materia', $this->requisitos->pluck('id_materia'))
            ->get()
            ->keyBy('id_materia');

        foreach ($this->requisitos as $requisito) {
            $condicionNombre = $historialPorMateria->get($requisito->id_materia)?->condicion?->nombre_condicion;

            $cumplida = $requisito->pivot->requiere_aprobada
                ? $condicionNombre === 'Aprobada'
                : (! $requisito->pivot->requiere_regularizada || in_array($condicionNombre, ['Regular', 'Aprobada'], true));

            if (! $cumplida) {
                return $requisito;
            }
        }

        return null;
    }
}
