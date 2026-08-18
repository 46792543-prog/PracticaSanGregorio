<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->foreignId('id_persona')->unique()->constrained('persona', 'id_persona')->cascadeOnDelete();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->foreignId('id_rol')->constrained('rol', 'id_rol');
            $table->foreignId('id_estado')->constrained('estado_usuario', 'id_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
