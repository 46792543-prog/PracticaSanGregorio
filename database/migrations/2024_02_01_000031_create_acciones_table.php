<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acciones', function (Blueprint $table) {
            $table->id('idAcciones');
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();
            $table->foreignId('id_persona')->constrained('persona', 'id_persona');
            $table->foreignId('id_tipo_accion')->constrained('tipo_accion', 'id_tipo_accion');
            $table->text('observaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones');
    }
};
