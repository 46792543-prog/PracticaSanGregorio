{{-- Tarjeta de indicador. tono: azul|celeste|amarillo|verde|rojo|violeta|gris. El slot opcional va debajo de la etiqueta. --}}
@props(['etiqueta', 'valor', 'icono' => 'chart', 'tono' => 'azul', 'href' => null, 'contar' => true])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "sg-card sg-card--hover sg-stat sg-tono--{$tono} block"]) }}>
    <span class="sg-stat__deco"></span>
    <div class="relative flex items-start justify-between gap-3">
        <span class="sg-chip sg-chip--{{ $tono }}"><x-sg.icono :nombre="$icono" /></span>
        @if ($href)
            <x-sg.icono nombre="arrow-right" class="h-4 w-4 text-slate-300 mt-1" />
        @endif
    </div>
    <p class="relative sg-stat__valor" @if ($contar) data-sg-contar @endif>{{ $valor }}</p>
    <p class="relative sg-stat__etiqueta">{{ $etiqueta }}</p>
    @if (trim($slot) !== '')
        <div class="relative mt-2 text-xs font-semibold">{{ $slot }}</div>
    @endif
    <span class="sg-stat__barra"></span>
</{{ $tag }}>
