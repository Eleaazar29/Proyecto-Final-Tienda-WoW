let carrito = [];
try {
    // Intento cargar el carrito guardado en el navegador del usuario.
    const guardado = localStorage.getItem("carrito");
    if (guardado) carrito = JSON.parse(guardado);
    if (!Array.isArray(carrito)) carrito = [];
    
    // Si hay datos corruptos de pruebas anteriores, los limpio y me aseguro de que los precios sean números.
    carrito = carrito.filter(item => item && item.id && item.nombre);
    carrito = carrito.map(item => ({...item, precio_original: parseFloat(item.precio_original || item.precio || 0), descuento: parseFloat(item.descuento || 0)}));
} catch (error) {
    // Si el archivo de guardado está roto, empiezo con un carrito vacío.
    console.error("Carrito corrupto. Reseteando...");
    carrito = [];
}

let totalGlobal = 0;

// Guardo cualquier cambio del carrito en la memoria del navegador.
function guardarCarrito() {
    localStorage.setItem("carrito", JSON.stringify(carrito));
}

// Uso esta pequeña función para traducir los textos de JavaScript al idioma que haya elegido el usuario.
function t(seccion, clave, textoPorDefecto) {
    if (typeof i18n_dict !== 'undefined' && i18n_dict[seccion] && i18n_dict[seccion][clave]) {
        return i18n_dict[seccion][clave];
    }
    return textoPorDefecto;
}

document.addEventListener("DOMContentLoaded", () => {
    // Me quedo escuchando todos los clics de la página. Si pulsan en el botón de comprar, recojo los datos del producto.
    document.body.addEventListener('click', function(event) {
        const boton = event.target.closest('.btn-comprar');
        if (boton) {
            let id = parseInt(boton.dataset.id);
            // Me protejo contra fallos cogiendo valores por defecto si el HTML está incompleto.
            let nombreHTML = boton.dataset.nombre || "Producto Desconocido";
            let precioHTML = parseFloat(boton.dataset.precio) || 0;
            let precioOriginalHTML = parseFloat(boton.dataset.precioOriginal) || precioHTML;
            let descuentoHTML = parseFloat(boton.dataset.descuento) || 0;
            let imagenHTML = boton.dataset.imagen || "img/horda.png";

            if (!id) return; 

            // Si el producto ya estaba en el carrito, solo le sumo uno a la cantidad. Si no, lo añado como nuevo.
            let productoExistente = carrito.find(p => p.id === id);
            if (productoExistente) {
                productoExistente.cantidad++;
            } else {
                carrito.push({ id, nombre: nombreHTML, precio: precioHTML, precio_original: precioOriginalHTML, descuento: descuentoHTML, imagen: imagenHTML, cantidad: 1 });
            }

            // Guardo, actualizo la vista y abro el menú lateral para que el usuario vea lo que ha añadido.
            guardarCarrito();
            actualizarCarrito();
            abrirCart();
        }
    });

    actualizarCarrito();
    // Si estoy dentro de la página "carrito.php", lanzo la función que dibuja los productos en grande.
    if (window.location.pathname.includes("carrito.php")) renderizarPaginaCarrito();
});

// Actualizo el numerito rojo que sale encima del icono del carrito para mostrar cuántas cosas hay.
function actualizarCarrito() {
    let cartCount = document.getElementById("cart-count");
    if (cartCount) {
        let totalItems = carrito.reduce((acc, p) => acc + p.cantidad, 0);
        cartCount.textContent = totalItems;
        // Le doy un pequeño efecto de rebote al numerito para que llame la atención.
        if (totalItems > 0) {
            cartCount.classList.add("animar-bump");
            setTimeout(() => cartCount.classList.remove("animar-bump"), 300);
        }
    }
    renderizarCarritoLateral();
}

