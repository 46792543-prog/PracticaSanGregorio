<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creá tu nueva contraseña · Instituto Superior San Gregorio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        @include('auth.partials.recuperar-sidebar', ['paso' => 3])

        <div class="flex-1 flex items-center justify-center px-6 py-16">
            <div class="w-full max-w-md">
                <div class="flex items-center gap-3 mb-8 text-sm font-bold">
                    <span class="flex items-center gap-1 text-green-600"><span class="h-6 w-6 rounded-full bg-green-500 text-white grid place-items-center text-xs">✓</span> Email</span>
                    <span class="flex-1 h-px bg-green-500"></span>
                    <span class="flex items-center gap-1 text-green-600"><span class="h-6 w-6 rounded-full bg-green-500 text-white grid place-items-center text-xs">✓</span> Código</span>
                    <span class="flex-1 h-px bg-green-500"></span>
                    <span class="flex items-center gap-1 text-[#1E4D8C]"><span class="h-6 w-6 rounded-full bg-[#1E4D8C] text-white grid place-items-center text-xs">3</span> Nueva clave</span>
                </div>

                <h1 class="text-2xl font-bold text-slate-800 mb-3">Creá tu nueva contraseña</h1>
                <p class="text-slate-500 mb-6">Elegí una contraseña segura. No podrás reutilizar tu contraseña anterior.</p>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.nueva') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">CONTRASEÑA NUEVA</label>
                        <input type="password" name="password" minlength="8" required autofocus
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">CONFIRMAR CONTRASEÑA</label>
                        <input type="password" name="password_confirmation" minlength="8" required placeholder="Repetí tu nueva contraseña"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>

                    <ul class="text-xs text-slate-500 bg-slate-50 rounded-xl px-4 py-3 space-y-1">
                        <li>• Mínimo 8 caracteres</li>
                        <li>• Recomendado: combiná mayúsculas, números y símbolos</li>
                    </ul>

                    <button type="submit" class="w-full rounded-xl bg-[#1E4D8C] hover:bg-[#173d70] text-white font-semibold py-3.5 text-sm transition">
                        💾 Guardar nueva contraseña
                    </button>

                    <a href="{{ route('password.verificar') }}" class="block text-center rounded-xl border border-[#1E4D8C] text-[#1E4D8C] font-semibold py-3.5 text-sm">
                        ← Volver al paso anterior
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
