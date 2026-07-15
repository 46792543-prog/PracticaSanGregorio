<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnioLectivo extends Model
{
    protected $table = 'anios_lectivos';

    protected $fillable = [
        'anio',
        'estado',
    ];

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function historialMaterias(): HasMany
    {
        return $this->hasMany(HistorialMateria::class);
    }

    public function mesasExamen(): HasMany
    {
        return $this->hasMany(MesaExamen::class);
    }
}
