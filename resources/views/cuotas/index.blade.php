@extends('layouts.portal')

@section('titulo', 'Mis cuotas')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm font-semibold text-slate-600 mb-4">Año lectivo {{ $anioLectivo }}</p>

        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase">✅ Estado actual</p>
                <p class="text-xl font-bold {{ $tieneVencidas ? 'text-red-600' : 'text-green-600' }} mt-1">
                    {{ $tieneVencidas ? 'Con deuda' : 'Al día' }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ $tieneVencidas ? 'Tenés cuotas vencidas' : 'Sin deudas pendientes' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase">📅 Próxima cuota pendiente</p>
                <p class="text-xl font-bold text-slate-800 mt-1">
                    {{ $proximaCuota ? ($proximaCuota->concepto ?: $proximaCuota->mes->nombre_mes) : '—' }}
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    @if ($proximaCuota)
                        {{ $proximaCuota->fecha_vencimiento ? 'Vence ' . \App\Support\FechaEsp::corta($proximaCuota->fecha_vencimiento) . ' · ' : '' }}$ {{ number_format($proximaCuota->monto, 0, ',', '.') }}
                    @else
                        No tenés cuotas pendientes
                    @endif
                </p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase">💵 Pagado en {{ $anioLectivo }}</p>
                <p class="text-xl font-bold text-slate-800 mt-1">$ {{ number_format($totalPagado, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $cantidadPagadas }} cuotas abonadas</p>
            </div>
        </div>

        <h3 class="font-bold text-slate-800 mb-3">🧾 Cuotas del año lectivo</h3>
        <div class="divide-y divide-slate-100">
            @foreach ($cuotas as $cuota)
                <div class="flex items-center justify-between py-4 {{ $cuota->pagado ? '' : ($cuota->vencida ? 'bg-red-50/50' : 'bg-blue-50/50') }} px-2 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span @class([
                                'h-8 w-8 rounded-full flex items-center justify-center text-white text-sm shrink-0',
                                'bg-green-500' => $cuota->pagado,
                                'bg-red-500' => $cuota->vencida,
                                'bg-blue-400' => ! $cuota->pagado && ! $cuota->vencida,
                            ])>
                            {{ $cuota->pagado ? '✓' : '—' }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-700 text-sm">{{ $cuota->concepto ?: 'Cuota ' . $cuota->mes->nombre_mes }}</p>
                            <p class="text-xs text-slate-400">
                                @if ($cuota->pagado)
                                    Pagada{{ $cuota->fecha_pago ? ' el ' . \App\Support\FechaEsp::corta($cuota->fecha_pago) : '' }}
                                @elseif ($cuota->fecha_vencimiento)
                                    {{ $cuota->vencida ? 'Venció' : 'Vence' }} el {{ \App\Support\FechaEsp::corta($cuota->fecha_vencimiento) }}
                                @else
                                    Pendiente de pago
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="font-bold text-sm {{ $cuota->pagado ? 'text-green-600' : 'text-slate-700' }}">
                        $ {{ number_format($cuota->monto, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
