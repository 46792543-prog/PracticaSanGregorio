@extends('auth.layout')

@section('titulo', 'Recuperar contraseña')
@section('panel-etiqueta', 'Recuperar acceso')
@section('panel-titulo')
    Recuperá tu acceso en <em>tres simples pasos</em>
@endsection
@section('panel-texto', 'Te enviamos un código a tu email institucional, lo verificás y creás una contraseña nueva. Rápido y seguro.')

@section('contenido')
    <a href="{{ route('login') }}" class="acc-volver">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Volver al inicio de sesión
    </a>

    @include('auth.partials.recuperar-sidebar', ['paso' => 1])

    <div class="acc-cabecera">
        <span class="acc-cabecera__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
        </span>
        <h1>¿Olvidaste tu contraseña?</h1>
        <p>Alumnos y personal: ingresá el correo con el que entrás al sistema y te enviaremos un código de 6 dígitos.</p>
    </div>

    @if ($errors->any())
        <div class="acc-aviso acc-aviso--error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.solicitar') }}" class="acc-form">
        @csrf
        <div class="acc-campo">
            <label for="rec-email">Tu correo</label>
            <div class="acc-input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <input id="rec-email" type="email" maxlength="100" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="el correo con el que ingresás">
            </div>
        </div>

        <button type="submit" class="acc-btn acc-btn--primario">
            Enviar código de verificación
            <svg class="acc-flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
            <span class="acc-spinner" aria-hidden="true"></span>
        </button>
        <a href="{{ route('login') }}" class="acc-btn acc-btn--secundario acc-btn--siempre">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H21" /></svg>
            Volver al inicio de sesión
        </a>
    </form>
@endsection
