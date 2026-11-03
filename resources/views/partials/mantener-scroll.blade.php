{{-- Al buscar, filtrar, paginar o guardar, la página vuelve a cargarse: la dejamos en la misma posición
     en vez de saltar arriba. Si hubo errores de validación se respeta el salto para que se vea el aviso. --}}
<script>
    (function () {
        const CLAVE = 'sg-scroll';
        let guardado = null;

        try {
            guardado = JSON.parse(sessionStorage.getItem(CLAVE) || 'null');
            sessionStorage.removeItem(CLAVE);
        } catch (e) {}

        window.addEventListener('pagehide', function () {
            try {
                sessionStorage.setItem(CLAVE, JSON.stringify({ ruta: location.pathname, y: window.scrollY }));
            } catch (e) {}
        });

        if (!guardado || guardado.ruta !== location.pathname || location.hash) return;

        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

        function restaurar() {
            if (document.querySelector('[data-sg-errores]')) return;
            window.scrollTo(0, guardado.y);
        }

        document.addEventListener('DOMContentLoaded', restaurar);
        window.addEventListener('load', restaurar);
    })();
</script>
