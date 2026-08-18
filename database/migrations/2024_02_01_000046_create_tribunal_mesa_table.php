<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tribunal_mesa', function (Blueprint $table) {
            $table->id('id_tribunal');
            $table->foreignId('id_mesa')->constrained('mesa_examen', 'id_mesa');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor');
            $table->foreignId('id_rol_tribunal')->constrained('rol_tribunal', 'id_rol_tribunal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tribunal_mesa');
    }
};
