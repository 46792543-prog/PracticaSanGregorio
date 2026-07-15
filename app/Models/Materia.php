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

    protected $fillable = [
        'carrera_id',
        'nombre',
        'numero_orden',
        'anio_cursada',
        'cuatrimestre',
        'regimen',
        'version_plan',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialMateria::class);
    }

    public function mesasExamen(): HasMany
    {
        return $this->hasMany(MesaExamen::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class);
    }

    /**
     * Materias que esta materia necesita como correlativa.
     */
    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativas',
            'materia_id',
            'materia_requisito_id'
        )->withPivot('requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Materias que tienen a esta materia como correlativa.
     */
    public function esRequisitoDe(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativas',
            'materia_requisito_id',
            'materia_id'
        )->withPivot('requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Devuelve la primera correlativa que el alumno todavía no cumple para
     * cursar/rendir esta materia, o null si tiene todas cumplidas.
     */
    public function correlativaFaltante(User $alumno): ?Materia
    {
        $condiciones = $alumno->historialMaterias()
            ->whereIn('materia_id', $this->requisitos->pluck('id'))
            ->pluck('condicion', 'materia_id');

        foreach ($this->requisitos as $requisito) {
            $condicion = $condiciones->get($requisito->id);
            $cumplida = $requisito->pivot->requiere_aprobada
                ? $condicion === 'aprobada'
                : (! $requisito->pivot->requiere_regularizada || in_array($condicion, ['regular', 'aprobada']));

            if (! $cumplida) {
                return $requisito;
            }
        }

        return null;
    }
}
