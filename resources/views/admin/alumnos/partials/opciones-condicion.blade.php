{{--
    Opciones del desplegable "Condición" del historial, agrupadas y con una
    aclaración (ver CondicionAlumno::GRUPOS_HISTORIAL).
    Recibe: $condiciones (las disponibles), $seleccionada (id, opcional) y
    $conVacio (true = primera opción "Elegí la condición...").
--}}
@php $condicionesPorNombre = $condiciones->keyBy('nombre_condicion'); @endphp
@if (! empty($conVacio))
    <option value="">Elegí la condición...</option>
@endif
@foreach (\App\Models\CondicionAlumno::GRUPOS_HISTORIAL as $grupo => $opciones)
    <optgroup label="{{ $grupo }}">
        @foreach ($opciones as $nombre => $texto)
            @if ($condicion = $condicionesPorNombre->get($nombre))
                <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $nombre }}" @selected(isset($seleccionada) && (int) $seleccionada === (int) $condicion->id_condicion)>{{ $texto }}</option>
            @endif
        @endforeach
    </optgroup>
@endforeach
