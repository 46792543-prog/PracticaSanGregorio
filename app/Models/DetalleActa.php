<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleActa extends Model
{
    protected $table = 'detalle_acta';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_acta',
        'id_persona_alumno',
        'nota_escrito',
        'nota_oral',
        'nota_final',
        'resultado',
    ];

    protected $casts = [
        'nota_escrito' => 'decimal:2',
        'nota_oral' => 'decimal:2',
        'nota_final' => 'decimal:2',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class, 'id_acta', 'id_acta');
    }

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }
}
