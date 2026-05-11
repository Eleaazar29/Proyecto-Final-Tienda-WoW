<?php
header('Content-Type: application/json');

// Reviso si ya tengo las noticias guardadas de hace menos de 5 minutos para cargar rápido y no saturar el servidor.
$archivo_cache = 'cache_noticias.json';
$tiempo_cache = 300; // 300 segundos = 5 minutos

if (file_exists($archivo_cache) && (time() - filemtime($archivo_cache)) < $tiempo_cache) {
    echo file_get_contents($archivo_cache);
    exit;
}

// Me conecto a la página de Wowhead haciéndome pasar por un navegador real para descargar sus noticias (RSS).
$rss_url = 'https://www.wowhead.com/news/rss/all';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rss_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$xml_data = curl_exec($ch);

// Si la conexión falla, intento mostrar las noticias que tenía guardadas. Si no las tengo, devuelvo un error.
if (!$xml_data) {
    if (file_exists($archivo_cache)) {
        echo file_get_contents($archivo_cache);
        exit;
    }
    $error_real = curl_error($ch);
    echo json_encode(['success' => false, 'error' => 'No se pudo conectar con Wowhead. Detalles: ' . $error_real]);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Intento leer el archivo descargado. Si Wowhead me ha bloqueado por seguridad, cargo mi copia de seguridad o devuelvo un error.
libxml_use_internal_errors(true); 
$rss = simplexml_load_string($xml_data);

if ($rss === false) {
    if (file_exists($archivo_cache)) {
        echo file_get_contents($archivo_cache);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Wowhead está bloqueando la conexión temporalmente (Anti-bots). Inténtalo en unos minutos.']);
    exit;
}

// Preparo mi lista de noticias y muestro 12 para no sobrecargar la web
$noticias = [];
$limite = 12; 
$count = 0;

// Cuento cuántas noticias me ha enviado Wowhead en total para mostrar el dato en la pantalla.
$total_disponibles = count($rss->channel->item);

foreach ($rss->channel->item as $item) {
    if ($count >= $limite) break;
    
    $descripcion_raw = (string)$item->description;
    $descripcion_limpia = strip_tags($descripcion_raw);
    $resumen = mb_substr($descripcion_limpia, 0, 150) . '...';
    
    // Busco la imagen de la noticia rastreando el texto o las etiquetas ocultas. Si no encuentro nada, le pongo mi imagen por defecto.
    $imagen = 'img/login.jpg'; 
    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $descripcion_raw, $matches)) {
        $imagen = $matches[1];
    } else {
        $namespaces = $rss->getNamespaces(true);
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if ($media->content && $media->content->attributes()->url) {
                $imagen = (string)$media->content->attributes()->url;
            }
        }
    }
    
    // Guardo los datos limpios de esta noticia concreta en mi lista.
    $noticias[] = [
        'titulo' => (string)$item->title,
        'link' => (string)$item->link,
        'fecha' => date('d/m/Y', strtotime((string)$item->pubDate)),
        'resumen' => $resumen,
        'imagen' => $imagen 
    ];
    $count++;
}

// Empaqueto la lista final, la guardo en mi caché para la próxima vez y se la envío a mi página.
$respuesta_json = json_encode([
    'success' => true, 
    'data' => $noticias,
    'total_disponibles' => $total_disponibles,
    'mostradas' => count($noticias)
]);

file_put_contents($archivo_cache, $respuesta_json);
echo $respuesta_json;
?>