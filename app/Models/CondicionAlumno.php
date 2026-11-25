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

    /**
     * Rindió el final y sacó menos de 4. La cursada sigue aprobada, así que
     * para el sistema cuenta como Regular: puede volver a rendir.
     */
    public const FINAL_DESAPROBADO = 'Final Desaprobado';

    /** Cursada aprobada que todavía debe el final. */
    public const REGULARIZADA = ['Regular', self::FINAL_DESAPROBADO];

    public static function esRegular(?string $nombreCondicion): bool
    {
        return in_array($nombreCondicion, self::REGULARIZADA, true);
    }

    /**
     * Nota mínima y máxima que admite cada condición de materia (null =
     * no lleva nota o es opcional sin rango propio). Promocionada pide 7 o
     * más; Final Aprobado y Equivalencia, 4 o más; Final Desaprobado, menos
     * de 4.
     */
    public const RANGO_NOTA = [
        'Promocionada' => [7, 10],
        'Examen Final' => [4, 10],
        'Equivalencia' => [4, 10],
        self::FINAL_DESAPROBADO => [1, 3],
    ];

    /** Cómo se muestra cada condición en pantalla (en la base "Examen Final" = "Final Aprobado"). */
    public const ETIQUETAS = [
        'Examen Final' => 'Final Aprobado',
    ];

    public static function etiqueta(?string $nombreCondicion): ?string
    {
        return self::ETIQUETAS[$nombreCondicion] ?? $nombreCondicion;
    }

    /**
     * Opciones del desplegable "Condición" al cargar el historial, agrupadas
     * y con una aclaración, para que no haya confusión. [grupo => [nombre en la base => texto]]
     */
    public const GRUPOS_HISTORIAL = [
        'Cursada (todavía debe el final)' => [
            'Regular' => 'Regular (aprobó la cursada, le falta rendir el final)',
            'Libre' => 'Libre (desaprobó la cursada o quedó libre por faltas)',
            'Cursando' => 'Cursando (la está cursando ahora)',
        ],
        'Materia aprobada (cerrada)' => [
            'Examen Final' => 'Final Aprobado',
            'Promocionada' => 'Promocionada (nota de 7 a 10)',
            'Equivalencia' => 'Equivalencia',
        ],
        'Materia desaprobada' => [
            self::FINAL_DESAPROBADO => 'Final Desaprobado (rindió el final y sacó menos de 4)',
        ],
    ];
}
