<?php
// Recupero o genero la sesión
session_start();

// Destruyo o elimino la sesión
session_destroy();

// Redirijo al usuario al index
header("Location: index.php");

// Detengo la ejecución del codigo despues de la redirección
exit;
?>
