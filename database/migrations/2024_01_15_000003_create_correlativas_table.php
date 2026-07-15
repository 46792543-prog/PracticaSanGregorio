<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('materia_requisito_id')->constrained('materias')->cascadeOnDelete();
            // una misma requisito puede exigirse regularizada para cursar Y aprobada para rendir a la vez
            $table->boolean('requiere_regularizada')->default(false);
            $table->boolean('requiere_aprobada')->default(false);
            $table->timestamps();

            $table->unique(['materia_id', 'materia_requisito_id'], 'uq_correlativa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativas');
    }
};
