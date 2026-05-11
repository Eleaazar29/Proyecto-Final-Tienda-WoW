<?php
// Iniciamos la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CargO el motor de idiomas
require_once __DIR__ . '/i18n.php';

// CONFIGURACIÓN DEL TEMA (CLARO / OSCURO)
// Leo la cookie. Si no existe, por defecto uso el 'oscuro'
$tema_cookie = $_COOKIE['tema'] ?? 'oscuro';

// Si la cookie dice 'claro', le pongo la clase que cree en el CSS
$clase_body = ($tema_cookie === 'claro') ? 'modo-claro' : '';

$titulo = $titulo_pagina ?? "Tienda Online WoW";
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">

    <title><?php echo $titulo; ?></title>
    
    <link rel="stylesheet" href="css/variables.css?v=7">
    <link rel="stylesheet" href="css/layout.css?v=7">
    <link rel="stylesheet" href="css/componentes.css?v=7">
    <link rel="stylesheet" href="css/admin.css?v=7">
    <link rel="stylesheet" href="css/paginas.css?v=7">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        const usuarioLogueado = <?php echo isset($_SESSION['usuario']) ? 'true' : 'false'; ?>;
        const i18n_lang = "<?php echo $idioma_actual; ?>";
        const i18n_dict = <?php echo json_encode($traducciones); ?>;
    </script>
</head>
<body class="<?php echo $clase_body; ?>">

<nav>
    <div class="nav-izquierda">
        <div id="menu">
            <a href="index.php"><img src="img/Logo.png" alt="Logo" id="logo"></a>
        </div>

        <div id="opciones">
            <ul>
                <li><a href="index.php"><?php echo t('nav.inicio'); ?></a></li>
                
                <?php if (isset($_SESSION['usuario'])): ?>
                    <li><a href="favoritos.php"><?php echo t('nav.favoritos'); ?></a></li>
                <?php endif; ?>
                
                <li><a href="carrito.php"><?php echo t('nav.compras'); ?></a></li>
                
                <?php if (isset($_SESSION['usuario'])): ?>
                    <li><a href="misPedidos.php"><?php echo t('nav.pedidos'); ?></a></li>
                <?php endif; ?>

                <li><a href="noticias.php"><?php echo t('nav.noticias'); ?></a></li>

                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <li><a href="admin.php"><?php echo t('admin.panel'); ?></a></li>
                <?php endif; ?>

                <li><a href="sobre_nosotros.php"><?php echo t('nav.sobre_nosotros'); ?></a></li>
            </ul>
        </div>
    </div>

    <div class="nav-buttons">   
        
        <div class="idioma-wrapper" id="custom-select-idioma">
            
            <div class="idioma-trigger" onclick="toggleDropdownIdioma()">
                <svg class="icono-mundo" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>

                <span id="idioma-texto">
                    <?php echo (isset($idioma_actual) && $idioma_actual == 'en') ? 'English' : 'Español'; ?>
                </span>

                <svg class="icono-flecha" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>

            <ul class="idioma-opciones" id="idioma-opciones">
                <li onclick="cambiarIdioma('es')" style="display: flex; justify-content: space-between; align-items: center;">
                    Español
                    <?php if ($idioma_actual === 'es'): ?>
                        <i class="fa-solid fa-check" style="font-size: 12px; margin-left: 10px; color: var(--color-primario);"></i>
                    <?php endif; ?>
                </li>
                <li onclick="cambiarIdioma('en')" style="display: flex; justify-content: space-between; align-items: center;">
                    English
                    <?php if ($idioma_actual === 'en'): ?>
                        <i class="fa-solid fa-check" style="font-size: 12px; margin-left: 10px; color: var(--color-primario);"></i>
                    <?php endif; ?>
                </li>
            </ul>
        </div>

        <button id="btn-tema" onclick="toggleTema()" title="<?php echo t('nav.tema'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icono-tema">
                <path id="svg-path-tema" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>

        <?php if (isset($_SESSION['usuario'])): ?>
            <div class="perfil-wrapper" id="custom-select-perfil">
                
                <div class="user-profile-menu" onclick="toggleDropdownPerfil()">
                    <img src="<?php echo $_SESSION['avatar'] ?? 'img/horda.png'; ?>" alt="Avatar" class="nav-avatar-img">
                    <span class="nav-username"><?php echo $_SESSION['usuario']; ?></span>
                    <svg class="icono-flecha-perfil" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 5px;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                <ul class="perfil-opciones" id="perfil-opciones">
                    <li>
                        <a href="ajustes.php">
                            <i class="fa-solid fa-gear"></i> <?php echo t('perfil.ajustes'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="asistencia.php">
                            <i class="fa-solid fa-circle-question"></i> <?php echo t('perfil.asistencia'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <a class="nav-action-btn btn-logout-outline" href="logout.php">
                <i class="fa-solid fa-sign-out-alt"></i> <?php echo t('nav.logout'); ?>
            </a>
        <?php else: ?>
            <a class="nav-action-btn btn-login-outline" href="login.php">
                <i class="fa-solid fa-user"></i> <?php echo t('nav.login'); ?>
            </a>
        <?php endif; ?>
        
    </div>
</nav>

<script>
    function toggleDropdownPerfil() {
        document.getElementById("perfil-opciones").classList.toggle("show");
        document.getElementById("custom-select-perfil").classList.toggle("open");
    }

    // Cierra el menú automáticamente si haces click en cualquier otro lado de la pantalla
    window.addEventListener('click', function(e) {
        const perfilWrapper = document.getElementById('custom-select-perfil');
        if (perfilWrapper && !perfilWrapper.contains(e.target)) {
            document.getElementById("perfil-opciones").classList.remove("show");
            perfilWrapper.classList.remove("open");
        }
    });
</script>

<button id="btn-subir" onclick="subirArriba()" title="<?php echo t('common.volver_arriba'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5"></line>
        <polyline points="5 12 12 5 19 12"></polyline>
    </svg>
</button>