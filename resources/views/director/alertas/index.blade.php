@extends('layouts.director')

@section('titulo', 'Central de Alertas')
@section('subtitulo', 'Cuotas vencidas y bajas recientes que requieren atención')

@section('contenido')
    @php
        $colores = [
            'cuota_vencida' => 'bg-rose-50 text-rose-600',
            'baja_alumno' => 'bg-amber-50 text-amber-700',
            'baja_profesor' => 'bg-amber-50 text-amber-700',
        ];
        $iconosPorTipo = [
            'cuota_vencida' => '💸',
            'baja_alumno' => '🚪',
            'baja_profesor' => '🚪',
        ];
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-rose-50 to-transparent">
            <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">🔔 Alertas activas ({{ $alertas->total() }})</h3>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 px-6 py-4 border-b border-slate-100">
            <select name="tipo" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Todos los tipos</option>
                @foreach ($tipos as $clave => $label)
                    <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($tipo)
                <a href="{{ route('director.alertas.index') }}" class="rounded-xl border border-slate-200 text-slate-500 text-sm font-semibold px-4 py-2">Limpiar</a>
            @endif
        </form>

        <div class="divide-y divide-slate-50">
            @forelse ($alertas as $alerta)
                <a href="{{ $alerta['ruta'] }}" class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50/70 transition">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg {{ $colores[$alerta['tipo']] }}">
                        {{ $iconosPorTipo[$alerta['tipo']] }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm text-slate-800">{{ $alerta['titulo'] }}</span>
                            @if ($alerta['severidad'] === 'alta')
                                <span class="text-[10px] font-bold uppercase tracking-wide rounded-full px-2 py-0.5 bg-rose-100 text-rose-700">Urgente</span>
                            @endif
                        </span>
                        <span class="block text-xs text-slate-500 mt-0.5">{{ $alerta['descripcion'] }}</span>
                    </span>
                    <span class="text-slate-300 text-sm">→</span>
                </a>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-3xl mb-2">✅</p>
                    <p class="text-slate-500 text-sm">No hay alertas activas por el momento.</p>
                </div>
            @endforelse
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $alertas->links() }}
        </div>
    </div>
@endsection
