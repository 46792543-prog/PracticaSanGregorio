@extends('layouts.director')

@section('titulo', 'Libro de Caja')
@section('subtitulo', 'Los cobros de cuotas se asientan automáticamente · ' . \App\Support\FechaEsp::mesAnio($mes))

@section('contenido')
    @php
        $tarjetas = [
            ['label' => 'Hoy', 'detalle' => \App\Support\FechaEsp::corta(now()), 'datos' => $resumenHoy, 'icono' => 'clock', 'tono' => 'amarillo'],
            ['label' => 'Esta semana', 'detalle' => 'Últimos 7 días', 'datos' => $resumenSemana, 'icono' => 'calendar', 'tono' => 'celeste'],
            ['label' => 'Este mes', 'detalle' => \App\Support\FechaEsp::mesAnio($mes), 'datos' => $resumenMes, 'icono' => 'chart', 'tono' => 'verde'],
        ];
    @endphp

    <x-sg.encabezado titulo="Libro de Caja" :subtitulo="'Los cobros de cuotas se asientan automáticamente · ' . \App\Support\FechaEsp::mesAnio($mes)" eyebrow="Finanzas" icono="book" />

    <div class="grid md:grid-cols-3 gap-5 mb-8" data-sg-escalonar>
        @foreach ($tarjetas as $tarjeta)
            <div class="sg-card sg-card--hover sg-stat sg-tono--{{ $tarjeta['tono'] }}">
                <span class="sg-stat__deco"></span>
                <div class="relative flex items-center gap-3 mb-4">
                    <span class="sg-chip sg-chip--{{ $tarjeta['tono'] }}"><x-sg.icono :nombre="$tarjeta['icono']" /></span>
                    <div>
                        <p class="font-display font-bold text-slate-800">{{ $tarjeta['label'] }}</p>
                        <p class="text-xs text-slate-400">{{ $tarjeta['detalle'] }}</p>
                    </div>
                </div>
                <div class="relative space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 flex items-center gap-1.5"><x-sg.icono nombre="trending-up" class="h-4 w-4 text-emerald-500" /> Ingresos</span>
                        <span class="font-bold text-emerald-600">$ {{ number_format($tarjeta['datos']['ingresos'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 flex items-center gap-1.5"><x-sg.icono nombre="trending-down" class="h-4 w-4 text-rose-500" /> Gastos</span>
                        <span class="font-bold text-rose-500">$ {{ number_format($tarjeta['datos']['gastos'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="relative flex justify-between items-end mt-4 pt-4 border-t border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Saldo</p>
                        <p class="font-display text-2xl font-extrabold text-[#0B3D91]">$ {{ number_format($tarjeta['datos']['saldo'], 0, ',', '.') }}</p>
                    </div>
                    <span class="sg-badge sg-badge--gris sg-badge--sin-punto">{{ $tarjeta['datos']['cantidad'] }} mov.</span>
                </div>
                <span class="sg-stat__barra"></span>
            </div>
        @endforeach
    </div>

    <div class="sg-card sg-revelar overflow-hidden mb-8 border-t-4 border-t-rose-400">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--rojo"><x-sg.icono nombre="trending-down" /></span>
                <div><h3>Registrar gasto</h3><p>Se asienta como egreso en el libro del mes</p></div>
            </div>
        </div>
        <form method="POST" action="{{ route('director.caja.gastos.store') }}" class="p-6 grid sm:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="sg-label">Concepto *</label>
                <input type="text" maxlength="30" name="concepto" required placeholder="Ej: Compra material de librería"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Monto *</label>
                <div class="sg-campo-icono">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">$</span>
                    <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0,00"
                           onkeydown="if (['e', 'E', '+', '-'].includes(event.key)) event.preventDefault();"
                           oninput="this.value = this.value.replace(/[eE+-]/g, ''); if (this.value.replace(/[^0-9]/g, '').length > 10) { this.value = this.value.slice(0, -1); }"
                           class="sg-input w-full !pl-8">
                </div>
            </div>
            <div>
                <label class="sg-label">Fecha *</label>
                <input type="date" name="fecha_movimiento" value="{{ now()->toDateString() }}" required
                       class="sg-input w-full">
            </div>
            <div class="sm:col-span-4 flex justify-end">
                <button type="submit" class="sg-btn sg-btn--peligro-lleno">
                    <x-sg.icono nombre="trending-down" /> Registrar gasto
                </button>
            </div>
        </form>
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="book" /></span>
                <div><h3>Planilla de control de gastos</h3><p>{{ \App\Support\FechaEsp::mesAnio($mes) }}</p></div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="mes" value="{{ $mes->format('Y-m') }}" onchange="this.form.submit()" class="sg-input sg-input--chico w-auto">
                </form>
                <a href="{{ route('director.caja.pdf', ['mes' => $mes->format('Y-m')]) }}" target="_blank"
                   class="sg-btn sg-btn--azul sg-btn--chico">
                    <x-sg.icono nombre="printer" /> PDF
                </a>
                <a href="{{ route('director.caja.excel', ['mes' => $mes->format('Y-m')]) }}"
                   class="sg-btn sg-btn--verde sg-btn--chico">
                    <x-sg.icono nombre="download" /> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Fecha y hora</th>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Registrado por</th>
                        <th class="text-right">Ingreso</th>
                        <th class="text-right">Gastos</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($movimientos as $i => $mov)
                        @php $esIngreso = $mov->tipo === 'Ingreso'; @endphp
                        <tr>
                            <td><span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">{{ str_pad($movimientos->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}</span></td>
                            <td class="whitespace-nowrap">{{ $mov->fecha_movimiento->format('d/m') }} · {{ $mov->fecha_movimiento->format('H:i') }}</td>
                            <td>
                                <span class="font-medium text-slate-800">{{ $mov->concepto->nombre_concepto }}</span>
                                @if ($mov->descripcion_detalle)
                                    <p class="text-xs text-slate-400">{{ $mov->descripcion_detalle }}</p>
                                @endif
                            </td>
                            <td>
                                <span class="sg-badge {{ $esIngreso ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                                    {{ $mov->tipo }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-2 whitespace-nowrap">
                                    <span class="sg-avatar !h-7 !w-7 !rounded-lg !text-[10px]">{{ mb_substr($mov->secretarioRegistra->nombre, 0, 1) }}{{ mb_substr($mov->secretarioRegistra->apellido, 0, 1) }}</span>
                                    <span class="text-slate-600 text-xs">{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</span>
                                    <span class="sg-badge sg-badge--sin-punto !py-0.5 {{ $mov->secretarioRegistra->usuario?->esDirector() ? 'sg-badge--amarillo' : 'sg-badge--gris' }}">
                                        {{ $mov->secretarioRegistra->usuario?->esDirector() ? 'Dir.' : 'Sec.' }}
                                    </span>
                                </span>
                            </td>
                            <td class="text-right font-display font-bold !text-emerald-600 whitespace-nowrap">{{ $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                            <td class="text-right font-display font-bold !text-rose-500 whitespace-nowrap">{{ ! $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="book">No hay movimientos registrados en este período.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
                @if ($movimientos->isNotEmpty())
                    <tfoot>
                        <tr class="bg-blue-50 font-bold text-slate-700">
                            <td colspan="5" class="px-6 py-4 text-right text-xs uppercase tracking-wider text-[#0B3D91]">Totales del mes</td>
                            <td class="px-6 py-4 text-right font-display text-emerald-600 whitespace-nowrap">$ {{ number_format($resumenMes['ingresos'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-display text-rose-500 whitespace-nowrap">$ {{ number_format($resumenMes['gastos'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($movimientos->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $movimientos->links() }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-10 px-10 py-8 text-center text-sm text-slate-500 border-t border-slate-100">
            <p class="border-t-2 border-dashed border-slate-200 pt-2">Secretaria</p>
            <p class="border-t-2 border-dashed border-slate-200 pt-2">Directora — Directora General</p>
        </div>
    </div>
@endsection
