<?php

namespace Database\Seeders;

use App\Models\Profesor;
use Illuminate\Database\Seeder;

class ProfesorSeeder extends Seeder
{
    public function run(): void
    {
        $profesores = [
            ['dni' => '25111222', 'nombre' => 'María', 'apellido' => 'Cabeza', 'especialidad' => 'Enfermería'],
            ['dni' => '24333444', 'nombre' => 'Roberto', 'apellido' => 'Toril', 'especialidad' => 'Enfermería'],
            ['dni' => '26555666', 'nombre' => 'Jorge', 'apellido' => 'Amaya', 'especialidad' => 'Enfermería'],
            ['dni' => '22777888', 'nombre' => 'Pedro', 'apellido' => 'Flores', 'especialidad' => 'Derecho / Legislación'],
            ['dni' => '27999000', 'nombre' => 'Laura', 'apellido' => 'Bustamante', 'especialidad' => 'Ciencias Sociales'],
            ['dni' => '23111333', 'nombre' => 'Marcela', 'apellido' => 'López', 'especialidad' => 'Enfermería'],
            ['dni' => '21444555', 'nombre' => 'Jorge', 'apellido' => 'Fernández', 'especialidad' => 'Enfermería'],
        ];

        foreach ($profesores as $profesor) {
            Profesor::updateOrCreate(['dni' => $profesor['dni']], $profesor);
        }
    }
}
