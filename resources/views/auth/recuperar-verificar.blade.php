<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificá tu identidad · {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body landing-login">
    @include('auth.partials.recuperar-fondo')

    <div class="landing-login__tarjeta landing-login__tarjeta--ancha">
        <a href="{{ route('password.solicitar') }}" class="landing-login__volver">← Volver al paso anterior</a>

        @include('auth.partials.recuperar-sidebar', ['paso' => 2])

        <div class="landing-login__cabecera" style="text-align:left;margin-bottom:18px;">
            <h1 style="font-size:1.3rem;">Verificá tu identidad</h1>
            <p style="margin-top:8px;line-height:1.55;">
                Ingresá el código de 6 dígitos que enviamos a tu email institucional.
                <span style="display:block;color:#94a3b8;font-size:.78rem;margin-top:4px;">Revisá también tu carpeta de spam.</span>
            </p>
        </div>

        <div class="landing-caja-info">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            Código enviado a <strong>{{ $email }}</strong>
        </div>

        @if ($codigoDemo)
            <div class="landing-caja-demo">
                Modo demo (no hay servidor de correo configurado): tu código es <strong>{{ $codigoDemo }}</strong>
            </div>
        @endif

        @if ($errors->any())
            <div class="landing-login__error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.verificar') }}" class="landing-form">
            @csrf
            <div class="landing-form__campo">
                <label for="rec-codigo">Código de 6 dígitos</label>
                <input id="rec-codigo" type="text" name="codigo" inputmode="numeric" maxlength="6" required autofocus placeholder="000000"
                       style="text-align:center;letter-spacing:.5em;font-size:1.4rem;font-weight:800;font-family:var(--landing-fuente-titulo);padding-left:16px;">
            </div>

            <button type="submit" class="landing-btn landing-btn--primario landing-btn--bloque">Verificar código →</button>
            <a href="{{ route('password.solicitar') }}" class="landing-btn landing-btn--outline-azul landing-btn--bloque">← Volver al paso anterior</a>
        </form>

        <p style="text-align:center;margin-top:18px;font-size:.85rem;color:var(--landing-texto-suave);">
            ¿No recibiste el código?
            <a href="{{ route('password.solicitar') }}" style="color:var(--landing-azul);font-weight:700;">Reenviar código</a>
        </p>
    </div>

    <script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
