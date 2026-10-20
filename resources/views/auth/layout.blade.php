{{--
    Layout compartido del acceso: login y flujo de recuperar contraseña.
    Secciones: titulo, panel-etiqueta, panel-titulo, panel-texto, contenido.
--}}
@php $nombreInstitucion = $config?->nombre_institucion ?? 'Instituto Superior San Gregorio'; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') · {{ $nombreInstitucion }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
</head>
<body class="acc">
    <main class="acc-grid">
        {{-- Panel institucional --}}
        <section class="acc-panel" aria-hidden="false">
            <div class="acc-panel__fondo" aria-hidden="true">
                <span class="acc-panel__luz acc-panel__luz--1"></span>
                <span class="acc-panel__luz acc-panel__luz--2"></span>
                <span class="acc-panel__anillo acc-panel__anillo--1"></span>
                <span class="acc-panel__anillo acc-panel__anillo--2"></span>
                <span class="acc-panel__puntos"></span>
                @foreach ([
                    ['M9 3.75h6v5.25h5.25v6H15v5.25H9V15H3.75V9H9V3.75Z', 'top:12%;left:9%', 34, 0],
                    ['M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z', 'top:24%;right:12%', 30, -3],
                    ['M6 3.5v5a4 4 0 0 0 8 0v-5M10 12.5v2.5a4.5 4.5 0 0 0 9 0v-1.5M19 13.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM5 3.5h2M13 3.5h2', 'bottom:26%;left:7%', 40, -6],
                    ['M10.5 20.25 3.75 13.5a4.773 4.773 0 0 1 6.75-6.75l6.75 6.75a4.773 4.773 0 0 1-6.75 6.75ZM7.125 10.125l6.75 6.75', 'bottom:14%;right:16%', 30, -9],
                    ['M8 3c0 5 8 5 8 9s-8 4-8 9M16 3c0 5-8 5-8 9s8 4 8 9M9 6.5h6M9.5 17.5h5M10.5 9.5h3M10.5 14.5h3', 'top:52%;right:6%', 32, -4],
                    ['M12 3.25s6 6.4 6 10.75a6 6 0 0 1-12 0c0-4.35 6-10.75 6-10.75Z', 'top:6%;right:36%', 22, -11],
                ] as [$ruta, $pos, $tam, $retraso])
                    <svg class="acc-panel__motivo" style="{{ $pos }};width:{{ $tam }}px;height:{{ $tam }}px;animation-delay:{{ $retraso }}s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $ruta }}" /></svg>
                @endforeach
            </div>

            <div class="acc-panel__contenido">
                <a href="{{ route('welcome') }}" class="acc-marca">
                    <span class="acc-marca__logo">
                        @if ($config?->logo_url)
                            <img src="{{ $config->logo_url }}" alt="">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" /><path stroke-linecap="round" d="M12 9v6M9 12h6" /></svg>
                        @endif
                    </span>
                    <span class="acc-marca__texto">
                        <strong>{{ $nombreInstitucion }}</strong>
                        <span>Educación superior en salud</span>
                    </span>
                </a>

                <div class="acc-panel__mensaje">
                    <span class="acc-panel__etiqueta"><span></span>@yield('panel-etiqueta', 'Plataforma institucional')</span>
                    <h2>@yield('panel-titulo')</h2>
                    <p>@yield('panel-texto')</p>
                </div>

                <svg class="acc-ecg" viewBox="0 0 600 70" preserveAspectRatio="none" aria-hidden="true">
                    <path class="acc-ecg__base" d="M0 35 H150 L170 35 L182 14 L198 58 L214 6 L230 50 L242 35 H330 L344 24 L356 35 H600" />
                    <path class="acc-ecg__pulso" d="M0 35 H150 L170 35 L182 14 L198 58 L214 6 L230 50 L242 35 H330 L344 24 L356 35 H600" />
                </svg>

                <div class="acc-tarjetas" aria-hidden="true">
                    <div class="acc-tarjeta acc-tarjeta--1">
                        <span class="acc-tarjeta__icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg></span>
                        <span><strong>Alumnos</strong><small>Tu cursada, mesas de examen y cuotas</small></span>
                    </div>
                    <div class="acc-tarjeta acc-tarjeta--2">
                        <span class="acc-tarjeta__icono acc-tarjeta__icono--amarillo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" /></svg></span>
                        <span><strong>Secretaría y Dirección</strong><small>Gestión académica, actas y caja</small></span>
                    </div>
                    <div class="acc-tarjeta acc-tarjeta--3">
                        <span class="acc-tarjeta__icono acc-tarjeta__icono--verde"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></span>
                        <span><strong>Acceso seguro</strong><small>Tus datos protegidos</small></span>
                    </div>
                </div>

            </div>
        </section>

        {{-- Formulario --}}
        <section class="acc-lado">
            <div class="acc-lado__fondo" aria-hidden="true">
                <span class="acc-lado__mancha acc-lado__mancha--1"></span>
                <span class="acc-lado__mancha acc-lado__mancha--2"></span>
                <span class="acc-lado__mancha acc-lado__mancha--3"></span>
                <span class="acc-lado__mancha acc-lado__mancha--4"></span>
                <span class="acc-lado__forma acc-lado__forma--anillo"></span>
                <span class="acc-lado__forma acc-lado__forma--cruz"></span>
                <span class="acc-lado__forma acc-lado__forma--punto"></span>
                <span class="acc-lado__forma acc-lado__forma--anillo2"></span>
            </div>
            <div class="acc-form-caja">
                @yield('contenido')
            </div>
        </section>
    </main>

    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}" defer></script>
</body>
</html>
