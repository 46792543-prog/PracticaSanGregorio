<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoRequisito extends Model
{
    protected $table = 'documento_requisito';
    protected $primaryKey = 'id_requisito';

    protected $fillable = [
        'nombre_documento',
        'es_obligatorio',
        'descripcion',
        'id_carrera',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function controlDocumentacion(): HasMany
    {
        return $this->hasMany(ControlDocumentacion::class, 'id_requisito', 'id_requisito');
    }

    // Alias de compatibilidad: el código existente usaba nombre/obligatorio.
    public function getNombreAttribute()
    {
        return $this->nombre_documento;
    }

    public function getObligatorioAttribute()
    {
        return $this->es_obligatorio;
    }
}
