<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CajaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        return ['Fecha', 'Concepto / categoría', 'Detalle', 'Beneficiario', 'Tipo', 'Registrado por', 'Ingreso', 'Egreso'];
    }

    public function map($movimiento): array
    {
        $esIngreso = $movimiento->tipo === 'Ingreso';

        return [
            $movimiento->fecha_movimiento->format('d/m/Y H:i'),
            $movimiento->concepto->nombre_concepto,
            $movimiento->descripcion_detalle,
            $movimiento->beneficiario,
            $movimiento->tipo,
            "{$movimiento->secretarioRegistra->nombre} {$movimiento->secretarioRegistra->apellido}",
            $esIngreso ? (float) $movimiento->monto : 0,
            ! $esIngreso ? (float) $movimiento->monto : 0,
        ];
    }
}
