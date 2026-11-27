<?php

namespace App\Support;

/**
 * CUIL (mismo cálculo que SIGEDOC / AFIP):
 *   prefijo (20 varón, 27 mujer, 23 si el dígito da 10) + DNI (8 dígitos) + dígito verificador.
 *   Dígito: se multiplica cada número por 5,4,3,2,7,6,5,4,3,2, se suma, y 11 - (suma % 11)
 *   (11 → 0; 10 → no vale, se usa el prefijo 23).
 */
class Cuil
{
    private const PESOS = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    private const PREFIJOS = ['20', '23', '24', '27'];

    // Dígito verificador de los 10 primeros números (null si da 10, que no es válido).
    public static function digito(string $diez): ?int
    {
        $suma = 0;
        foreach (str_split($diez) as $i => $n) {
            $suma += (int) $n * self::PESOS[$i];
        }
        $digito = 11 - ($suma % 11);

        return match ($digito) { 11 => 0, 10 => null, default => $digito };
    }

    // CUIL que le corresponde a un DNI según el sexo (para completarlo solo).
    public static function calcular(string $dni, ?string $sexo): ?string
    {
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);
        $prefijo = $sexo === 'F' ? '27' : '20';

        if (($d = self::digito($prefijo . $dni)) !== null) {
            return $prefijo . $dni . $d;
        }

        // Caso especial: si da 10 se usa 23 (termina en 9 varón, 4 mujer).
        return '23' . $dni . ($sexo === 'F' ? '4' : '9');
    }

    // Motivo por el que el CUIL no vale (o null si está bien).
    public static function error(string $cuil, string $dni): ?string
    {
        if (! preg_match('/^\d{11}$/', $cuil)) {
            return 'El CUIL tiene que tener 11 números, sin guiones.';
        }

        if (! in_array(substr($cuil, 0, 2), self::PREFIJOS, true)) {
            return 'El CUIL tiene que empezar con 20, 23, 24 o 27.';
        }

        if (substr($cuil, 2, 8) !== str_pad($dni, 8, '0', STR_PAD_LEFT)) {
            return 'El CUIL no coincide con el DNI del docente.';
        }

        if (self::digito(substr($cuil, 0, 10)) !== (int) $cuil[10]) {
            return 'El CUIL no es válido: el último número (dígito verificador) no corresponde.';
        }

        return null;
    }
}
