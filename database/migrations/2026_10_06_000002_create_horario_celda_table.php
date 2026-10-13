<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_celda', function (Blueprint $table) {
            $table->id('id_horario_celda');
            $table->foreignId('id_horario_modulo')->constrained('horario_modulo', 'id_horario_modulo')->cascadeOnDelete();
            $table->enum('dia_semana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']);
            $table->string('contenido', 150)->nullable();
            $table->timestamps();

            $table->unique(['id_horario_modulo', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_celda');
    }
};
