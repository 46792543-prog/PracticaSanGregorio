<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Catálogo de licencias docentes: artículo, topes de días y si es con goce de sueldo.
class TipoLicencia extends Model
{
    protected $table = 'tipo_licencia';
    protected $primaryKey = 'id_tipo_licencia';

    protected $fillable = ['nombre', 'articulo', 'tope_anual', 'tope_mensual', 'con_goce'];

    protected $casts = ['con_goce' => 'boolean', 'tope_anual' => 'integer', 'tope_mensual' => 'integer'];

    // Ej: "Art. 115 · tope 6 días por año · máximo 2 por mes · con goce de sueldo"
    public function getResumenAttribute(): string
    {
        return collect([
            $this->articulo ? "Art. {$this->articulo}" : 'Artículo sin cargar',
            $this->tope_anual ? "tope {$this->tope_anual} días por año" : null,
            $this->tope_mensual ? "máximo {$this->tope_mensual} por mes" : null,
            $this->con_goce ? 'con goce de sueldo' : 'sin goce de sueldo (se descuenta)',
        ])->filter()->join(' · ');
    }
}
