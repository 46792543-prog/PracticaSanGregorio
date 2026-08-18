<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovimientoCaja extends Model
{
    protected $table = 'movimiento_caja';
    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_concepto',
        'monto',
        'fecha_movimiento',
        'descripcion_detalle',
        'id_secretario_registra',
        'id_medio_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoCaja::class, 'id_concepto', 'id_concepto');
    }

    public function secretarioRegistra(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_registra', 'id_persona');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'id_medio_pago', 'id_medio_pago');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaAlumno::class, 'id_movimiento_caja', 'id_movimiento');
    }

    // Alias de compatibilidad: antes "tipo" (ingreso/gasto) vivía en el propio movimiento.
    public function getTipoAttribute()
    {
        return $this->concepto->tipoMovimiento->nombre_tipo ?? null;
    }
}
