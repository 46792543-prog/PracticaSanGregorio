<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_mesa', function (Blueprint $table) {
            $table->id('id_estado_mesa');
            $table->string('nombre_estado', 20)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_mesa');
    }
};
