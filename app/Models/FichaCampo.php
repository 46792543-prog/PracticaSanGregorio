<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FichaCampo extends Model
{
    protected $table = 'ficha_campo';
    protected $primaryKey = 'id_campo';

    protected $fillable = [
        'id_seccion',
        'etiqueta',
        'tipo',
        'opciones',
        'obligatorio',
        'orden',
        'activo',
    ];

    protected $casts = [
        'opciones' => 'array',
        'obligatorio' => 'boolean',
        'activo' => 'boolean',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(FichaSeccion::class, 'id_seccion', 'id_seccion');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(FichaRespuesta::class, 'id_campo', 'id_campo');
    }
}
