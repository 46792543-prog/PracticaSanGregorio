<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regimen_aprobacion', function (Blueprint $table) {
            $table->id('id_regimen');
            $table->string('nombre_regimen', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regimen_aprobacion');
    }
};