// Dibujo los productos uno a uno en el menú lateral deslizable y calculo el total de dinero al vuelo.
function renderizarCarritoLateral() {
    const contenedor = document.getElementById("cart-items");
    const totalSidebar = document.getElementById("cart-sidebar-total");
    if (!contenedor || !totalSidebar) return;

    contenedor.innerHTML = "";
    let total = 0;

    if (carrito.length === 0) {
        contenedor.innerHTML = `<p class="empty-msg" style="color: var(--texto-secundario); text-align: center; padding: 20px;">${t('carrito', 'vacio', 'El carrito está vacío')}</p>`;
        totalSidebar.textContent = "0.00 €";
        return;
    }

    carrito.forEach(item => {
        total += (parseFloat(item.precio) || 0) * (parseInt(item.cantidad) || 1);
        contenedor.innerHTML += `
            <div class="cart-item">
                <img src="${item.imagen}" alt="${item.nombre}">
                <div class="item-details">
                    <h4>${item.nombre}</h4>
                    <p>${(parseFloat(item.precio) || 0).toFixed(2)} €</p>
                    <div class="item-controls">
                        <button class="btn-mini" onclick="cambiarCantidad(${item.id}, -1)">-</button>
                        <span>${item.cantidad}</span>
                        <button class="btn-mini" onclick="cambiarCantidad(${item.id}, 1)">+</button>
                    </div>
                </div>
                <button class="btn-remove" onclick="eliminarDelCarrito(${item.id})">×</button>
            </div>
        `;
    });
    
    totalGlobal = total;
    totalSidebar.textContent = total.toFixed(2) + " €";
}

// Subo o bajo la cantidad de un producto. Si la cantidad baja a 0, lo elimino del carrito automáticamente.
function cambiarCantidad(id, cambio) {
    let producto = carrito.find(p => p.id === id);
    if (producto) {
        producto.cantidad += cambio;
        if (producto.cantidad <= 0) eliminarDelCarrito(id);
        else {
            guardarCarrito();
            actualizarCarrito();
            if (window.location.pathname.includes("carrito.php")) renderizarPaginaCarrito();
        }
    }
}

// Borro un producto específico del carrito y actualizo las pantallas.
function eliminarDelCarrito(id) {
    carrito = carrito.filter(p => p.id !== id);
    guardarCarrito();
    actualizarCarrito();
    if (window.location.pathname.includes("carrito.php")) renderizarPaginaCarrito();
}

// Vacío todo el carrito de un plumazo.
function vaciarCarritoLateral() {
    carrito = [];
    guardarCarrito();
    actualizarCarrito();
    if (window.location.pathname.includes("carrito.php")) renderizarPaginaCarrito();
}

// Abro o cierro el menú lateral del carrito dependiendo de cómo estuviese antes.
function toggleCart() {
    try {
        const sidebar = document.getElementById("cart-sidebar");
        const overlay = document.getElementById("cart-overlay");
        if (sidebar) {
            sidebar.classList.toggle("open");
            sidebar.style.right = sidebar.classList.contains("open") ? "0px" : "-400px";
        }
        if (overlay) {
            overlay.classList.toggle("open");
            overlay.style.display = overlay.classList.contains("open") ? "block" : "none";
        }
    } catch(e) {}
}

// Fuerzo la apertura del menú lateral del carrito de forma directa.
function abrirCart() {
    try {
        const sidebar = document.getElementById("cart-sidebar");
        const overlay = document.getElementById("cart-overlay");
        if (sidebar) { sidebar.classList.add("open"); sidebar.style.right = "0px"; }
        if (overlay) { overlay.classList.add("open"); overlay.style.display = "block"; }
    } catch(e) {}
}

