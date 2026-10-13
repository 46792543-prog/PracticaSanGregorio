<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodo_pago_docente', function (Blueprint $table) {
            $table->id('id_metodo_pago_docente');
            $table->string('nombre_metodo', 30)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodo_pago_docente');
    }
};
