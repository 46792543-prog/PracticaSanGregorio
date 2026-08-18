<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mes', function (Blueprint $table) {
            $table->id('id_mes');
            $table->string('nombre_mes', 20)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mes');
    }
};
