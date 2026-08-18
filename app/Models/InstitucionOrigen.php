<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitucionOrigen extends Model
{
    protected $table = 'institucion_origen';
    protected $primaryKey = 'id_institucion';

    protected $fillable = ['nombre_institucion', 'localidad'];

    public function equivalencias()
    {
        return $this->hasMany(Equivalencia::class, 'id_institucion_origen', 'id_institucion');
    }
}
