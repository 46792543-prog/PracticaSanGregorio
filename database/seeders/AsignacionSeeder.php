<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Database\Seeder;

class AsignacionSeeder extends Seeder
{
    public function run(): void
    {
        $carrera = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();
        $anioLectivo = AnioLectivo::where('anio', 2026)->firstOrFail();

        $materiaPorOrden = fn (int $orden) => Materia::where('carrera_id', $carrera->id)
            ->where('numero_orden', $orden)
            ->firstOrFail();
        $profesor = fn (string $dni) => Profesor::where('dni', $dni)->firstOrFail();

        $asignaciones = [
            ['dni' => '25111222', 'orden' => 12, 'dias' => ['martes', 'jueves'], 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'aula' => 'Aula 2', 'turno' => 'Mañana'],
            ['dni' => '24333444', 'orden' => 11, 'dias' => ['lunes', 'miercoles'], 'hora_inicio' => '09:00', 'hora_fin' => '11:00', 'aula' => 'Aula 1', 'turno' => 'Mañana'],
            ['dni' => '26555666', 'orden' => 21, 'dias' => ['lunes', 'miercoles', 'viernes'], 'hora_inicio' => '08:00', 'hora_fin' => '09:30', 'aula' => 'Aula 3', 'turno' => 'Mañana'],
            ['dni' => '22777888', 'orden' => 10, 'dias' => ['jueves'], 'hora_inicio' => '15:00', 'hora_fin' => '17:00', 'aula' => 'Sala B', 'turno' => 'Tarde'],
            ['dni' => '27999000', 'orden' => 14, 'dias' => ['martes', 'viernes'], 'hora_inicio' => '15:00', 'hora_fin' => '17:00', 'aula' => 'Aula 4', 'turno' => 'Tarde'],
        ];

        foreach ($asignaciones as $asignacion) {
            AsignacionProfesorMateria::updateOrCreate(
                [
                    'profesor_id' => $profesor($asignacion['dni'])->id,
                    'materia_id' => $materiaPorOrden($asignacion['orden'])->id,
                    'anio_lectivo_id' => $anioLectivo->id,
                ],
                [
                    'dias_cursada' => $asignacion['dias'],
                    'hora_inicio' => $asignacion['hora_inicio'],
                    'hora_fin' => $asignacion['hora_fin'],
                    'aula' => $asignacion['aula'],
                    'turno' => $asignacion['turno'],
                ]
            );
        }
    }
}
