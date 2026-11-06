{{-- Asistente "Goyo": guía local paso a paso para Secretaría y Dirección. El catálogo está en App\Support\GuiaAsistente. --}}
@php
    $guiasAsistente = \App\Support\GuiaAsistente::paraUsuario(auth()->user());
@endphp

@if ($guiasAsistente)
    <link rel="stylesheet" href="{{ asset('css/guia.css') }}?v={{ filemtime(public_path('css/guia.css')) }}">

    <svg width="0" height="0" class="sg-guia-simbolos" aria-hidden="true">
        <symbol id="sg-goyo" viewBox="0 0 64 64">
            <circle cx="32" cy="37" r="22" fill="#fff"/>
            <circle cx="24" cy="36" r="4.5" fill="#0B3D91"/>
            <circle cx="40" cy="36" r="4.5" fill="#0B3D91"/>
            <circle cx="25.5" cy="34.5" r="1.5" fill="#fff"/>
            <circle cx="41.5" cy="34.5" r="1.5" fill="#fff"/>
            <path d="M25 46c4 4 10 4 14 0" fill="none" stroke="#0B3D91" stroke-width="3" stroke-linecap="round"/>
            <path d="M32 6 6 17l26 11 26-11L32 6Z" fill="#FFC107"/>
            <path d="M54 19v12" stroke="#FFC107" stroke-width="3" stroke-linecap="round"/>
            <circle cx="54" cy="33" r="3" fill="#FFC107"/>
        </symbol>
    </svg>

    <button type="button" id="sg-guia-boton" class="sg-guia-boton" aria-label="Abrir la guía del sistema" aria-expanded="false" aria-controls="sg-guia-panel">
        <svg class="sg-guia-boton__goyo" aria-hidden="true"><use href="#sg-goyo"/></svg>
        <span class="sg-guia-boton__pregunta" aria-hidden="true">?</span>
        <span class="sg-guia-boton__texto">¿Te ayudo?</span>
    </button>

    <section id="sg-guia-panel" class="sg-guia-panel" role="dialog" aria-labelledby="sg-guia-titulo" hidden>
        <header class="sg-guia-panel__cabecera">
            <span class="sg-guia-panel__avatar"><svg aria-hidden="true"><use href="#sg-goyo"/></svg></span>
            <div class="sg-guia-panel__nombre">
                <h2 id="sg-guia-titulo">Goyo</h2>
                <p>Tu guía del sistema</p>
            </div>
            <button type="button" id="sg-guia-reiniciar" class="sg-guia-panel__accion" title="Empezar de nuevo" aria-label="Empezar de nuevo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button type="button" id="sg-guia-cerrar" class="sg-guia-panel__accion" title="Cerrar" aria-label="Cerrar la guía">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <div id="sg-guia-chat" class="sg-guia-panel__chat" aria-live="polite"></div>

        <form id="sg-guia-form" class="sg-guia-panel__form" autocomplete="off">
            <input type="text" id="sg-guia-input" maxlength="100" placeholder="Ej: cargar horario docente" aria-label="¿Qué querés hacer?">
            <button type="submit" aria-label="Enviar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>
    </section>

    <script type="application/json" id="sg-guia-datos">@json($guiasAsistente)</script>
    <script src="{{ asset('js/guia.js') }}?v={{ filemtime(public_path('js/guia.js')) }}" defer></script>
@endif
