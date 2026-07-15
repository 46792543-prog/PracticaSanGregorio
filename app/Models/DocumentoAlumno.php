<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoAlumno extends Model
{
    protected $table = 'documentos_alumno';

    protected $fillable = [
        'user_id',
        'documento_requisito_id',
        'estado',
        'fecha_entrega',
        'fecha_aprobacion',
        'secretario_revisa_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_aprobacion' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentoRequisito(): BelongsTo
    {
        return $this->belongsTo(DocumentoRequisito::class);
    }

    public function secretarioRevisa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretario_revisa_id');
    }
}
