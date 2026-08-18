<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->string('concepto', 100)->nullable()->after('id_mes');
            $table->decimal('recargo', 10, 2)->default(0)->after('monto');
            $table->date('fecha_pago')->nullable()->after('pagado');
        });
    }

    public function down(): void
    {
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->dropColumn(['concepto', 'recargo', 'fecha_pago']);
        });
    }
};
