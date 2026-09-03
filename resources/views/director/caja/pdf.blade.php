<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro de Caja - {{ \App\Support\FechaEsp::mesAnio($mes) }}</title>
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
        table.resumen td.ingresos { color: #059669; font-weight: bold; }
        table.resumen td.gastos { color: #e11d48; font-weight: bold; }
        table.resumen td.saldo { color: #1E4D8C; font-weight: bold; }

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
            <p class="direccion">Libro de Caja - Panel de Dirección</p>
        </div>
    </div>

    <h1>Planilla de Control de Gastos — {{ \App\Support\FechaEsp::mesAnio($mes) }}</h1>

    <table class="resumen">
        <tr>
            <td class="label">Ingresos del mes<br><span class="ingresos">$ {{ number_format($resumenMes['ingresos'], 0, ',', '.') }}</span></td>
            <td class="label">Gastos del mes<br><span class="gastos">$ {{ number_format($resumenMes['gastos'], 0, ',', '.') }}</span></td>
            <td class="label">Saldo<br><span class="saldo">$ {{ number_format($resumenMes['saldo'], 0, ',', '.') }}</span></td>
            <td class="label">Movimientos<br>{{ $resumenMes['cantidad'] }}</td>
        </tr>
    </table>

    <table class="movimientos">
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Tipo</th>
                <th>Registrado por</th>
                <th class="derecha">Ingreso</th>
                <th class="derecha">Gastos</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $i => $mov)
                @php $esIngreso = $mov->tipo === 'Ingreso'; @endphp
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y H:i') }}</td>
                    <td>{{ $mov->concepto->nombre_concepto }}</td>
                    <td>{{ $mov->tipo }}</td>
                    <td>{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</td>
                    <td class="derecha">{{ $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                    <td class="derecha">{{ ! $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center;">No hay movimientos registrados en este período.</td></tr>
            @endforelse
        </tbody>
        @if ($movimientos->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="derecha">TOTALES DEL MES:</td>
                    <td class="derecha">$ {{ number_format($resumenMes['ingresos'], 0, ',', '.') }}</td>
                    <td class="derecha">$ {{ number_format($resumenMes['gastos'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="pie">Generado el {{ \App\Support\FechaEsp::corta(now()) }} desde el Panel de Gestión — Instituto Superior San Gregorio</p>
</body>
</html>
