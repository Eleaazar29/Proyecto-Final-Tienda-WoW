document.addEventListener("DOMContentLoaded", () => {
    // Si la página actual tiene el carrusel de imágenes, lo activo y configuro su movimiento automático.
    if (document.querySelector('.mySwiper')) {
        new Swiper('.mySwiper', {
            slidesPerView: 1.5, spaceBetween: 40, centeredSlides: true,
            loop: true, autoplay: { delay: 3500, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
    }

    // Si estoy en la página "Sobre Nosotros" y encuentro el espacio para el mapa, dibujo el mapa interactivo centrado en Canarias.
    if (document.getElementById('mapa')) {
        let lat = 28.1248, lon = -15.4300;
        const map = L.map('mapa').setView([lat, lon], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([lat, lon]).addTo(map).bindPopup("<b>Tienda WoW HQ</b>").openPopup();
    }
});

// Cambio la apariencia de toda la web alternando entre el modo oscuro (por defecto) y el modo claro, y guardo la preferencia en una cookie.
function toggleTema() {
    const body = document.body;
    body.classList.toggle('modo-claro');
    const temaActual = body.classList.contains('modo-claro') ? 'claro' : 'oscuro';
    document.cookie = `tema=${temaActual}; path=/; max-age=31536000`;
}

// Guardo el nuevo idioma elegido por el usuario en una cookie y recargo la página para que se apliquen los cambios.
function cambiarIdioma(idiomaElegido) {
    document.cookie = `idioma=${idiomaElegido}; path=/; max-age=31536000`;
    window.location.reload();
}

// Abro o cierro el menú desplegable del selector de idiomas en la cabecera.
function toggleDropdownIdioma() {
    const opciones = document.getElementById('idioma-opciones');
    const wrapper = document.getElementById('custom-select-idioma');
    if (opciones && wrapper) {
        opciones.classList.toggle('show'); 
        wrapper.classList.toggle('open');  
    }
}

// Me aseguro de que el selector de idiomas se cierre automáticamente si el usuario hace clic en cualquier otra parte de la pantalla.
document.addEventListener('click', function(event) {
    const wrapperIdioma = document.getElementById('custom-select-idioma');
    const opcionesIdioma = document.getElementById('idioma-opciones');
    if (wrapperIdioma && opcionesIdioma && !wrapperIdioma.contains(event.target)) {
        opcionesIdioma.classList.remove('show');
        wrapperIdioma.classList.remove('open');
    }
});

// Vigilo constantemente el desplazamiento de la página.
window.addEventListener('scroll', function() {
    const btnSubir = document.getElementById('btn-subir');
    const cartBtn = document.querySelector('.cart-floating-btn');
    const footer = document.querySelector('.footer');

    // Muestro el botón de "subir arriba" solo cuando el usuario ya ha bajado un poco.
    if (btnSubir) {
        if (window.scrollY > 300) btnSubir.classList.add('mostrar');
        else btnSubir.classList.remove('mostrar');
    }

    // Evito que los botones flotantes se pisen con el texto del final.
    if (footer) {
        const scrollPosition = window.scrollY + window.innerHeight;
        const footerTop = footer.offsetTop;

        if (scrollPosition > footerTop) {
            const overlap = scrollPosition - footerTop;
            if (cartBtn) cartBtn.style.bottom = (30 + overlap) + 'px';
            if (btnSubir) btnSubir.style.bottom = (30 + overlap) + 'px';
        } else {
            if (cartBtn) cartBtn.style.bottom = '30px';
            if (btnSubir) btnSubir.style.bottom = '30px';
        }
    }
});

// Devuelvo al usuario al principio de la página con un movimiento suave.
function subirArriba() { window.scrollTo({ top: 0, behavior: 'smooth' }); }