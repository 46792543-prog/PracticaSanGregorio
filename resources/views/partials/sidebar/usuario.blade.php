{{-- Pie del menú lateral: usuario conectado + cerrar sesión. Espera $nombre, $detalle e $iniciales. --}}
<div class="relative px-4 pt-3 pb-5">
    <div class="flex items-center gap-3 rounded-2xl bg-white/[0.07] ring-1 ring-inset ring-white/10 p-2.5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#FFD54F] to-[#FFC107] text-[#082A66] text-xs font-extrabold shadow-md shadow-black/20">
            {{ $iniciales }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold text-white truncate">{{ $nombre }}</p>
            <p class="text-[11px] text-[#8FD3F8]/80 truncate">{{ $detalle }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-blue-100/70 hover:bg-white/10 hover:text-[#FFC107] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H8.25m9.75 0-3-3m3 3-3 3" />
                </svg>
            </button>
        </form>
    </div>
</div>
