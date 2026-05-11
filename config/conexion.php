<?php
$host = 'localhost';     
$db   = 'tienda_wow';    
$user = 'root';         
$pass = '';              

try {
    // Creo la conexión
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Configuro PDO para que nos avise si hay algún error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>