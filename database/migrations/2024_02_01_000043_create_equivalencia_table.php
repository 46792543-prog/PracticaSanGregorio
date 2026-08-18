<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equivalencia', function (Blueprint $table) {
            $table->id('id_equivalencia');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_materia_destino')->constrained('materia', 'id_materia');
            $table->foreignId('id_institucion_origen')->constrained('institucion_origen', 'id_institucion');
            $table->string('materia_origen_nombre', 150);
            $table->string('num_resolucion_interna', 50);
            $table->date('fecha_aprobacion');
            $table->foreignId('id_director_firmante')->constrained('persona', 'id_persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equivalencia');
    }
};
