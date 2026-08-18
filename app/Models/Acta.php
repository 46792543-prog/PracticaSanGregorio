<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acta extends Model
{
    protected $table = 'acta';
    protected $primaryKey = 'id_acta';

    protected $fillable = [
        'libro',
        'folio',
        'id_mesa',
        'id_tipo_acta',
        'estado',
        'observaciones',
        'fecha_generacion',
        'id_secretario_creador',
        'id_director_firmante',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class, 'id_mesa', 'id_mesa');
    }

    public function tipoActa(): BelongsTo
    {
        return $this->belongsTo(TipoActa::class, 'id_tipo_acta', 'id_tipo_acta');
    }

    public function secretarioCreador(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_creador', 'id_persona');
    }

    public function directorFirmante(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_director_firmante', 'id_persona');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleActa::class, 'id_acta', 'id_acta');
    }
}
