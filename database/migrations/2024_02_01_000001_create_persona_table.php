<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('dni', 20)->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 250)->nullable();
            $table->string('localidad', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
