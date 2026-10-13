<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_modulo', function (Blueprint $table) {
            $table->id('id_horario_modulo');
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');
            $table->foreignId('id_turno_cursada')->constrained('turno_cursada', 'id_turno_cursada');
            $table->unsignedTinyInteger('modulo');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();

            $table->unique(['id_carrera', 'id_turno_cursada', 'modulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_modulo');
    }
};
