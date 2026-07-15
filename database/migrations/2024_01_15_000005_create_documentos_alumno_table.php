<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('documento_requisito_id')->constrained('documento_requisitos')->cascadeOnDelete();
            $table->enum('estado', ['pendiente', 'entregado', 'aprobado', 'rechazado'])->default('pendiente');
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->foreignId('secretario_revisa_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'documento_requisito_id'], 'uq_documento_alumno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_alumno');
    }
};
