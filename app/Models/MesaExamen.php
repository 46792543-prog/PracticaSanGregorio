<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MesaExamen extends Model
{
    protected $table = 'mesas_examen';

    protected $fillable = [
        'materia_id',
        'anio_lectivo_id',
        'turno',
        'llamado',
        'fecha_examen',
        'fecha_inicio_inscripcion',
        'fecha_fin_inscripcion',
        'estado',
    ];

    protected $casts = [
        'fecha_examen' => 'date',
        'fecha_inicio_inscripcion' => 'date',
        'fecha_fin_inscripcion' => 'date',
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionMesa::class);
    }

    public function tribunal(): HasMany
    {
        return $this->hasMany(TribunalMesa::class);
    }

    public function acta(): HasOne
    {
        return $this->hasOne(Acta::class);
    }
}
