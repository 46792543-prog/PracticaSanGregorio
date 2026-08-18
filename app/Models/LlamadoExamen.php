<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlamadoExamen extends Model
{
    protected $table = 'llamado_examen';
    protected $primaryKey = 'id_llamado';

    protected $fillable = ['nombre_llamado'];
}
