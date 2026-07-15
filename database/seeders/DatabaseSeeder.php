<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CarreraSeeder::class,
            MateriaSeeder::class,
            CatalogoSeeder::class,
            UserSeeder::class,
            AcademicoSeeder::class,
            DocumentoAlumnoSeeder::class,
            CuotaSeeder::class,
            MovimientoCajaSeeder::class,
            ProfesorSeeder::class,
            AsignacionSeeder::class,
            MesaExamenSeeder::class,
            ActaSeeder::class,
        ]);
    }
}
