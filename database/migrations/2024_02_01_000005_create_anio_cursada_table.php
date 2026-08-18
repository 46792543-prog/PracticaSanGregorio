<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anio_cursada', function (Blueprint $table) {
            $table->id('id_anio_cursada');
            $table->string('nombre_anio', 30)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anio_cursada');
    }
};
