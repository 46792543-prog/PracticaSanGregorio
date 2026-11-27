<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El día de una clase aceptaba solo "Miercoles" (sin tilde) y de lunes a
 * viernes: el sistema usa "Miércoles" y la franja horaria permite sábado y
 * domingo. Se pasa a los 7 días con tilde (los "Miercoles" viejos se corrigen).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE horario_asignacion MODIFY dia_semana VARCHAR(10) NOT NULL");
        DB::table('horario_asignacion')->where('dia_semana', 'Miercoles')->update(['dia_semana' => 'Miércoles']);
        DB::statement("ALTER TABLE horario_asignacion MODIFY dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE horario_asignacion MODIFY dia_semana VARCHAR(10) NOT NULL");
        DB::table('horario_asignacion')->where('dia_semana', 'Miércoles')->update(['dia_semana' => 'Miercoles']);
        DB::table('horario_asignacion')->whereIn('dia_semana', ['Sábado', 'Domingo'])->delete();
        DB::statement("ALTER TABLE horario_asignacion MODIFY dia_semana ENUM('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes') NOT NULL");
    }
};
