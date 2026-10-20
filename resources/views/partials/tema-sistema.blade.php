{{--
    Tema visual compartido por todos los paneles internos (Secretaría, Dirección y
    Portal del Alumno). Usa la misma paleta y tipografías que la landing pública
    (public/css/landing.css) para que todo el sistema se vea como una sola marca.

    La paleta se aplica remapeando las escalas de Tailwind (slate, blue, amber),
    así todas las vistas existentes heredan los colores nuevos sin tocar su markup.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<style>
    :root {
        --sg-azul: #0B3D91;
        --sg-azul-oscuro: #082A66;
        --sg-celeste: #4FC3F7;
        --sg-celeste-claro: #E3F4FD;
        --sg-amarillo: #FFC107;
        --sg-texto: #122038;
        --sg-suave: #5A6B85;
        --sg-borde: #D7E6F5;
        --sg-fondo: #F3F7FC;
    }

    html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; color: var(--sg-texto); background: var(--sg-fondo); }
    h1, h2, h3, .font-display { font-family: 'Sora', 'Inter', sans-serif; letter-spacing: -.01em; }
    ::selection { background: rgba(79, 195, 247, .35); color: var(--sg-azul-oscuro); }

    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #BED0E6; border-radius: 999px; border: 2px solid transparent; background-clip: padding-box; }
    ::-webkit-scrollbar-thumb:hover { background: #8798B2; background-clip: padding-box; }

    /* ---------- Contenido de las pantallas ---------- */
    .sg-contenido a, .sg-contenido button, .sg-contenido input, .sg-contenido select, .sg-contenido textarea {
        transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, color .18s ease, transform .18s ease;
    }
    .sg-contenido input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=submit]),
    .sg-contenido select,
    .sg-contenido textarea {
        background-color: #fff;
    }
    .sg-contenido input:not([type=checkbox]):not([type=radio]):focus,
    .sg-contenido select:focus,
    .sg-contenido textarea:focus {
        outline: none;
        border-color: var(--sg-azul);
        box-shadow: 0 0 0 4px rgba(79, 195, 247, .22);
    }
    .sg-contenido input[type=checkbox], .sg-contenido input[type=radio] { accent-color: var(--sg-azul); }
    .sg-contenido input:disabled, .sg-contenido select:disabled, .sg-contenido textarea:disabled { background-color: #F5F9FD; cursor: not-allowed; }
    .sg-contenido label { letter-spacing: .01em; }

    /* Botones con fondo azul o amarillo: leve elevación al pasar el mouse */
    .sg-contenido a[class*="bg-[#0B3D91]"]:hover, .sg-contenido button[class*="bg-[#0B3D91]"]:hover,
    .sg-contenido a[class*="bg-[#FFC107]"]:hover, .sg-contenido button[class*="bg-[#FFC107]"]:hover {
        transform: translateY(-1px);
    }
    .sg-contenido button[class*="bg-[#FFC107]"], .sg-contenido a[class*="bg-[#FFC107]"] {
        box-shadow: 0 10px 20px -10px rgba(255, 193, 7, .7);
    }

    /* Tablas */
    .sg-contenido table thead tr { background-color: #F5F9FD; }
    .sg-contenido table thead th {
        color: var(--sg-suave);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .sg-contenido table tbody tr { transition: background-color .15s ease; }
    .sg-contenido table tbody tr:hover { background-color: #F5FAFF; }
    /* En pantallas chicas las tablas anchas se desplazan dentro de su tarjeta en vez de cortarse */
    .sg-contenido div:has(> table) { overflow-x: auto; }

    /* Tarjetas */
    .sg-contenido .rounded-2xl.bg-white, .sg-contenido .bg-white.rounded-2xl,
    .sg-contenido .rounded-3xl.bg-white, .sg-contenido .bg-white.rounded-3xl { border-color: #E3ECF7; }

    /* Calendario (flatpickr) con colores de marca */
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--sg-azul) !important; border-color: var(--sg-azul) !important; }
    .flatpickr-day.today { border-color: var(--sg-celeste) !important; }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-monthDropdown-months { font-family: 'Sora', 'Inter', sans-serif; }

    /* ---------- Menú lateral ---------- */
    .sg-sidebar { background: linear-gradient(170deg, #0B3D91 0%, #082A66 55%, #061F4D 100%); }
    .sg-sidebar__nav::-webkit-scrollbar { width: 6px; }
    .sg-sidebar__nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .18); border: 0; }
    .sg-sidebar__link { position: relative; }
    .sg-sidebar__link.activo::before {
        content: ''; position: absolute; left: -12px; top: 8px; bottom: 8px; width: 4px;
        border-radius: 0 4px 4px 0; background: var(--sg-amarillo);
        box-shadow: 0 0 12px rgba(255, 193, 7, .6);
    }

    /* Menú lateral en pantallas chicas: se desliza desde la izquierda */
    @media (max-width: 1023px) {
        .sg-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 60; transform: translateX(-100%); transition: transform .3s cubic-bezier(.22, .61, .36, 1); }
        body.sg-menu-abierto .sg-sidebar { transform: translateX(0); box-shadow: 0 0 60px rgba(4, 21, 58, .45); }
        body.sg-menu-abierto { overflow: hidden; }
    }
    .sg-velo { display: none; }
    @media (max-width: 1023px) {
        body.sg-menu-abierto .sg-velo { display: block; }
    }

    /* El contenido se muestra al instante (sin animación de entrada) para que la navegación se sienta inmediata. */
    @media (prefers-reduced-motion: reduce) {
        .sg-sidebar { animation: none !important; transition: none !important; }
    }
</style>
<link rel="stylesheet" href="{{ asset('css/sistema.css') }}?v={{ filemtime(public_path('css/sistema.css')) }}">
{{-- Tailwind ya compilado (npm run css). Va después de sistema.css, igual que antes cuando lo inyectaba cdn.tailwindcss.com
     (que compilaba todo en el navegador en cada página y hacía lento el sistema). --}}
<link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ filemtime(public_path('css/tailwind.css')) }}">
<script>document.documentElement.classList.add('sg-js');</script>
{{-- Precarga la pantalla de un enlace interno cuando el mouse se apoya sobre él (o al tocarlo en el
     celular), así al hacer clic ya está descargada. Solo pide la página (no ejecuta nada) y se excluyen
     las descargas. Los navegadores que no lo soportan lo ignoran. --}}
<script type="speculationrules">
{
    "prefetch": [{
        "where": {
            "and": [
                { "href_matches": "/*" },
                { "not": { "href_matches": "/*/pdf" } },
                { "not": { "href_matches": "/*/excel" } },
                { "not": { "href_matches": "/*/descarga" } },
                { "not": { "href_matches": "/*/archivo/*" } },
                { "not": { "selector_matches": "[download], [target], [data-sin-precarga]" } }
            ]
        },
        "eagerness": "moderate"
    }]
}
</script>
<script src="{{ asset('js/sistema.js') }}?v={{ filemtime(public_path('js/sistema.js')) }}" defer></script>
<script>
    // Abre/cierra el menú lateral en celulares y tablets (solo visual).
    document.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-sg-menu-toggle]')) {
            document.body.classList.toggle('sg-menu-abierto');
        } else if (evento.target.closest('[data-sg-menu-cerrar]')) {
            document.body.classList.remove('sg-menu-abierto');
        }
    });
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') document.body.classList.remove('sg-menu-abierto');
    });
</script>
