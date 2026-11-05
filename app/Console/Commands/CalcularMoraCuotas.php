<?php

namespace App\Console\Commands;

use App\Support\CalculadoraMora;
use Illuminate\Console\Command;

/**
 * Corre todos los días (ver schedule() en Kernel.php): les aplica el
 * interés por mora configurado a las cuotas vencidas sin pagar de todos
 * los alumnos, en todos los años lectivos que tengan una configuración de
 * cuotas cargada. Es lo mismo que hace el botón "Calcular moras del día"
 * del panel, pero sin que nadie tenga que entrar a apretarlo.
 */
class CalcularMoraCuotas extends Command
{
    protected $signature = 'cuotas:calcular-mora';

    protected $description = 'Aplica el interés por mora a las cuotas vencidas sin pagar de todos los alumnos';

    public function handle(): int
    {
        $afectadas = CalculadoraMora::aplicarATodos();

        $this->info("Se calculó el interés por mora en {$afectadas} cuota(s) vencida(s).");

        return self::SUCCESS;
    }
}
