<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Un correo enviado a mano a un alumno desde la Central de Alertas (con su resultado).
class ComunicacionAlumno extends Model
{
    protected $table = 'comunicacion_alumno';
    protected $primaryKey = 'id_comunicacion';

    protected $fillable = ['id_persona_alumno', 'destinatario', 'asunto', 'mensaje', 'enviado', 'error', 'id_persona_envia'];

    protected $casts = ['enviado' => 'boolean'];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_envia', 'id_persona');
    }
}
