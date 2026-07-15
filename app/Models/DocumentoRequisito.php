<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoRequisito extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'obligatorio',
        'carrera_id',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function documentosAlumno(): HasMany
    {
        return $this->hasMany(DocumentoAlumno::class);
    }
}
