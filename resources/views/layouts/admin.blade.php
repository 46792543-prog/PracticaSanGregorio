<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.session-guard')
    <title>@yield('titulo', 'Panel de Secretaría') · Instituto Superior San Gregorio</title>
    @include('partials.tema-sistema')
    @include('partials.validacion-inputs')
</head>
<body class="text-slate-800">
    <div class="flex min-h-screen">
        @include('partials.admin-sidebar')
        <div class="sg-velo fixed inset-0 z-50 bg-[#04153A]/50 backdrop-blur-sm" data-sg-menu-cerrar></div>

        <div class="flex-1 flex flex-col min-w-0">
            @include('partials.sistema-topbar', [
                'seccion' => 'Secretaría',
                'titulo' => $__env->yieldContent('titulo', 'Panel de Secretaría'),
                'conCorte' => true,
            ])

            <main class="sg-contenido flex-1 px-4 py-6 sm:px-6 lg:px-9 lg:py-8">
                <div class="max-w-[1400px] mx-auto sg-aparecer">
                    @hasSection('header')
                        @include('partials.sistema-encabezado')
                    @endif

                    @include('partials.sistema-alertas')

                    @yield('contenido')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
