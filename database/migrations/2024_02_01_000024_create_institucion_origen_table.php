<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institucion_origen', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre_institucion', 150)->unique();
            $table->string('localidad', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institucion_origen');
    }
};
