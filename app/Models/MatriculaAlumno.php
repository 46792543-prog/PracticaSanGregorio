<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaAlumno extends Model
{
    protected $table = 'matricula_alumno';
    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'id_persona_alumno',
        'id_anio_lectivo',
        'monto',
        'pagado',
        'fecha_pago',
        'id_movimiento_caja',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado' => 'boolean',
        'fecha_pago' => 'date',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function movimientoCaja(): BelongsTo
    {
        return $this->belongsTo(MovimientoCaja::class, 'id_movimiento_caja', 'id_movimiento');
    }
}
