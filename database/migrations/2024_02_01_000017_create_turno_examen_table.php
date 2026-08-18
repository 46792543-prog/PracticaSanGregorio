<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_examen', function (Blueprint $table) {
            $table->id('id_turno');
            $table->string('nombre_turno', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_examen');
    }
};
