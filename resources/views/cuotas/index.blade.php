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
                <p class="text-xs font-semibold text-slate-400 uppercase">📅 Próximo vencimiento</p>
                <p class="text-xl font-bold text-slate-800 mt-1">
                    {{ $proximaCuota ? \Carbon\Carbon::parse($proximaCuota->fecha_vencimiento)->format('d/m') : '—' }}
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $proximaCuota ? $proximaCuota->concepto . ' · $' . number_format($proximaCuota->monto, 0, ',', '.') : 'No tenés cuotas pendientes' }}
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
                @php $pagada = $cuota->estado === 'pagado'; @endphp
                <div class="flex items-center justify-between py-4 {{ $pagada ? '' : ($cuota->estado === 'vencido' ? 'bg-red-50/50' : 'bg-blue-50/50') }} px-2 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span @class([
                                'h-8 w-8 rounded-full flex items-center justify-center text-white text-sm shrink-0',
                                'bg-green-500' => $pagada,
                                'bg-red-500' => $cuota->estado === 'vencido',
                                'bg-blue-400' => $cuota->estado === 'pendiente',
                            ])>
                            {{ $pagada ? '✓' : '—' }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-700 text-sm">{{ $cuota->concepto }}</p>
                            <p class="text-xs text-slate-400">
                                @if ($pagada)
                                    Pagada el {{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }} · {{ ucfirst($cuota->medio_pago) }}
                                @else
                                    Vence el {{ \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="font-bold text-sm {{ $pagada ? 'text-green-600' : 'text-slate-700' }}">
                        $ {{ number_format($cuota->monto, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
