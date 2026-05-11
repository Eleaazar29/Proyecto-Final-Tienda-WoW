<?php
$titulo_pagina = "Tienda Online WoW";
require_once 'includes/header.php';
require_once 'config/conexion.php';

// Reviso quién está conectado y qué idioma tiene seleccionado en su navegador.
$usuario_actual = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
$idioma_real = isset($_COOKIE['idioma']) ? $_COOKIE['idioma'] : 'es';

// Preparo la consulta para traerme todos los productos, su nota media y comprobar si ya los he votado.
$sql = "SELECT p.id, p.nombre_es, p.nombre_en, p.precio, p.descuento, p.imagen,
               COALESCE(AVG(v.valoracion), 0) as media,
               COUNT(v.valoracion) as total,
               SUM(CASE WHEN v.usuario = :usuario THEN 1 ELSE 0 END) as ya_voto
        FROM productos p
        LEFT JOIN votos v ON p.id = v.id_producto
        GROUP BY p.id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario' => $usuario_actual]);
$productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separo los productos en dos listas (juegos y suscripciones) buscando palabras clave en su nombre.
$juegos = [];
$suscripciones = [];

foreach ($productos_db as $prod) {
    $nombre_mostrar = ($idioma_real === 'en' && !empty($prod['nombre_en'])) ? $prod['nombre_en'] : $prod['nombre_es'];
    $prod['nombre_mostrar'] = $nombre_mostrar;

    $texto_busqueda = strtolower(($prod['nombre_es'] ?? '') . ' ' . ($prod['nombre_en'] ?? ''));
    
    if (strpos($texto_busqueda, 'mes') !== false || strpos($texto_busqueda, 'suscrip') !== false || strpos($texto_busqueda, 'month') !== false || strpos($texto_busqueda, 'sub') !== false) {
        $suscripciones[] = $prod; 
    } else {
        $juegos[] = $prod; 
    }
}

$estaLogueado = isset($_SESSION['usuario']) ? 'true' : 'false';
?>

