<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_cursada', function (Blueprint $table) {
            $table->id('id_turno_cursada');
            $table->string('nombre_turno', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_cursada');
    }
};
