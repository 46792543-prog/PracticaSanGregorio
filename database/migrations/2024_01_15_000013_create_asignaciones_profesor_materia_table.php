<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_profesor_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('anio_lectivo_id')->constrained('anios_lectivos')->cascadeOnDelete();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula', 20)->nullable();
            $table->string('turno', 20)->nullable();
            // ej. ["martes","jueves"]
            $table->json('dias_cursada');
            $table->timestamps();

            $table->unique(['profesor_id', 'materia_id', 'anio_lectivo_id'], 'uq_asignacion_profesor_materia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_profesor_materia');
    }
};
