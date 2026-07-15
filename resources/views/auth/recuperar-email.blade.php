<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña · Instituto Superior San Gregorio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        @include('auth.partials.recuperar-sidebar', ['paso' => 1])

        <div class="flex-1 flex items-center justify-center px-6 py-16">
            <div class="w-full max-w-md">
                <div class="flex items-center gap-3 mb-8 text-sm font-bold">
                    <span class="flex items-center gap-1 text-[#1E4D8C]"><span class="h-6 w-6 rounded-full bg-[#1E4D8C] text-white grid place-items-center text-xs">1</span> Email</span>
                    <span class="flex-1 h-px bg-slate-200"></span>
                    <span class="flex items-center gap-1 text-slate-300"><span class="h-6 w-6 rounded-full border border-slate-300 grid place-items-center text-xs">2</span> Código</span>
                    <span class="flex-1 h-px bg-slate-200"></span>
                    <span class="flex items-center gap-1 text-slate-300"><span class="h-6 w-6 rounded-full border border-slate-300 grid place-items-center text-xs">3</span> Nueva clave</span>
                </div>

                <h1 class="text-2xl font-bold text-slate-800 mb-3">¿Olvidaste tu contraseña?</h1>
                <p class="text-slate-500 mb-6">
                    Ingresá el email institucional con el que te registraste y te enviaremos un código de 6 dígitos para recuperar tu acceso.
                </p>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.solicitar') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">EMAIL INSTITUCIONAL</label>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span>✉️</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="usuario@sangregorio.edu.ar"
                                   class="flex-1 bg-transparent text-sm focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-[#1E4D8C] hover:bg-[#173d70] text-white font-semibold py-3.5 text-sm transition">
                        ✉️ Enviar código de verificación
                    </button>

                    <a href="{{ route('login') }}" class="block text-center rounded-xl border border-[#1E4D8C] text-[#1E4D8C] font-semibold py-3.5 text-sm">
                        Cancelar
                    </a>
                </form>

                <p class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-[#1E4D8C] text-sm font-semibold">← Volver al inicio de sesión</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
