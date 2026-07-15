<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acta extends Model
{
    protected $fillable = [
        'mesa_examen_id',
        'libro',
        'folio',
        'fecha_generacion',
        'secretario_id',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class);
    }

    public function secretario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleActa::class);
    }
}
