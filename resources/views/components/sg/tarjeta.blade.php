{{-- Tarjeta con cabecera opcional. Slots: default (cuerpo), "acciones" (cabecera derecha), "pie". $cuerpo=false quita el padding del cuerpo (tablas/listas). --}}
@props(['titulo' => null, 'subtitulo' => null, 'icono' => null, 'tono' => 'azul', 'cuerpo' => true])
<section {{ $attributes->merge(['class' => 'sg-card sg-revelar']) }}>
    @if ($titulo)
        <header class="sg-card__head">
            <div class="sg-card__titulo">
                @if ($icono)
                    <span class="sg-chip sg-chip--chico sg-chip--{{ $tono }}"><x-sg.icono :nombre="$icono" /></span>
                @endif
                <div class="min-w-0">
                    <h3>{{ $titulo }}</h3>
                    @if ($subtitulo)
                        <p>{{ $subtitulo }}</p>
                    @endif
                </div>
            </div>
            @isset($acciones)
                <div class="flex items-center gap-2 flex-wrap">{{ $acciones }}</div>
            @endisset
        </header>
    @endif
    <div @class(['sg-card__body' => $cuerpo])>{{ $slot }}</div>
    @isset($pie)
        <footer class="sg-card__foot">{{ $pie }}</footer>
    @endisset
</section>
