<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correlativa extends Model
{
    protected $fillable = [
        'materia_id',
        'materia_requisito_id',
        'requiere_regularizada',
        'requiere_aprobada',
    ];

    protected $casts = [
        'requiere_regularizada' => 'boolean',
        'requiere_aprobada' => 'boolean',
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function materiaRequisito(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_requisito_id');
    }
}
