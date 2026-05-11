// En cuanto la página carga por completo, mando a pedir las noticias automáticamente.
document.addEventListener("DOMContentLoaded", () => {
    cargarNoticiasAPI();
});

// Esta función se conecta con mi archivo PHP para descargar las noticias en segundo plano sin recargar la web.
async function cargarNoticiasAPI() {
    const contenedor = document.getElementById('contenedor-noticias');
    // Busco dónde poner las noticias en la pantalla. Si no existe ese espacio, me detengo para evitar errores.
    if (!contenedor) return;

    try {
        // Llamo a mi propia API y espero a que me devuelva los datos empaquetados en un archivo JSON.
        const respuesta = await fetch('api_noticias.php');
        const json = await respuesta.json();

        // Si la conexión ha sido un éxito, empiezo a pintar los datos.
        if (json.success) {
            contenedor.innerHTML = ''; 

            // Creo un pequeño texto para decirle al usuario cuántas noticias le estoy enseñando del total que hay.
            const infoContador = document.createElement('p');
            infoContador.style.gridColumn = '1 / -1';
            infoContador.style.color = 'var(--texto-secundario)';
            infoContador.style.marginBottom = '20px';
            infoContador.style.fontSize = '15px';
            // Traduccion aplicada
            infoContador.innerHTML = `<i class="fa-solid fa-circle-info"></i> ${t('noticias', 'mostrando', 'Mostrando')} <strong>${json.mostradas}</strong> ${t('noticias', 'de_las', 'de las')} <strong>${json.total_disponibles}</strong> ${t('noticias', 'recientes', 'noticias recientes.')}`;
            contenedor.appendChild(infoContador);

            // Recorro la lista de noticias una a una y voy dibujando una tarjeta HTML con su foto, título, resumen y botón.
            json.data.forEach(noticia => {
                contenedor.innerHTML += `
                    <div class="noticia-card">
                        <img src="${noticia.imagen}" alt="Wowhead Image" class="noticia-img">
                        <div class="noticia-contenido">
                            <span class="noticia-fecha"><i class="fa-regular fa-calendar"></i> ${noticia.fecha}</span>
                            <h3>${noticia.titulo}</h3>
                            <p>${noticia.resumen}</p>
                        </div>
                        <div class="noticia-footer">
                            <a href="${noticia.link}" target="_blank" class="btn-leer-mas">${t('noticias', 'leer_mas', 'Leer en Wowhead')} <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                `;
            });
        } else {
            // Si mi API me devuelve un fallo, muestro el error en rojo.
            contenedor.innerHTML = `<p style="color: #dc3545; text-align: center;">Error API: ${json.error}</p>`;
        }
    } catch (error) {
        // Si el Fetch falla catastróficamente o el servidor se cae, atrapo el error para que la web no se congele y aviso.
        console.error("Error API:", error);
        contenedor.innerHTML = `<p style="color: #dc3545; text-align: center;">Error con el servidor.</p>`;
    }
}