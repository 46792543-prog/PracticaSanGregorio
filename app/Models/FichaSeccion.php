<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FichaSeccion extends Model
{
    protected $table = 'ficha_seccion';
    protected $primaryKey = 'id_seccion';

    protected $fillable = ['nombre', 'orden', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function campos(): HasMany
    {
        return $this->hasMany(FichaCampo::class, 'id_seccion', 'id_seccion')->orderBy('orden');
    }
}
