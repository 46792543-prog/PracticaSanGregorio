<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->id('id_materia');
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');
            $table->foreignId('id_anio_cursada')->constrained('anio_cursada', 'id_anio_cursada');
            $table->foreignId('id_periodo')->constrained('periodo_dictado', 'id_periodo');
            $table->foreignId('id_regimen')->constrained('regimen_aprobacion', 'id_regimen');
            $table->foreignId('id_nombre_materia')->constrained('nombre_materia', 'id_nombre_materia');
            $table->string('version_plan', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};
