@php
    $corteActual = \App\Support\CorteActivo::actual();
    $cortesDisponibles = \App\Models\AnioLectivo::with('estadoAnio')->orderByDesc('anio')->get();
@endphp

@if ($cortesDisponibles->isNotEmpty())
    <form method="POST" action="{{ route('corte.seleccionar') }}" class="flex items-center gap-2">
        @csrf
        <label for="select-corte-activo" class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Corte</label>
        <select id="select-corte-activo" name="id_anio_lectivo" onchange="this.form.submit()"
                class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-bold text-[#1E4D8C] focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30">
            @foreach ($cortesDisponibles as $corte)
                <option value="{{ $corte->id_anio_lectivo }}" @selected($corteActual?->id_anio_lectivo === $corte->id_anio_lectivo)>
                    {{ $corte->anio }}{{ $corte->estadoAnio?->nombre_estado !== 'Activo' ? ' (Cerrado)' : '' }}
                </option>
            @endforeach
        </select>
    </form>
@endif
