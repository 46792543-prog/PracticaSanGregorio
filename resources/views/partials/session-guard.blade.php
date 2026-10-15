{{--
    Cierra la sesión cuando se vuelve a entrar al sistema sin pasar por el login.

    La cookie de sesión la comparten todas las pestañas y puede sobrevivir al
    cierre del navegador, así que no alcanza sola. Cada pestaña guarda en
    sessionStorage (que el navegador borra al cerrar la pestaña) una marca de
    "sesión activa" con la hora de la última actividad. Se cierra la sesión si:

      - la pestaña no tiene marca y no se llegó desde una pantalla interna del
        sistema (se escribió la URL, se usó un favorito, se abrió el navegador
        de nuevo, etc.);
      - la marca existe pero el sistema estuvo cerrado más de MINUTOS_AUSENCIA
        (el navegador restauró la pestaña al volver a abrirse).

    Los enlaces internos que abren otra pestaña (ej. el acta) siguen
    funcionando: llegan con un "referrer" de una pantalla del sistema.
--}}
@auth
<script>
    (function () {
        var MARCADOR = 'sg_sesion_activa';
        var MINUTOS_AUSENCIA = 10;
        var PAGINAS_PUBLICAS = /^\/(login|recuperar-contrasena(\/.*)?)?\/?$/;
        var recienLogueado = @json((bool) session('recien_logueado'));

        function marcarActividad() {
            try { sessionStorage.setItem(MARCADOR, String(Date.now())); } catch (e) {}
        }

        function cerrarSesion() {
            try { sessionStorage.removeItem(MARCADOR); } catch (e) {}
            document.documentElement.style.visibility = 'hidden';

            var enviar = function () {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('logout'));
                form.innerHTML = '<input type="hidden" name="_token" value="' + @json(csrf_token()) + '">'
                    + '<input type="hidden" name="motivo" value="reingreso">';
                document.body.appendChild(form);
                form.submit();
            };

            document.body ? enviar() : document.addEventListener('DOMContentLoaded', enviar);
        }

        function vieneDeUnaPantallaInterna() {
            try {
                var origen = new URL(document.referrer);
                return origen.origin === location.origin && ! PAGINAS_PUBLICAS.test(origen.pathname);
            } catch (e) {
                return false;
            }
        }

        function estuvoAusente() {
            var ultima = parseInt(sessionStorage.getItem(MARCADOR) || '0', 10);
            return ultima > 0 && Date.now() - ultima > MINUTOS_AUSENCIA * 60 * 1000;
        }

        var marca;
        try { marca = sessionStorage.getItem(MARCADOR); } catch (e) { marca = '1'; }

        if (! recienLogueado) {
            if ((! marca && ! vieneDeUnaPantallaInterna()) || (marca && estuvoAusente())) {
                cerrarSesion();
                return;
            }
        }

        marcarActividad();

        // Mientras la página está abierta se renueva la marca, así una pantalla
        // abierta mucho tiempo no cuenta como "ausencia".
        setInterval(marcarActividad, 60 * 1000);

        // Al volver a la pestaña (o al despertar la compu) se controla de nuevo.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState !== 'visible') return;
            if (estuvoAusente()) { cerrarSesion(); return; }
            marcarActividad();
        });

        // Si el navegador muestra la página desde su caché al ir "atrás", se la pide de nuevo.
        window.addEventListener('pageshow', function (evento) {
            if (evento.persisted) location.reload();
        });
    })();
</script>
@endauth
