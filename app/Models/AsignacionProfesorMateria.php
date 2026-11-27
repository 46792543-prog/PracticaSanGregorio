<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Designación de un profesor en una materia (antes "asignación"): horas
 * cátedra, tipo (Titular / Interino / Suplente), fecha de alta, fecha de baja
 * prevista, estado y resolución. Sus horarios por día están en horarios().
 */
class AsignacionProfesorMateria extends Model
{
    public const TIPOS = ['Titular', 'Interino', 'Suplente', 'Provisional'];
    public const DIAS_POR_VENCER = 30;

    protected $table = 'asignacion_profesor_materia';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_profesor',
        'id_materia',
        'id_anio_lectivo',
        'fecha_asignacion',
        'id_anio_cursada',
        'id_turno_cursada',
        'aula',
        'horas_catedra',
        'tipo',
        'fecha_alta',
        'fecha_baja',
        'activa',
        'resolucion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'activa' => 'boolean',
        'horas_catedra' => 'integer',
    ];

    // Vigentes: activas y sin fecha de baja ya pasada (son las que ocupan las horas de la materia).
    public function scopeVigentes($query)
    {
        return $query->where('activa', true)
            ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhereDate('fecha_baja', '>=', today()));
    }

    // Activa / Por vencer (baja en 30 días o menos) / Vencida / Finalizada.
    public function getEstadoAttribute(): string
    {
        return match (true) {
            ! $this->activa => 'Finalizada',
            $this->fecha_baja && $this->fecha_baja->lt(today()) => 'Vencida',
            $this->fecha_baja && $this->fecha_baja->lte(today()->addDays(self::DIAS_POR_VENCER)) => 'Por vencer',
            default => 'Activa',
        };
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function anioCursada(): BelongsTo
    {
        return $this->belongsTo(AnioCursada::class, 'id_anio_cursada', 'id_anio_cursada');
    }

    public function turnoCursada(): BelongsTo
    {
        return $this->belongsTo(TurnoCursada::class, 'id_turno_cursada', 'id_turno_cursada');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioAsignacion::class, 'id_asignacion', 'id_asignacion');
    }

    public function inasistencias(): HasMany
    {
        return $this->hasMany(InasistenciaDocente::class, 'id_asignacion', 'id_asignacion');
    }
}
