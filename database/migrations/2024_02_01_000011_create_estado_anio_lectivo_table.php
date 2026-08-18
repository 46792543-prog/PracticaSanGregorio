<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_anio_lectivo', function (Blueprint $table) {
            $table->id('id_estado_anio');
            $table->string('nombre_estado', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_anio_lectivo');
    }
};
