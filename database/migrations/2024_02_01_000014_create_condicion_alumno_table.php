<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condicion_alumno', function (Blueprint $table) {
            $table->id('id_condicion');
            $table->string('nombre_condicion', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condicion_alumno');
    }
};
