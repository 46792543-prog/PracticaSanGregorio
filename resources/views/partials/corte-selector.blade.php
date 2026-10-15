@php
    $corteActual = \App\Support\CorteActivo::actual();
    $cortesDisponibles = \App\Models\AnioLectivo::with('estadoAnio')->orderByDesc('anio')->get();
@endphp

@if ($cortesDisponibles->isNotEmpty())
    <form method="POST" action="{{ route('corte.seleccionar') }}" class="flex items-center gap-2 rounded-full bg-[#0B3D91] pl-3 pr-1 py-1 shadow-sm">
        @csrf
        <label for="select-corte-activo" class="text-[10px] font-bold text-[#FFD54F] uppercase tracking-[.14em]">Año lectivo</label>
        <select id="select-corte-activo" name="id_anio_lectivo" onchange="this.form.submit()"
                class="rounded-full border-0 bg-white pl-3 pr-7 py-1 text-xs font-bold text-[#0B3D91] cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#4FC3F7]">
            @foreach ($cortesDisponibles as $corte)
                <option value="{{ $corte->id_anio_lectivo }}" @selected($corteActual?->id_anio_lectivo === $corte->id_anio_lectivo)>
                    {{ $corte->anio }}{{ $corte->estadoAnio?->nombre_estado !== 'Activo' ? ' (Cerrado)' : '' }}
                </option>
            @endforeach
        </select>
    </form>
@endif