// Dibujo la página grande de "Tu Carrito", calculando los descuentos, precios tachados y totales.
function renderizarPaginaCarrito() {
    const contenedor = document.getElementById("carrito-pagina-grid");
    const totalBox = document.getElementById("total");
    if (!contenedor || !totalBox) return;

    contenedor.innerHTML = "";
    let total = 0;

    if (carrito.length === 0) {
        contenedor.innerHTML = `<p style="grid-column: 1/-1; text-align:center; font-size: 1.2em; padding: 40px; color: var(--texto-secundario);">${t('carrito', 'vacio', 'El carrito está vacío')}</p>`;
        totalBox.textContent = "0.00 €";
        return;
    }

    carrito.forEach(producto => {
        const precioUnitario = parseFloat(producto.precio) || 0;
        const cantidad = parseInt(producto.cantidad) || 1;
        const totalArticulo = precioUnitario * cantidad;
        const descuento = parseFloat(producto.descuento) || 0;
        const precioOriginal = parseFloat(producto.precio_original) || precioUnitario;
        total += totalArticulo;

        const htmlDescuento = descuento > 0 ? `<div class="badge-descuento">-${Math.round(descuento)}%</div>` : '';
        const htmlPrecioPrincipal = descuento > 0
            ? `<span class="precio-original-tachado">${precioOriginal.toFixed(2)} €</span><span class="precio-rebajado">${precioUnitario.toFixed(2)} €</span>`
            : `<span>${precioUnitario.toFixed(2)} €</span>`;

        contenedor.innerHTML += `
            <div class="product-card" style="display: flex; flex-direction: column; height: 100%;">
                ${htmlDescuento}
                <img src="${producto.imagen}" alt="${producto.nombre}">
                <h3 style="flex-grow: 1;">${producto.nombre}</h3>
                <div class="card-footer" style="flex-direction: column; align-items: flex-start; gap: 15px;">
                    <span class="precio" style="display:flex; align-items:baseline; justify-content:flex-start; width:100%;">${htmlPrecioPrincipal}<span style="margin-left:8px; color: var(--texto-secundario);">${t('carrito', 'por_unidad', 'c/u')}</span><span style="margin-left:8px; color:#3b82f6; font-weight:700;">${totalArticulo.toFixed(2)} €</span></span>
                    <div class="cantidad-selector">
                        <button onclick="cambiarCantidad(${producto.id}, -1)">-</button>
                        <input type="number" value="${producto.cantidad}" readonly>
                        <button onclick="cambiarCantidad(${producto.id}, 1)">+</button>
                    </div>
                    <button class="btn-eliminar-elegante" onclick="eliminarDelCarrito(${producto.id})" style="width: 100%; margin-top: 10px;">
                        <i class="fa-solid fa-trash"></i> ${t('carrito', 'eliminar', 'Remove')}
                    </button>
                </div>
            </div>
        `;
    });

    totalGlobal = total;
    totalBox.textContent = total.toFixed(2) + " €";
}

// Compruebo que el usuario esté conectado. Si todo está bien, le envío los datos a PHP para que me cobre y genere el pedido.
function finalizarCompra() {
    if (carrito.length === 0) {
        if (typeof Swal !== 'undefined') Swal.fire({title: t('mensajes', 'atencion', 'Attention'), text: t('carrito', 'vacio', 'El carrito está vacío'), icon: 'warning', background: 'var(--bg-nav)', color: 'var(--texto-principal)'});
        return;
    }

    const isLogged = document.querySelector('.btn-logout-outline') !== null || document.querySelector('.user-profile-menu') !== null;
    if (!isLogged) {
        if (typeof Swal !== 'undefined') Swal.fire({title: t('mensajes', 'req_login_titulo', 'Inicia Sesión'), text: t('mensajes', 'req_login_texto', 'Debes iniciar sesión para finalizar la compra.'), icon: 'info', background: 'var(--bg-nav)', color: 'var(--texto-principal)', showCancelButton: true, confirmButtonText: t('mensajes', 'ir_login', 'Ir a Iniciar Sesión'), confirmButtonColor: 'var(--color-primario)'}).then((res) => { if(res.isConfirmed) window.location.href = 'login.php'; });
        return;
    }

    if (typeof Swal !== 'undefined') Swal.fire({ title: t('mensajes', 'procesando', 'Processing...'), allowOutsideClick: false, background: 'var(--bg-nav)', color: 'var(--texto-principal)', didOpen: () => { Swal.showLoading() } });

    fetch('procesar_pedido.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ carrito: carrito, total: totalGlobal }) })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') Swal.fire({ title: t('mensajes', 'compra_exito', '¡Compra completada!'), text: t('mensajes', 'pedido_num', 'Pedido #') + data.id_pedido, icon: 'success', background: 'var(--bg-nav)', color: 'var(--texto-principal)', confirmButtonColor: 'var(--color-primario)' }).then(() => { carrito = []; guardarCarrito(); window.location.href = 'misPedidos.php'; });
            else { carrito = []; guardarCarrito(); window.location.href = 'misPedidos.php'; }
        } else {
            if (typeof Swal !== 'undefined') Swal.fire('Error', data.error, 'error');
        }
    });
}