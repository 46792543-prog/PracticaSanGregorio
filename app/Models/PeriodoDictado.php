<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoDictado extends Model
{
    protected $table = 'periodo_dictado';
    protected $primaryKey = 'id_periodo';

    protected $fillable = ['nombre_periodo'];
}
