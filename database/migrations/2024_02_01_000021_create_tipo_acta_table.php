<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_acta', function (Blueprint $table) {
            $table->id('id_tipo_acta');
            $table->string('nombre_tipo', 25)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_acta');
    }
};
