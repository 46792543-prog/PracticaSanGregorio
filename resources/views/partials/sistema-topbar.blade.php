{{-- Barra superior de los paneles. Espera $seccion (texto de la miga de pan) y opcionalmente $conCorte / $extra. --}}
<div class="sg-topbar sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200/70">
    <div class="flex items-center gap-3 px-4 sm:px-6 lg:px-9 h-16">
        <button type="button" data-sg-menu-toggle class="lg:hidden -ml-1 flex h-10 w-10 items-center justify-center rounded-xl text-[#0B3D91] hover:bg-blue-100" aria-label="Abrir menú">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h10.5" />
            </svg>
        </button>

        <div class="min-w-0 flex-1">
            <p class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-[.12em]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#FFC107]"></span>{{ $seccion }}
            </p>
            <p class="font-display font-bold text-[15px] sm:text-base text-[#0B3D91] truncate leading-tight mt-0.5">{!! $titulo !!}</p>
        </div>

        <div class="flex items-center gap-3">
            @if (! empty($extra))
                <span class="hidden xl:inline-flex items-center gap-2 rounded-full bg-blue-100 text-[#0B3D91] text-xs font-semibold px-3 py-1.5 max-w-[260px]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                    <span class="truncate">{{ $extra }}</span>
                </span>
            @endif
            <span class="hidden md:inline-flex items-center gap-2 rounded-full border border-slate-200 text-slate-500 text-xs font-medium px-3 py-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#4FC3F7]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ now()->format('d/m/Y') }}
            </span>
            @if (! empty($conCorte))
                @include('partials.corte-selector')
            @endif
        </div>
    </div>
</div>
