<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EspecialidadProfesor extends Model
{
    protected $table = 'especialidad_profesor';
    protected $primaryKey = 'id_especialidad';

    protected $fillable = ['nombre_especialidad'];

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_especialidad', 'id_especialidad', 'id_profesor');
    }
}
