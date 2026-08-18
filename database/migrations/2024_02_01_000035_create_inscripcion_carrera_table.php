<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_carrera', function (Blueprint $table) {
            $table->id('id_inscripcion_carrera');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_turno_cursada')->constrained('turno_cursada', 'id_turno_cursada');
            $table->foreignId('id_condicion')->constrained('condicion_alumno', 'id_condicion');
            $table->dateTime('fecha_inscripcion')->useCurrent();
            $table->foreignId('id_secretario_registra')->constrained('persona', 'id_persona');
            $table->foreignId('id_estado_inscripcion')->constrained('estado_inscripcion', 'id_estado_inscripcion');
            $table->foreignId('id_secretario_baja')->nullable()->constrained('persona', 'id_persona');
            $table->dateTime('fecha_baja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_carrera');
    }
};
