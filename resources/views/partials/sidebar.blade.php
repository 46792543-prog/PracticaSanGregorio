@php
    $configInstitucion = \App\Models\ConfiguracionInstitucion::first();
    $enlaces = [
        ['ruta' => 'panel.index', 'texto' => 'Panel principal', 'icono' => 'home'],
        ['ruta' => 'estado-academico.index', 'texto' => 'Mi estado académico', 'icono' => 'chart'],
        ['ruta' => 'cursada.index', 'texto' => 'Inscripción a cursada', 'icono' => 'cap'],
        ['ruta' => 'mesas-examen.index', 'texto' => 'Mesas de examen', 'icono' => 'clipboard'],
        ['ruta' => 'inscripciones.index', 'texto' => 'Mis inscripciones', 'icono' => 'pencil'],
        ['ruta' => 'cuotas.index', 'texto' => 'Mis cuotas', 'icono' => 'cash'],
        ['ruta' => 'mis-datos.edit', 'texto' => 'Mis datos', 'icono' => 'user'],
    ];

    $iconos = [
        'home' => 'M3 9.75 12 3l9 6.75V21a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75v-5.25h-4.5V21a.75.75 0 0 1-.75.75H3.75A.75.75 0 0 1 3 21V9.75Z',
        'chart' => 'M3 3v18h18M8.25 17.25V11m4.5 6.25V7m4.5 10.25v-4.5',
        'clipboard' => 'M9 3.75h6a1.5 1.5 0 0 1 1.5 1.5v.75H7.5v-.75A1.5 1.5 0 0 1 9 3.75Zm-3 3h12v13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20.25V6.75Zm3 4.5h6m-6 3.75h6',
        'cap' => 'M4.26 10.147a60.437 60.437 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443',
        'pencil' => 'M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L8.25 19.038l-4.5 1.125 1.125-4.5L16.862 4.487Z',
        'cash' => 'M2.25 8.25h19.5M2.25 8.25v9a1.5 1.5 0 0 0 1.5 1.5h16.5a1.5 1.5 0 0 0 1.5-1.5v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
        'user' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0',
    ];
@endphp

<aside class="sg-sidebar w-[280px] shrink-0 text-white flex flex-col lg:sticky lg:top-0 lg:h-screen overflow-hidden">
    @include('partials.sidebar.decoracion')

    @include('partials.sidebar.marca', ['etiqueta' => 'Portal del alumno'])

    <nav class="sg-sidebar__nav relative flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        <p class="px-3 pb-2 text-[10px] font-bold tracking-[.16em] text-[#8FD3F8]/60 uppercase">Mi cursada</p>
        @foreach ($enlaces as $enlace)
            @include('partials.sidebar.enlace', [
                'href' => route($enlace['ruta']),
                'texto' => $enlace['texto'],
                'icono' => $iconos[$enlace['icono']],
                'activo' => request()->routeIs($enlace['ruta']),
                'badge' => null,
            ])
        @endforeach
    </nav>

    @include('partials.sidebar.usuario', [
        'nombre' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'detalle' => 'Alumno',
        'iniciales' => mb_substr(auth()->user()->nombre, 0, 1) . mb_substr(auth()->user()->apellido, 0, 1),
    ])
</aside>
