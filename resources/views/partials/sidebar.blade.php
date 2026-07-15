@php
    $enlaces = [
        ['ruta' => 'panel.index', 'texto' => 'Panel principal', 'icono' => 'home'],
        ['ruta' => 'estado-academico.index', 'texto' => 'Mi estado académico', 'icono' => 'chart'],
        ['ruta' => 'mesas-examen.index', 'texto' => 'Mesas de examen', 'icono' => 'clipboard'],
        ['ruta' => 'inscripciones.index', 'texto' => 'Mis inscripciones', 'icono' => 'pencil'],
        ['ruta' => 'cuotas.index', 'texto' => 'Mis cuotas', 'icono' => 'cash'],
        ['ruta' => 'mis-datos.edit', 'texto' => 'Mis datos', 'icono' => 'user'],
    ];

    $iconos = [
        'home' => 'M3 9.75 12 3l9 6.75V21a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75v-5.25h-4.5V21a.75.75 0 0 1-.75.75H3.75A.75.75 0 0 1 3 21V9.75Z',
        'chart' => 'M3 3v18h18M8.25 17.25V11m4.5 6.25V7m4.5 10.25v-4.5',
        'clipboard' => 'M9 3.75h6a1.5 1.5 0 0 1 1.5 1.5v.75H7.5v-.75A1.5 1.5 0 0 1 9 3.75Zm-3 3h12v13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20.25V6.75Zm3 4.5h6m-6 3.75h6',
        'pencil' => 'M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L8.25 19.038l-4.5 1.125 1.125-4.5L16.862 4.487Z',
        'cash' => 'M2.25 8.25h19.5M2.25 8.25v9a1.5 1.5 0 0 0 1.5 1.5h16.5a1.5 1.5 0 0 0 1.5-1.5v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
        'user' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0',
    ];
@endphp

<aside class="w-72 shrink-0 bg-[#16305a] text-white flex flex-col">
    <div class="px-6 py-7 text-center border-b border-white/10">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white/10 ring-2 ring-amber-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.5" class="h-7 w-7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
            </svg>
        </div>
        <p class="font-bold text-sm leading-tight">Instituto Superior<br>San Gregorio</p>
        <p class="text-[11px] tracking-wide text-amber-400 font-semibold mt-1">PORTAL DEL ALUMNO</p>
    </div>

    <nav class="flex-1 px-3 py-5 space-y-1">
        @foreach ($enlaces as $enlace)
            @php $activo = request()->routeIs($enlace['ruta']); @endphp
            <a href="{{ route($enlace['ruta']) }}"
               class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition
                      {{ $activo ? 'bg-amber-500 text-white shadow' : 'text-blue-100 hover:bg-white/10' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$enlace['icono']] }}" />
                </svg>
                {{ $enlace['texto'] }}
            </a>
        @endforeach
    </nav>

    <div class="px-3 py-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-blue-100 hover:bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H8.25m9.75 0-3-3m3 3-3 3" />
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>
