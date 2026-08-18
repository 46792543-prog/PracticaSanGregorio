<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llamado_examen', function (Blueprint $table) {
            $table->id('id_llamado');
            $table->string('nombre_llamado', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llamado_examen');
    }
};
