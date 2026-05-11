<?php
$titulo_pagina = "Mis Pedidos - Tienda WoW";
require_once 'includes/header.php';
require_once 'config/conexion.php';

// Si el usuario intenta entrar aquí sin haber iniciado sesión, lo mando al login.
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Guardo el nombre del usuario actual para buscar solo sus compras.
$usuario = $_SESSION['usuario'];

// Hago una consulta a la base de datos uniendo tres tablas para traerme los pedidos, los productos comprados y sus fotos.
$stmt = $pdo->prepare("
    SELECT p.id, p.fecha_pedido, p.total, pd.nombre_producto, pd.cantidad, pd.precio_unitario, pr.imagen 
    FROM pedidos p
    JOIN pedidos_detalle pd ON p.id = pd.id_pedido
    LEFT JOIN productos pr ON pd.id_producto = pr.id
    WHERE p.usuario = ?
    ORDER BY p.fecha_pedido DESC
");
$stmt->execute([$usuario]);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reorganizo los datos que me devuelve la base de datos para agrupar los productos dentro de su pedido correspondiente.
$pedidos = [];
foreach ($datos as $fila) {
    $pedidos[$fila['id']]['fecha'] = $fila['fecha_pedido'];
    $pedidos[$fila['id']]['total'] = $fila['total'];
    $pedidos[$fila['id']]['items'][] = $fila;
}
?>

<style>
    .item-clicable {
        cursor: pointer;
        transition: color 0.3s ease;
    }
    .item-clicable:hover {
        color: var(--color-primario);
    }
</style>

<div class="contenido-pagina">
    
    <div class="header-seccion" style="margin-top: 40px; margin-bottom: 20px;">
        <h2 class="titulo-seccion"><?php echo t('nav.pedidos') ?? 'MIS PEDIDOS'; ?></h2>
    </div>

    <div style="width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 20px;">
        
        <?php if (empty($pedidos)): ?>
            <p style="text-align:center; padding: 50px; color: var(--texto-secundario);">
                <?php echo t('historial.vacio') ?? 'Aún no has realizado ninguna compra.'; ?>
            </p>
        <?php else: ?>
            
            <?php foreach ($pedidos as $id => $info): ?>
                <div class="resumen-compra" style="flex-direction: column; align-items: stretch; margin-bottom: 30px; padding: 20px; cursor: default;">
                    
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--borde-color); padding-bottom: 10px; margin-bottom: 15px;">
                        <span style="font-weight: bold; color: var(--color-primario); font-size: 1.2rem;">
                            <?php echo t('historial.numero'); ?><?php echo $id; ?>
                        </span>
                        <span style="color: var(--texto-secundario); font-weight: bold;">
                            <?php echo date('d/m/Y H:i', strtotime($info['fecha'])); ?>
                        </span>
                    </div>
                    
                    <?php foreach ($info['items'] as $item): 
                        $ruta_imagen = !empty($item['imagen']) ? $item['imagen'] : 'img/Logo.png'; 
                    ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px;">
                            <span class="item-clicable" onclick="verImagenProducto('<?php echo htmlspecialchars($ruta_imagen); ?>', '<?php echo htmlspecialchars($item['nombre_producto']); ?>')">
                                <i class="fa-solid fa-gamepad" style="color: var(--texto-secundario); margin-right: 8px;"></i> 
                                <?php echo htmlspecialchars($item['nombre_producto'], ENT_QUOTES, 'UTF-8'); ?>
                                <strong style="color: var(--color-primario);">(x<?php echo $item['cantidad']; ?>)</strong>
                            </span>
                            
                            <span><?php echo number_format($item['precio_unitario'], 2); ?> €/ud</span>
                        </div>
                    <?php endforeach; ?>

                    <div style="text-align: right; margin-top: 15px; font-size: 22px; font-weight: 900; color: var(--color-primario); border-top: 1px dashed var(--borde-color); padding-top: 15px;">
                        <?php echo t('historial.total_pedido') ?? 'TOTAL'; ?>: <?php echo number_format($info['total'], 2); ?> €
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

<script>
// Uso esta función para mostrar una ventana emergente con la carátula del juego cuando hacen clic en su nombre.
function verImagenProducto(url, nombre) {
    Swal.fire({
        title: nombre,
        imageUrl: url,
        imageHeight: 400, 
        imageAlt: nombre,
        background: 'var(--bg-nav)', 
        color: 'var(--texto-principal)',
        confirmButtonColor: 'var(--color-primario)',
        confirmButtonText: '<?php echo t("historial.cerrar"); ?>' 
    });
}
</script>

<?php 
// Cargo el pie de página para cerrar la web.
require_once 'includes/footer.php'; 
?>