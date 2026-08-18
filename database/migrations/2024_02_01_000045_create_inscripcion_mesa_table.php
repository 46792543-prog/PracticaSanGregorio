<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_mesa', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->foreignId('id_mesa')->constrained('mesa_examen', 'id_mesa');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->timestamp('fecha_inscripcion')->nullable()->useCurrent();
            $table->foreignId('id_estado_inscripcion')->default(1)->constrained('estado_inscripcion', 'id_estado_inscripcion');
            $table->foreignId('id_secretario_baja')->nullable()->constrained('persona', 'id_persona');
            $table->dateTime('fecha_baja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_mesa');
    }
};
