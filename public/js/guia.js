/*
 * Asistente "Goyo" (partials/guia-asistente). Todo corre en el navegador: busca lo que
 * escribe el usuario en el catálogo de App\Support\GuiaAsistente, sin servicios externos.
 */
(function () {
    // Palabras que no aportan a la búsqueda.
    var VACIAS = ('a al algo alguna ayuda ayudame ayudar como con cual cuando dar darle de del donde el en es esta este '
        + 'favor hacer hago hay la las le lo los me mi mis necesito o para pero por porfa puedo que quiero quisiera '
        + 'se sistema sobre su sus te tengo tiene un una uno unos unas va voy y ya').split(' ');

    // Formas de decir "crear": cuentan poco, porque casi todas las guías cargan algo.
    var CREAR = ('agrega agregar agrego armar armo carga cargar cargo cargue crear creo generar genero '
        + 'hacer ingresar ingreso nueva nuevas nuevo nuevos poner registrar registro sumar').split(' ');

    var PESO_FUERTE = 4;
    var PESO_CREAR = 1;
    var PUNTAJE_MINIMO = 3;
    var VENTAJA_PARA_DECIDIR = 1.5;
    var MAX_OPCIONES = 4;

    function normalizar(texto) {
        return String(texto).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim();
    }

    function palabras(texto) {
        return normalizar(texto).split(' ').reduce(function (lista, palabra) {
            if (!palabra || VACIAS.indexOf(palabra) !== -1) return lista;
            if (CREAR.indexOf(palabra) !== -1) palabra = 'crear';
            else if (palabra.length > 3 && palabra.slice(-1) === 's') palabra = palabra.slice(0, -1);
            if (lista.indexOf(palabra) === -1) lista.push(palabra);
            return lista;
        }, []);
    }

    function distancia(a, b) {
        var fila = [];
        for (var j = 0; j <= b.length; j++) fila[j] = j;
        for (var i = 1; i <= a.length; i++) {
            var previo = fila[0];
            fila[0] = i;
            for (var k = 1; k <= b.length; k++) {
                var actual = fila[k];
                fila[k] = Math.min(fila[k] + 1, fila[k - 1] + 1, previo + (a[i - 1] === b[k - 1] ? 0 : 1));
                previo = actual;
            }
        }
        return fila[b.length];
    }

    // Qué tan parecidas son dos palabras, de 0 a 1. Tolera plurales, "profe" y errores de tipeo.
    function parecido(a, b) {
        if (a === b) return 1;
        var corta = Math.min(a.length, b.length);
        if (corta < 4) return 0;
        if (a.indexOf(b) === 0 || b.indexOf(a) === 0) return 0.8;
        if (corta < 5) return 0; // En palabras cortas un error cambia el sentido: "acta" y "alta".
        var tope = corta >= 7 ? 2 : 1;
        return distancia(a, b) <= tope ? 0.6 : 0;
    }

    function mejorParecido(palabra, lista) {
        return lista.reduce(function (mejor, otra) { return Math.max(mejor, parecido(palabra, otra)); }, 0);
    }

    function prepararIndice(guias) {
        return guias.map(function (guia) {
            var frases = guia.claves.map(palabras);
            var todas = palabras(guia.titulo);
            frases.forEach(function (frase) {
                frase.forEach(function (palabra) {
                    if (todas.indexOf(palabra) === -1) todas.push(palabra);
                });
            });
            return { guia: guia, todas: todas, frases: frases };
        });
    }

    function puntuar(entrada, pedido) {
        var puntaje = pedido.reduce(function (total, palabra) {
            return total + mejorParecido(palabra, entrada.todas) * (palabra === 'crear' ? PESO_CREAR : PESO_FUERTE);
        }, 0);

        // Premio si el pedido contiene una frase clave entera ("horario docente").
        var premio = entrada.frases.reduce(function (mejor, frase) {
            if (frase.length < 2) return mejor;
            var completa = frase.every(function (palabra) { return mejorParecido(palabra, pedido) > 0; });
            if (!completa) return mejor;
            var valor = frase.reduce(function (suma, palabra) { return suma + (palabra === 'crear' ? 1 : 3); }, 0);
            return Math.max(mejor, valor);
        }, 0);

        return puntaje + premio;
    }

    /* Devuelve { guia } si hay un claro ganador, { opciones } si hay varias candidatas, o {} si no encontró nada. */
    function buscar(texto, indice) {
        var pedido = palabras(texto);
        var resultados = indice
            .map(function (entrada) { return { guia: entrada.guia, puntaje: puntuar(entrada, pedido) }; })
            .filter(function (resultado) { return resultado.puntaje >= PUNTAJE_MINIMO; })
            .sort(function (a, b) { return b.puntaje - a.puntaje; });

        if (!resultados.length) return {};

        var segundo = resultados[1] ? resultados[1].puntaje : 0;
        if (resultados[0].puntaje >= segundo * VENTAJA_PARA_DECIDIR) return { guia: resultados[0].guia };

        return {
            opciones: resultados
                .filter(function (resultado) { return resultado.puntaje >= resultados[0].puntaje * 0.6; })
                .slice(0, MAX_OPCIONES)
                .map(function (resultado) { return resultado.guia; }),
        };
    }

    window.SgGuia = { buscar: buscar, prepararIndice: prepararIndice };

    var datos = document.getElementById('sg-guia-datos');
    if (!datos) return;

    var guias = JSON.parse(datos.textContent);
    var indice = prepararIndice(guias);
    var boton = document.getElementById('sg-guia-boton');
    var panel = document.getElementById('sg-guia-panel');
    var chat = document.getElementById('sg-guia-chat');
    var form = document.getElementById('sg-guia-form');
    var input = document.getElementById('sg-guia-input');
    var CLAVE_ESTADO = 'sg-guia';

    // Recuerda el panel y la guía abierta al cambiar de pantalla, para seguir viendo los pasos.
    function leerEstado() {
        try {
            return JSON.parse(sessionStorage.getItem(CLAVE_ESTADO)) || {};
        } catch (error) {
            return {};
        }
    }

    function guardarEstado(cambios) {
        var estado = leerEstado();
        Object.keys(cambios).forEach(function (clave) { estado[clave] = cambios[clave]; });
        try {
            sessionStorage.setItem(CLAVE_ESTADO, JSON.stringify(estado));
        } catch (error) {
            // Sin almacenamiento el asistente funciona igual, solo no recuerda la guía.
        }
    }

    function crear(etiqueta, clase, texto) {
        var elemento = document.createElement(etiqueta);
        if (clase) elemento.className = clase;
        if (texto !== undefined) elemento.textContent = texto;
        return elemento;
    }

    function agregar(elemento) {
        chat.appendChild(elemento);
        chat.scrollTop = chat.scrollHeight;
        return elemento;
    }

    function decir(texto) {
        return agregar(crear('div', 'sg-guia-msj sg-guia-msj--goyo', texto));
    }

    function opcion(texto, alElegir, clase) {
        var elemento = crear('button', 'sg-guia-opcion' + (clase ? ' ' + clase : ''), texto);
        elemento.type = 'button';
        elemento.addEventListener('click', alElegir);
        return elemento;
    }

    function opcionGuia(guia) {
        return opcion(guia.titulo, function () {
            agregar(crear('div', 'sg-guia-msj sg-guia-msj--usuario', guia.titulo));
            mostrarGuia(guia);
        });
    }

    function mostrarOpciones(lista) {
        var contenedor = crear('div', 'sg-guia-opciones');
        lista.forEach(function (guia) { contenedor.appendChild(opcionGuia(guia)); });
        contenedor.appendChild(opcion('Ver todas las guías', mostrarTodas, 'sg-guia-opcion--suave'));
        agregar(contenedor);
    }

    function estaEnLaPantalla(guia) {
        return new URL(guia.url, location.href).pathname === location.pathname;
    }

    function sugerencias() {
        var deEstaPantalla = guias.filter(estaEnLaPantalla);
        var destacadas = guias.filter(function (guia) { return guia.destacada && deEstaPantalla.indexOf(guia) === -1; });
        return deEstaPantalla.concat(destacadas).slice(0, 5);
    }

    function saludar() {
        decir('¡Hola! Soy Goyo. ¿Qué querés hacer?');
        mostrarOpciones(sugerencias());
    }

    function mostrarGuia(guia) {
        guardarEstado({ guia: guia.id });

        var tarjeta = crear('article', 'sg-guia-tarjeta');
        tarjeta.appendChild(crear('h3', '', guia.titulo));
        tarjeta.appendChild(crear('p', 'sg-guia-tarjeta__panel', 'Panel de ' + guia.panel));

        var pasos = crear('ol');
        guia.pasos.forEach(function (paso) { pasos.appendChild(crear('li', '', paso)); });
        tarjeta.appendChild(pasos);

        if (guia.nota) tarjeta.appendChild(crear('p', 'sg-guia-tarjeta__nota', guia.nota));

        if (estaEnLaPantalla(guia)) {
            tarjeta.appendChild(crear('p', 'sg-guia-tarjeta__aca', 'Ya estás en esta pantalla'));
        } else {
            var ir = crear('a', 'sg-guia-tarjeta__ir', 'Ir a la pantalla');
            ir.href = guia.url;
            tarjeta.appendChild(ir);
        }

        agregar(tarjeta);

        var seguir = crear('div', 'sg-guia-opciones');
        seguir.appendChild(opcion('Buscar otra cosa', function () {
            decir('Dale, ¿qué más querés hacer?');
            input.focus();
        }, 'sg-guia-opcion--suave'));
        agregar(seguir);

        // Que se vea el principio de la guía, no el final.
        chat.scrollTop = tarjeta.offsetTop - chat.offsetTop - 8;
    }

    function mostrarTodas() {
        decir('Estas son todas las guías:');
        var contenedor = crear('div');
        var paneles = [];
        guias.forEach(function (guia) {
            if (paneles.indexOf(guia.panel) === -1) paneles.push(guia.panel);
        });
        paneles.forEach(function (nombre) {
            contenedor.appendChild(crear('p', 'sg-guia-grupo', nombre));
            var grupo = crear('div', 'sg-guia-opciones');
            guias.forEach(function (guia) {
                if (guia.panel === nombre) grupo.appendChild(opcionGuia(guia));
            });
            contenedor.appendChild(grupo);
        });
        agregar(contenedor);
    }

    function responder(texto) {
        agregar(crear('div', 'sg-guia-msj sg-guia-msj--usuario', texto));

        var resultado = buscar(texto, indice);
        if (resultado.guia) {
            mostrarGuia(resultado.guia);
        } else if (resultado.opciones) {
            decir('Encontré estas opciones, ¿cuál es?');
            mostrarOpciones(resultado.opciones);
        } else {
            decir('No encontré una guía para eso. Probá con otras palabras, como "alumno", "mesa" o "horario", o elegí una de acá:');
            mostrarOpciones(sugerencias());
        }
    }

    function abrir(conFoco) {
        panel.hidden = false;
        boton.hidden = true;
        boton.setAttribute('aria-expanded', 'true');
        guardarEstado({ abierto: true });
        if (!chat.childElementCount) saludar();
        if (conFoco) input.focus();
    }

    function cerrar() {
        panel.hidden = true;
        boton.hidden = false;
        boton.setAttribute('aria-expanded', 'false');
        guardarEstado({ abierto: false });
        boton.focus();
    }

    function reiniciar() {
        chat.textContent = '';
        guardarEstado({ guia: null });
        saludar();
        input.focus();
    }

    boton.addEventListener('click', function () { abrir(true); });
    document.getElementById('sg-guia-cerrar').addEventListener('click', cerrar);
    document.getElementById('sg-guia-reiniciar').addEventListener('click', reiniciar);

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var texto = input.value.trim();
        if (!texto) return;
        input.value = '';
        responder(texto);
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !panel.hidden) cerrar();
    });

    var estado = leerEstado();
    var guiaAbierta = guias.filter(function (guia) { return guia.id === estado.guia; })[0];
    if (estado.abierto) {
        if (guiaAbierta) mostrarGuia(guiaAbierta);
        abrir(false);
    }
})();
