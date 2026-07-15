<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel de Dirección') · Instituto Superior San Gregorio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#f4f6fb] text-slate-800">
    <div class="flex min-h-screen">
        @include('partials.director-sidebar')

        <div class="flex-1 flex flex-col min-w-0">
            @hasSection('header')
                <header class="relative bg-gradient-to-r from-[#1E4D8C] to-[#2a5ba3] text-white px-8 py-6 shadow-sm overflow-hidden">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/5"></div>
                    <div class="absolute right-16 bottom-0 h-16 w-16 rounded-full bg-[#D4A017]/10"></div>
                    <div class="relative">
                        <h1 class="text-xl font-bold tracking-tight">@yield('titulo')</h1>
                        <p class="text-sm text-blue-100/80 mt-0.5">@yield('subtitulo')</p>
                    </div>
                </header>
            @endif

            <main class="flex-1 p-6 md:p-9">
                <div class="max-w-[1400px] mx-auto">
                    @if (session('status'))
                        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 px-5 py-3.5 text-sm shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 shrink-0 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span class="pt-0.5">{{ session('status') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-red-50 border border-red-100 text-red-700 px-5 py-3.5 text-sm shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 shrink-0 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <ul class="list-disc list-inside space-y-0.5 pt-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('contenido')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
