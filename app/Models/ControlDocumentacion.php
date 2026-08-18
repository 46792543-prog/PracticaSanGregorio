<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlDocumentacion extends Model
{
    protected $table = 'control_documentacion';
    protected $primaryKey = 'id_control';

    protected $fillable = [
        'id_persona_alumno',
        'id_requisito',
        'fecha_entrega',
        'id_secretario_recibe',
        'id_estado_documento',
        'fecha_aprobacion',
        'observaciones',
        'archivo_path',
        'archivo_nombre_original',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_aprobacion' => 'date',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function documentoRequisito(): BelongsTo
    {
        return $this->belongsTo(DocumentoRequisito::class, 'id_requisito', 'id_requisito');
    }

    public function secretarioRecibe(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_recibe', 'id_persona');
    }

    public function estadoDocumento(): BelongsTo
    {
        return $this->belongsTo(EstadoDocumento::class, 'id_estado_documento', 'id_estado_documento');
    }
}
