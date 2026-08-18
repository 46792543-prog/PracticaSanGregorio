<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carrera';
    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'nombre_carrera',
        'familia_profesional',
        'duracion_anos',
        'resolucion_ministerial',
        'id_estado_carrera',
    ];

    public function getNombreAttribute()
    {
        return $this->nombre_carrera;
    }

    public function estadoCarrera(): BelongsTo
    {
        return $this->belongsTo(EstadoCarrera::class, 'id_estado_carrera', 'id_estado_carrera');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'id_carrera', 'id_carrera');
    }

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_carrera', 'id_carrera');
    }

    public function documentoRequisitos(): HasMany
    {
        return $this->hasMany(DocumentoRequisito::class, 'id_carrera', 'id_carrera');
    }
}
