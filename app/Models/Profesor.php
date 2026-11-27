<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'id_profesor';

    protected $fillable = [
        'id_persona',
        'cuil',
        'sexo',
        'fecha_ingreso',
        'condicion',
        'email',
        'activo',
        'id_secretario_baja',
        'fecha_baja',
        'id_secretario_reactiva',
        'fecha_reactivacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_baja' => 'datetime',
        'fecha_reactivacion' => 'datetime',
        'fecha_ingreso' => 'date',
    ];

    public const CONDICIONES = ['Titular', 'Interino', 'Suplente'];
    public const SEXOS = ['F' => 'Femenino', 'M' => 'Masculino', 'X' => 'No binario'];

    // Títulos del docente (varios, del catálogo de títulos).
    public function titulos(): BelongsToMany
    {
        return $this->belongsToMany(Titulo::class, 'profesor_titulo', 'id_profesor', 'id_titulo')->orderBy('nombre');
    }

    // Antigüedad desde la fecha de ingreso: "5 años y 3 meses".
    public function getAntiguedadAttribute(): ?string
    {
        if (! $this->fecha_ingreso) {
            return null;
        }

        $d = $this->fecha_ingreso->diff(now());
        $partes = array_filter([
            $d->y ? $d->y . ' año' . ($d->y === 1 ? '' : 's') : null,
            $d->m ? $d->m . ' mes' . ($d->m === 1 ? '' : 'es') : null,
        ]);

        return $partes ? implode(' y ', $partes) : 'Menos de un mes';
    }

    // CUIL con guiones: 20-12345678-9
    public function getCuilFormateadoAttribute(): ?string
    {
        return $this->cuil ? substr($this->cuil, 0, 2) . '-' . substr($this->cuil, 2, 8) . '-' . substr($this->cuil, 10) : null;
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function secretarioBaja(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_baja', 'id_persona');
    }

    public function secretarioReactiva(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_reactiva', 'id_persona');
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(EspecialidadProfesor::class, 'profesor_especialidad', 'id_profesor', 'id_especialidad');
    }

    public function getEspecialidadesTextoAttribute(): string
    {
        return $this->especialidades->pluck('nombre_especialidad')->join(', ');
    }

    // Proxies de identidad: el profesor ya no duplica estos datos, vienen de persona.
    public function getNombreAttribute()
    {
        return $this->persona->nombre ?? null;
    }

    public function getApellidoAttribute()
    {
        return $this->persona->apellido ?? null;
    }

    public function getDniAttribute()
    {
        return $this->persona->dni ?? null;
    }

    public function getTelefonoAttribute()
    {
        return $this->persona->telefono ?? null;
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class, 'id_profesor', 'id_profesor');
    }

    public function participacionesTribunal(): HasMany
    {
        return $this->hasMany(TribunalMesa::class, 'id_profesor', 'id_profesor');
    }
}
