<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionMesa extends Model
{
    protected $table = 'inscripciones_mesa';

    protected $fillable = [
        'mesa_examen_id',
        'user_id',
        'fecha_inscripcion',
        'estado',
        'nota_examen',
        'resultado',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'nota_examen' => 'decimal:2',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
