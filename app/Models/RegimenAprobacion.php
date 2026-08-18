<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegimenAprobacion extends Model
{
    protected $table = 'regimen_aprobacion';
    protected $primaryKey = 'id_regimen';

    protected $fillable = ['nombre_regimen'];
}
