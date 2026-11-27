<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inasistencias de los docentes: un día que no dio clase en una designación.
 * Las injustificadas se descuentan del pago (horas de falta × valor de la hora
 * cátedra); las justificadas quedan registradas pero no descuentan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inasistencia_docente', function (Blueprint $table) {
            $table->id('id_inasistencia');
            $table->unsignedBigInteger('id_asignacion');
            $table->date('fecha');
            $table->unsignedTinyInteger('horas_catedra'); // horas de clase que no dio ese día
            $table->boolean('justificada')->default(false);
            $table->string('motivo', 255)->nullable();
            $table->unsignedBigInteger('id_persona_registra')->nullable(); // quién la cargó
            $table->timestamps();

            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignacion_profesor_materia')->cascadeOnDelete();
            $table->unique(['id_asignacion', 'fecha']); // una falta por designación y día
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inasistencia_docente');
    }
};
