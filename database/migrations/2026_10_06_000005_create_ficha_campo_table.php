<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_campo', function (Blueprint $table) {
            $table->id('id_campo');
            $table->foreignId('id_seccion')->constrained('ficha_seccion', 'id_seccion')->cascadeOnDelete();
            $table->string('etiqueta', 150);
            $table->enum('tipo', ['texto', 'texto_largo', 'numero', 'fecha', 'seleccion', 'si_no']);
            $table->text('opciones')->nullable();
            $table->boolean('obligatorio')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_campo');
    }
};
