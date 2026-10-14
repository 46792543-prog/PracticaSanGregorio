{{-- Aviso destacado de alertas pendientes (paneles principales). --}}
@props(['cantidad', 'destacadas', 'href'])
<a href="{{ $href }}" class="group sg-card sg-card--hover sg-revelar flex items-center gap-4 flex-wrap px-5 py-4 mb-8 border-rose-100 bg-gradient-to-r from-rose-50 via-white to-white">
    <span class="relative sg-chip sg-chip--rojo">
        <x-sg.icono nombre="bell-alert" />
        <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-rose-500 ring-2 ring-white sg-pulso"></span>
    </span>
    <div class="min-w-0 flex-1">
        <p class="font-display font-bold text-sm text-rose-700">{{ $cantidad }} {{ $cantidad === 1 ? 'alerta requiere' : 'alertas requieren' }} tu atención</p>
        <p class="text-xs text-rose-500/90 mt-0.5 truncate">
            @foreach ($destacadas as $alerta)
                {{ $alerta['titulo'] }}{{ ! $loop->last ? ' · ' : '' }}
            @endforeach
        </p>
    </div>
    <span class="inline-flex items-center gap-1.5 text-sm font-bold text-rose-600">
        Ver todas <x-sg.icono nombre="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
    </span>
</a>
