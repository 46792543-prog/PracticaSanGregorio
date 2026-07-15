<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    public function run(): void
    {
        Carrera::updateOrCreate(
            ['nombre' => 'Tecnicatura Superior en Enfermería'],
            [
                'familia_profesional' => 'Salud Comunitaria',
                'duracion_anios' => 3,
                'resolucion_ministerial' => '451/24',
                'estado' => 'activa',
            ]
        );
    }
}
