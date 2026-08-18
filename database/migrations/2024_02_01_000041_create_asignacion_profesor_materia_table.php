<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_profesor_materia', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor');
            $table->foreignId('id_materia')->constrained('materia', 'id_materia');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->date('fecha_asignacion');
            $table->foreignId('id_anio_cursada')->nullable()->constrained('anio_cursada', 'id_anio_cursada');
            $table->foreignId('id_turno_cursada')->nullable()->constrained('turno_cursada', 'id_turno_cursada');
            $table->string('aula', 20)->nullable();
            $table->unique(['id_profesor', 'id_materia', 'id_anio_lectivo'], 'uq_profesor_materia_anio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_profesor_materia');
    }
};
