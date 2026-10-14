/*
 * Acceso al sistema: interacciones visuales (no cambia qué se envía al servidor).
 *  - Mostrar/ocultar contraseña y aviso de Bloq Mayús.
 *  - Botón con indicador de carga al enviar y onda al hacer clic.
 *  - Casillas del código de 6 dígitos (sobre el mismo campo "codigo").
 *  - Medidor de seguridad de la nueva contraseña.
 *  - Leve paralaje de los motivos del panel con el mouse.
 */
(function () {
    var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var iconoOjo = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>';
    var iconoOjoTachado = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>';

    function contrasenas() {
        document.querySelectorAll('[data-acc-ojo]').forEach(function (boton) {
            var input = document.getElementById(boton.getAttribute('data-acc-ojo'));
            if (!input) return;
            boton.innerHTML = iconoOjo;
            boton.addEventListener('click', function () {
                var oculto = input.type === 'password';
                input.type = oculto ? 'text' : 'password';
                boton.innerHTML = oculto ? iconoOjoTachado : iconoOjo;
                boton.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
                input.focus();
            });
        });

        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            var aviso = document.querySelector('[data-acc-mayus="' + input.id + '"]');
            if (!aviso) return;
            function revisar(e) {
                if (e.getModifierState) aviso.classList.toggle('acc-visible', e.getModifierState('CapsLock'));
            }
            input.addEventListener('keydown', revisar);
            input.addEventListener('keyup', revisar);
            input.addEventListener('blur', function () { aviso.classList.remove('acc-visible'); });
        });
    }

    function botones() {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var boton = form.querySelector('.acc-btn--primario[type="submit"]');
                if (boton) boton.classList.add('acc-cargando');
            });
        });
        window.addEventListener('pageshow', function () {
            document.querySelectorAll('.acc-cargando').forEach(function (b) { b.classList.remove('acc-cargando'); });
        });
        if (reducido) return;
        document.addEventListener('pointerdown', function (e) {
            var boton = e.target.closest && e.target.closest('.acc-btn');
            if (!boton) return;
            var r = boton.getBoundingClientRect();
            var lado = Math.max(r.width, r.height) * 2;
            var onda = document.createElement('span');
            onda.className = 'acc-onda';
            onda.style.width = onda.style.height = lado + 'px';
            onda.style.left = (e.clientX - r.left - lado / 2) + 'px';
            onda.style.top = (e.clientY - r.top - lado / 2) + 'px';
            boton.appendChild(onda);
            setTimeout(function () { onda.remove(); }, 650);
        });
    }

    function codigo() {
        var caja = document.querySelector('.acc-codigo');
        if (!caja) return;
        var input = caja.querySelector('input');
        var casillas = caja.querySelectorAll('.acc-codigo__casillas span');
        function pintar() {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
            var enFoco = document.activeElement === input;
            casillas.forEach(function (c, i) {
                c.textContent = input.value[i] || '';
                c.classList.toggle('acc-llena', i < input.value.length);
                c.classList.toggle('acc-actual', enFoco && i === Math.min(input.value.length, 5) && input.value.length < 6);
            });
        }
        ['input', 'focus', 'blur', 'keyup'].forEach(function (ev) { input.addEventListener(ev, pintar); });
        pintar();
    }

    function fuerza() {
        var input = document.querySelector('[data-acc-fuerza]');
        if (!input) return;
        var medidor = document.getElementById(input.getAttribute('data-acc-fuerza'));
        var texto = medidor && medidor.querySelector('.acc-fuerza__texto');
        var reglas = document.querySelectorAll('[data-acc-regla]');
        var confirmar = document.querySelector('[data-acc-confirma]');
        var avisoCoincide = document.getElementById('acc-coincide');
        var etiquetas = ['Escribí una contraseña', 'Débil', 'Aceptable', 'Buena', 'Muy segura'];
        var pruebas = {
            largo: function (v) { return v.length >= 8; },
            letra: function (v) { return /[A-Za-zÁÉÍÓÚÑáéíóúñ]/.test(v); },
            mayus: function (v) { return /[A-ZÁÉÍÓÚÑ]/.test(v) && /[a-záéíóúñ]/.test(v); },
            numero: function (v) { return /\d/.test(v); },
            simbolo: function (v) { return /[^A-Za-z0-9ÁÉÍÓÚÑáéíóúñ]/.test(v); },
        };
        function evaluar() {
            var v = input.value, puntos = 0;
            reglas.forEach(function (li) {
                var ok = pruebas[li.getAttribute('data-acc-regla')](v);
                li.classList.toggle('acc-ok', ok);
                if (ok) puntos++;
            });
            var nivel = v.length === 0 ? 0 : Math.max(1, puntos);
            if (medidor) medidor.setAttribute('data-nivel', nivel);
            if (texto) texto.textContent = etiquetas[nivel];
            coincide();
        }
        function coincide() {
            if (!confirmar || !avisoCoincide) return;
            var mostrar = confirmar.value.length > 0;
            var ok = confirmar.value === input.value;
            avisoCoincide.style.display = mostrar ? 'flex' : 'none';
            avisoCoincide.style.color = ok ? '#047857' : '#B91C1C';
            avisoCoincide.querySelector('span').textContent = ok ? 'Las contraseñas coinciden' : 'Las contraseñas todavía no coinciden';
        }
        input.addEventListener('input', evaluar);
        if (confirmar) confirmar.addEventListener('input', coincide);
        evaluar();
    }

    function paralaje() {
        var panel = document.querySelector('.acc-panel');
        if (!panel || reducido || !window.matchMedia('(pointer: fine)').matches) return;
        var capas = panel.querySelectorAll('.acc-panel__motivo, .acc-panel__luz');
        panel.addEventListener('pointermove', function (e) {
            var r = panel.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width - 0.5;
            var y = (e.clientY - r.top) / r.height - 0.5;
            capas.forEach(function (capa, i) {
                var f = (i % 3 + 1) * 10;
                capa.style.translate = (x * f) + 'px ' + (y * f) + 'px';
            });
        });
        panel.addEventListener('pointerleave', function () {
            capas.forEach(function (capa) { capa.style.translate = '0 0'; });
        });
    }

    function iniciar() {
        contrasenas();
        botones();
        codigo();
        fuerza();
        paralaje();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
