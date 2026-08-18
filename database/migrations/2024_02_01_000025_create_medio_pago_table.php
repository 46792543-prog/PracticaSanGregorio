<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medio_pago', function (Blueprint $table) {
            $table->id('id_medio_pago');
            $table->string('nombre_medio', 20)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medio_pago');
    }
};
