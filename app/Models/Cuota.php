<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuota extends Model
{
    protected $fillable = [
        'user_id',
        'anio_lectivo_id',
        'concepto',
        'monto',
        'recargo',
        'fecha_vencimiento',
        'estado',
        'fecha_pago',
        'medio_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'recargo' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function montoTotal(): string
    {
        return bcadd((string) $this->monto, (string) $this->recargo, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }
}
