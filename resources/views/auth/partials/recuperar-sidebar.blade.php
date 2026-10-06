{{--
    Indicador de los 3 pasos del flujo "olvidé mi contraseña".
    $paso: paso actual (1, 2 o 3). Los anteriores se marcan como hechos.
--}}
@php $paso = $paso ?? 1; @endphp
<div class="landing-pasos" role="list" aria-label="Pasos para recuperar la contraseña">
    @foreach ([1 => 'Email', 2 => 'Código', 3 => 'Nueva clave'] as $n => $etiqueta)
        <span class="landing-paso {{ $n < $paso ? 'landing-paso--hecho' : ($n === $paso ? 'landing-paso--activo' : '') }}">
            <span class="landing-paso__circulo">{{ $n < $paso ? '✓' : $n }}</span>
            <span>{{ $etiqueta }}</span>
        </span>
        @if ($n < 3)
            <span class="landing-paso__linea {{ $n < $paso ? 'landing-paso__linea--hecha' : '' }}"></span>
        @endif
    @endforeach
</div>
