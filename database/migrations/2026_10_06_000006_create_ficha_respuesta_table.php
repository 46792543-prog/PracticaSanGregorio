<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_respuesta', function (Blueprint $table) {
            $table->id('id_respuesta');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_campo')->constrained('ficha_campo', 'id_campo')->cascadeOnDelete();
            $table->text('valor')->nullable();
            $table->timestamps();

            $table->unique(['id_persona_alumno', 'id_campo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_respuesta');
    }
};
