<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenLanding extends Model
{
    protected $table = 'imagen_landing';
    protected $primaryKey = 'id_imagen';

    protected $fillable = ['path', 'orden'];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
