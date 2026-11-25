<?php

namespace App\Console\Commands;

use App\Support\RecalculadorCuotas;
use Illuminate\Console\Command;

/**
 * Corre todos los días (ver schedule() en Kernel.php): pone todas las cuotas
 * impagas al precio vigente y con la mora de hoy (ver RecalculadorCuotas).
 */
class CalcularMoraCuotas extends Command
{
    protected $signature = 'cuotas:calcular-mora';

    protected $description = 'Actualiza precio y mora de las cuotas impagas';

    public function handle(): int
    {
        $afectadas = RecalculadorCuotas::aplicarATodos();

        $this->info("Se actualizaron {$afectadas} cuota(s) impaga(s) con el precio y la mora de hoy.");

        return self::SUCCESS;
    }
}
