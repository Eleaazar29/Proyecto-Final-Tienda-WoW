<footer class="footer">
    <div class="footer-container">
        
        <!-- ENLACES SUPERIORES -->
        <div class="footer-enlaces-wrapper" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div class="footer-nav">
                <a href="index.php"><?php echo t('nav.inicio'); ?></a>
                
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="favoritos.php"><?php echo t('nav.favoritos'); ?></a>
                <?php endif; ?>
                
                <a href="carrito.php"><?php echo t('nav.compras'); ?></a>
                
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="misPedidos.php"><?php echo t('nav.pedidos'); ?></a>
                <?php endif; ?>

                <a href="noticias.php"><?php echo t('nav.noticias'); ?></a>

                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <a href="admin.php"><?php echo t('admin.panel'); ?></a>
                <?php endif; ?>

                <a href="sobre_nosotros.php"><?php echo t('nav.sobre_nosotros'); ?></a>
            </div>

            <div class="footer-asistencia">
                <a href="asistencia.php" class="btn-asistencia-footer">
                    <i class="fa-solid fa-circle-question"></i> <?php echo t('perfil.asistencia'); ?>
                </a>
            </div>
        </div>
        <hr>
        
        <!-- LOGOS DEL FOOTER -->
        <div class="footer-bottom">
            
            <div class="footer-logo">
                <a href="index.php">
                    <img src="img/Logo.png" alt="Logo WoW">
                </a>
            </div>
            
            <div class="footer-legal">
                <p><?php echo t('footer.derechos'); ?></p>
                <p><?php echo t('footer.broma'); ?></p>
            </div>
            
            <div class="footer-social">
                <a href="https://github.com/Eleaazar29/Proyecto-Final-Tienda-WoW.git" target="_blank" title="GitHub">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.03c3.18-.35 6.5-1.5 6.5-7.1a5.25 5.25 0 0 0-1.5-3.8 4.33 4.33 0 0 0 .1-3.8s-1.2-.4-3.9 1.4a13.38 13.38 0 0 0-7 0c-2.7-1.8-3.9-1.4-3.9-1.4a4.33 4.33 0 0 0 .1 3.8 5.25 5.25 0 0 0-1.5 3.8c0 5.6 3.3 6.75 6.5 7.1a4.8 4.8 0 0 0-1 3.03v4"/><path d="M9 20c-5 1.5-5-2.5-7-3"/></svg>
                </a>
                
                <a href="https://www.linkedin.com/jobs/" target="_blank" title="LinkedIn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
            </div>

        </div>
    </div>
</footer>

<!-- CARRITO LATERAL -->
<div id="cart-sidebar" class="cart-sidebar">
    <div class="cart-header">
        <h3><?php echo t('carrito.titulo'); ?></h3>
        <button class="close-cart" onclick="toggleCart()">×</button>
    </div>
    <div id="cart-items" class="cart-items">
        <p class="empty-msg"><?php echo t('carrito.vacio'); ?></p>
    </div>
    <div class="cart-footer">
        <div class="cart-total">
            <span><?php echo t('carrito.total'); ?>:</span>
            <span id="cart-sidebar-total">0.00 €</span>
        </div>
        <button class="btn-vaciar" onclick="vaciarCarritoLateral()"><?php echo t('carrito.vaciar'); ?></button>
        <a href="carrito.php" class="btn-checkout"><?php echo t('carrito.ver_pedido'); ?></a>
    </div>
</div>
<div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>
<div class="cart-floating-btn" onclick="toggleCart()">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
    <span id="cart-count">0</span>
</div>

<!-- SCRIPTS GLOBALES -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="js/main.js"></script> <script src="js/productos.js"></script>
<script src="js/carrito.js"></script>
<script src="js/noticias.js"></script>
<script src="js/tienda.js"></script>

</body>
</html>