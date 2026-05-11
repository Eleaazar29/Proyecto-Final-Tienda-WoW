<?php
$titulo_pagina = "Noticias de Azeroth - Tienda WoW";
require_once 'includes/header.php';
?>

<main style="flex-grow: 1; width: 100%;">
    <div class="hero-titulo">
        <h1><?php echo t('nav.noticias'); ?></h1>
        <p><?php echo t('noticias.subtitulo'); ?></p>
    </div>

    <div class="contenedor-principal">
        <div id="contenedor-noticias" class="noticias-grid">
            <p style="text-align: center; width: 100%; color: var(--texto-secundario);">
                <i class="fa-solid fa-spinner fa-spin"></i> <?php echo t('noticias.conectando'); ?>
            </p>
        </div>
    </div>
</main>

<script src="js/noticias.js?v=8"></script>

<?php 
require_once 'includes/footer.php';
?>