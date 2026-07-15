@php
    $cuotasPendientesBadge = \App\Models\Cuota::where('estado', 'pendiente')->count();

    $enlaces = [
        ['ruta' => 'director.panel.index', 'texto' => 'Panel principal', 'icono' => 'home'],
        ['ruta' => 'director.cuotas.index', 'texto' => 'Registrar Cuotas', 'icono' => 'card', 'badge' => $cuotasPendientesBadge],
        ['ruta' => 'director.caja.index', 'texto' => 'Libro de caja', 'icono' => 'book'],
    ];

    $iconos = [
        'home' => 'M11.47 3.84a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06l-.22-.22V19.5a1.5 1.5 0 0 1-1.5 1.5h-3a.75.75 0 0 1-.75-.75V15a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v5.25a.75.75 0 0 1-.75.75h-3a1.5 1.5 0 0 1-1.5-1.5v-7.32l-.22.22a.75.75 0 1 1-1.06-1.06l7.5-7.5Z',
        'card' => 'M2.25 8.25h19.5M2.25 8.25v9a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
        'book' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'grid' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        'logout' => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H8.25m9.75 0-3-3m3 3-3 3',
        'chevron' => 'm8.25 4.5 7.5 7.5-7.5 7.5',
    ];
@endphp

<aside class="w-72 shrink-0 bg-gradient-to-b from-[#122a52] via-[#0e2242] to-[#0a1a34] text-white flex flex-col relative">
    <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
         style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative px-6 pt-7 pb-6 flex items-center gap-3 border-b border-white/10">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#a97b0e] shadow-lg shadow-black/20 ring-1 ring-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0e2242" stroke-width="1.7" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="font-bold text-[15px] leading-tight truncate">Instituto Superior</p>
            <p class="font-bold text-[15px] leading-tight truncate">San Gregorio</p>
            <p class="text-[10px] font-semibold tracking-widest text-[#e8c465] mt-0.5">DIRECCIÓN</p>
        </div>
    </div>

    <nav class="relative flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        @foreach ($enlaces as $enlace)
            @php $activo = request()->routeIs($enlace['ruta'] . '*'); @endphp
            <a href="{{ route($enlace['ruta']) }}"
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all
                      {{ $activo ? 'bg-white text-[#122a52] shadow-md shadow-black/20' : 'text-blue-100/80 hover:bg-white/[0.07] hover:text-white' }}">
                <span @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                        'bg-[#D4A017]/15 text-[#D4A017]' => $activo,
                        'bg-white/[0.06] text-blue-200 group-hover:bg-white/10 group-hover:text-white' => ! $activo,
                    ])>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$enlace['icono']] }}" />
                    </svg>
                </span>
                <span class="truncate flex-1 {{ $activo ? 'font-semibold' : '' }}">{{ $enlace['texto'] }}</span>
                @if (! empty($enlace['badge']))
                    <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A017] text-[#122a52] text-[11px] font-bold px-1.5">
                        {{ $enlace['badge'] }}
                    </span>
                @elseif ($activo)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5 text-[#D4A017]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['chevron'] }}" />
                    </svg>
                @endif
            </a>
        @endforeach

        <p class="px-3.5 pt-5 pb-1.5 text-[10px] font-bold tracking-widest text-blue-300/50 uppercase">Acceso administrativo</p>
        <a href="{{ route('admin.panel.index') }}"
           class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-blue-100/80 hover:bg-white/[0.07] hover:text-white transition-all">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.06] text-blue-200 group-hover:bg-white/10 group-hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['grid'] }}" />
                </svg>
            </span>
            <span class="truncate">Ir al Panel Administrativo</span>
        </a>
    </nav>

    <div class="relative px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] px-3 py-2.5 mb-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#D4A017] to-[#a97b0e] text-[#122a52] text-xs font-bold">
                {{ mb_substr(auth()->user()->nombre, 0, 1) }}{{ mb_substr(auth()->user()->apellido, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">Dra. {{ auth()->user()->apellido }}</p>
                <p class="text-[11px] text-blue-300/70">Directora General</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-blue-100/70 hover:bg-white/[0.07] hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px] ml-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['logout'] }}" />
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>
