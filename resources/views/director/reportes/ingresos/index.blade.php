@extends('layouts.director')

@section('titulo', 'Reporte de Ingresos')
@section('subtitulo', \App\Support\FechaEsp::corta($desde) . ' — ' . \App\Support\FechaEsp::corta($hasta))

@section('contenido')
    <div class="flex items-center gap-2 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 text-[#1E4D8C]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
        </svg>
        <h2 class="font-bold text-slate-800">Reporte de Ingresos</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
        <form method="GET" class="grid sm:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">DESDE</label>
                <input type="date" name="desde" value="{{ $desde->toDateString() }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">HASTA</label>
                <input type="date" name="hasta" value="{{ $hasta->toDateString() }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">CONCEPTO</label>
                <select name="concepto" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    <option value="">Todos</option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{ $concepto->id_concepto }}" @selected($conceptoId == $concepto->id_concepto)>{{ $concepto->nombre_concepto }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MEDIO DE PAGO</label>
                <select name="medio_pago" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    <option value="">Todos</option>
                    @foreach ($mediosPago as $medio)
                        <option value="{{ $medio->id_medio_pago }}" @selected($medioPagoId == $medio->id_medio_pago)>{{ $medio->nombre_medio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-4 py-2.5 transition">
                    Filtrar
                </button>
                <a href="{{ route('director.reportes.ingresos.pdf', request()->query()) }}" target="_blank"
                   class="flex-1 text-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-4 py-2.5 shadow-sm hover:shadow-md transition">
                    📄 PDF
                </a>
                <a href="{{ route('director.reportes.ingresos.excel', request()->query()) }}"
                   class="flex-1 text-center rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm px-4 py-2.5 shadow-sm hover:shadow-md transition">
                    📊 Excel
                </a>
            </div>
        </form>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-l-4 border-l-emerald-400 border-slate-100 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Total ingresado</p>
            <p class="text-2xl font-bold text-emerald-600">$ {{ number_format($resumen['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-l-4 border-l-blue-400 border-slate-100 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Cantidad de cobros</p>
            <p class="text-2xl font-bold text-[#1E4D8C]">{{ $resumen['cantidad'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-l-4 border-l-amber-400 border-slate-100 p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Promedio por cobro</p>
            <p class="text-2xl font-bold text-amber-600">$ {{ number_format($resumen['promedio'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/80">
                <h3 class="font-bold text-slate-700 text-sm">Por concepto</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($porConcepto as $fila)
                    <div class="flex items-center justify-between px-5 py-2.5 text-sm">
                        <span class="text-slate-600">{{ $fila['concepto'] }} <span class="text-slate-400 text-xs">({{ $fila['cantidad'] }})</span></span>
                        <span class="font-semibold text-emerald-600">$ {{ number_format($fila['total'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-slate-400 text-sm">Sin datos en el período.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/80">
                <h3 class="font-bold text-slate-700 text-sm">Por medio de pago</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($porMedioPago as $fila)
                    <div class="flex items-center justify-between px-5 py-2.5 text-sm">
                        <span class="text-slate-600">{{ $fila['medio'] }} <span class="text-slate-400 text-xs">({{ $fila['cantidad'] }})</span></span>
                        <span class="font-semibold text-emerald-600">$ {{ number_format($fila['total'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-slate-400 text-sm">Sin datos en el período.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-transparent">
            <h3 class="font-bold text-slate-700 text-sm">Detalle de cobros</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                        <th class="px-6 py-3 font-semibold">Fecha</th>
                        <th class="px-6 py-3 font-semibold">Concepto</th>
                        <th class="px-6 py-3 font-semibold">Medio de pago</th>
                        <th class="px-6 py-3 font-semibold">Registrado por</th>
                        <th class="px-6 py-3 font-semibold text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($ingresos as $mov)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-3 text-slate-500">{{ $mov->fecha_movimiento->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3 text-slate-700">
                                {{ $mov->concepto->nombre_concepto }}
                                @if ($mov->descripcion_detalle)
                                    <p class="text-xs text-slate-400">{{ $mov->descripcion_detalle }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $mov->medioPago->nombre_medio ?? '—' }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-emerald-600">$ {{ number_format($mov->monto, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400">No hay ingresos registrados en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $ingresos->links() }}
            </div>
        </div>
    </div>
@endsection
