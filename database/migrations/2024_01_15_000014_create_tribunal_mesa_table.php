<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tribunal_mesa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_examen_id')->constrained('mesas_examen')->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->enum('rol', ['presidente', 'vocal1', 'vocal2']);
            $table->timestamps();

            $table->unique(['mesa_examen_id', 'rol'], 'uq_tribunal_mesa_rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tribunal_mesa');
    }
};
