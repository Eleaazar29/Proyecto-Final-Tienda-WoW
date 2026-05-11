<?php
$titulo_pagina = "Sobre Nosotros - Tienda WoW";
require_once 'includes/header.php';
?>

<div class="contenido-pagina">
    
    <div class="contenedor-principal" style="margin-top: 50px; text-align: center;">
        <h2 class="titulo-seccion" style="margin-bottom: 20px; text-align: center; justify-content: center;">
            <?php echo t('sobre_nosotros.titulo'); ?>
        </h2>
        
        <div class="preferencias-card" style="width: 100%; max-width: 800px; margin: 0 auto 50px auto; padding: 40px; line-height: 1.8; border: 3px solid var(--color-primario);">
            <p style="font-size: 18px; color: var(--texto-principal);">
                <?php echo t('sobre_nosotros.historia'); ?>
            </p>
        </div>
    </div>

    <div class="valores-fondo-nav">
        <div class="valores-container">
            <div class="valores-subtitulo"><?php echo t('valores.subtitulo'); ?></div>
            <h2 class="valores-titulo"><?php echo t('valores.titulo'); ?></h2>
            <p class="valores-desc"><?php echo t('valores.descripcion'); ?></p>

            <div class="valores-grid">
                
                <div class="valor-card">
                    <div class="valor-bg bg-verde"></div>
                    <div class="valor-img" style="background-image: url('img/goblin.png');"></div>
                    <div class="valor-estandarte"><i class="fa-solid fa-coins"></i></div>
                    <h3><?php echo t('valores.v1'); ?></h3> 
                </div>
                
                <div class="valor-card">
                    <div class="valor-bg bg-naranja"></div>
                    <div class="valor-img" style="background-image: url('img/katy_estampilla.png');"></div>
                    <div class="valor-estandarte"><i class="fa-solid fa-bolt"></i></div>
                    <h3><?php echo t('valores.v2'); ?></h3> 
                </div>
                
                <div class="valor-card">
                    <div class="valor-bg bg-oro"></div>
                    <div class="valor-img" style="background-image: url('img/uther.png');"></div>
                    <div class="valor-estandarte"><i class="fa-solid fa-headset"></i></div>
                    <h3><?php echo t('valores.v3'); ?></h3> 
                </div>
                
                <div class="valor-card">
                    <div class="valor-bg bg-morado"></div>
                    <div class="valor-img" style="background-image: url('img/etereo.webp');"></div>
                    <div class="valor-estandarte"><i class="fa-solid fa-users"></i></div>
                    <h3><?php echo t('valores.v4'); ?></h3> 
                </div>
                
                <div class="valor-card">
                    <div class="valor-bg bg-magenta"></div>
                    <div class="valor-img" style="background-image: url('img/guardias.png');"></div>
                    <div class="valor-estandarte"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3><?php echo t('valores.v5'); ?></h3> 
                </div>

            </div>
        </div>
    </div>

    <div id="contenedor-mapa" style="width: 100%; max-width: 1100px; margin-bottom: 60px;">
        <h2 class="titulo-seccion" style="text-align: center; margin-bottom: 20px; justify-content: center;">
            <?php echo t('mensajes.geo_titulo'); ?>
        </h2>
        <div id="mapa" style="height: 450px;"></div>
    </div>
    
</div>

<?php 
// Cargo el pie de página para cerrar la web correctamente.
require_once 'includes/footer.php'; 
?>