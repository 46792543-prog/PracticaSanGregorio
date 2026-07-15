<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Plan de estudio 2024 de la Tecnicatura Superior en Enfermería, réplica exacta
     * de la tabla "Ver Plan de Estudio" del Figma (24 materias, 3 años).
     *
     * regularizan/aprobadas referencian el nº de orden de la materia requisito.
     */
    private const PLAN = [
        ['orden' => 1, 'nombre' => 'Fundamentos de Enfermería Básica y Comunitaria', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'solo_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 2, 'nombre' => 'Anatomía y Fisiología Humana', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 3, 'nombre' => 'Microbiología y Parasitología', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 4, 'nombre' => 'Expresión Oral y Escrita', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 5, 'nombre' => 'Física y Química Aplicada a la Enfermería', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 6, 'nombre' => 'Antropología Filosófica y Socio Cultural', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 7, 'nombre' => 'Informática Básica', 'anio' => 1, 'cuatrimestre' => '2do_cuatrimestre', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 8, 'nombre' => 'Práctica Profesionalizante I', 'anio' => 1, 'cuatrimestre' => 'anual', 'regimen' => 'solo_promocion', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 9, 'nombre' => 'Enfermería Médica y Especialidades', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'solo_examen_final', 'regularizan' => [1, 2], 'aprobadas' => [1, 2]],
        ['orden' => 10, 'nombre' => 'Ética y Marco Legal en la Práctica de Enfermería', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [5], 'aprobadas' => []],
        ['orden' => 11, 'nombre' => 'Farmacología', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'solo_examen_final', 'regularizan' => [2, 3, 5], 'aprobadas' => [2, 5]],
        ['orden' => 12, 'nombre' => 'Nutrición', 'anio' => 2, 'cuatrimestre' => '2do_cuatrimestre', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [1, 2, 3, 5], 'aprobadas' => [2, 5]],
        ['orden' => 13, 'nombre' => 'Enfermería Quirúrgica y Especialidades', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [1, 2, 3, 5], 'aprobadas' => [3, 5]],
        ['orden' => 14, 'nombre' => 'Psicología', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [6], 'aprobadas' => [6]],
        ['orden' => 15, 'nombre' => 'Introducción a la Metodología de la Investigación', 'anio' => 2, 'cuatrimestre' => '2do_cuatrimestre', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [4, 7], 'aprobadas' => [4, 7]],
        ['orden' => 16, 'nombre' => 'EDI I', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 17, 'nombre' => 'Práctica Profesionalizante II', 'anio' => 2, 'cuatrimestre' => 'anual', 'regimen' => 'solo_promocion', 'regularizan' => [1, 3, 5], 'aprobadas' => [2, 3, 5, 8]],
        ['orden' => 18, 'nombre' => 'Enfermería Materno Infanto Juvenil', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'solo_examen_final', 'regularizan' => [9, 11, 13], 'aprobadas' => [9, 11, 12, 13, 14]],
        ['orden' => 19, 'nombre' => 'Informática Aplicada a la Enfermería', 'anio' => 3, 'cuatrimestre' => '2do_cuatrimestre', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [15], 'aprobadas' => [15]],
        ['orden' => 20, 'nombre' => 'Inglés Técnico', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 21, 'nombre' => 'Organización y Gestión de Enfermería', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [10, 15], 'aprobadas' => [9, 11, 13]],
        ['orden' => 22, 'nombre' => 'Enfermería en Salud Mental', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'solo_examen_final', 'regularizan' => [9, 13], 'aprobadas' => [9, 11, 12, 13, 14]],
        ['orden' => 23, 'nombre' => 'EDI II', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'promocion_o_examen_final', 'regularizan' => [], 'aprobadas' => []],
        ['orden' => 24, 'nombre' => 'Práctica Profesionalizante III', 'anio' => 3, 'cuatrimestre' => 'anual', 'regimen' => 'solo_promocion', 'regularizan' => [9, 11, 12, 13, 14], 'aprobadas' => [9, 10, 11, 12, 13, 14, 15, 17]],
    ];

    public function run(): void
    {
        $carrera = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();

        $materiaPorOrden = [];
        foreach (self::PLAN as $item) {
            $materiaPorOrden[$item['orden']] = Materia::updateOrCreate(
                [
                    'carrera_id' => $carrera->id,
                    'nombre' => $item['nombre'],
                    'version_plan' => '2024',
                ],
                [
                    'numero_orden' => $item['orden'],
                    'anio_cursada' => $item['anio'],
                    'cuatrimestre' => $item['cuatrimestre'],
                    'regimen' => $item['regimen'],
                ]
            );
        }

        foreach (self::PLAN as $item) {
            $requisitos = [];
            foreach ($item['regularizan'] as $ordenRequisito) {
                $requisitos[$ordenRequisito]['requiere_regularizada'] = true;
            }
            foreach ($item['aprobadas'] as $ordenRequisito) {
                $requisitos[$ordenRequisito]['requiere_aprobada'] = true;
            }

            foreach ($requisitos as $ordenRequisito => $flags) {
                Correlativa::updateOrCreate(
                    [
                        'materia_id' => $materiaPorOrden[$item['orden']]->id,
                        'materia_requisito_id' => $materiaPorOrden[$ordenRequisito]->id,
                    ],
                    [
                        'requiere_regularizada' => $flags['requiere_regularizada'] ?? false,
                        'requiere_aprobada' => $flags['requiere_aprobada'] ?? false,
                    ]
                );
            }
        }
    }
}
