{{--
    Tema visual compartido por todos los paneles internos (Secretaría, Dirección y
    Portal del Alumno). Usa la misma paleta y tipografías que la landing pública
    (public/css/landing.css) para que todo el sistema se vea como una sola marca.

    La paleta se aplica remapeando las escalas de Tailwind (slate, blue, amber),
    así todas las vistas existentes heredan los colores nuevos sin tocar su markup.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'Segoe UI', 'sans-serif'],
                    display: ['Sora', 'Inter', 'sans-serif'],
                },
                colors: {
                    // Neutros con matiz azul (texto #122038, texto suave #5A6B85, borde #D7E6F5)
                    slate: {
                        50: '#F5F9FD', 100: '#EAF2FA', 200: '#D7E6F5', 300: '#BED0E6', 400: '#8798B2',
                        500: '#5A6B85', 600: '#46566F', 700: '#33425B', 800: '#1E2D46', 900: '#122038', 950: '#0A1428',
                    },
                    // Azul institucional + celeste de la landing
                    blue: {
                        50: '#EEF7FE', 100: '#E3F4FD', 200: '#BDE5FA', 300: '#8FD3F8', 400: '#4FC3F7',
                        500: '#1E88E5', 600: '#1565C0', 700: '#0B3D91', 800: '#082A66', 900: '#061F4D', 950: '#04153A',
                    },
                    // Amarillo de acento (#FFC107 / #FFD54F)
                    amber: {
                        50: '#FFF8E1', 100: '#FFECB3', 200: '#FFE082', 300: '#FFD54F', 400: '#FFC107',
                        500: '#F5B000', 600: '#D99A00', 700: '#A87400', 800: '#7A5400', 900: '#523800',
                    },
                    // La paginación de Laravel usa "gray": se alinea con los mismos neutros
                    gray: {
                        50: '#F5F9FD', 100: '#EAF2FA', 200: '#D7E6F5', 300: '#BED0E6', 400: '#8798B2',
                        500: '#5A6B85', 600: '#46566F', 700: '#33425B', 800: '#1E2D46', 900: '#122038',
                    },
                    marca: {
                        azul: '#0B3D91', oscuro: '#082A66', celeste: '#4FC3F7', 'celeste-claro': '#E3F4FD',
                        amarillo: '#FFC107', 'amarillo-claro': '#FFD54F', texto: '#122038', suave: '#5A6B85', borde: '#D7E6F5',
                    },
                },
                boxShadow: {
                    sm: '0 1px 2px rgba(11, 61, 145, .05), 0 4px 16px -8px rgba(11, 61, 145, .12)',
                    md: '0 4px 10px -2px rgba(11, 61, 145, .08), 0 14px 30px -12px rgba(11, 61, 145, .22)',
                    lg: '0 10px 24px -6px rgba(11, 61, 145, .14), 0 24px 48px -18px rgba(11, 61, 145, .28)',
                },
            },
        },
    };
</script>
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

    @keyframes sg-aparecer { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .sg-aparecer { animation: sg-aparecer .35s ease-out backwards; }
    @media (prefers-reduced-motion: reduce) {
        .sg-aparecer, .sg-sidebar { animation: none !important; transition: none !important; }
    }
</style>
<link rel="stylesheet" href="{{ asset('css/sistema.css') }}">
<script>document.documentElement.classList.add('sg-js');</script>
<script src="{{ asset('js/sistema.js') }}" defer></script>
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
