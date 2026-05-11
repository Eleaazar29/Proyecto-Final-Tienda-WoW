<?php
session_start();
require_once 'config/conexion.php';
header('Content-Type: application/json');

// Compruebo si el usuario actual es un administrador. Si no lo es, le corto el paso y lanzo un error.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["success" => false, "error" => "No autorizado"]);
    exit;
}

// Recojo los datos que me envía el formulario de administración a través de JavaScript.
$input = json_decode(file_get_contents('php://input'), true);
$accion = $input['accion'] ?? '';

try {
    // Dependiendo de la acción que me hayan pedido, ejecuto una consulta diferente en la base de datos:
    switch ($accion) {
        case 'crear':
            // Añado un nuevo producto a la tienda con todos los datos del formulario.
            $sql = "INSERT INTO productos (nombre_es, nombre_en, precio, descuento, imagen) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$input['nombre_es'], $input['nombre_en'], $input['precio'], $input['descuento'], $input['imagen']]);
            break;

        case 'editar':
            // Busco un producto existente por su ID y actualizo su información.
            $sql = "UPDATE productos SET nombre_es=?, nombre_en=?, precio=?, descuento=?, imagen=? WHERE id=?";
            $pdo->prepare($sql)->execute([$input['nombre_es'], $input['nombre_en'], $input['precio'], $input['descuento'], $input['imagen'], $input['id']]);
            break;

        case 'borrar':
            // Elimino un producto del catálogo usando su ID.
            $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$input['id']]);
            break;

        case 'cambiar_rol':
            // Busco qué rol tiene el usuario actualmente. Si es admin, lo bajo a user; si es user, lo subo a admin.
            $u = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
            $u->execute([$input['id']]);
            $rolActual = $u->fetchColumn();
            $nuevoRol = ($rolActual === 'admin') ? 'user' : 'admin';
            
            $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?")->execute([$nuevoRol, $input['id']]);
            break;

        case 'borrar_usuario':
            // Elimino por completo a un usuario del sistema.
            $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$input['id']]);
            break;

        default:
            // Si llega una acción que no tengo programada, provoco un error.
            throw new Exception("Acción no válida");
    }

    // Si el bloque anterior termina sin problemas, devuelvo una respuesta de éxito.
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    // Si la base de datos falla o hay algún problema, lo atrapo y devuelvo el mensaje de error.
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>