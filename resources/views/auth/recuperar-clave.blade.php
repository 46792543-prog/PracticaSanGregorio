<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creá tu nueva contraseña · {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body landing-login">
    @include('auth.partials.recuperar-fondo')

    <div class="landing-login__tarjeta landing-login__tarjeta--ancha">
        <a href="{{ route('password.verificar') }}" class="landing-login__volver">← Volver al paso anterior</a>

        @include('auth.partials.recuperar-sidebar', ['paso' => 3])

        <div class="landing-login__cabecera" style="text-align:left;margin-bottom:18px;">
            <h1 style="font-size:1.3rem;">Creá tu nueva contraseña</h1>
            <p style="margin-top:8px;line-height:1.55;">Elegí una contraseña segura. No podrás reutilizar tu contraseña anterior.</p>
        </div>

        @if ($errors->any())
            <div class="landing-login__error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.nueva') }}" class="landing-form">
            @csrf
            <div class="landing-form__campo">
                <label for="rec-password">Contraseña nueva</label>
                <div class="landing-input-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    <input id="rec-password" type="password" name="password" minlength="8" maxlength="100" required autofocus>
                </div>
            </div>
            <div class="landing-form__campo">
                <label for="rec-password-confirm">Confirmar contraseña</label>
                <div class="landing-input-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    <input id="rec-password-confirm" type="password" name="password_confirmation" minlength="8" maxlength="100" required placeholder="Repetí tu nueva contraseña">
                </div>
            </div>

            <ul style="font-size:.78rem;color:var(--landing-texto-suave);background:var(--landing-celeste-claro);border-radius:12px;padding:12px 16px;display:grid;gap:4px;list-style:none;">
                <li>• Mínimo 8 caracteres</li>
                <li>• Recomendado: combiná mayúsculas, números y símbolos</li>
            </ul>

            <button type="submit" class="landing-btn landing-btn--primario landing-btn--bloque">Guardar nueva contraseña →</button>
            <a href="{{ route('password.verificar') }}" class="landing-btn landing-btn--outline-azul landing-btn--bloque">← Volver al paso anterior</a>
        </form>
    </div>

    <script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
