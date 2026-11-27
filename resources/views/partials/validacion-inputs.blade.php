<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    // El selector de fecha nativo del navegador muestra el calendario en el idioma
    // configurado en el sistema operativo/navegador del usuario (a veces inglés,
    // aunque la página esté en español). Flatpickr reemplaza ese calendario por uno
    // propio, siempre en español, sin depender de esa configuración.
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr.localize(flatpickr.l10ns.es);
        document.querySelectorAll('input[type="date"]').forEach(function (el) {
            flatpickr(el, { altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d' });
        });
    });
</script>

<script>
    // Filtra en tiempo real lo que el usuario escribe/pega en campos marcados con
    // data-solo o data-nota. Es una ayuda de UX (evita que se pueda ni siquiera
    // escribir una letra en un DNI, por ejemplo): la validación real y obligatoria
    // sigue estando del lado del servidor, esto solo evita el error antes de tiempo.
    document.addEventListener('input', function (evento) {
        var el = evento.target;

        var modo = el.dataset.solo;
        if (modo) {
            var patrones = {
                letras: /[^\p{L}\s'-]/gu,
                numeros: /[^0-9]/g,
                telefono: /[^0-9+\-\s()]/g,
                alfanumerico: /[^\p{L}0-9\s]/gu,
                direccion: /[^\p{L}0-9\s,.#'-]/gu,
            };
            if (patrones[modo]) {
                el.value = el.value.replace(patrones[modo], '');
            }
            if (el.dataset.maxLen) {
                el.value = el.value.slice(0, parseInt(el.dataset.maxLen, 10));
            }
        }

        // Para campos de dinero (data-max-digitos="7"): recorta la parte entera
        // a esa cantidad de dígitos, sin tocar los centavos después del punto.
        if (el.dataset.maxDigitos) {
            var limite = parseInt(el.dataset.maxDigitos, 10);
            var partes = el.value.split('.');
            if (partes[0].length > limite) {
                partes[0] = partes[0].slice(0, limite);
                el.value = partes.join('.');
            }
        }

        // Tope de valor (data-max-valor="31" en el día, "1000000" en montos):
        // si se pasa, queda en el máximo.
        if (el.dataset.maxValor && parseFloat(el.value) > parseFloat(el.dataset.maxValor)) {
            el.value = el.dataset.maxValor;
        }

        if ('nota' in el.dataset) {
            el.value = el.value.replace(/[^0-9]/g, '').slice(0, 2);
        }

        // Buscadores mixtos (nombre, apellido o DNI en el mismo campo): si lo que
        // hay escrito es solo números lo tratamos como DNI (tope 8), si tiene
        // alguna letra lo tratamos como nombre/apellido (tope 50).
        if ('busqueda' in el.dataset) {
            var tope = /^[0-9]*$/.test(el.value) ? 8 : 50;
            el.value = el.value.slice(0, tope);
        }
    });

    // En los campos de número no se puede tipear signos: "-" (negativos),
    // "+" ni "e" (notación científica, ej. 1e5).
    document.addEventListener('keydown', function (evento) {
        if (evento.target.type === 'number' && ['-', '+', 'e', 'E'].includes(evento.key)) {
            evento.preventDefault();
        }
    });

    // El clamp a [1,10] de las notas se hace al salir del campo (blur), no en cada
    // tecla: si se hiciera mientras se escribe, tipear "1" para llegar a "10" se
    // convertiría inmediatamente en "1" y nunca dejaría completar el segundo dígito.
    document.addEventListener('blur', function (evento) {
        var el = evento.target;
        if (!('nota' in el.dataset) || el.value === '') return;

        var n = parseInt(el.value, 10);
        if (isNaN(n) || n < 1) el.value = '1';
        else if (n > 10) el.value = '10';
    }, true);
</script>

<script>
    // Confirmación de acciones destructivas (data-confirm="mensaje" en el <form>)
    // sin usar el confirm() nativo del navegador: Chrome bloquea en silencio los
    // diálogos si se disparan varias veces seguidas en la misma página (sin avisar
    // ni mostrar nada), lo que hacía que un "Dar de baja" pareciera no hacer nada.
    // En su lugar se muestra una ventanita propia con Cancelar y Confirmar.
    // Antes de borrar / finalizar / dar de baja (form con data-confirm="mensaje"):
    // una ventanita "¿Estás seguro?" con Cancelar y Confirmar.
    function sgVentanaConfirmar() {
        var v = document.getElementById('sg-confirmar');
        if (v) return v;
        v = document.createElement('div');
        v.id = 'sg-confirmar';
        v.className = 'hidden fixed inset-0 z-[100] items-center justify-center bg-[#04153A]/50 p-4 backdrop-blur-sm';
        v.innerHTML = '<div class="w-full max-w-md rounded-3xl bg-white p-6 text-center shadow-2xl">'
            + '<div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-2xl">⚠️</div>'
            + '<h3 class="font-display text-lg font-bold text-slate-800">¿Estás seguro?</h3>'
            + '<p data-mensaje class="mt-2 mb-5 text-sm text-slate-600"></p>'
            + '<div class="flex gap-3">'
            + '<button type="button" data-cancelar class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>'
            + '<button type="button" data-aceptar class="sg-btn sg-btn--peligro flex-1 justify-center">Sí, confirmar</button>'
            + '</div></div>';
        document.body.appendChild(v);
        v.addEventListener('click', function (e) { if (e.target === v) sgCerrarConfirmar(); });
        v.querySelector('[data-cancelar]').addEventListener('click', sgCerrarConfirmar);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') sgCerrarConfirmar(); });
        return v;
    }
    function sgCerrarConfirmar() {
        var v = document.getElementById('sg-confirmar');
        if (v) { v.classList.add('hidden'); v.classList.remove('flex'); }
    }

    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        var mensaje = form.dataset.confirm;
        if (!mensaje || form.dataset.confirmado === 'si') return;

        evento.preventDefault();
        var boton = evento.submitter || null;
        var v = sgVentanaConfirmar();
        // Mensaje de varias líneas (ej: resumen de lo que se va a guardar): alineado a la izquierda.
        var parrafo = v.querySelector('[data-mensaje]');
        parrafo.textContent = mensaje;
        parrafo.className = 'mt-2 mb-5 text-sm text-slate-600 whitespace-pre-line ' + (mensaje.indexOf('\n') > -1 ? 'text-left rounded-2xl bg-slate-50 p-4' : '');
        // data-confirm-tipo="guardar": botón azul (no es una acción destructiva).
        v.querySelector('[data-aceptar]').className = 'sg-btn flex-1 justify-center ' + (form.dataset.confirmTipo === 'guardar' ? 'sg-btn--azul' : 'sg-btn--peligro');
        v.querySelector('[data-aceptar]').onclick = function () {
            sgCerrarConfirmar();
            form.dataset.confirmado = 'si';
            if (form.requestSubmit) { form.requestSubmit(boton); } else { form.submit(); }
            setTimeout(function () { form.dataset.confirmado = 'no'; }, 1000);
        };
        v.classList.remove('hidden');
        v.classList.add('flex');
        v.querySelector('[data-cancelar]').focus();
    });
