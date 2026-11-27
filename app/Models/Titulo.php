<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Catálogo de títulos (nombre + nivel). Un docente puede tener varios.
class Titulo extends Model
{
    public const NIVELES = ['Secundario', 'Terciario', 'Universitario', 'Posgrado'];

    protected $table = 'titulo';
    protected $primaryKey = 'id_titulo';

    protected $fillable = ['nombre', 'nivel'];
}
