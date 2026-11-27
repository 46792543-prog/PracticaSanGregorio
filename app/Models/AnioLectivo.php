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

    /**
     * El año lectivo "activo" para el día de hoy. Puede haber más de un año
     * marcado Activo a la vez (se abre el ciclo siguiente por adelantado,
     * ej. 2027 activo ya en 2026, antes de que termine) — por eso nunca se
     * puede usar el primero que aparezca sin más: hay que elegir el que
     * coincide con el año calendario actual, y solo si no hay ninguno así,
     * recién ahí mirar otros (ver CorteActivo, que es la misma idea para
     * cuando Dirección puede elegir manualmente qué año operar).
     */
    public static function activo(): ?self
    {
        $activos = self::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'));

        return (clone $activos)->where('anio', now()->year)->first()
            ?? (clone $activos)->where('anio', '<=', now()->year)->orderByDesc('anio')->first()
            ?? (clone $activos)->orderBy('anio')->first();
    }

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaAlumno::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function configuracionCuota(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ConfiguracionCuota::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function configuracionMatricula(): HasMany
    {
        return $this->hasMany(ConfiguracionMatricula::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(MatriculaAlumno::class, 'id_anio_lectivo', 'id_anio_lectivo');
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
