/**
 * Landing pública — Instituto Superior San Gregorio
 * JS vanilla, sin dependencias externas. Respeta prefers-reduced-motion
 * desactivando el autoplay del carrusel y los conteos animados.
 */
(function () {
    'use strict';

    var prefiereMenosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Header: cambia de estilo al hacer scroll ---------- */
    var header = document.querySelector('.landing-header');
    if (header) {
        var actualizarHeader = function () {
            header.classList.toggle('con-scroll', window.scrollY > 40);
        };
        actualizarHeader();
        window.addEventListener('scroll', actualizarHeader, { passive: true });
    }

    /* ---------- Menú móvil ---------- */
    var hamburguesa = document.querySelector('.landing-hamburguesa');
    var menuMovil = document.querySelector('.landing-menu-movil');
    if (hamburguesa && menuMovil) {
        hamburguesa.addEventListener('click', function () {
            var abierto = menuMovil.classList.toggle('abierto');
            hamburguesa.classList.toggle('abierto', abierto);
            hamburguesa.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            document.body.style.overflow = abierto ? 'hidden' : '';
        });
        menuMovil.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                menuMovil.classList.remove('abierto');
                hamburguesa.classList.remove('abierto');
                hamburguesa.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });
    }

    /* ---------- Resaltar el link activo según la sección visible ---------- */
    var enlacesNav = document.querySelectorAll('.landing-nav__link, .landing-menu-movil__link');
    var secciones = Array.prototype.slice.call(document.querySelectorAll('[data-landing-seccion]'));
    if (secciones.length && enlacesNav.length) {
        var ioNav = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (!entrada.isIntersecting) return;
                var id = entrada.target.getAttribute('id');
                enlacesNav.forEach(function (link) {
                    var esActivo = link.getAttribute('href') === '#' + id;
                    link.classList.toggle('activo', esActivo);
                });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        secciones.forEach(function (s) { ioNav.observe(s); });
    }

    /* ---------- Revelar al hacer scroll ---------- */
    var elementosRevelar = document.querySelectorAll('.landing-reveal');
    if (elementosRevelar.length) {
        var ioRevelar = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    entrada.target.classList.add('landing-visible');
                    ioRevelar.unobserve(entrada.target);
                }
            });
        }, { threshold: .12, rootMargin: '0px 0px -40px 0px' });
        elementosRevelar.forEach(function (el) { ioRevelar.observe(el); });
    }

    /* ---------- Contadores animados ---------- */
    var contadores = document.querySelectorAll('[data-landing-contar]');
    if (contadores.length) {
        var ioContar = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (!entrada.isIntersecting) return;
                var el = entrada.target;
                var destino = parseInt(el.getAttribute('data-landing-contar'), 10) || 0;
                var sufijo = el.getAttribute('data-landing-sufijo') || '';

                if (prefiereMenosMovimiento) {
                    el.textContent = destino + sufijo;
                    ioContar.unobserve(el);
                    return;
                }

                var inicio = null;
                var duracion = 1300;
                function paso(marca) {
                    if (!inicio) inicio = marca;
                    var progreso = Math.min((marca - inicio) / duracion, 1);
                    var valor = Math.floor(progreso * destino);
                    el.textContent = valor + sufijo;
                    if (progreso < 1) requestAnimationFrame(paso);
                    else el.textContent = destino + sufijo;
                }
                requestAnimationFrame(paso);
                ioContar.unobserve(el);
            });
        }, { threshold: .5 });
        contadores.forEach(function (el) { ioContar.observe(el); });
    }

    /* ---------- Carrusel de novedades ---------- */
    var carrusel = document.querySelector('[data-landing-carrusel]');
    if (carrusel) {
        var pista = carrusel.querySelector('.landing-carrusel__pista');
        var slides = Array.prototype.slice.call(carrusel.querySelectorAll('.landing-carrusel__slide'));
        var puntosCont = carrusel.querySelector('.landing-carrusel__puntos');
        var btnAnterior = carrusel.querySelector('[data-landing-anterior]');
        var btnSiguiente = carrusel.querySelector('[data-landing-siguiente]');
        var barraProgreso = carrusel.querySelector('[data-landing-progreso]');
        var indice = 0;
        var autoplayMs = 5500;
        var temporizador = null;

        function reiniciarBarra() {
            if (!barraProgreso || prefiereMenosMovimiento) return;
            barraProgreso.classList.remove('correr');
            void barraProgreso.offsetWidth;
            barraProgreso.classList.add('correr');
        }

        var puntos = slides.map(function (_, i) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'landing-carrusel__punto';
            btn.setAttribute('aria-label', 'Ir a la novedad ' + (i + 1));
            btn.addEventListener('click', function () { irA(i); reiniciarAutoplay(); });
            puntosCont.appendChild(btn);
            return btn;
        });

        function render() {
            pista.style.transform = 'translateX(-' + (indice * 100) + '%)';
            puntos.forEach(function (p, i) { p.classList.toggle('activo', i === indice); });
        }

        function irA(i) {
            indice = (i + slides.length) % slides.length;
            render();
        }

        function siguiente() { irA(indice + 1); }
        function anterior() { irA(indice - 1); }

        function reiniciarAutoplay() {
            reiniciarBarra();
            if (prefiereMenosMovimiento) return;
            clearInterval(temporizador);
            temporizador = setInterval(siguiente, autoplayMs);
        }

        if (btnSiguiente) btnSiguiente.addEventListener('click', function () { siguiente(); reiniciarAutoplay(); });
        if (btnAnterior) btnAnterior.addEventListener('click', function () { anterior(); reiniciarAutoplay(); });

        carrusel.addEventListener('mouseenter', function () {
            clearInterval(temporizador);
            if (barraProgreso) barraProgreso.style.animationPlayState = 'paused';
        });
        carrusel.addEventListener('mouseleave', function () {
            if (barraProgreso) barraProgreso.style.animationPlayState = 'running';
            reiniciarAutoplay();
        });

        // Soporte táctil (swipe)
        var xInicial = null;
        pista.addEventListener('touchstart', function (e) { xInicial = e.touches[0].clientX; clearInterval(temporizador); }, { passive: true });
        pista.addEventListener('touchend', function (e) {
            if (xInicial === null) return;
            var delta = e.changedTouches[0].clientX - xInicial;
            if (Math.abs(delta) > 40) { delta < 0 ? siguiente() : anterior(); }
            xInicial = null;
            reiniciarAutoplay();
        });

        render();
        reiniciarAutoplay();
    }

    /* ---------- Carrusel de fondo del hero (fotos de la institución) ---------- */
    var heroFondo = document.querySelector('[data-landing-hero-carrusel]');
    if (heroFondo) {
        var fotos = Array.prototype.slice.call(heroFondo.querySelectorAll('.landing-hero__fondo-img'));
        var puntosHero = document.querySelector('.landing-hero__puntos');
        var indiceHero = 0;

        if (puntosHero) {
            fotos.forEach(function (_, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'landing-carrusel__punto' + (i === 0 ? ' activo' : '');
                btn.setAttribute('aria-label', 'Mostrar foto ' + (i + 1));
                btn.addEventListener('click', function () { mostrarFoto(i); });
                puntosHero.appendChild(btn);
            });
        }

        function mostrarFoto(i) {
            fotos[indiceHero].classList.remove('activa');
            if (puntosHero) puntosHero.children[indiceHero].classList.remove('activo');
            indiceHero = i;
            fotos[indiceHero].classList.add('activa');
            if (puntosHero) puntosHero.children[indiceHero].classList.add('activo');
        }

        if (fotos.length > 1 && !prefiereMenosMovimiento) {
            setInterval(function () { mostrarFoto((indiceHero + 1) % fotos.length); }, 6000);
        }
    }

    /* ---------- Pestañas de carrera ---------- */
    var tabsCarrera = document.querySelectorAll('[data-landing-tab-carrera]');
    if (tabsCarrera.length) {
        tabsCarrera.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var id = tab.getAttribute('data-landing-tab-carrera');

                tabsCarrera.forEach(function (t) {
                    t.classList.toggle('activo', t === tab);
                    t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
                });
                document.querySelectorAll('[data-landing-panel-carrera]').forEach(function (panel) {
                    panel.classList.toggle('activo', panel.getAttribute('data-landing-panel-carrera') === id);
                });
            });
        });
    }
})();
