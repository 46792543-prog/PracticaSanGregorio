<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnioLectivo extends Model
{
    protected $table = 'anio_lectivo';
    protected $primaryKey = 'id_anio_lectivo';

    protected $fillable = [
        'anio',
        'id_estado_anio',
    ];

    public function estadoAnio(): BelongsTo
    {
        return $this->belongsTo(EstadoAnioLectivo::class, 'id_estado_anio', 'id_estado_anio');
    }

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaAlumno::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function historialAlumno(): HasMany
    {
        return $this->hasMany(HistorialAlumno::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function mesasExamen(): HasMany
    {
        return $this->hasMany(MesaExamen::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }
}
