<script>
    // La cookie de sesión la comparten todas las pestañas del navegador, así que
    // por sí sola no alcanza para exigir "volver a loguearse al cerrar la pestaña".
    // sessionStorage, en cambio, es exclusivo de cada pestaña: el navegador lo borra
    // cuando esa pestaña se cierra. Lo usamos como marca de "esta pestaña ya inició
    // sesión"; si no está presente, forzamos el cierre de sesión y volvemos al login.
    (function () {
        var MARCADOR = 'sg_sesion_activa';
        var recienLogueado = @json((bool) session('recien_logueado'));

        if (recienLogueado) {
            sessionStorage.setItem(MARCADOR, '1');
            return;
        }

        if (sessionStorage.getItem(MARCADOR)) {
            return;
        }

        fetch(@json(route('logout')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': @json(csrf_token()) },
        }).finally(function () {
            window.location.replace(@json(route('login')));
        });
    })();
</script>
