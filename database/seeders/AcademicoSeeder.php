<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\HistorialMateria;
use App\Models\InscripcionCarrera;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicoSeeder extends Seeder
{
    public function run(): void
    {
        $lucia = User::where('dni', '41205678')->firstOrFail();
        $carrera = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();
        $anioLectivo = AnioLectivo::where('anio', 2026)->firstOrFail();
        $secretaria = User::where('rol', 'secretario')->first();

        InscripcionCarrera::updateOrCreate(
            ['user_id' => $lucia->id, 'carrera_id' => $carrera->id],
            [
                'anio_lectivo_id' => $anioLectivo->id,
                'anio_actual' => 2,
                'turno' => 'mañana',
                'condicion' => 'regular',
                'estado' => 'activo',
                'fecha_inscripcion' => '2025-03-01 09:00:00',
                'secretario_registra_id' => $secretaria?->id,
            ]
        );

        // 1er año (Plan 2024, orden 1-8): ya cursado y aprobado en su totalidad durante 2025.
        $aprobadas1erAnio = [
            1 => 8.50,  // Fundamentos de Enfermería Básica y Comunitaria
            2 => 8.00,  // Anatomía y Fisiología Humana
            3 => 7.00,  // Microbiología y Parasitología
            4 => 9.00,  // Expresión Oral y Escrita
            5 => 7.50,  // Física y Química Aplicada a la Enfermería
            6 => 8.00,  // Antropología Filosófica y Socio Cultural
            7 => 8.00,  // Informática Básica
            8 => 8.50,  // Práctica Profesionalizante I
        ];

        foreach ($aprobadas1erAnio as $orden => $nota) {
            $this->registrarHistorial($lucia, $carrera, $anioLectivo, $orden, 'aprobada', $nota);
        }

        // 2do año (orden 9-17): año en curso 2026. Las correlativas de 1er año ya están
        // todas aprobadas, así que ninguna materia de 2do año está bloqueada.
        $estados2doAnio = [
            9 => ['condicion' => 'regular', 'nota' => null],   // Enfermería Médica y Especialidades (anual, examen final)
            10 => ['condicion' => 'regular', 'nota' => null],  // Ética y Marco Legal en la Práctica de Enfermería
            11 => ['condicion' => 'regular', 'nota' => null],  // Farmacología (anual, examen final)
            12 => ['condicion' => 'pendiente', 'nota' => null], // Nutrición (2do cuatrimestre, todavía no arrancó)
            13 => ['condicion' => 'regular', 'nota' => null],  // Enfermería Quirúrgica y Especialidades
            14 => ['condicion' => 'aprobada', 'nota' => 9.00], // Psicología (promocionada)
            15 => ['condicion' => 'pendiente', 'nota' => null], // Introducción a la Metodología de la Investigación (2do cuatrimestre)
            16 => ['condicion' => 'regular', 'nota' => null],  // EDI I
            17 => ['condicion' => 'regular', 'nota' => null],  // Práctica Profesionalizante II
        ];

        foreach ($estados2doAnio as $orden => $datos) {
            $this->registrarHistorial($lucia, $carrera, $anioLectivo, $orden, $datos['condicion'], $datos['nota']);
        }

        // 3er año (orden 18-24): todavía no disponible. El front calcula "No disponible"
        // cruzando esto con las correlativas reales del plan.
        foreach (range(18, 24) as $orden) {
            $this->registrarHistorial($lucia, $carrera, $anioLectivo, $orden, 'pendiente', null);
        }
    }

    private function registrarHistorial(
        User $alumno,
        Carrera $carrera,
        AnioLectivo $anioLectivo,
        int $numeroOrden,
        string $condicion,
        ?float $nota
    ): void {
        $materia = Materia::where('carrera_id', $carrera->id)->where('numero_orden', $numeroOrden)->firstOrFail();

        HistorialMateria::updateOrCreate(
            [
                'user_id' => $alumno->id,
                'materia_id' => $materia->id,
                'anio_lectivo_id' => $anioLectivo->id,
            ],
            [
                'condicion' => $condicion,
                'nota_cursada' => $nota,
                'fecha_ultima_modificacion' => now()->toDateString(),
            ]
        );
    }
}
