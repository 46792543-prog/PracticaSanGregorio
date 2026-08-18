<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedioPago extends Model
{
    protected $table = 'medio_pago';
    protected $primaryKey = 'id_medio_pago';

    protected $fillable = ['nombre_medio'];
}
