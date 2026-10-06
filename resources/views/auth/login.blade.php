<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body landing-login">
    @include('auth.partials.recuperar-fondo')

    <div class="landing-login__tarjeta">
        <a href="{{ route('welcome') }}" class="landing-login__volver">← Volver al inicio</a>

        <div class="landing-login__cabecera">
            <span class="landing-login__icono">
                @if ($config?->logo_url)
                    <img src="{{ $config->logo_url }}" alt="">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                        <path stroke-linecap="round" d="M12 9v6M9 12h6" />
                    </svg>
                @endif
            </span>
            <span class="landing-etiqueta" style="margin-bottom:10px;">
                <span class="landing-etiqueta__punto"></span> Acceso institucional
            </span>
            <h1>Iniciá sesión</h1>
            <p>Con tu usuario del instituto</p>
        </div>

        @if ($errors->any())
            <div class="landing-login__error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="landing-form">
            @csrf
            <div class="landing-form__campo">
                <label for="login-email">Correo electrónico</label>
                <div class="landing-input-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372a1.125 1.125 0 0 0-.852-1.09l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    <input id="login-email" type="email" maxlength="100" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@institucion.edu.ar">
                </div>
            </div>
            <div class="landing-form__campo">
                <label for="login-password">Contraseña</label>
                <div class="landing-input-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    <input id="login-password" type="password" maxlength="100" name="password" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="landing-btn landing-btn--primario landing-btn--bloque">Ingresar →</button>

            <p style="text-align:center;margin-top:4px;">
                <a href="{{ route('password.solicitar') }}" style="color:var(--landing-azul);font-weight:700;font-size:.88rem;">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
    </div>

    <script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
