<?php

namespace Database\Seeders;

use App\Models\Cuota;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Database\Seeder;

class MovimientoCajaSeeder extends Seeder
{
    public function run(): void
    {
        $secretaria = User::where('rol', 'secretario')->firstOrFail();

        // Un ingreso en caja por cada cuota que ya está pagada.
        Cuota::where('estado', 'pagado')->with('user')->get()->each(function (Cuota $cuota) use ($secretaria) {
            MovimientoCaja::updateOrCreate(
                ['cuota_id' => $cuota->id],
                [
                    'tipo' => 'ingreso',
                    'concepto' => "Cobro {$cuota->concepto} — {$cuota->user->apellido}, {$cuota->user->nombre}",
                    'monto' => $cuota->monto,
                    'fecha_movimiento' => $cuota->fecha_pago,
                    'turno' => 'Mañana',
                    'registrado_por_id' => $secretaria->id,
                ]
            );
        });

        // Un par de gastos de ejemplo, como en el libro de caja del Figma.
        $gastos = [
            ['concepto' => 'Compra material de librería', 'monto' => 8500, 'dias_atras' => 12, 'turno' => 'Tarde'],
            ['concepto' => 'Pago servicios (luz, agua)', 'monto' => 12700, 'dias_atras' => 6, 'turno' => 'Mañana'],
        ];

        foreach ($gastos as $gasto) {
            MovimientoCaja::updateOrCreate(
                ['concepto' => $gasto['concepto'], 'tipo' => 'gasto'],
                [
                    'monto' => $gasto['monto'],
                    'fecha_movimiento' => now()->subDays($gasto['dias_atras']),
                    'turno' => $gasto['turno'],
                    'registrado_por_id' => $secretaria->id,
                ]
            );
        }
    }
}
