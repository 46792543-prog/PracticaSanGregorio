{{-- Encabezado del menú lateral: logo + nombre de la institución. Espera $configInstitucion y $etiqueta. --}}
<div class="relative px-6 pt-7 pb-6 flex items-center gap-3">
    <div class="sg-sidebar__marca flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#4FC3F7] to-[#0B3D91] border-2 border-[#FFC107] overflow-hidden">
        @if ($configInstitucion?->logo_url)
            <img src="{{ $configInstitucion->logo_url }}" alt="Logo institucional" class="h-full w-full object-cover">
        @else
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                <path stroke-linecap="round" d="M12 9v6M9 12h6" />
            </svg>
        @endif
    </div>
    <div class="min-w-0 flex-1">
        <p class="font-display font-extrabold text-[14px] leading-tight text-white line-clamp-2">{{ $configInstitucion?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</p>
        <p class="mt-1 inline-flex items-center gap-1.5 text-[10px] font-bold tracking-[.14em] text-[#FFC107] uppercase">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FFC107]"></span>{{ $etiqueta }}
        </p>
    </div>
    <button type="button" data-sg-menu-cerrar class="lg:hidden -mr-2 flex h-9 w-9 items-center justify-center rounded-xl text-blue-100/80 hover:bg-white/10 hover:text-white" aria-label="Cerrar menú">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>
</div>
<div class="relative mx-4 h-6 sg-deco__separador" aria-hidden="true">
    <svg class="sg-deco__ecg" viewBox="0 0 280 60" preserveAspectRatio="none">
        <path class="sg-deco__ecg-base" d="M0 30 H90 L100 30 L108 14 L118 48 L128 6 L138 42 L146 30 H180 L188 22 L196 30 H280" />
        <path class="sg-deco__ecg-pulso" d="M0 30 H90 L100 30 L108 14 L118 48 L128 6 L138 42 L146 30 H180 L188 22 L196 30 H280" />
    </svg>
</div>
