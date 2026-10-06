<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña · {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body landing-login">
    @include('auth.partials.recuperar-fondo')

    <div class="landing-login__tarjeta landing-login__tarjeta--ancha">
        <a href="{{ route('login') }}" class="landing-login__volver">← Volver al inicio de sesión</a>

        @include('auth.partials.recuperar-sidebar', ['paso' => 1])

        <div class="landing-login__cabecera" style="text-align:left;margin-bottom:18px;">
            <h1 style="font-size:1.3rem;">¿Olvidaste tu contraseña?</h1>
            <p style="margin-top:8px;line-height:1.55;">
                Ingresá el email institucional con el que te registraste y te enviaremos un código de 6 dígitos para recuperar tu acceso.
            </p>
        </div>

        @if ($errors->any())
            <div class="landing-login__error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.solicitar') }}" class="landing-form">
            @csrf
            <div class="landing-form__campo">
                <label for="rec-email">Email institucional</label>
                <div class="landing-input-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    <input id="rec-email" type="email" maxlength="100" name="email" value="{{ old('email') }}" required autofocus placeholder="usuario@sangregorio.edu.ar">
                </div>
            </div>

            <button type="submit" class="landing-btn landing-btn--primario landing-btn--bloque">Enviar código de verificación →</button>
            <a href="{{ route('login') }}" class="landing-btn landing-btn--outline-azul landing-btn--bloque">Cancelar</a>
        </form>
    </div>

    <script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
