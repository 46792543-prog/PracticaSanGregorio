<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Catálogo de títulos (nombre + nivel). Un docente puede tener varios.
class Titulo extends Model
{
    public const NIVELES = ['Secundario', 'Terciario', 'Universitario', 'Posgrado'];

    protected $table = 'titulo';
    protected $primaryKey = 'id_titulo';

    protected $fillable = ['nombre', 'nivel'];

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_titulo', 'id_titulo', 'id_profesor');
    }
}
