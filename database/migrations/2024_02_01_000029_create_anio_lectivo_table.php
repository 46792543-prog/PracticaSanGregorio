<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anio_lectivo', function (Blueprint $table) {
            $table->id('id_anio_lectivo');
            $table->integer('anio')->unique();
            $table->foreignId('id_estado_anio')->constrained('estado_anio_lectivo', 'id_estado_anio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anio_lectivo');
    }
};
