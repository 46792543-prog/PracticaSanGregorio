<?php

namespace App\Support;

use Carbon\Carbon;

class FechaEsp
{
    private const DIAS = [
        'Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado',
    ];

    private const MESES = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    /**
     * Ej: "Lunes, 15 de junio de 2026"
     */
    public static function larga(Carbon $fecha): string
    {
        $dia = self::DIAS[$fecha->format('l')];
        $mes = self::MESES[$fecha->month];

        return "{$dia}, {$fecha->day} de {$mes} de {$fecha->year}";
    }

    /**
     * Ej: "15/06/2026"
     */
    public static function corta(?Carbon $fecha): ?string
    {
        return $fecha?->format('d/m/Y');
    }

    /**
     * Ej: "JUL"
     */
    public static function mesAbrev(Carbon $fecha): string
    {
        return mb_strtoupper(mb_substr(self::MESES[$fecha->month], 0, 3));
    }

    /**
     * Ej: "Junio 2026"
     */
    public static function mesAnio(Carbon $fecha): string
    {
        return ucfirst(self::MESES[$fecha->month]) . ' ' . $fecha->year;
    }
}
