@extends('auth.layout')

@section('titulo', 'Iniciar sesión')
@section('panel-etiqueta', 'Campus virtual')
@section('panel-titulo')
    Tu lugar en el instituto, <em>siempre a mano</em>
@endsection
@section('panel-texto', 'Alumnos, docentes y personal ingresan desde acá: inscripciones, mesas de examen, estado académico, cuotas y gestión institucional en un solo lugar.')

@section('contenido')
    <a href="{{ route('welcome') }}" class="acc-volver">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Volver al inicio
    </a>

    <div class="acc-cabecera">
        <span class="acc-cabecera__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
        </span>
        <h1>¡Hola! Ingresá a tu cuenta</h1>
        <p>Usá el correo y la contraseña que te dio el instituto.</p>
    </div>

    @if ($errors->any())
        <div class="acc-aviso acc-aviso--error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="acc-form">
        @csrf
        <div class="acc-campo">
            <label for="login-email">Correo electrónico</label>
            <div class="acc-input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <input id="login-email" type="email" maxlength="100" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu correo del instituto">
            </div>
        </div>

        <div class="acc-campo">
            <label for="login-password">
                Contraseña
                <a href="{{ route('password.solicitar') }}">¿La olvidaste?</a>
            </label>
            <div class="acc-input acc-input--con-boton">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                <input id="login-password" type="password" maxlength="100" name="password" required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="acc-ojo" data-acc-ojo="login-password" aria-label="Mostrar contraseña"></button>
            </div>
            <p class="acc-mayus" data-acc-mayus="login-password">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                Bloq Mayús está activado
            </p>
        </div>

        <button type="submit" class="acc-btn acc-btn--primario">
            Ingresar
            <svg class="acc-flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            <span class="acc-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <p class="acc-ayuda">¿Sos alumno/a y no tenés usuario? Pedilo en Secretaría.</p>

    <p class="acc-seguro">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
        Conexión segura · Solo para la comunidad institucional
    </p>
@endsection
