<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'familia_profesional',
        'duracion_anios',
        'resolucion_ministerial',
        'estado',
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class);
    }

    public function documentoRequisitos(): HasMany
    {
        return $this->hasMany(DocumentoRequisito::class);
    }
}
