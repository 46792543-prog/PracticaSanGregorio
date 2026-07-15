<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones_carrera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('anio_lectivo_id')->constrained('anios_lectivos')->cascadeOnDelete();
            $table->unsignedTinyInteger('anio_actual'); // 1er, 2do, 3er año que cursa
            $table->enum('turno', ['mañana', 'tarde', 'noche'])->nullable();
            $table->enum('condicion', ['regular', 'promocion', 'libre'])->default('regular');
            $table->enum('estado', ['activo', 'baja'])->default('activo');
            $table->dateTime('fecha_inscripcion')->useCurrent();
            $table->foreignId('secretario_registra_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('secretario_baja_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_baja')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'carrera_id'], 'uq_inscripcion_carrera');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones_carrera');
    }
};
