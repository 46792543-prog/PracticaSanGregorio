<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las categorías de egreso ahora se agregan y editan desde el Libro de Caja.
 * Se quitan los conceptos de egreso viejos que quedaron sin movimientos (sus
 * movimientos ya pasaron a una categoría en la migración anterior), para que
 * no aparezcan como categorías. No se borra ningún movimiento.
 */
return new class extends Migration
{
    public function up(): void
    {
        $egreso = DB::table('tipo_movimiento')->where('nombre_tipo', 'Egreso')->value('id_tipo_movimiento');

        DB::table('concepto_caja')
            ->where('id_tipo_movimiento', $egreso)
            ->whereIn('nombre_concepto', ['Gasto general', 'compra de una computadora', 'pago de internet'])
            ->whereNotExists(fn ($q) => $q->from('movimiento_caja')->whereColumn('movimiento_caja.id_concepto', 'concepto_caja.id_concepto'))
            ->delete();
    }

    public function down(): void
    {
        // Eran conceptos vacíos: no hace falta recrearlos.
    }
};
