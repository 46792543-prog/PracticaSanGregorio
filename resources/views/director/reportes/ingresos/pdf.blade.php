<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos - {{ \App\Support\FechaEsp::corta($desde) }} a {{ \App\Support\FechaEsp::corta($hasta) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: table; width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 14px; }
        .header .logo { display: table-cell; width: 40px; }
        .header .logo span { display: inline-block; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; background: #1E4D8C; color: #fff; font-weight: bold; font-size: 10px; }
        .header .info { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .header .info p { margin: 0; }
        .header .info .institucion { font-weight: bold; font-size: 13px; }
        .header .info .direccion { color: #64748b; font-size: 9px; }

        h1 { text-align: center; font-size: 14px; margin: 10px 0 16px; }

        table.resumen { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
        table.resumen td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        table.resumen td.label { color: #64748b; text-transform: uppercase; font-size: 9px; }
        table.resumen td.total { color: #059669; font-weight: bold; }

        .columnas { display: table; width: 100%; margin-bottom: 16px; }
        .columnas .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }

        table.desglose { width: 100%; border-collapse: collapse; font-size: 9.5px; margin-bottom: 6px; }
        table.desglose th, table.desglose td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        table.desglose th { background: #f8fafc; text-transform: uppercase; font-size: 8.5px; color: #64748b; }
        table.desglose td.derecha { text-align: right; }

        table.movimientos { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.movimientos th, table.movimientos td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        table.movimientos th { background: #f8fafc; text-transform: uppercase; font-size: 8.5px; color: #64748b; }
        table.movimientos td.derecha { text-align: right; }
        table.movimientos tfoot td { font-weight: bold; background: #f8fafc; }

        .pie { text-align: center; font-size: 9px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><span>ISG</span></div>
        <div class="info">
            <p class="institucion">Instituto Superior San Gregorio</p>
            <p class="direccion">Reporte de Ingresos - Panel de Dirección</p>
        </div>
    </div>

    <h1>Reporte de Ingresos — {{ \App\Support\FechaEsp::corta($desde) }} al {{ \App\Support\FechaEsp::corta($hasta) }}</h1>

    <table class="resumen">
        <tr>
            <td class="label">Total ingresado<br><span class="total">$ {{ number_format($resumen['total'], 0, ',', '.') }}</span></td>
            <td class="label">Cantidad de cobros<br>{{ $resumen['cantidad'] }}</td>
            <td class="label">Promedio por cobro<br>$ {{ number_format($resumen['promedio'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="columnas">
        <div class="col">
            <table class="desglose">
                <thead><tr><th>Concepto</th><th class="derecha">Cant.</th><th class="derecha">Total</th></tr></thead>
                <tbody>
                    @forelse ($porConcepto as $fila)
                        <tr>
                            <td>{{ $fila['concepto'] }}</td>
                            <td class="derecha">{{ $fila['cantidad'] }}</td>
                            <td class="derecha">$ {{ number_format($fila['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col">
            <table class="desglose">
                <thead><tr><th>Medio de pago</th><th class="derecha">Cant.</th><th class="derecha">Total</th></tr></thead>
                <tbody>
                    @forelse ($porMedioPago as $fila)
                        <tr>
                            <td>{{ $fila['medio'] }}</td>
                            <td class="derecha">{{ $fila['cantidad'] }}</td>
                            <td class="derecha">$ {{ number_format($fila['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <table class="movimientos">
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Medio de pago</th>
                <th>Registrado por</th>
                <th class="derecha">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ingresos as $i => $mov)
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y H:i') }}</td>
                    <td>{{ $mov->concepto->nombre_concepto }}</td>
                    <td>{{ $mov->medioPago->nombre_medio ?? '—' }}</td>
                    <td>{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</td>
                    <td class="derecha">$ {{ number_format($mov->monto, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center;">No hay ingresos registrados en este período.</td></tr>
            @endforelse
        </tbody>
        @if ($ingresos->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="derecha">TOTAL DEL PERÍODO:</td>
                    <td class="derecha">$ {{ number_format($resumen['total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="pie">Generado el {{ \App\Support\FechaEsp::corta(now()) }} desde el Panel de Gestión — Instituto Superior San Gregorio</p>
</body>
</html>
