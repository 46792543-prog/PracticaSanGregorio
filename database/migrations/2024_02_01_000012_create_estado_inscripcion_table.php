<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_inscripcion', function (Blueprint $table) {
            $table->id('id_estado_inscripcion');
            $table->string('nombre_estado', 25)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_inscripcion');
    }
};
