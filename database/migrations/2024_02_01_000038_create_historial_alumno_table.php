<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_alumno', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_materia')->constrained('materia', 'id_materia');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_condicion')->constrained('condicion_alumno', 'id_condicion');
            $table->decimal('nota_cursada', 4, 2)->nullable();
            $table->date('fecha_ultima_modificacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_alumno');
    }
};