<main style="flex-grow: 1; width: 100%;">

    <div class="hero-titulo">
        <h1><?php echo t('titulos.principal'); ?></h1>
        <p><?php echo t('titulos.subprincipal'); ?></p>
    </div>

    <div class="contenedor-carrusel">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="img/WoW_Classic_logo.jpg" alt="Banner 1"></div>
                <div class="swiper-slide"><img src="img/WoW_Tbc_logo.png" alt="Banner 2"></div>
                <div class="swiper-slide"><img src="img/WoW_LK_logo.jpg" alt="Banner 3"></div>
                <div class="swiper-slide"><img src="img/WoW_Cata_logo.png" alt="Banner 4"></div>
                <div class="swiper-slide"><img src="img/WoW_Pandaria_logo.jpg" alt="Banner 5"></div>
                <div class="swiper-slide"><img src="img/WoW_Draenor_logo.webp" alt="Banner 6"></div>
                <div class="swiper-slide"><img src="img/WoW_Legion_logo.jpg" alt="Banner 7"></div>
                <div class="swiper-slide"><img src="img/WoW_Bfa_logo.png" alt="Banner 8"></div>
                <div class="swiper-slide"><img src="img/WoW_ShadowLands_logo.png" alt="Banner 9"></div>
                <div class="swiper-slide"><img src="img/WoW_DF_logo.jpg" alt="Banner 10"></div>
                <div class="swiper-slide"><img src="img/WoW_WW_logo.avif" alt="Banner 11"></div>
                <div class="swiper-slide"><img src="img/WoW_MN_logo.webp" alt="Banner 12"></div>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <?php if (isset($_SESSION['usuario'])): ?>
        <div class="bienvenida">
            <?php echo t('mensajes.bienvenido'); ?> <strong><?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?></strong>!
        </div>
    <?php endif; ?>

    <div class="buscador">
        <input type="text" id="buscar" placeholder="<?php echo t('productos.buscar'); ?>" onkeyup="filtrarProductos()">
    </div>

    <div class="header-seccion">
        <h2 class="titulo-seccion"><?php echo t('titulos.juegos'); ?></h2>
        <button class="btn-ordenar" data-target="grid-juegos" data-orden="asc" onclick="ordenarProductos(this)">
            <span>A-Z</span>
            <svg class="icono-orden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 8 4-4 4 4"/><path d="M7 4v16"/><path d="M20 8h-5"/><path d="M15 10V6.5a2.5 2.5 0 0 1 5 0V10"/><path d="M15 14h5l-5 6h5"/>
            </svg>
        </button>
    </div>

    <div class="product-grid" id="grid-juegos">
        <?php 
        // Empiezo a dibujar las tarjetas de los juegos, calculando sus descuentos y estrellas de valoración
        foreach ($juegos as $prod): 
            $media = (float)$prod['media'];
            $mediaTxt = $media > 0 ? '(' . number_format($media, 1) . ')' : '';
            $totalTxt = $prod['total'] > 0 ? $prod['total'] . ' Val.' : '0 Val.';
            $claseVotado = $prod['ya_voto'] > 0 ? 'votado' : '';
            $cursorEstrellas = $prod['ya_voto'] > 0 ? 'default' : 'pointer';
            
            $precioBase = (float)$prod['precio'];
            $descuento = (float)$prod['descuento'];
            $precioFinal = $descuento > 0 ? $precioBase - ($precioBase * ($descuento / 100)) : $precioBase;
        ?>
            <div class="product-card">
                <?php if (!empty($prod['descuento']) && $prod['descuento'] > 0): ?>
                    <div class="badge-descuento">-<?php echo $prod['descuento']; ?>%</div>
                <?php endif; ?>

                <img src="<?php echo $prod['imagen']; ?>" alt="<?php echo htmlspecialchars($prod['nombre_mostrar']); ?>">
                <h3><?php echo $prod['nombre_mostrar']; ?></h3>
                
                <div class="valoracion-box <?php echo $claseVotado; ?>" data-id="<?php echo $prod['id']; ?>" data-tipo="juego" data-logueado="<?php echo $estaLogueado; ?>">
                    <span class="estrellas-interactivas" style="cursor: <?php echo $cursorEstrellas; ?>;">
                        <?php 
                        for($i=1; $i<=5; $i++): 
                            if ($media >= $i): ?>
                                <i class="fa-solid fa-star star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php elseif ($media >= ($i - 0.5)): ?>
                                <i class="fa-solid fa-star-half-stroke star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php endif; 
                        endfor; 
                        ?>
                    </span>
                    <span class="media-votos" style="color: var(--texto-principal); font-weight: bold; font-size: 14px;"><?php echo $mediaTxt; ?></span>
                    <span class="total-votos" style="font-size: 12px; color: var(--texto-secundario);"><?php echo $totalTxt; ?></span>
                </div>
                
                <div class="card-footer">
                    <span class="precio" style="display: flex; align-items: baseline;">
                        <?php 
                        if ($descuento > 0) {
                            echo '<span class="precio-original-tachado">' . number_format($precioBase, 2) . ' €</span>';
                            echo '<span class="precio-rebajado">' . number_format($precioFinal, 2) . ' €</span>';
                        } else {
                            echo number_format($precioBase, 2) . ' €';
                        }
                        ?>
                    </span>
                    <div class="botones-derecha">
                        <button class="btn-favorito" data-id="<?php echo $prod['id']; ?>" onclick="toggleFavorito(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                        <button class="btn-comprar" 
                                data-id="<?php echo $prod['id']; ?>" 
                                data-nombre="<?php echo htmlspecialchars($prod['nombre_mostrar'], ENT_QUOTES); ?>"
                                data-precio="<?php echo number_format($precioFinal, 2, '.', ''); ?>"
                                data-imagen="<?php echo htmlspecialchars($prod['imagen'], ENT_QUOTES); ?>"
                                data-precio-original="<?php echo number_format($precioBase, 2, '.', ''); ?>"
                                data-descuento="<?php echo number_format($descuento, 2, '.', ''); ?>"
                                title="<?php echo t('productos.añadir'); ?>">
                            <svg class="icono-carrito" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="header-seccion">
        <h2 class="titulo-seccion"><?php echo t('titulos.mensualidades'); ?></h2>
        <button class="btn-ordenar" data-target="grid-mensualidades" data-orden="asc" onclick="ordenarProductos(this)">
            <span>A-Z</span>
            <svg class="icono-orden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 8 4-4 4 4"/><path d="M7 4v16"/><path d="M20 8h-5"/><path d="M15 10V6.5a2.5 2.5 0 0 1 5 0V10"/><path d="M15 14h5l-5 6h5"/>
            </svg>
        </button>
    </div>

    <div class="product-grid" id="grid-mensualidades">
        <?php 
        // Hago exactamente lo mismo de antes, pero ahora dibujo las tarjetas de las suscripciones
        foreach ($suscripciones as $prod): 
            $media = (float)$prod['media'];
            $mediaTxt = $media > 0 ? '(' . number_format($media, 1) . ')' : '';
            $totalTxt = $prod['total'] > 0 ? $prod['total'] . ' Val.' : '0 Val.';
            $claseVotado = $prod['ya_voto'] > 0 ? 'votado' : '';
            $cursorEstrellas = $prod['ya_voto'] > 0 ? 'default' : 'pointer';
            
            $precioBase = (float)$prod['precio'];
            $descuento = (float)$prod['descuento'];
            $precioFinal = $descuento > 0 ? $precioBase - ($precioBase * ($descuento / 100)) : $precioBase;
        ?>
            <div class="product-card">
                <?php if (!empty($prod['descuento']) && $prod['descuento'] > 0): ?>
                    <div class="badge-descuento">-<?php echo $prod['descuento']; ?>%</div>
                <?php endif; ?>

                <img src="<?php echo $prod['imagen']; ?>" alt="<?php echo htmlspecialchars($prod['nombre_mostrar']); ?>">
                <h3><?php echo $prod['nombre_mostrar']; ?></h3>
                
                <div class="valoracion-box <?php echo $claseVotado; ?>" data-id="<?php echo $prod['id']; ?>" data-tipo="suscripcion" data-logueado="<?php echo $estaLogueado; ?>">
                    <span class="estrellas-interactivas" style="cursor: <?php echo $cursorEstrellas; ?>;">
                        <?php 
                        for($i=1; $i<=5; $i++): 
                            if ($media >= $i): ?>
                                <i class="fa-solid fa-star star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php elseif ($media >= ($i - 0.5)): ?>
                                <i class="fa-solid fa-star-half-stroke star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star star-btn" data-value="<?php echo $i; ?>" style="color: gold; transition: transform 0.1s;"></i>
                            <?php endif; 
                        endfor; 
                        ?>
                    </span>
                    <span class="media-votos" style="color: var(--texto-principal); font-weight: bold; font-size: 14px;"><?php echo $mediaTxt; ?></span>
                    <span class="total-votos" style="font-size: 12px; color: var(--texto-secundario);"><?php echo $totalTxt; ?></span>
                </div>
                
                <div class="card-footer">
                    <span class="precio" style="display: flex; align-items: baseline;">
                        <?php 
                        if ($descuento > 0) {
                            echo '<span class="precio-original-tachado">' . number_format($precioBase, 2) . ' €</span>';
                            echo '<span class="precio-rebajado">' . number_format($precioFinal, 2) . ' €</span>';
                        } else {
                            echo number_format($precioBase, 2) . ' €';
                        }
                        ?>
                    </span>
                    <div class="botones-derecha">
                        <button class="btn-favorito" data-id="<?php echo $prod['id']; ?>" onclick="toggleFavorito(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                        <button class="btn-comprar" 
                                data-id="<?php echo $prod['id']; ?>" 
                                data-nombre="<?php echo htmlspecialchars($prod['nombre_mostrar'], ENT_QUOTES); ?>"
                                data-precio="<?php echo number_format($precioFinal, 2, '.', ''); ?>"
                                data-imagen="<?php echo htmlspecialchars($prod['imagen'], ENT_QUOTES); ?>"
                                data-precio-original="<?php echo number_format($precioBase, 2, '.', ''); ?>"
                                data-descuento="<?php echo number_format($descuento, 2, '.', ''); ?>"
                                title="<?php echo t('productos.añadir'); ?>">
                            <svg class="icono-carrito" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php 
// Cargo el pie de página para cerrar la web correctamente.
require_once 'includes/footer.php';
?>