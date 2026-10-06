<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaRespuesta extends Model
{
    protected $table = 'ficha_respuesta';
    protected $primaryKey = 'id_respuesta';

    protected $fillable = [
        'id_persona_alumno',
        'id_campo',
        'valor',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function campo(): BelongsTo
    {
        return $this->belongsTo(FichaCampo::class, 'id_campo', 'id_campo');
    }
}
