{{-- Encabezado de pantalla: ícono + eyebrow + título + subtítulo, con acciones a la derecha (slot). --}}
@props(['titulo', 'subtitulo' => null, 'eyebrow' => null, 'icono' => 'sparkles'])
<div {{ $attributes->merge(['class' => 'sg-encabezado sg-revelar']) }}>
    <div class="sg-encabezado__info">
        <span class="sg-encabezado__icono"><x-sg.icono :nombre="$icono" /></span>
        <div class="min-w-0">
            @if ($eyebrow)
                <span class="sg-eyebrow">{{ $eyebrow }}</span>
            @endif
            <h1>{{ $titulo }}</h1>
            @if ($subtitulo)
                <p class="sg-encabezado__sub">{{ $subtitulo }}</p>
            @endif
        </div>
    </div>
    @if (trim($slot) !== '')
        <div class="sg-encabezado__acciones">{{ $slot }}</div>
    @endif
</div>
