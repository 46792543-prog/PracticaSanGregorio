<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EstadoPagosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $alumnos)
    {
    }

    public function collection(): Collection
    {
        return $this->alumnos;
    }

    public function headings(): array
    {
        return ['Alumno', 'DNI', 'Carrera', 'Cuotas pagadas', 'Cuotas pendientes', 'Monto adeudado', 'Recargo adeudado'];
    }

    public function map($alumno): array
    {
        return [
            "{$alumno->apellido}, {$alumno->nombre}",
            $alumno->dni,
            $alumno->inscripcionesCarrera->first()?->carrera?->nombre_carrera ?? '',
            $alumno->cuotas_pagadas_count,
            $alumno->cuotas_pendientes_count,
            (float) ($alumno->monto_pendiente ?? 0),
            (float) ($alumno->recargo_pendiente ?? 0),
        ];
    }
}
