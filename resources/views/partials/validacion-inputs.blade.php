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
            };
            if (patrones[modo]) {
                el.value = el.value.replace(patrones[modo], '');
            }
            if (el.dataset.maxLen) {
                el.value = el.value.slice(0, parseInt(el.dataset.maxLen, 10));
            }
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
    // En su lugar, el primer clic cambia el botón a un estado "¿Confirmar?" y recién
    // el segundo clic (dentro de los 4s) envía el formulario de verdad.
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        var mensaje = form.dataset.confirm;
        if (!mensaje || form.dataset.confirmado === 'si') return;

        evento.preventDefault();

        var boton = evento.submitter || form.querySelector('button[type="submit"], button:not([type])');
        if (!boton) return;

        var textoOriginal = boton.textContent;
        boton.textContent = '¿Confirmar? Clic de nuevo';
        boton.classList.add('animate-pulse');
        form.dataset.confirmado = 'si';

        clearTimeout(form._confirmTimeout);
        form._confirmTimeout = setTimeout(function () {
            form.dataset.confirmado = 'no';
            boton.textContent = textoOriginal;
            boton.classList.remove('animate-pulse');
        }, 4000);
    });
</script>
