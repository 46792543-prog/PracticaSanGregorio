<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionAlumno extends Model
{
    protected $table = 'condicion_alumno';
    protected $primaryKey = 'id_condicion';

    protected $fillable = ['nombre_condicion'];

    /**
     * Las tres formas en que una materia queda aprobada — reemplazan a la
     * vieja condición genérica "Aprobada", distinguiendo cómo se aprobó
     * para que el historial académico se pueda mostrar como un analítico.
     */
    public const APROBADA = ['Promocionada', 'Examen Final', 'Equivalencia'];

    public static function esAprobada(?string $nombreCondicion): bool
    {
        return in_array($nombreCondicion, self::APROBADA, true);
    }
}
