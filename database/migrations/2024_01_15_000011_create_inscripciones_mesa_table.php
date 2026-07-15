<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones_mesa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_examen_id')->constrained('mesas_examen')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_inscripcion')->useCurrent();
            $table->enum('estado', ['en_proceso', 'aceptada', 'rechazada'])->default('en_proceso');
            $table->decimal('nota_examen', 4, 2)->nullable();
            $table->enum('resultado', ['aprobado', 'desaprobado', 'ausente'])->nullable();
            $table->timestamps();

            $table->unique(['mesa_examen_id', 'user_id'], 'uq_inscripcion_mesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones_mesa');
    }
};
