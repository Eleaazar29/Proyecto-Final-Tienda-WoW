<?php
session_start();
require_once 'config/conexion.php';
header('Content-Type: application/json');

// Compruebo si el usuario ha iniciado sesión. Si no, rechazo la compra de inmediato.
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "No logueado"]);
    exit;
}

// Recojo los datos del carrito que me envía JavaScript. 
// Por seguridad, de aquí solo me fiaré del ID del producto y la cantidad, nunca del precio que venga del navegador.
$input = json_decode(file_get_contents('php://input'), true);
$carrito = $input['carrito']; 
$usuario = $_SESSION['usuario'];

try {
    // Inicio una transacción: si algo falla a medias, deshago todo para no dejar compras a medio guardar.
    $pdo->beginTransaction();

    // 1. Calculo el precio total real consultando directamente mi base de datos para evitar que modifiquen el precio.
    $total_real_calculado = 0;
    $items_procesados = [];

    foreach ($carrito as $item) {
        // Busco el precio original y el descuento de cada producto en mi base de datos.
        $stmtPrecio = $pdo->prepare("SELECT nombre_es, precio, descuento FROM productos WHERE id = ?");
        $stmtPrecio->execute([$item['id']]);
        $productoDB = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

        if ($productoDB) {
            $precioBase = (float)$productoDB['precio'];
            $descuento = (float)$productoDB['descuento'];
            
            // Aplico el descuento si el producto lo tiene.
            $precioFinal = $descuento > 0 ? $precioBase - ($precioBase * ($descuento / 100)) : $precioBase;
            
            // Calculo el subtotal multiplicando el precio final por la cantidad comprada.
            $subtotal = $precioFinal * (int)$item['cantidad'];
            $total_real_calculado += $subtotal;

            // Guardo estos datos limpios y seguros para usarlos en el paso de insertar.
            $items_procesados[] = [
                'id' => $item['id'],
                'nombre' => $productoDB['nombre_es'],
                'cantidad' => (int)$item['cantidad'],
                'precio' => $precioFinal
            ];
        }
    }

    // 2. Guardo el pedido principal en la base de datos usando el total.
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario, total) VALUES (?, ?)");
    $stmt->execute([$usuario, $total_real_calculado]);
    $id_pedido = $pdo->lastInsertId();

    // 3. Guardo cada uno de los productos dentro del ticket (pedido) usando los precios verificados.
    $stmtDetalle = $pdo->prepare("INSERT INTO pedidos_detalle (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($items_procesados as $item_seguro) {
        $stmtDetalle->execute([
            $id_pedido, 
            $item_seguro['id'], 
            $item_seguro['nombre'], 
            $item_seguro['cantidad'], 
            $item_seguro['precio']
        ]);
    }

    // Confirmo y guardo todos los cambios en la base de datos definitivamente.
    $pdo->commit();
    echo json_encode(["success" => true, "id_pedido" => $id_pedido, "total_cobrado" => $total_real_calculado]);

} catch (Exception $e) {
    // Si ha habido algún error en el proceso, cancelo toda la operación para que no haya cobros fantasma ni errores.
    $pdo->rollBack();
    echo json_encode(["success" => false, "error" => "Error interno del servidor"]);
}
?>