@extends('layouts.director')

@section('titulo', 'Reporte de Ingresos')
@section('subtitulo', \App\Support\FechaEsp::corta($desde) . ' — ' . \App\Support\FechaEsp::corta($hasta))

@section('contenido')
    <x-sg.encabezado titulo="Reporte de Ingresos" :subtitulo="'Período ' . \App\Support\FechaEsp::corta($desde) . ' — ' . \App\Support\FechaEsp::corta($hasta)" eyebrow="Finanzas" icono="trending-up">
        <a href="{{ route('director.reportes.ingresos.pdf', request()->query()) }}" target="_blank" class="sg-btn sg-btn--borde">
            <x-sg.icono nombre="printer" /> PDF
        </a>
        <a href="{{ route('director.reportes.ingresos.excel', request()->query()) }}" class="sg-btn sg-btn--verde">
            <x-sg.icono nombre="download" /> Excel
        </a>
    </x-sg.encabezado>

    <div class="sg-card sg-revelar p-5 mb-6">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label class="sg-label">Desde</label>
                <input type="date" name="desde" value="{{ $desde->toDateString() }}"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta->toDateString() }}"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Concepto</label>
                <select name="concepto" class="sg-input w-full">
                    <option value="">Todos</option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{ $concepto->id_concepto }}" @selected($conceptoId == $concepto->id_concepto)>{{ $concepto->nombre_concepto }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Medio de pago</label>
                <select name="medio_pago" class="sg-input w-full">
                    <option value="">Todos</option>
                    @foreach ($mediosPago as $medio)
                        <option value="{{ $medio->id_medio_pago }}" @selected($medioPagoId == $medio->id_medio_pago)>{{ $medio->nombre_medio }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="sg-btn sg-btn--azul w-full">
                <x-sg.icono nombre="filter" /> Filtrar
            </button>
        </form>
    </div>

    <div class="grid md:grid-cols-3 gap-5 mb-6" data-sg-escalonar>
        <x-sg.stat etiqueta="Total ingresado" :valor="'$ ' . number_format($resumen['total'], 0, ',', '.')" icono="banknotes" tono="verde" />
        <x-sg.stat etiqueta="Cantidad de cobros" :valor="$resumen['cantidad']" icono="hashtag" tono="azul" />
        <x-sg.stat etiqueta="Promedio por cobro" :valor="'$ ' . number_format($resumen['promedio'], 0, ',', '.')" icono="chart" tono="amarillo" />
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        @foreach ([['Por concepto', 'list', 'azul', $porConcepto, 'concepto'], ['Por medio de pago', 'card', 'celeste', $porMedioPago, 'medio']] as [$titulo, $icono, $tono, $filas, $clave])
            <x-sg.tarjeta :titulo="$titulo" :icono="$icono" :tono="$tono">
                <div class="space-y-4 sg-filas">
                    @forelse ($filas as $fila)
                        @php $pct = $resumen['total'] > 0 ? round($fila['total'] / $resumen['total'] * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-slate-700">{{ $fila[$clave] }} <span class="text-slate-400 text-xs">({{ $fila['cantidad'] }})</span></span>
                                <span class="font-display font-bold text-emerald-600">$ {{ number_format($fila['total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="sg-progreso flex-1"><span style="width: {{ $pct }}%"></span></div>
                                <span class="w-10 text-right text-xs font-bold text-slate-400">{{ $pct }}%</span>
                            </div>
                        </div>
                    @empty
                        <x-sg.vacio icono="chart">Sin datos en el período.</x-sg.vacio>
                    @endforelse
                </div>
            </x-sg.tarjeta>
        @endforeach
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="table" /></span>
                <h3>Detalle de cobros</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Medio de pago</th>
                        <th>Registrado por</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($ingresos as $mov)
                        <tr>
                            <td class="whitespace-nowrap">{{ $mov->fecha_movimiento->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="font-medium text-slate-800">{{ $mov->concepto->nombre_concepto }}</span>
                                @if ($mov->descripcion_detalle)
                                    <p class="text-xs text-slate-400">{{ $mov->descripcion_detalle }}</p>
                                @endif
                            </td>
                            <td><span class="sg-badge sg-badge--celeste sg-badge--sin-punto">{{ $mov->medioPago->nombre_medio ?? '—' }}</span></td>
                            <td>{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</td>
                            <td class="text-right font-display font-bold !text-emerald-600 whitespace-nowrap">$ {{ number_format($mov->monto, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-sg.vacio icono="banknotes">No hay ingresos registrados en este período.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ingresos->hasPages())
            <div class="sg-card__foot">{{ $ingresos->links() }}</div>
        @endif
    </div>
@endsection
