<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.session-guard')
    <title>@yield('titulo', 'Portal del Alumno') · Instituto Superior San Gregorio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @include('partials.validacion-inputs')
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <div id="sidebar-overlay-alumno" onclick="cerrarSidebarAlumno()" class="hidden fixed inset-0 bg-black/50 z-30 md:hidden"></div>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-[#1b3a63] text-white px-4 md:px-8 py-4 md:py-5 shadow flex items-center gap-3">
                <button type="button" onclick="toggleSidebarAlumno()" aria-label="Abrir menú"
                        class="md:hidden shrink-0 -ml-1 p-2 rounded-lg hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-lg md:text-xl font-bold truncate">@yield('titulo')</h1>
                    <p class="text-xs md:text-sm text-blue-200 mt-0.5 truncate">@yield('subtitulo')</p>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 md:p-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebarAlumno() {
            document.getElementById('sidebar-alumno').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay-alumno').classList.toggle('hidden');
        }
        function cerrarSidebarAlumno() {
            document.getElementById('sidebar-alumno').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay-alumno').classList.add('hidden');
        }
    </script>
</body>
</html>
