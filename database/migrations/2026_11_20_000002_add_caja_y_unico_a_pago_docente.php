<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - id_movimiento_caja: vincula el pago con el Egreso que generó en el
 *   Libro de Caja (null si el pago no quedó en estado "Pagado").
 * - unique(id_profesor, periodo): un docente no puede tener dos pagos
 *   cargados para el mismo mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago_docente', function (Blueprint $table) {
            $table->foreignId('id_movimiento_caja')->nullable()->after('observaciones')
                ->constrained('movimiento_caja', 'id_movimiento')->nullOnDelete();
            $table->unique(['id_profesor', 'periodo'], 'pago_docente_profesor_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pago_docente', function (Blueprint $table) {
            $table->dropUnique('pago_docente_profesor_periodo_unique');
            $table->dropConstrainedForeignId('id_movimiento_caja');
        });
    }
};
