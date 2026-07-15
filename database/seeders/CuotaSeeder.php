<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\Cuota;
use App\Models\User;
use Illuminate\Database\Seeder;

class CuotaSeeder extends Seeder
{
    public function run(): void
    {
        $lucia = User::where('dni', '41205678')->firstOrFail();
        $anioLectivo = AnioLectivo::where('anio', 2026)->firstOrFail();

        $cuotas = [
            ['mes' => 'Enero', 'vencimiento' => '2026-01-10', 'estado' => 'pagado', 'fecha_pago' => '2026-01-05', 'medio_pago' => 'transferencia'],
            ['mes' => 'Febrero', 'vencimiento' => '2026-02-10', 'estado' => 'pagado', 'fecha_pago' => '2026-02-04', 'medio_pago' => 'transferencia'],
            ['mes' => 'Marzo', 'vencimiento' => '2026-03-10', 'estado' => 'pagado', 'fecha_pago' => '2026-03-03', 'medio_pago' => 'efectivo'],
            ['mes' => 'Abril', 'vencimiento' => '2026-04-10', 'estado' => 'pagado', 'fecha_pago' => '2026-04-08', 'medio_pago' => 'transferencia'],
            ['mes' => 'Mayo', 'vencimiento' => '2026-05-10', 'estado' => 'pagado', 'fecha_pago' => '2026-05-06', 'medio_pago' => 'efectivo'],
            ['mes' => 'Junio', 'vencimiento' => '2026-06-10', 'estado' => 'pagado', 'fecha_pago' => '2026-06-14', 'medio_pago' => 'transferencia'],
            ['mes' => 'Julio', 'vencimiento' => '2026-07-10', 'estado' => 'pendiente', 'fecha_pago' => null, 'medio_pago' => null],
        ];

        foreach ($cuotas as $cuota) {
            Cuota::updateOrCreate(
                [
                    'user_id' => $lucia->id,
                    'anio_lectivo_id' => $anioLectivo->id,
                    'concepto' => "Cuota {$cuota['mes']} 2026",
                ],
                [
                    'monto' => 18000.00,
                    'fecha_vencimiento' => $cuota['vencimiento'],
                    'estado' => $cuota['estado'],
                    'fecha_pago' => $cuota['fecha_pago'],
                    'medio_pago' => $cuota['medio_pago'],
                ]
            );
        }
    }
}
