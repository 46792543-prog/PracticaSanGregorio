@extends('auth.layout')

@section('titulo', 'Verificá tu identidad')
@section('panel-etiqueta', 'Recuperar acceso · Paso 2')
@section('panel-titulo')
    Revisá tu correo, <em>ya casi estás</em>
@endsection
@section('panel-texto', 'Ingresá el código que te enviamos para confirmar que sos vos. Por seguridad, el código vence a los pocos minutos.')

@section('contenido')
    <a href="{{ route('password.solicitar') }}" class="acc-volver">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Volver al paso anterior
    </a>

    @include('auth.partials.recuperar-sidebar', ['paso' => 2])

    <div class="acc-cabecera">
        <span class="acc-cabecera__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z" /></svg>
        </span>
        <h1>Verificá tu identidad</h1>
        <p>
            Ingresá el código de 6 dígitos que enviamos a tu email institucional.
            <small>Revisá también tu carpeta de spam.</small>
        </p>
    </div>

    <div class="acc-aviso acc-aviso--info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
        <span>Código enviado a <strong>{{ $email }}</strong></span>
    </div>

    @if ($codigoDemo)
        <div class="acc-aviso acc-aviso--demo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            <span>Modo demo (no hay servidor de correo configurado): tu código es <strong>{{ $codigoDemo }}</strong></span>
        </div>
    @endif

    @if ($errors->any())
        <div class="acc-aviso acc-aviso--error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.verificar') }}" class="acc-form">
        @csrf
        <div class="acc-campo">
            <label for="rec-codigo">Código de 6 dígitos</label>
            <div class="acc-codigo">
                <div class="acc-codigo__casillas" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
                <input id="rec-codigo" type="text" name="codigo" inputmode="numeric" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="000000">
            </div>
        </div>

        <button type="submit" class="acc-btn acc-btn--primario">
            Verificar código
            <svg class="acc-flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            <span class="acc-spinner" aria-hidden="true"></span>
        </button>
        <a href="{{ route('password.solicitar') }}" class="acc-btn acc-btn--secundario">Volver al paso anterior</a>
    </form>

    <p class="acc-ayuda">¿No recibiste el código? <a href="{{ route('password.solicitar') }}">Reenviar código</a></p>
@endsection
