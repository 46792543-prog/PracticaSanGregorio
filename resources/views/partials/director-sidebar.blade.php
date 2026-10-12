@php
    $cuotasPendientesBadge = \App\Models\CuotaAlumno::where('pagado', false)->count();
    $alertasBadge = \App\Support\CentralAlertas::cantidad();
    $configInstitucion = \App\Models\ConfiguracionInstitucion::first();

    $enlaces = [
        ['ruta' => 'director.panel.index', 'texto' => 'Panel principal', 'icono' => 'home'],
        ['ruta' => 'director.alertas.index', 'texto' => 'Central de Alertas', 'icono' => 'bell', 'badge' => $alertasBadge],
        ['ruta' => 'director.cuotas.index', 'texto' => 'Registrar Cuotas', 'icono' => 'card', 'badge' => $cuotasPendientesBadge],
        ['ruta' => 'director.pagos.index', 'texto' => 'Estado de Pagos', 'icono' => 'chart'],
        ['ruta' => 'director.caja.index', 'texto' => 'Libro de caja', 'icono' => 'book'],
        ['ruta' => 'director.reportes.ingresos.index', 'texto' => 'Reporte de Ingresos', 'icono' => 'report'],
        ['ruta' => 'director.auditoria.index', 'texto' => 'Auditoría', 'icono' => 'audit'],
        ['ruta' => 'director.configuracion.index', 'texto' => 'Configuración', 'icono' => 'settings'],
    ];

    $iconos = [
        'home' => 'M11.47 3.84a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06l-.22-.22V19.5a1.5 1.5 0 0 1-1.5 1.5h-3a.75.75 0 0 1-.75-.75V15a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v5.25a.75.75 0 0 1-.75.75h-3a1.5 1.5 0 0 1-1.5-1.5v-7.32l-.22.22a.75.75 0 1 1-1.06-1.06l7.5-7.5Z',
        'card' => 'M2.25 8.25h19.5M2.25 8.25v9a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'book' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'grid' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        'settings' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.752.43.992l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.752-.43-.992l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'audit' => 'M9 12h3.75M9 15h3.75M9 18h3.75M3.75 4.5A2.25 2.25 0 0 1 6 2.25h6.879a1.5 1.5 0 0 1 1.06.44l4.622 4.62a1.5 1.5 0 0 1 .439 1.061V19.5a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V4.5Zm14.15 15.5-1.9-1.9m1.15-2.6a3.25 3.25 0 1 1-6.5 0 3.25 3.25 0 0 1 6.5 0Z',
        'report' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
        'bell' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
        'logout' => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H8.25m9.75 0-3-3m3 3-3 3',
        'chevron' => 'm8.25 4.5 7.5 7.5-7.5 7.5',
    ];
@endphp

<aside class="sg-sidebar w-[280px] shrink-0 text-white flex flex-col lg:sticky lg:top-0 lg:h-screen overflow-hidden">
    @include('partials.sidebar.decoracion')

    @include('partials.sidebar.marca', ['etiqueta' => 'Dirección'])

    <nav class="sg-sidebar__nav relative flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        <p class="px-3 pb-2 text-[10px] font-bold tracking-[.16em] text-[#8FD3F8]/60 uppercase">Menú principal</p>
        @foreach ($enlaces as $enlace)
            @include('partials.sidebar.enlace', [
                'href' => route($enlace['ruta']),
                'texto' => $enlace['texto'],
                'icono' => $iconos[$enlace['icono']],
                'activo' => request()->routeIs($enlace['ruta'] . '*'),
                'badge' => $enlace['badge'] ?? null,
            ])
        @endforeach

        <p class="px-3 pt-6 pb-2 text-[10px] font-bold tracking-[.16em] text-[#8FD3F8]/60 uppercase">Acceso administrativo</p>
        @include('partials.sidebar.enlace', [
            'href' => route('admin.panel.index'),
            'texto' => 'Ir al Panel Administrativo',
            'icono' => $iconos['grid'],
            'activo' => false,
            'badge' => null,
        ])
    </nav>

    @include('partials.sidebar.usuario', [
        'nombre' => 'Dra. ' . auth()->user()->apellido,
        'detalle' => 'Directora General',
        'iniciales' => mb_substr(auth()->user()->nombre, 0, 1) . mb_substr(auth()->user()->apellido, 0, 1),
    ])
</aside>
