<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleActa extends Model
{
    protected $table = 'detalle_acta';

    protected $fillable = [
        'acta_id',
        'user_id',
        'nota_escrito',
        'nota_oral',
        'nota_promedio',
        'resultado',
    ];

    protected $casts = [
        'nota_escrito' => 'decimal:2',
        'nota_oral' => 'decimal:2',
        'nota_promedio' => 'decimal:2',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
