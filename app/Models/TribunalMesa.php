<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TribunalMesa extends Model
{
    protected $table = 'tribunal_mesa';

    protected $fillable = [
        'mesa_examen_id',
        'profesor_id',
        'rol',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }
}
