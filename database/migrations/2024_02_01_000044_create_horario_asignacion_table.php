<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_asignacion', function (Blueprint $table) {
            $table->id('id_horario');
            $table->foreignId('id_asignacion')->constrained('asignacion_profesor_materia', 'id_asignacion');
            $table->enum('dia_semana', ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes']);
            $table->time('hora_desde');
            $table->time('hora_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_asignacion');
    }
};
