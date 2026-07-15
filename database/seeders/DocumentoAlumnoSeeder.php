<?php

namespace Database\Seeders;

use App\Models\DocumentoAlumno;
use App\Models\DocumentoRequisito;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentoAlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $lucia = User::where('dni', '41205678')->firstOrFail();

        // Entregó todo, pero la secretaría todavía no aprobó el DNI ni el título.
        $estados = [
            'DNI (frente y dorso)' => 'entregado',
            'Certificado de título secundario' => 'entregado',
            'Apto médico' => 'aprobado',
            'Foto carnet' => 'pendiente',
            'Certificado de vacunación' => 'aprobado',
        ];

        foreach ($estados as $nombreDocumento => $estado) {
            $documento = DocumentoRequisito::where('nombre', $nombreDocumento)->firstOrFail();

            DocumentoAlumno::updateOrCreate(
                ['user_id' => $lucia->id, 'documento_requisito_id' => $documento->id],
                [
                    'estado' => $estado,
                    'fecha_entrega' => in_array($estado, ['entregado', 'aprobado']) ? now()->subDays(20)->toDateString() : null,
                    'fecha_aprobacion' => $estado === 'aprobado' ? now()->subDays(15)->toDateString() : null,
                ]
            );
        }
    }
}
