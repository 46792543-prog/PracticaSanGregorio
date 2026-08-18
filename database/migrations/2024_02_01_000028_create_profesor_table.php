<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor', function (Blueprint $table) {
            $table->id('id_profesor');
            $table->foreignId('id_persona')->unique()->constrained('persona', 'id_persona')->cascadeOnDelete();
            $table->foreignId('id_especialidad')->constrained('especialidad_profesor', 'id_especialidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor');
    }
};
