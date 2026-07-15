<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesores';

    protected $fillable = [
        'dni',
        'nombre',
        'apellido',
        'especialidad',
        'telefono',
        'email',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class);
    }

    public function participacionesTribunal(): HasMany
    {
        return $this->hasMany(TribunalMesa::class);
    }
}
