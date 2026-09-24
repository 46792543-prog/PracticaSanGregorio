<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IngresosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $movimientos)
    {
    }

    public function collection(): Collection
    {
        return $this->movimientos;
    }

    public function headings(): array
    {
        return ['Fecha', 'Concepto', 'Descripción', 'Medio de pago', 'Registrado por', 'Monto'];
    }

    public function map($movimiento): array
    {
        return [
            $movimiento->fecha_movimiento->format('d/m/Y H:i'),
            $movimiento->concepto->nombre_concepto,
            $movimiento->descripcion_detalle,
            $movimiento->medioPago->nombre_medio ?? '',
            "{$movimiento->secretarioRegistra->nombre} {$movimiento->secretarioRegistra->apellido}",
            (float) $movimiento->monto,
        ];
    }
}
