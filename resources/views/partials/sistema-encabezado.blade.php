{{-- Encabezado destacado de la pantalla (título + subtítulo), con el estilo del hero de la landing. --}}
<header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B3D91] via-[#0B3D91] to-[#082A66] text-white px-6 sm:px-8 py-7 mb-8 shadow-lg">
    <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-[#4FC3F7]/25 blur-2xl"></div>
    <div class="pointer-events-none absolute right-24 -bottom-14 h-36 w-36 rounded-full border-[10px] border-[#FFC107]/20"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
         style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>
    <div class="relative">
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-inset ring-white/15 px-3 py-1 text-[10px] font-bold tracking-[.14em] uppercase text-[#FFD54F]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FFC107]"></span>Instituto Superior San Gregorio
        </span>
        <h1 class="mt-3 text-2xl sm:text-[28px] font-extrabold tracking-tight">@yield('titulo')</h1>
        @hasSection('subtitulo')
            <p class="text-sm text-blue-100/85 mt-1.5 max-w-2xl">@yield('subtitulo')</p>
        @endif
    </div>
</header>
