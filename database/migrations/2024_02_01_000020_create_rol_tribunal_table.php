<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_tribunal', function (Blueprint $table) {
            $table->id('id_rol_tribunal');
            $table->string('nombre_rol', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_tribunal');
    }
};
