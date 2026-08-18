<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrera', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable()->after('nombre_carrera');
        });

        Schema::table('materia', function (Blueprint $table) {
            $table->unsignedInteger('numero_orden')->nullable()->after('id_carrera');
        });

        Schema::table('profesor', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->unique()->after('id_especialidad');
        });
    }

    public function down(): void
    {
        Schema::table('carrera', function (Blueprint $table) {
            $table->dropColumn('familia_profesional');
        });

        Schema::table('materia', function (Blueprint $table) {
            $table->dropColumn('numero_orden');
        });

        Schema::table('profesor', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
