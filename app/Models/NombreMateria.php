<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NombreMateria extends Model
{
    protected $table = 'nombre_materia';
    protected $primaryKey = 'id_nombre_materia';

    protected $fillable = ['nombre'];
}
