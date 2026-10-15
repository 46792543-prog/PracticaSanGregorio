/**
 * Tailwind del sistema (paneles de secretaría, dirección y alumno).
 *
 * Antes se cargaba cdn.tailwindcss.com, que compila el CSS en el navegador en
 * cada página (lento). Ahora se compila una sola vez a public/css/tailwind.css:
 *
 *   npm run css        → compila (hay que correrlo después de usar clases nuevas)
 *   npm run css:watch  → recompila solo mientras se trabaja en las vistas
 */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php',
        './public/js/**/*.js',
    ],
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
