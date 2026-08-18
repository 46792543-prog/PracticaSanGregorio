<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'id_profesor';

    protected $fillable = [
        'id_persona',
        'id_especialidad',
        'email',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(EspecialidadProfesor::class, 'id_especialidad', 'id_especialidad');
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
