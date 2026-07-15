<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('anio_lectivo_id')->constrained('anios_lectivos')->cascadeOnDelete();
            $table->enum('condicion', ['pendiente', 'cursando', 'regular', 'aprobada'])->default('pendiente');
            $table->decimal('nota_cursada', 4, 2)->nullable();
            $table->date('fecha_ultima_modificacion')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'materia_id', 'anio_lectivo_id'], 'uq_historial_materia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_materias');
    }
};
