{{-- Fondo animado del menú lateral: motivos de salud que flotan, línea de electrocardiograma y partículas. Solo decorativo. --}}
@php
    $motivos = [
        // [ícono, posición (top / left|right), tamaño, duración, retraso]
        ['cruz', 'top:16%;right:10%', 26, 14, 0],
        ['corazon', 'top:30%;left:7%', 22, 17, -4],
        ['estetoscopio', 'top:44%;right:8%', 30, 19, -8],
        ['capsula', 'top:58%;left:12%', 24, 16, -2],
        ['gota', 'top:70%;right:16%', 20, 15, -6],
        ['adn', 'top:84%;left:6%', 28, 21, -10],
        ['cruz', 'top:92%;right:30%', 16, 13, -3],
        ['corazon', 'top:8%;left:40%', 14, 18, -12],
    ];
    $rutas = [
        'cruz' => 'M9 3.75h6v5.25h5.25v6H15v5.25H9V15H3.75V9H9V3.75Z',
        'corazon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
        'estetoscopio' => 'M6 3.5v5a4 4 0 0 0 8 0v-5M10 12.5v2.5a4.5 4.5 0 0 0 9 0v-1.5M19 13.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM5 3.5h2M13 3.5h2',
        'capsula' => 'M10.5 20.25 3.75 13.5a4.773 4.773 0 0 1 6.75-6.75l6.75 6.75a4.773 4.773 0 0 1-6.75 6.75ZM7.125 10.125l6.75 6.75',
        'gota' => 'M12 3.25s6 6.4 6 10.75a6 6 0 0 1-12 0c0-4.35 6-10.75 6-10.75Z',
        'adn' => 'M8 3c0 5 8 5 8 9s-8 4-8 9M16 3c0 5-8 5-8 9s8 4 8 9M9 6.5h6M9.5 17.5h5M10.5 9.5h3M10.5 14.5h3',
    ];
@endphp
<div class="sg-deco" aria-hidden="true">
    <span class="sg-deco__luz"></span>
    <span class="sg-deco__puntos"></span>

    @foreach ($motivos as $i => [$icono, $pos, $tam, $dur, $retraso])
        <svg class="sg-deco__motivo" style="{{ $pos }};width:{{ $tam }}px;height:{{ $tam }}px;animation-duration:{{ $dur }}s;animation-delay:{{ $retraso }}s"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $rutas[$icono] }}" />
        </svg>
    @endforeach


    {{-- Partículas que suben despacio --}}
    @foreach ([8, 22, 37, 51, 64, 78, 90] as $j => $izq)
        <span class="sg-deco__particula" style="left:{{ $izq }}%;animation-delay:{{ -$j * 2.3 }}s;animation-duration:{{ 12 + ($j % 3) * 3 }}s"></span>
    @endforeach
</div>
