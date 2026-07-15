<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar · Sistema Escuela de Enfermería</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        {{-- Panel izquierdo institucional --}}
        <div class="md:w-[38%] bg-[#1E4D8C] text-white px-10 py-12 flex flex-col items-center text-center justify-center relative overflow-hidden">
            <div class="relative z-10 flex flex-col items-center">
                <div class="h-32 w-32 rounded-full bg-white/10 ring-4 ring-[#D4A017] flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D4A017" stroke-width="1.3" class="h-16 w-16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold">Instituto Superior</h2>
                <h2 class="text-2xl font-bold mb-3">San Gregorio</h2>
                <div class="w-24 h-0.5 bg-[#D4A017] mb-3"></div>
                <p class="text-[#D4A017] font-bold mb-8">Carreras para el Bienestar</p>

                <div class="h-16 w-16 rounded-full ring-2 ring-white/70 flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" class="h-8 w-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7.5-4.5-9.75-9A5.25 5.25 0 0 1 12 6.5 5.25 5.25 0 0 1 21.75 12c-2.25 4.5-9.75 9-9.75 9Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h3l1.5-3 3 6 1.5-3h6" />
                    </svg>
                </div>
                <p class="font-semibold text-white/90 max-w-xs">
                    Formamos profesionales comprometidos con el cuidado y la salud de las personas
                </p>
            </div>
        </div>

        {{-- Panel derecho: formulario --}}
        <div class="flex-1 flex items-center justify-center px-6 py-16">
            <div class="w-full max-w-md">
                <h1 class="text-3xl md:text-4xl font-extrabold text-[#1E4D8C] mb-10 leading-tight">
                    SISTEMA ESCUELA DE ENFERMERÍA
                </h1>

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm text-slate-700 mb-2">CORREO ELECTRONICO</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-700 mb-2">CONTRASEÑA</label>
                        <input type="password" name="password" required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>

                    <button type="submit"
                            class="w-full rounded-2xl bg-[#1E4D8C] hover:bg-[#173d70] text-white font-semibold py-4 text-base transition">
                        INICIAR SESION
                    </button>

                    <p class="text-center">
                        <a href="{{ route('password.solicitar') }}" class="text-[#1E4D8C] hover:underline text-sm">¿Olvidaste la contraseña?</a>
                    </p>
                </form>

                <p class="text-xs text-slate-400 text-center mt-10">
                    Alumna de prueba: lucia.fernandez@gmail.com / alumna123<br>
                    Secretaría de prueba: secretaria@sangregorio.edu.ar / secretaria123
                </p>
            </div>
        </div>
    </div>
</body>
</html>
