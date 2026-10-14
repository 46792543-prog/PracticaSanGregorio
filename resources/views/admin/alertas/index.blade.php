@extends('layouts.admin')

@section('titulo', 'Central de Alertas')
@section('subtitulo', 'Cuotas vencidas y bajas recientes que requieren atención')

@section('contenido')
    @php
        $estilos = [
            'cuota_vencida' => ['icono' => 'banknotes', 'tono' => 'rojo', 'etiqueta' => 'Cuota vencida'],
            'baja_alumno' => ['icono' => 'user-minus', 'tono' => 'amarillo', 'etiqueta' => 'Baja de alumno'],
            'baja_profesor' => ['icono' => 'briefcase', 'tono' => 'amarillo', 'etiqueta' => 'Baja de profesor'],
        ];
    @endphp

    <x-sg.encabezado titulo="Central de Alertas" subtitulo="Cuotas vencidas y bajas recientes que requieren atención" eyebrow="Seguimiento" icono="bell-alert">
        <span class="sg-badge sg-badge--rojo text-sm px-4 py-2">{{ $alertas->total() }} {{ $alertas->total() === 1 ? 'alerta activa' : 'alertas activas' }}</span>
    </x-sg.encabezado>

    <x-sg.tarjeta :cuerpo="false">
        <form method="GET" class="flex flex-wrap items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60 rounded-t-[1.25rem]">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2"><x-sg.icono nombre="filter" class="h-4 w-4" /> Filtrar</span>
            <select name="tipo" onchange="this.form.submit()" class="sg-input w-auto min-w-[220px] py-2">
                <option value="">Todos los tipos</option>
                @foreach ($tipos as $clave => $label)
                    <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($tipo)
                <a href="{{ route('admin.alertas.index') }}" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <div class="sg-lista sg-filas">
            @forelse ($alertas as $alerta)
                @php $e = $estilos[$alerta['tipo']]; @endphp
                <a href="{{ $alerta['ruta'] }}" class="sg-fila group py-4">
                    <span class="sg-chip sg-chip--{{ $e['tono'] }}"><x-sg.icono :nombre="$e['icono']" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm text-slate-800">{{ $alerta['titulo'] }}</span>
                            @if ($alerta['severidad'] === 'alta')
                                <span class="sg-badge sg-badge--rojo">Urgente</span>
                            @endif
                        </span>
                        <span class="block text-xs text-slate-500 mt-1">{{ $alerta['descripcion'] }}</span>
                    </span>
                    <span class="hidden sm:inline-flex sg-badge sg-badge--gris sg-badge--sin-punto">{{ $e['etiqueta'] }}</span>
                    <x-sg.icono nombre="chevron-right" class="sg-fila__flecha h-5 w-5" />
                </a>
            @empty
                <x-sg.vacio icono="check-circle" titulo="¡Todo en orden!">No hay alertas activas por el momento.</x-sg.vacio>
            @endforelse
        </div>

        @if ($alertas->hasPages())
            <x-slot:pie>{{ $alertas->links() }}</x-slot:pie>
        @endif
    </x-sg.tarjeta>
@endsection
