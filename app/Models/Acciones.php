<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acciones extends Model
{
    protected $table = 'acciones';
    protected $primaryKey = 'idAcciones';

    protected $fillable = [
        'desde',
        'hasta',
        'id_persona',
        'id_tipo_accion',
        'observaciones',
    ];

    protected $casts = [
        'desde' => 'date',
        'hasta' => 'date',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function tipoAccion(): BelongsTo
    {
        return $this->belongsTo(TipoAccion::class, 'id_tipo_accion', 'id_tipo_accion');
    }
}
