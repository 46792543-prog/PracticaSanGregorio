<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativa', function (Blueprint $table) {
            $table->foreignId('id_materia_principal')->constrained('materia', 'id_materia');
            $table->foreignId('id_materia_requisito')->constrained('materia', 'id_materia');
            $table->foreignId('id_tipo_correlativa')->constrained('tipo_correlativa', 'id_tipo_correlativa');
            $table->primary(['id_materia_principal', 'id_materia_requisito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativa');
    }
};
