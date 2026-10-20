/*
 * Movimiento de los paneles internos (solo visual, no toca datos ni formularios).
 *  - [data-sg-contar]: el número se cuenta desde 0 cuando entra en pantalla (cuenta corta).
 *  - Tarjetas .sg-card: brillo que sigue al mouse; las .sg-card--hover además se inclinan levemente.
 *  - Botones .sg-btn: onda al hacer clic.
 *  - Fondo ambiental, barra de progreso de scroll y botón "volver arriba".
 *
 * Para que la navegación se sienta inmediata ya no hay fundido al salir de una pantalla
 * ni aparición escalonada del contenido (.sg-revelar / [data-sg-escalonar] se muestran tal cual).
 */
(function () {
    var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var conPuntero = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function observar(selector, alEntrar, opciones) {
        var elementos = document.querySelectorAll(selector);
        if (reducido || !('IntersectionObserver' in window)) {
            elementos.forEach(function (el) { alEntrar(el, true); });
            return;
        }
        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    observador.unobserve(entrada.target);
                    alEntrar(entrada.target, false);
                }
            });
        }, opciones || { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        elementos.forEach(function (el) { observador.observe(el); });
    }

    // Cuenta números enteros con separador de miles "." (formato es-AR), conservando prefijos/sufijos.
    function contar() {
        observar('[data-sg-contar]', function (el, inmediato) {
            var texto = el.textContent.trim();
            var partes = texto.match(/^([^\d]*)(\d{1,3}(?:\.\d{3})+|\d+)([^\d,]*)$/);
            if (!partes || inmediato) return;
            var destino = parseInt(partes[2].replace(/\./g, ''), 10);
            if (!destino) return;
            var inicio = null, duracion = Math.min(600, 300 + destino * 4);
            function paso(t) {
                if (!inicio) inicio = t;
                var avance = Math.min((t - inicio) / duracion, 1);
                var suave = 1 - Math.pow(1 - avance, 4);
                el.textContent = partes[1] + Math.round(destino * suave).toLocaleString('es-AR') + partes[3];
                if (avance < 1) requestAnimationFrame(paso);
            }
            el.textContent = partes[1] + '0' + partes[3];
            requestAnimationFrame(paso);
        }, { threshold: 0.4 });
    }

    // Brillo que sigue al mouse dentro de cada tarjeta + leve inclinación 3D en las tarjetas interactivas.
    function efectosTarjetas() {
        if (reducido || !conPuntero) return;
        document.addEventListener('pointermove', function (e) {
            var tarjeta = e.target.closest && e.target.closest('.sg-card, .sg-hero');
            if (!tarjeta) return;
            var r = tarjeta.getBoundingClientRect();
            var x = e.clientX - r.left, y = e.clientY - r.top;
            tarjeta.style.setProperty('--mx', x + 'px');
            tarjeta.style.setProperty('--my', y + 'px');
            if (tarjeta.classList.contains('sg-card--hover') && !tarjeta.classList.contains('sg-revelar')) {
                tarjeta.style.setProperty('--ry', ((x / r.width) - 0.5) * 6 + 'deg');
                tarjeta.style.setProperty('--rx', (0.5 - (y / r.height)) * 6 + 'deg');
            }
        }, { passive: true });
        document.addEventListener('pointerout', function (e) {
            var tarjeta = e.target.closest && e.target.closest('.sg-card--hover');
            if (tarjeta && !tarjeta.contains(e.relatedTarget)) {
                tarjeta.style.removeProperty('--rx');
                tarjeta.style.removeProperty('--ry');
            }
        });
    }

    // Onda al hacer clic en los botones.
    function ondasBotones() {
        if (reducido) return;
        document.addEventListener('pointerdown', function (e) {
            var boton = e.target.closest && e.target.closest('.sg-btn');
            if (!boton || boton.disabled) return;
            var r = boton.getBoundingClientRect();
            var lado = Math.max(r.width, r.height) * 2;
            var onda = document.createElement('span');
            onda.className = 'sg-onda';
            onda.style.width = onda.style.height = lado + 'px';
            onda.style.left = (e.clientX - r.left - lado / 2) + 'px';
            onda.style.top = (e.clientY - r.top - lado / 2) + 'px';
            boton.appendChild(onda);
            setTimeout(function () { onda.remove(); }, 650);
        });
    }

    // Fondo ambiental, barra de progreso, sombra de la barra superior y botón "volver arriba".
    function ambiente() {
        var contenido = document.querySelector('.sg-contenido');
        if (!contenido) return;

        var fondo = document.createElement('div');
        fondo.className = 'sg-fondo';
        fondo.setAttribute('aria-hidden', 'true');
        fondo.innerHTML = '<span></span><span></span><span></span>';
        document.body.appendChild(fondo);

        var barraSuperior = document.querySelector('.sg-topbar');
        var progreso = document.createElement('div');
        progreso.className = 'sg-progreso-scroll';
        if (barraSuperior) barraSuperior.appendChild(progreso);

        var arriba = document.createElement('button');
        arriba.type = 'button';
        arriba.className = 'sg-arriba';
        arriba.setAttribute('aria-label', 'Volver arriba');
        arriba.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>';
        arriba.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reducido ? 'auto' : 'smooth' }); });
        document.body.appendChild(arriba);

        var pendiente = false;
        function actualizar() {
            pendiente = false;
            var alto = document.documentElement.scrollHeight - window.innerHeight;
            var y = window.scrollY;
            progreso.style.transform = 'scaleX(' + (alto > 0 ? Math.min(y / alto, 1) : 0) + ')';
            if (barraSuperior) barraSuperior.classList.toggle('sg-con-scroll', y > 8);
            arriba.classList.toggle('sg-visible', y > 500);
        }
        window.addEventListener('scroll', function () {
            if (!pendiente) { pendiente = true; requestAnimationFrame(actualizar); }
        }, { passive: true });
        actualizar();
    }

    function iniciar() {
        contar();
        efectosTarjetas();
        ondasBotones();
        ambiente();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
