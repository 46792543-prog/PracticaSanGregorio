<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_acta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('nota_escrito', 4, 2)->nullable();
            $table->decimal('nota_oral', 4, 2)->nullable();
            $table->decimal('nota_promedio', 4, 2)->nullable();
            $table->enum('resultado', ['aprobado', 'desaprobado', 'ausente'])->nullable();
            $table->timestamps();

            $table->unique(['acta_id', 'user_id'], 'uq_detalle_acta_alumno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_acta');
    }
};
