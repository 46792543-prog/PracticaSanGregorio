<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion_mesa', function (Blueprint $table) {
            $table->decimal('nota_examen', 4, 2)->nullable()->after('id_estado_inscripcion');
            $table->string('resultado', 20)->nullable()->after('nota_examen');
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion_mesa', function (Blueprint $table) {
            $table->dropColumn(['nota_examen', 'resultado']);
        });
    }
};
