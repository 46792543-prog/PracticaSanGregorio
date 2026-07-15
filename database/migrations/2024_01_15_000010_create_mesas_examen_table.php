<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('anio_lectivo_id')->constrained('anios_lectivos')->cascadeOnDelete();
            $table->enum('turno', ['febrero_marzo', 'julio', 'noviembre_diciembre']);
            $table->enum('llamado', ['primer_llamado', 'segundo_llamado']);
            $table->date('fecha_examen');
            $table->date('fecha_inicio_inscripcion');
            $table->date('fecha_fin_inscripcion');
            $table->enum('estado', ['programada', 'finalizada', 'cancelada'])->default('programada');
            $table->timestamps();

            $table->unique(['materia_id', 'anio_lectivo_id', 'turno', 'llamado'], 'uq_mesa_examen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas_examen');
    }
};
