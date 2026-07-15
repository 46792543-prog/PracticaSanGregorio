<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('familia_profesional', 100)->nullable();
            $table->unsignedTinyInteger('duracion_anios');
            $table->string('resolucion_ministerial', 25)->nullable();
            $table->enum('estado', ['activa', 'inactiva', 'en_espera'])->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
