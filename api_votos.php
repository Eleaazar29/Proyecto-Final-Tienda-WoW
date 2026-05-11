<?php
session_start();
require_once 'config/conexion.php';
header('Content-Type: application/json');

// Compruebo que el usuario haya iniciado sesión. Si no, le corto el paso.
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(["error" => "Debes iniciar sesión para valorar"]);
    exit;
}

// Recojo los datos del producto y la puntuación que me envía el frontend.
$input = json_decode(file_get_contents('php://input'), true);
$id_producto = isset($input['id_producto']) ? (int)$input['id_producto'] : 0;
// Acepto decimales por si me mandan medias estrellas.
$valoracion = isset($input['valoracion']) ? (float)$input['valoracion'] : 0;

// Guardo el nombre del usuario que está votando.
$usuario = $_SESSION['usuario'];

if ($id_producto <= 0 || $valoracion < 1 || $valoracion > 5) {
    http_response_code(400);
    echo json_encode(["error" => "Datos de votación inválidos"]);
    exit;
}

// Reviso en la base de datos si este usuario ya había votado este producto antes.
$stmtCheck = $pdo->prepare("SELECT id FROM votos WHERE usuario = ? AND id_producto = ?");
$stmtCheck->execute([$usuario, $id_producto]);

if ($stmtCheck->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "Ya has valorado este producto"]);
    exit;
}

// Guardo el nuevo voto definitivamente en la base de datos.
$stmtInsert = $pdo->prepare("INSERT INTO votos (usuario, id_producto, valoracion) VALUES (?, ?, ?)");
$stmtInsert->execute([$usuario, $id_producto, $valoracion]);

// Calculo la nueva nota media del producto y cuántos votos tiene en total.
$stmtStats = $pdo->prepare("SELECT AVG(valoracion) as media, COUNT(valoracion) as total FROM votos WHERE id_producto = ?");
$stmtStats->execute([$id_producto]);
$stats = $stmtStats->fetch();
$media = round((float)$stats['media'], 1);
$total = (int)$stats['total'];

// Envío los datos actualizados de vuelta a la página para que cambien las estrellas en pantalla.
http_response_code(200);
echo json_encode([
    "success" => true,
    "media" => $media,
    "total" => $total
]);
?>