{{--
    Header fijo. Cambia de transparente a sólido al hacer scroll (ver
    landing.js) y resalta automáticamente el link de la sección visible.
    El logo sale de Configuración (Director); si no se cargó ninguno, se
    usa el ícono "+" por defecto.
--}}
<header class="landing-header" role="banner">
    <div class="landing-contenedor landing-header__fila">
        <a href="#inicio" class="landing-logo" aria-label="Ir al inicio">
            <span class="landing-logo__marca" aria-hidden="true">
                @if ($config?->logo_url)
                    <img src="{{ $config->logo_url }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="22" height="22">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                @endif
            </span>
            <span class="landing-logo__texto">
                <strong>INSTITUTO SUPERIOR</strong>
                <span>{{ mb_strtoupper($config?->nombre_institucion ?? 'SAN GREGORIO') }}</span>
            </span>
        </a>

        <nav class="landing-nav" aria-label="Navegación principal">
            <a href="#inicio" class="landing-nav__link activo">Inicio</a>
            <a href="#nosotros" class="landing-nav__link">Quiénes somos</a>
            <a href="#servicios" class="landing-nav__link">Información</a>
            <a href="#carreras" class="landing-nav__link">Carreras</a>
            <a href="#ubicacion" class="landing-nav__link">Ubicación</a>
        </nav>

        <div class="landing-header__acciones">
            <a href="{{ route('login') }}" class="landing-btn landing-btn--primario" style="padding:11px 22px;">
                Iniciar sesión
            </a>
            <button type="button" class="landing-hamburguesa" aria-label="Abrir menú" aria-expanded="false" aria-controls="landing-menu-movil">
                <span class="landing-hamburguesa__icono"></span>
            </button>
        </div>
    </div>
</header>

<nav id="landing-menu-movil" class="landing-menu-movil" aria-label="Navegación móvil">
    <a href="#inicio" class="landing-menu-movil__link">Inicio</a>
    <a href="#nosotros" class="landing-menu-movil__link">Quiénes somos</a>
    <a href="#servicios" class="landing-menu-movil__link">Información</a>
    <a href="#carreras" class="landing-menu-movil__link">Carreras</a>
    <a href="#ubicacion" class="landing-menu-movil__link">Ubicación</a>
</nav>