</script>
<script>
    // En los campos de número (montos, notas, días), la ruedita del mouse
    // cambia el valor de a un "step": si después de escribir 250000 se
    // scrollea la página con el cursor encima, queda 249999.99 sin que nadie
    // lo note. Al girar la ruedita sobre un campo de número enfocado, se le
    // saca el foco: el valor no cambia y la página scrollea normal.
    document.addEventListener('wheel', function (evento) {
        var el = evento.target;
        if (el instanceof HTMLInputElement && el.type === 'number' && document.activeElement === el) {
            el.blur();
        }
    }, { passive: true });
</script>
<script>
    // Montos con separador de miles (input con data-miles): se ve "100.000" o "100.000,50"
    // mientras se escribe, y al servidor se manda el número limpio ("100000.50") en un
    // campo oculto con el mismo nombre. Para cargarle un valor desde JS: sgMiles(input, 100000).
    function sgFormatearMiles(texto) {
        var partes = String(texto).replace(/[^0-9,]/g, '').split(',');
        var enteros = partes[0].replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return partes.length > 1 ? enteros + ',' + partes[1].slice(0, 2) : enteros;
    }
    function sgMilesCrudo(el) {
        return el.value.replace(/\./g, '').replace(',', '.');
    }
    function sgMiles(el, valor) {
        // Si todavía no se preparó el campo (antes de DOMContentLoaded), se deja el número crudo y se formatea al prepararlo.
        if (!el._sgOculto) { el.value = valor ?? ''; return; }
        el.value = valor === null || valor === undefined || valor === '' ? '' : sgFormatearMiles(String(Number(valor)).replace('.', ','));
        el._sgOculto.value = sgMilesCrudo(el);
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[data-miles]').forEach(function (el) {
            var oculto = document.createElement('input');
            oculto.type = 'hidden';
            oculto.name = el.name;
            el.removeAttribute('name');
            el.type = 'text';
            el.inputMode = 'decimal';
            el.insertAdjacentElement('afterend', oculto);
            el._sgOculto = oculto;
            sgMiles(el, el.value);
            el.addEventListener('input', function () {
                // Se mantiene el cursor a la misma distancia del final al reformatear.
                var desdeElFinal = el.value.length - el.selectionEnd;
                var tope = parseFloat(el.dataset.maxValor);
                el.value = sgFormatearMiles(el.value);
                if (tope && parseFloat(sgMilesCrudo(el)) > tope) el.value = sgFormatearMiles(String(tope).replace('.', ','));
                oculto.value = sgMilesCrudo(el);
                var pos = Math.max(0, el.value.length - desdeElFinal);
                el.setSelectionRange(pos, pos);
            });
        });
    });
</script>
