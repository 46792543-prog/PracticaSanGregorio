{{-- Estado vacío amigable. --}}
@props(['titulo' => null, 'icono' => 'inbox'])
<div {{ $attributes->merge(['class' => 'sg-vacio']) }}>
    <span class="sg-vacio__icono"><x-sg.icono :nombre="$icono" /></span>
    @if ($titulo)
        <strong>{{ $titulo }}</strong>
    @endif
    <p>{{ $slot }}</p>
</div>
