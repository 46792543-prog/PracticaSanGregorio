<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrera', function (Blueprint $table) {
            $table->id('id_carrera');
            $table->string('nombre_carrera', 50)->unique();
            $table->integer('duracion_anos');
            $table->string('resolucion_ministerial', 25)->nullable();
            $table->foreignId('id_estado_carrera')->nullable()->constrained('estado_carrera', 'id_estado_carrera');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera');
    }
};
