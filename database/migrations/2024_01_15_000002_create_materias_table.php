<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->unsignedTinyInteger('numero_orden')->nullable(); // Nº de orden en el plan de estudio
            $table->unsignedTinyInteger('anio_cursada'); // 1, 2, 3...
            $table->enum('cuatrimestre', ['anual', '1er_cuatrimestre', '2do_cuatrimestre'])->default('anual');
            $table->enum('regimen', ['solo_promocion', 'solo_examen_final', 'promocion_o_examen_final'])->default('promocion_o_examen_final');
            $table->string('version_plan', 20)->default('2024');
            $table->timestamps();

            $table->unique(['carrera_id', 'nombre', 'version_plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
