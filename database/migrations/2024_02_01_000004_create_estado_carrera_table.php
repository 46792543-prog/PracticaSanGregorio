<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_carrera', function (Blueprint $table) {
            $table->id('id_estado_carrera');
            $table->string('nombre_estado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_carrera');
    }
};
