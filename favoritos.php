<?php
$titulo_pagina = "Tus Favoritos - Tienda WoW";
require_once 'includes/header.php';
require_once 'config/conexion.php';

// Reviso qué idioma tiene puesto el usuario en su navegador y quién es el que está conectado.
$idioma_real = isset($_COOKIE['idioma']) ? $_COOKIE['idioma'] : 'es';
$usuario_actual = $_SESSION['usuario'] ?? '';

// Preparo una consulta para traer los productos, sus notas medias y saber si el usuario ya los ha votado.
$sql = "SELECT p.id, p.nombre_es, p.nombre_en, p.precio, p.descuento, p.imagen,
               COALESCE(AVG(v.valoracion), 0) as media,
               COUNT(v.valoracion) as total,
               SUM(CASE WHEN v.usuario = :usuario THEN 1 ELSE 0 END) as ya_voto
        FROM productos p
        LEFT JOIN votos v ON p.id = v.id_producto
        GROUP BY p.id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario' => $usuario_actual]);
$productos_bd_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productos_bd = [];
// Recorro la lista de productos para elegir el nombre en español o inglés según el idioma activo.
foreach ($productos_bd_raw as $prod) {
    $prod['nombre'] = ($idioma_real === 'en' && !empty($prod['nombre_en'])) ? $prod['nombre_en'] : $prod['nombre_es'];
    $productos_bd[] = $prod;
}
?>

<script>
    // Paso los datos de mis productos de PHP a JavaScript para poder filtrarlos y ordenarlos en el cliente.
    const productosBD = <?php echo json_encode($productos_bd); ?>;
</script>

<div class="contenido-pagina">
    <div class="header-seccion" style="margin-bottom: 10px;">
        // Muestro el título traducido y el botón para ordenar la lista de la A a la Z.
        <h2 class="titulo-seccion"><?php echo t('nav.favoritos') ?? 'Tus Favoritos'; ?></h2>
        
        <button class="btn-ordenar" data-target="lista-favoritos" data-orden="asc" onclick="ordenarProductos(this)">
            <span>A-Z</span>
            <svg class="icono-orden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 8 4-4 4 4"/><path d="M7 4v16"/><path d="M20 8h-5"/><path d="M15 10V6.5a2.5 2.5 0 0 1 5 0V10"/><path d="M15 14h5l-5 6h5"/>
            </svg>
        </button>
    </div>

    // Aquí es donde mi código JavaScript pintará las tarjetas de los productos que el usuario haya guardado.
    <div id="lista-favoritos" class="product-grid" style="min-height: 50vh;">
        <p style="grid-column: 1/-1; text-align:center; font-size: 1.2em; padding: 40px; color: var(--texto-secundario);">
            <?php echo t('favoritos.cargando') ?? 'Loading your favorite games...'; ?>
        </p>
    </div>
</div>

<?php 
// Cargo el pie de página para cerrar la estructura HTML.
require_once 'includes/footer.php'; 
?>