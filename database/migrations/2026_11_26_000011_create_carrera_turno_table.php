<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turnos en que se dicta cada carrera (Mañana / Tarde / Noche). Es la misma
 * carrera, con las mismas materias y el mismo plan, ofrecida en varios turnos.
 * A las carreras que ya existían se les marcan los turnos de sus alumnos (o
 * los tres, si todavía no tienen alumnos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrera_turno', function (Blueprint $table) {
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera')->cascadeOnDelete();
            $table->foreignId('id_turno_cursada')->constrained('turno_cursada', 'id_turno_cursada');
            $table->primary(['id_carrera', 'id_turno_cursada']);
        });

        $todos = DB::table('turno_cursada')->pluck('id_turno_cursada');

        foreach (DB::table('carrera')->pluck('id_carrera') as $idCarrera) {
            $usados = DB::table('inscripcion_carrera')->where('id_carrera', $idCarrera)->whereNotNull('id_turno_cursada')
                ->distinct()->pluck('id_turno_cursada');

            foreach ($usados->isNotEmpty() ? $usados : $todos as $idTurno) {
                DB::table('carrera_turno')->insert(['id_carrera' => $idCarrera, 'id_turno_cursada' => $idTurno]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera_turno');
    }
};
