let favoritos = [];
try {
    // Intento recuperar la lista de juegos favoritos que el usuario guardó en visitas anteriores.
    const guardados = localStorage.getItem("favoritos");
    if (guardados) favoritos = JSON.parse(guardados);
    if (!Array.isArray(favoritos)) favoritos = [];
    // Me aseguro de que todos los identificadores (IDs) sean texto puro para no tener problemas al compararlos después.
    favoritos = favoritos.map(String);
} catch (error) {
    favoritos = [];
}

// Cuando la página termina de cargar, activo los corazones de los favoritos y preparo el sistema de votación.
document.addEventListener('DOMContentLoaded', () => {
    marcarFavoritosGuardados();
    // Si estoy en la página de favoritos, dibujo directamente los productos que el usuario haya guardado.
    if (window.location.pathname.includes("favoritos.php")) cargarPaginaFavoritos();
    
    // Busco todas las zonas de estrellas de la página y las activo para que el usuario pueda interactuar con ellas.
    const cajasValoracion = document.querySelectorAll('.valoracion-box');
    cajasValoracion.forEach(caja => inicializarCajaValoracion(caja));
});

// Función que lee lo que el usuario escribe en la barra superior y esconde los productos que no coincidan.
function filtrarProductos() {
    const inputBuscador = document.getElementById('buscar');
    if (!inputBuscador) return;
    const textoBuscado = inputBuscador.value.toLowerCase();

    function filtrarSeccion(idGrid) {
        const grid = document.getElementById(idGrid);
        if (!grid) return;
        let seccionTieneVisibles = false;
        const tarjetas = grid.querySelectorAll('.product-card');

        // Reviso cada tarjeta una a una. Si su título contiene lo que se busca, la muestro. Si no, la oculto.
        tarjetas.forEach(tarjeta => {
            const titulo = tarjeta.querySelector('h3').textContent.toLowerCase();
            if (titulo.includes(textoBuscado)) {
                tarjeta.style.display = 'flex';
                seccionTieneVisibles = true;
            } else {
                tarjeta.style.display = 'none';
            }
        });

        // Si una sección entera se queda sin resultados, oculto también su título para que la página quede limpia.
        const headerSeccion = grid.previousElementSibling;
        if (headerSeccion && headerSeccion.classList.contains('header-seccion')) {
            headerSeccion.style.display = seccionTieneVisibles ? 'flex' : 'none';
        }
    }
    filtrarSeccion('grid-juegos');
    filtrarSeccion('grid-mensualidades');
}

// Función para ordenar alfabéticamente (A-Z o Z-A) los productos de una sección.
function ordenarProductos(boton) {
    const targetId = boton.getAttribute('data-target');
    const grid = document.getElementById(targetId);
    if (!grid) return;

    let ordenActual = boton.getAttribute('data-orden');
    const tarjetas = Array.from(grid.querySelectorAll('.product-card'));
    let nuevoOrden = ordenActual === 'asc' ? 'desc' : 'asc';
    boton.setAttribute('data-orden', nuevoOrden);

    // Comparo los títulos de las tarjetas para ordenarlas.
    tarjetas.sort((a, b) => {
        const tituloA = a.querySelector('h3').textContent.toLowerCase();
        const tituloB = b.querySelector('h3').textContent.toLowerCase();
        if (nuevoOrden === 'asc') return tituloA.localeCompare(tituloB);
        else return tituloB.localeCompare(tituloA);
    });

    // Vacío la sección y vuelvo a meter las tarjetas ya ordenadas.
    grid.innerHTML = '';
    tarjetas.forEach(tarjeta => grid.appendChild(tarjeta));
}

// LÓGICA DE FAVORITOS 
// Si le dan clic al botón de favorito, lo añado o lo quito de la lista y lo guardo en la memoria del navegador.
function toggleFavorito(boton) {
    try {
        const idProducto = String(boton.dataset.id);
        const index = favoritos.indexOf(idProducto);

        if (index === -1) {
            favoritos.push(idProducto);
            boton.classList.add('favorito-activo');
        } else {
            favoritos.splice(index, 1);
            boton.classList.remove('favorito-activo');
        }

        localStorage.setItem("favoritos", JSON.stringify(favoritos));

        // Si estoy en la pantalla de favoritos y quito uno, refresco la página para que desaparezca al momento.
        if (window.location.pathname.includes("favoritos.php")) {
            cargarPaginaFavoritos();
        }
    } catch(e) {
        console.error("Error favoritos:", e);
    }
}

// Recorro todos los corazones de la pantalla y pinto de rojo los que coincidan con la lista guardada del usuario.
function marcarFavoritosGuardados() {
    try {
        const botonesFavoritos = document.querySelectorAll('.btn-favorito');
        botonesFavoritos.forEach(boton => {
            const id = String(boton.dataset.id);
            if (favoritos.includes(id)) {
                boton.classList.add('favorito-activo');
            } else {
                boton.classList.remove('favorito-activo');
            }
        });
    } catch(e) {}
}

