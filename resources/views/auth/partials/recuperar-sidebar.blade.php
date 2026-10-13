{{--
    Indicador de los 3 pasos del flujo "olvidé mi contraseña".
    $paso: paso actual (1, 2 o 3). Los anteriores se marcan como hechos.
--}}
@php $paso = $paso ?? 1; @endphp
<div class="acc-pasos" role="list" aria-label="Pasos para recuperar la contraseña">
    @foreach ([1 => 'Email', 2 => 'Código', 3 => 'Nueva clave'] as $n => $etiqueta)
        <span role="listitem" class="acc-paso {{ $n < $paso ? 'acc-paso--hecho' : ($n === $paso ? 'acc-paso--activo' : '') }}" @if ($n === $paso) aria-current="step" @endif>
            <span class="acc-paso__circulo">
                @if ($n < $paso)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    {{ $n }}
                @endif
            </span>
            <span>{{ $etiqueta }}</span>
        </span>
        @if ($n < 3)
            <span class="acc-paso__linea {{ $n < $paso ? 'acc-paso__linea--hecha' : '' }}"></span>
        @endif
    @endforeach
</div>
