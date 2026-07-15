<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\DocumentoRequisito;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        AnioLectivo::updateOrCreate(['anio' => 2026], ['estado' => 'activo']);

        $enfermeria = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();

        $documentos = [
            ['nombre' => 'DNI (frente y dorso)', 'obligatorio' => true, 'carrera_id' => null],
            ['nombre' => 'Certificado de título secundario', 'descripcion' => 'Analítico o título en trámite aceptado', 'obligatorio' => true, 'carrera_id' => null],
            ['nombre' => 'Apto médico', 'descripcion' => 'Emitido por médico certificado, vigencia 1 año', 'obligatorio' => true, 'carrera_id' => null],
            ['nombre' => 'Foto carnet', 'descripcion' => 'Fondo blanco, formato JPG o PNG', 'obligatorio' => false, 'carrera_id' => null],
            ['nombre' => 'Certificado de vacunación', 'descripcion' => 'Solo Enfermería y Cs. de la Salud', 'obligatorio' => true, 'carrera_id' => $enfermeria->id],
        ];

        foreach ($documentos as $documento) {
            DocumentoRequisito::updateOrCreate(
                ['nombre' => $documento['nombre']],
                [
                    'descripcion' => $documento['descripcion'] ?? null,
                    'obligatorio' => $documento['obligatorio'],
                    'carrera_id' => $documento['carrera_id'],
                ]
            );
        }
    }
}
