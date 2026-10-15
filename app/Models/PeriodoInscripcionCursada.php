<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoInscripcionCursada extends Model
{
    protected $table = 'periodo_inscripcion_cursada';
    protected $primaryKey = 'id_periodo_inscripcion';

    protected $fillable = [
        'id_anio_lectivo',
        'id_periodo',
        'id_carrera',
        'abierto',
    ];

    protected $casts = [
        'abierto' => 'boolean',
    ];

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoDictado::class, 'id_periodo', 'id_periodo');
    }
}
