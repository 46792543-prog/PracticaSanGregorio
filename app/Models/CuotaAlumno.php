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
        'id_carrera', // cada carrera tiene sus propias cuotas
        'id_mes',
        'concepto',
        'fecha_vencimiento',
        'monto',
        'recargo',
        'mora_aplicada',
        'pagado',
        'fecha_pago',
        'id_movimiento_caja',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'recargo' => 'decimal:2',
        'mora_aplicada' => 'boolean',
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

    /**
     * Texto para mostrar en pantalla: el concepto puede repetirse igual en
     * varias cuotas de un mismo alumno (ej. "cuota mensual" cargado una
     * sola vez en Configuración de Cuotas y copiado a todos los meses), así
     * que acá siempre se agrega el mes para poder distinguir una tarjeta de
     * otra.
     */
    public function getEtiquetaAttribute(): string
    {
        $mes = $this->mes->nombre_mes;
        // Si cursa dos carreras, el nombre de la carrera distingue una cuota de otra.
        $carrera = $this->carrera ? " ({$this->carrera->nombre_carrera})" : '';

        return ($this->concepto ? "{$this->concepto} — {$mes}" : "Cuota {$mes}") . $carrera;
    }

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
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
