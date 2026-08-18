<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolTribunal extends Model
{
    protected $table = 'rol_tribunal';
    protected $primaryKey = 'id_rol_tribunal';

    protected $fillable = ['nombre_rol'];
}
