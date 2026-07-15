<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_examen_id')->unique()->constrained('mesas_examen')->cascadeOnDelete();
            $table->string('libro', 20)->nullable();
            $table->string('folio', 20)->nullable();
            $table->dateTime('fecha_generacion')->nullable();
            $table->foreignId('secretario_id')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', ['borrador', 'generada'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};
