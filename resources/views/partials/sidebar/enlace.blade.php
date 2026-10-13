{{-- Un ítem del menú lateral. Espera $href, $texto, $icono (path SVG), $activo y opcionalmente $badge. --}}
<a href="{{ $href }}" @class([
        'sg-sidebar__link group flex items-center gap-3 rounded-xl px-3 py-2 text-[13.5px] font-medium',
        'activo bg-white/[0.12] text-white ring-1 ring-inset ring-white/10' => $activo,
        'text-blue-100/75 hover:bg-white/[0.06] hover:text-white' => ! $activo,
    ])>
    <span @class([
            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
            'bg-[#FFC107] text-[#082A66] shadow-md shadow-amber-500/30' => $activo,
            'bg-white/[0.07] text-[#8FD3F8] group-hover:bg-white/[0.12] group-hover:text-white' => ! $activo,
        ])>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icono }}" />
        </svg>
    </span>
    <span @class(['truncate flex-1', 'font-semibold' => $activo])>{{ $texto }}</span>
    @if (! empty($badge))
        <span class="flex h-5 min-w-[22px] items-center justify-center rounded-full bg-[#FFC107] text-[#082A66] text-[11px] font-extrabold px-1.5 shadow-sm shadow-amber-500/40">
            {{ $badge }}
        </span>
    @endif
</a>
