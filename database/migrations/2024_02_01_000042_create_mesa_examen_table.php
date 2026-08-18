<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesa_examen', function (Blueprint $table) {
            $table->id('id_mesa');
            $table->foreignId('id_materia')->constrained('materia', 'id_materia');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_turno')->constrained('turno_examen', 'id_turno');
            $table->foreignId('id_llamado')->constrained('llamado_examen', 'id_llamado');
            $table->date('fecha_examen');
            $table->date('fecha_inicio_inscripcion');
            $table->date('fecha_fin_inscripcion');
            $table->foreignId('id_estado_mesa')->default(1)->constrained('estado_mesa', 'id_estado_mesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_examen');
    }
};
