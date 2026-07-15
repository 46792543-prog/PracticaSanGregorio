<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Portal del Alumno') · Instituto Superior San Gregorio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-[#1b3a63] text-white px-8 py-5 shadow">
                <h1 class="text-xl font-bold">@yield('titulo')</h1>
                <p class="text-sm text-blue-200 mt-0.5">@yield('subtitulo')</p>
            </header>

            <main class="flex-1 p-6 md:p-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>
</body>
</html>
