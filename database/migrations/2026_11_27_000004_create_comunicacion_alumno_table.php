<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de correos que Secretaría le envía a mano a un alumno desde la
 * Central de Alertas (ej. aviso de cuotas vencidas). No hay envíos automáticos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicacion_alumno', function (Blueprint $table) {
            $table->id('id_comunicacion');
            $table->unsignedBigInteger('id_persona_alumno');
            $table->string('destinatario', 150);
            $table->string('asunto', 150);
            $table->text('mensaje');
            $table->boolean('enviado');                 // resultado del envío
            $table->string('error', 500)->nullable();   // por qué falló (si falló)
            $table->unsignedBigInteger('id_persona_envia')->nullable(); // quién lo envió
            $table->timestamps();                       // created_at = fecha y hora del envío

            $table->foreign('id_persona_alumno')->references('id_persona')->on('persona')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicacion_alumno');
    }
};
