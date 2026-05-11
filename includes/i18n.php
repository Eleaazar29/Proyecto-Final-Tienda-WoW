<?php
// Averiguo el idioma actual mirando la cookie 'idioma'
// Si la cookie no existe todavía, uso español por defecto.
$idioma_actual = $_COOKIE['idioma'] ?? 'es';

// Construyo la ruta absoluta al archivo JSON correspondiente
$ruta_json = __DIR__ . "/../i18n/{$idioma_actual}.json";

// Cargo el contenido del archivo JSON
if (file_exists($ruta_json)) {
    $json_data = file_get_contents($ruta_json);
    $traducciones = json_decode($json_data, true);
    
    if (!is_array($traducciones)) {
        $traducciones = [];
    }
} else {
    $traducciones = [];
}

/**
 * Busco y devuelvo textos traducidos desde mis archivos de idioma.
 * * @param string $clave La ruta exacta del texto que quiero buscar (ej: 'nav.inicio').
 * @return string Devuelvo el texto ya traducido, o la clave original si no lo encuentro.
 */
function t($clave) {
    global $traducciones;

    $partes = explode('.', $clave);
    $resultado = $traducciones;
    
    foreach ($partes as $parte) {
        if (isset($resultado[$parte])) {
            $resultado = $resultado[$parte];
        } else {
            return $clave;
        }
    }
    
    return $resultado;
}
?>