<?php
$titulo_pagina = "Tu Carrito - Tienda WoW";
require_once 'includes/header.php';
?>

<div class="contenido-pagina">
    
    <div class="header-seccion" style="margin-top: 40px; margin-bottom: 20px;">
        // Muestro el título de la sección y la fecha actual usando mi sistema de traducciones.
        <h2 class="titulo-seccion"><?php echo t('nav.compras') ?? 'TU CARRITO'; ?></h2>
        
        <p class="fecha-compra" style="margin: 0; color: var(--texto-secundario); font-weight: bold;">
            <?php echo t('compras.fecha'); ?>: <span id="fecha-hoy"></span>
        </p>
    </div>

    // Dejo este contenedor vacío para que mi código JavaScript pinte aquí las tarjetas de los productos.
    <div id="carrito-pagina-grid" class="product-grid" style="min-height: 20vh; padding-top: 0;"></div>

    <div class="resumen-compra" style="width: 100%; max-width: 1100px; margin: 0 auto 40px auto;">
            <div class="total-caja">
                // Traduzco el texto del "Total" y muestro el precio final acumulado.
                <span class="total-texto">
                    <?php 
                        $texto_total = t('compras.total');
                        echo ($texto_total === 'compras.total') ? 'PRECIO TOTAL' : $texto_total;
                    ?>:
                </span>
                <span id="total" class="precio-destacado">0.00 €</span>
            </div>

            // Pongo el botón para pagar, traduciendo el texto y llamando a la función que procesa el pedido.
            <button onclick="finalizarCompra()" class="btn-finalizar-premium">
                <?php 
                    $texto_boton = t('compras.finalizar');
                    echo ($texto_boton === 'compras.finalizar') ? 'FINALIZAR COMPRA' : $texto_boton;
                ?>
            </button>
        </div>
    
</div>

<script>
    // Uso JavaScript para capturar la fecha de hoy y ponerla en el formato correcto según el idioma elegido.
    document.addEventListener('DOMContentLoaded', () => {
        const hoy = new Date();
        document.getElementById("fecha-hoy").textContent = hoy.toLocaleDateString((document.documentElement.lang === 'en' ? 'en-US' : 'es-ES'), { day: '2-digit', month: '2-digit', year: 'numeric' });
    });
</script>

// Cargo el cierre de la página y los scripts necesarios.
<?php require_once 'includes/footer.php'; ?>