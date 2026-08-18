<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_acta', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_acta')->constrained('acta', 'id_acta')->cascadeOnDelete();
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->decimal('nota_escrito', 4, 2)->nullable();
            $table->decimal('nota_oral', 4, 2)->nullable();
            $table->decimal('nota_final', 4, 2);
            $table->string('resultado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_acta');
    }
};
