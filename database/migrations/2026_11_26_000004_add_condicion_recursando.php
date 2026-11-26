<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Condición "Recursando" del historial: la materia se está cursando de nuevo
 * (tenía una cursada anterior no aprobada). Con al menos una materia así, el
 * alumno pasa solo a condición Recursante en esa carrera.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('condicion_alumno')->where('nombre_condicion', 'Recursando')->exists()) {
            DB::table('condicion_alumno')->insert(['nombre_condicion' => 'Recursando', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('condicion_alumno')->where('nombre_condicion', 'Recursando')->delete();
    }
};