// Esta función dibuja las tarjetas de los productos en la página exclusiva de favoritos.
function cargarPaginaFavoritos() {
    const contenedor = document.getElementById('lista-favoritos');
    if (!contenedor) return;

    contenedor.innerHTML = '';

    if (favoritos.length === 0) {
        contenedor.innerHTML = `<p style="grid-column: 1/-1; text-align:center; font-size: 1.2em; padding: 40px; color: var(--texto-secundario);">${t('mensajes', 'sin_favoritos', 'No tienes productos favoritos todavía')}</p>`;
        return;
    }

    // Busco los datos completos de los productos favoritos en la base de datos que pasé a JS, y genero su HTML.
    if (typeof productosBD !== 'undefined') {
        const productosFavoritos = productosBD.filter(prod => favoritos.includes(String(prod.id)));
        
        productosFavoritos.forEach(prod => {
            let precio = parseFloat(prod.precio);
            let descuento = parseFloat(prod.descuento);
            let precioFinal = descuento > 0 ? precio - (precio * (descuento / 100)) : precio;

            let htmlDescuento = descuento > 0 ? `<div class="badge-descuento">-${descuento}%</div>` : '';
            let htmlPrecio = descuento > 0 
                ? `<span class="precio-original-tachado">${precio.toFixed(2)} €</span><span class="precio-rebajado">${precioFinal.toFixed(2)} €</span>` 
                : `${precio.toFixed(2)} €`;

            contenedor.innerHTML += `
                <div class="product-card">
                    ${htmlDescuento}
                    <img src="${prod.imagen}" alt="${prod.nombre}">
                    <h3>${prod.nombre}</h3>
                    <div class="card-footer">
                        <span class="precio" style="display: flex; align-items: baseline;">${htmlPrecio}</span>
                        <div class="botones-derecha">
                            <button class="btn-favorito favorito-activo" data-id="${prod.id}" onclick="toggleFavorito(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            <button class="btn-comprar" data-id="${prod.id}" data-nombre="${prod.nombre}" data-precio="${precioFinal.toFixed(2)}" data-imagen="${prod.imagen}" data-precio-original="${precio.toFixed(2)}" data-descuento="${descuento.toFixed(2)}">
                                <svg class="icono-carrito" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
}

// SISTEMA DE VOTOS
// Preparo el bloque de estrellas de un producto para que detecte por dónde pasa el ratón y los clics.
function inicializarCajaValoracion(caja) {
    const estrellas = caja.querySelectorAll('.star-btn');
    const contenedorEstrellas = caja.querySelector('.estrellas-interactivas');
    const idProducto = caja.dataset.id;
    const isLogged = caja.dataset.logueado === 'true';
    let currentHoverValue = 0;

    estrellas.forEach(estrella => {
        // Si el ratón está en la mitad izquierda de la estrella, doy media puntuación, si está en la derecha, entera.
        estrella.addEventListener('mousemove', (e) => {
            if (caja.classList.contains('votado')) return;
            contenedorEstrellas.style.cursor = 'pointer';

            const rect = estrella.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const starBaseValue = parseInt(estrella.dataset.value);
            currentHoverValue = x < rect.width / 2 ? starBaseValue - 0.5 : starBaseValue;
            
            actualizarEstrellasVisuales(estrellas, currentHoverValue);
        });

        // Al hacer clic, compruebo si el usuario ha iniciado sesión antes de enviar el voto.
        estrella.addEventListener('click', () => {
            if (caja.classList.contains('votado')) return;
            if (!isLogged) {
                if (typeof Swal !== 'undefined') Swal.fire({title: t('mensajes', 'req_login_titulo', 'Inicia Sesión'), text: t('mensajes', 'votos_req_login', 'Debes iniciar sesión para valorar.'), icon: 'warning', background: 'var(--bg-nav)', color: 'var(--texto-principal)', confirmButtonColor: 'var(--color-primario)'});
                return;
            }
            if (currentHoverValue > 0) enviarVotoBackend(idProducto, currentHoverValue, caja);
        });
    });

    // Si el ratón sale del bloque de estrellas y no ha votado, las vuelvo a poner como estaban.
    contenedorEstrellas.addEventListener('mouseleave', () => {
        if (caja.classList.contains('votado')) return;
        estrellas.forEach(e => {
            e.className = 'fa-regular fa-star star-btn';
            e.style.transform = 'scale(1)';
        });
    });
}

// Pinto las estrellas, oro a la mitad, o vacías, dependiendo de dónde tenga el ratón el usuario en ese momento.
function actualizarEstrellasVisuales(estrellas, valor) {
    estrellas.forEach(e => {
        const starValue = parseInt(e.dataset.value);
        if (starValue <= Math.floor(valor)) { e.className = 'fa-solid fa-star star-btn'; e.style.transform = 'scale(1.1)'; } 
        else if (starValue === Math.ceil(valor) && !Number.isInteger(valor)) { e.className = 'fa-solid fa-star-half-stroke star-btn'; e.style.transform = 'scale(1.1)'; } 
        else { e.className = 'fa-regular fa-star star-btn'; e.style.transform = 'scale(1)'; }
    });
}

// Envío silenciosamente la puntuación a mi servidor PHP. Si todo va bien, bloqueo las estrellas para que no vuelva a votar.
function enviarVotoBackend(idProducto, valoracion, caja) {
    fetch('api_votos.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_producto: idProducto, valoracion: valoracion }) })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            caja.classList.add('votado');
            caja.querySelector('.estrellas-interactivas').style.cursor = 'default';
            caja.querySelector('.media-votos').textContent = `(${data.media.toFixed(1)})`;
            caja.querySelector('.total-votos').textContent = `${data.total} Val.`;
            if (typeof Swal !== 'undefined') Swal.fire({ title: t('mensajes', 'gracias', 'Thank you!'), text: t('mensajes', 'valoracion_guardada', 'Your rating has been saved.'), icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: 'var(--bg-nav)', color: 'var(--texto-principal)' });
        } else {
            if (typeof Swal !== 'undefined') Swal.fire('Error', data.error, 'error');
        }
    });
}