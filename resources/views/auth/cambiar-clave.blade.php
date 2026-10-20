@extends('auth.layout')

@section('titulo', 'Cambiá tu clave inicial')
@section('panel-etiqueta', 'Primer ingreso')
@section('panel-titulo')
    Bienvenido/a: creá tu <em>contraseña personal</em>
@endsection
@section('panel-texto', 'La clave que te llegó por mail es temporal. Reemplazala por una que solo vos conozcas antes de empezar a usar el sistema.')

@section('contenido')
    <div class="acc-cabecera">
        <span class="acc-cabecera__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
        </span>
        <h1>Cambiá tu clave inicial</h1>
        <p>Ingresá la clave que te llegó por mail y elegí una contraseña nueva.</p>
    </div>

    @if ($errors->any())
        <div class="acc-aviso acc-aviso--error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.cambiar') }}" class="acc-form">
        @csrf
        @method('PUT')
        <div class="acc-campo">
            <label for="cam-password-actual">Clave actual (la que te llegó por mail)</label>
            <div class="acc-input acc-input--con-boton">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
                <input id="cam-password-actual" type="password" name="password_actual" maxlength="100" required autofocus autocomplete="current-password" placeholder="ISG-0000-XXXXXXXX">
                <button type="button" class="acc-ojo" data-acc-ojo="cam-password-actual" aria-label="Mostrar contraseña"></button>
            </div>
        </div>

        <div class="acc-campo">
            <label for="rec-password">Contraseña nueva</label>
            <div class="acc-input acc-input--con-boton">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                <input id="rec-password" type="password" name="password" minlength="8" maxlength="100" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" data-acc-fuerza="acc-medidor">
                <button type="button" class="acc-ojo" data-acc-ojo="rec-password" aria-label="Mostrar contraseña"></button>
            </div>
            <p class="acc-mayus" data-acc-mayus="rec-password">Bloq Mayús está activado</p>
            <div class="acc-fuerza" id="acc-medidor" data-nivel="0">
                <div class="acc-fuerza__barras"><span></span><span></span><span></span><span></span></div>
                <p class="acc-fuerza__texto">Escribí una contraseña</p>
            </div>
        </div>

        <div class="acc-campo">
            <label for="rec-password-confirm">Confirmar contraseña</label>
            <div class="acc-input acc-input--con-boton">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                <input id="rec-password-confirm" type="password" name="password_confirmation" minlength="8" maxlength="100" required autocomplete="new-password" placeholder="Repetí tu nueva contraseña" data-acc-confirma>
                <button type="button" class="acc-ojo" data-acc-ojo="rec-password-confirm" aria-label="Mostrar contraseña"></button>
            </div>
            <p class="acc-mayus" id="acc-coincide" style="display:none;"><span></span></p>
        </div>

        <ul class="acc-requisitos">
            <li data-acc-regla="largo">Mínimo 8 caracteres</li>
            <li data-acc-regla="letra">Al menos una letra</li>
            <li data-acc-regla="numero">Al menos un número</li>
            <li data-acc-regla="mayus">Mayúsculas y minúsculas (recomendado)</li>
            <li data-acc-regla="simbolo">Al menos un símbolo (recomendado)</li>
        </ul>

        <button type="submit" class="acc-btn acc-btn--primario">
            Guardar nueva contraseña
            <svg class="acc-flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            <span class="acc-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="acc-form">
        @csrf
        <button type="submit" class="acc-btn acc-btn--secundario">Cerrar sesión</button>
    </form>
@endsection
