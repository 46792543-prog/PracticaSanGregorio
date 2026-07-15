@extends('layouts.director')

@section('titulo', 'Libro de Caja')
@section('subtitulo', 'Los cobros de cuotas se asientan automáticamente · ' . \App\Support\FechaEsp::mesAnio($mes))

@section('contenido')
    @php
        $tarjetas = [
            ['label' => 'Hoy — ' . \App\Support\FechaEsp::corta(now()), 'datos' => $resumenHoy, 'color' => 'amber'],
            ['label' => 'Esta semana', 'datos' => $resumenSemana, 'color' => 'blue'],
            ['label' => 'Este mes — ' . \App\Support\FechaEsp::mesAnio($mes), 'datos' => $resumenMes, 'color' => 'emerald'],
        ];
        $colorBar = ['amber' => 'border-l-amber-400', 'blue' => 'border-l-blue-400', 'emerald' => 'border-l-emerald-400'];
    @endphp

    <div class="flex items-center gap-2 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 text-[#1E4D8C]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
        <h2 class="font-bold text-slate-800">Resumen de ingresos y gastos</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-8">
        @foreach ($tarjetas as $tarjeta)
            <div class="bg-white rounded-2xl shadow-sm border border-l-4 {{ $colorBar[$tarjeta['color']] }} border-slate-100 p-5">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $tarjeta['label'] }}</p>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-500">Ingresos</span>
                    <span class="font-bold text-emerald-600">$ {{ number_format($tarjeta['datos']['ingresos'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-500">Gastos</span>
                    <span class="font-bold text-rose-500">$ {{ number_format($tarjeta['datos']['gastos'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-slate-100">
                    <span class="text-slate-600 font-semibold">Saldo</span>
                    <span class="font-bold text-[#1E4D8C]">$ {{ number_format($tarjeta['datos']['saldo'], 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">{{ $tarjeta['datos']['cantidad'] }} movimiento(s)</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">🧾 Registrar gasto</h2>
        </div>
        <form method="POST" action="{{ route('director.caja.gastos.store') }}" class="p-6 grid sm:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">CONCEPTO *</label>
                <input type="text" name="concepto" required placeholder="Ej: Compra material de librería"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MONTO *</label>
                <div class="flex items-center gap-1">
                    <span class="text-slate-400 text-sm">$</span>
                    <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0,00"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO</label>
                <select name="turno" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    <option value="">—</option>
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA *</label>
                <input type="date" name="fecha_movimiento" value="{{ now()->toDateString() }}" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div class="sm:col-span-4 flex justify-end">
                <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm px-6 py-2.5 shadow-sm hover:shadow-md transition">
                    − Registrar gasto
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-transparent">
            <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">📖 Planilla de Control de Gastos — {{ \App\Support\FechaEsp::mesAnio($mes) }}</h3>
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="mes" value="{{ $mes->format('Y-m') }}" onchange="this.form.submit()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs">
                <select name="turno" onchange="this.form.submit()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs">
                    <option value="">Todos los turnos</option>
                    <option value="Mañana" @selected($turno === 'Mañana')>Mañana</option>
                    <option value="Tarde" @selected($turno === 'Tarde')>Tarde</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                        <th class="px-6 py-3 font-semibold">N°</th>
                        <th class="px-6 py-3 font-semibold">Fecha y hora</th>
                        <th class="px-6 py-3 font-semibold">Turno</th>
                        <th class="px-6 py-3 font-semibold">Concepto</th>
                        <th class="px-6 py-3 font-semibold">Tipo</th>
                        <th class="px-6 py-3 font-semibold">Registrado por</th>
                        <th class="px-6 py-3 font-semibold text-right">Ingreso</th>
                        <th class="px-6 py-3 font-semibold text-right">Gastos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($movimientos as $i => $mov)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-3 text-slate-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $mov->fecha_movimiento->format('d/m') }} · {{ $mov->fecha_movimiento->format('H:i') }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $mov->turno ?? '—' }}</td>
                            <td class="px-6 py-3 text-slate-700">{{ $mov->concepto }}</td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $mov->tipo === 'ingreso' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-6 w-6 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold grid place-items-center">{{ mb_substr($mov->registradoPor->nombre, 0, 1) }}{{ mb_substr($mov->registradoPor->apellido, 0, 1) }}</span>
                                    <span class="text-slate-600 text-xs">{{ $mov->registradoPor->nombre }} {{ $mov->registradoPor->apellido }}</span>
                                    <span class="text-[10px] font-semibold rounded px-1.5 py-0.5 {{ $mov->registradoPor->esDirector() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $mov->registradoPor->esDirector() ? 'Dir.' : 'Sec.' }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-emerald-600">{{ $mov->tipo === 'ingreso' ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-rose-500">{{ $mov->tipo === 'gasto' ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-slate-400">No hay movimientos registrados en este período.</td></tr>
                    @endforelse
                </tbody>
                @if ($movimientos->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50 font-bold text-slate-700">
                            <td colspan="6" class="px-6 py-3 text-right">TOTALES DEL MES:</td>
                            <td class="px-6 py-3 text-right text-emerald-600">$ {{ number_format($resumenMes['ingresos'], 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right text-rose-500">$ {{ number_format($resumenMes['gastos'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="grid grid-cols-2 gap-6 px-10 py-8 text-center text-sm text-slate-500">
            <p class="border-t border-slate-300 pt-2">Secretaria</p>
            <p class="border-t border-slate-300 pt-2">Directora — Directora General</p>
        </div>
    </div>
@endsection
