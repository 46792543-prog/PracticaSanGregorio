<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuotaAlumno extends Model
{
    protected $table = 'cuota_alumno';
    protected $primaryKey = 'id_cuota';

    protected $fillable = [
        'id_persona_alumno',
        'id_anio_lectivo',
        'id_mes',
        'concepto',
        'fecha_vencimiento',
        'monto',
        'recargo',
        'pagado',
        'fecha_pago',
        'id_movimiento_caja',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'recargo' => 'decimal:2',
        'pagado' => 'boolean',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function montoTotal(): float
    {
        return (float) bcadd((string) $this->monto, (string) $this->recargo, 2);
    }

    public function getVencidaAttribute(): bool
    {
        return ! $this->pagado && $this->fecha_vencimiento !== null && $this->fecha_vencimiento->isPast();
    }

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function mes(): BelongsTo
    {
        return $this->belongsTo(Mes::class, 'id_mes', 'id_mes');
    }

    public function movimientoCaja(): BelongsTo
    {
        return $this->belongsTo(MovimientoCaja::class, 'id_movimiento_caja', 'id_movimiento');
    }
}
