<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_requisito', function (Blueprint $table) {
            $table->id('id_requisito');
            $table->string('nombre_documento', 150)->unique();
            $table->boolean('es_obligatorio')->default(true);
            $table->string('descripcion', 255)->nullable();
            $table->foreignId('id_carrera')->nullable()->constrained('carrera', 'id_carrera');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_requisito');
    }
};
