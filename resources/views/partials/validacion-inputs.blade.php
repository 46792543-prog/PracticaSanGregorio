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
