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

    /**
     * Condiciones de la inscripción a la carrera (no de cada materia):
     *  - Regular: cursa normalmente y paga la cuota mensual completa.
     *  - Recursante: vuelve a cursar solo algunas materias. Su cuota depende
     *    de cuántas materias cursa, así que no se le genera sola: el director
     *    se la genera desde "Registrar Cuotas" con el monto que corresponda.
     *  - Cursada completa: ya terminó de cursar y solo debe finales. No paga
     *    cuotas mensuales: solo la matrícula del año en que rinde y la cuota
     *    del mes de la mesa. Es el único que puede anotarse a mesas
     *    extraordinarias.
     */
    public const REGULAR = 'Regular';
    public const RECURSANTE = 'Recursante';
    public const CURSADA_COMPLETA = 'Cursada completa';
    public const DE_INSCRIPCION = [self::REGULAR, self::RECURSANTE, self::CURSADA_COMPLETA];

    public static function esAprobada(?string $nombreCondicion): bool
    {
        return in_array($nombreCondicion, self::APROBADA, true);
    }
}
