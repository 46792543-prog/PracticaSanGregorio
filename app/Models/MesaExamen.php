<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MesaExamen extends Model
{
    protected $table = 'mesa_examen';
    protected $primaryKey = 'id_mesa';

    protected $fillable = [
        'id_materia',
        'id_anio_lectivo',
        'id_turno',
        'id_llamado',
        'fecha_examen',
        'fecha_inicio_inscripcion',
        'fecha_fin_inscripcion',
        'id_estado_mesa',
        'cupo_maximo',
    ];

    protected $casts = [
        'fecha_examen' => 'date',
        'fecha_inicio_inscripcion' => 'date',
        'fecha_fin_inscripcion' => 'date',
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function turnoExamen(): BelongsTo
    {
        return $this->belongsTo(TurnoExamen::class, 'id_turno', 'id_turno');
    }

    public function llamadoExamen(): BelongsTo
    {
        return $this->belongsTo(LlamadoExamen::class, 'id_llamado', 'id_llamado');
    }

    public function estadoMesa(): BelongsTo
    {
        return $this->belongsTo(EstadoMesa::class, 'id_estado_mesa', 'id_estado_mesa');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionMesa::class, 'id_mesa', 'id_mesa');
    }

    public function tribunal(): HasMany
    {
        return $this->hasMany(TribunalMesa::class, 'id_mesa', 'id_mesa');
    }

    public function acta(): HasOne
    {
        return $this->hasOne(Acta::class, 'id_mesa', 'id_mesa');
    }
}
