{{--
    Hero principal. El fondo es un carrusel de fotos reales de la
    institución (configurable desde Configuración > Imágenes de portada);
    si todavía no se cargó ninguna, se usa un degradé de respaldo para que
    la sección nunca se vea rota. La línea de pulso (ECG) y los íconos
    flotantes van siempre por delante de las fotos.
--}}
<section id="inicio" data-landing-seccion class="landing-hero">
    @if ($imagenesHero->isNotEmpty())
        <div class="landing-hero__fondo" data-landing-hero-carrusel aria-hidden="true">
            @foreach ($imagenesHero as $i => $imagen)
                <img src="{{ $imagen->url }}" alt="" class="landing-hero__fondo-img {{ $i === 0 ? 'activa' : '' }}">
            @endforeach
        </div>
    @endif
    <div class="landing-hero__overlay" aria-hidden="true"></div>

    <div class="landing-hero__decoracion" aria-hidden="true">
        <span class="landing-burbuja" style="width:260px;height:260px;background:var(--landing-celeste);top:-60px;right:-40px;animation-delay:-2s;"></span>
        <span class="landing-burbuja" style="width:200px;height:200px;background:var(--landing-amarillo);bottom:40px;left:-60px;animation-delay:-7s;"></span>

        {{-- Estetoscopio flotando --}}
        <svg class="landing-flotar-icono" style="top:20%;left:7%;--landing-rot:-6deg;width:46px;height:46px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3v6a3.5 3.5 0 0 0 7 0V3M8 3v2.2M4.5 3v2.2M11.5 11.5V13a5.5 5.5 0 0 0 11 0v-1.2" />
            <circle cx="20" cy="9" r="2.3" />
        </svg>

        {{-- Librito flotando --}}
        <svg class="landing-flotar-icono landing-flotar-icono--amarillo" style="top:60%;left:13%;--landing-rot:8deg;width:34px;height:34px;animation-delay:-3s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.04A9 9 0 0 0 6 3.75c-1.05 0-2.06.18-3 .51v14.25A9 9 0 0 1 6 18c2.3 0 4.4.87 6 2.29m0-14.25a9 9 0 0 1 6-2.29c1.05 0 2.06.18 3 .51v14.25A9 9 0 0 0 18 18a9 9 0 0 0-6 2.29m0-14.25v14.25" />
        </svg>

        {{-- Cruz médica flotando --}}
        <svg class="landing-flotar-icono" style="top:32%;right:6%;--landing-rot:12deg;width:28px;height:28px;animation-delay:-5s" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 2h2v9h9v2h-9v9h-2v-9H2v-2h9V2Z"/></svg>

        {{-- Segundo estetoscopio, más chico, a la derecha --}}
        <svg class="landing-flotar-icono landing-flotar-icono--lento" style="bottom:16%;right:14%;--landing-rot:-10deg;width:36px;height:36px;animation-delay:-2.4s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3v6a3.5 3.5 0 0 0 7 0V3M8 3v2.2M4.5 3v2.2M11.5 11.5V13a5.5 5.5 0 0 0 11 0v-1.2" />
            <circle cx="20" cy="9" r="2.3" />
        </svg>

    </div>

    {{--
        El div de afuera es un .landing-contenedor normal (1180px, centrado),
        igual que el del header — así el texto de abajo queda alineado
        exactamente con el logo. El ancho acotado (680px) y el no-centrado
        van en el div de ADENTRO, que al no tener margin:auto simplemente
        se queda pegado a la izquierda del contenedor.
    --}}
    <div class="landing-contenedor">
        <div class="landing-hero__contenido landing-reveal" style="--landing-retraso:0ms">
            <span class="landing-etiqueta" style="background:rgba(255,255,255,.12);color:#fff;">
                <span class="landing-etiqueta__punto"></span> Formación en salud
            </span>
            <h1 class="landing-hero__titulo">
                Aprendemos<br><span class="landing-texto-degradado">a cuidar juntos</span>
            </h1>
            <p class="landing-hero__bajada">
                Formamos Técnicos y Técnicas Superiores en Enfermería con rigor académico, calidez humana
                y prácticas reales desde el primer año.
            </p>
            <div class="landing-hero__acciones">
                <a href="#carreras" class="landing-btn landing-btn--primario">Descubrí la carrera →</a>
                <a href="{{ route('login') }}" class="landing-btn landing-btn--outline">Ya soy alumno/a</a>
            </div>
        </div>
    </div>

    @if ($imagenesHero->count() > 1)
        <div class="landing-hero__puntos" role="tablist" aria-label="Seleccionar foto de portada"></div>
    @endif
</section>
