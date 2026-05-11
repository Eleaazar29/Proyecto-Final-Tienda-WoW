<?php
session_start();
require_once 'config/conexion.php';

// Compruebo si el usuario ha iniciado sesión. Si no, lo envío directamente a la página de login.
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$exito = '';

// Si el usuario me envía el formulario, proceso los datos del ticket.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recojo y limpio los datos que me han escrito para evitar que me inyecten código malicioso.
    $categoria = htmlspecialchars(trim($_POST['categoria'] ?? ''), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8');
    $usuario_actual = $_SESSION['usuario'];

    // Verifico que no se hayan dejado ningún campo importante en blanco.
    if (empty($categoria) || empty($descripcion)) {
        $error = t('asistencia.err_campos');
    } else {
        // Guardo el nuevo ticket de soporte en la base de datos.
        $stmt = $pdo->prepare("INSERT INTO tickets (usuario, categoria, descripcion) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$usuario_actual, $categoria, $descripcion])) {
            $exito = t('asistencia.exito_envio');
        } else {
            $error = t('asistencia.err_envio');
        }
    }
}

$titulo_pagina = "Centro de Asistencia - Tienda WoW";
require_once 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="contenedor-principal" style="flex-grow: 1;">
    <div class="hero-titulo">
        <h1><?php echo t('asistencia.titulo'); ?></h1>
        <p><?php echo t('asistencia.subtitulo'); ?></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 50px;">
        
        <div class="noticia-card" style="padding: 30px; text-align: center; justify-content: center; align-items: center;">
            <i class="fa-solid fa-key" style="font-size: 45px; color: var(--color-primario); margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 10px;"><?php echo t('asistencia.card1_titulo'); ?></h3>
            <p style="text-align: center; margin-bottom: 0;"><?php echo t('asistencia.card1_texto'); ?></p>
        </div>

        <div class="noticia-card" style="padding: 30px; text-align: center; justify-content: center; align-items: center;">
            <i class="fa-solid fa-credit-card" style="font-size: 45px; color: var(--color-primario); margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 10px;"><?php echo t('asistencia.card2_titulo'); ?></h3>
            <p style="text-align: center; margin-bottom: 0;"><?php echo t('asistencia.card2_texto'); ?></p>
        </div>

        <div class="noticia-card" style="padding: 30px; text-align: center; justify-content: center; align-items: center;">
            <i class="fa-solid fa-shield-halved" style="font-size: 45px; color: var(--color-primario); margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 10px;"><?php echo t('asistencia.card3_titulo'); ?></h3>
            <p style="text-align: center; margin-bottom: 0;"><?php echo t('asistencia.card3_texto'); ?></p>
        </div>

    </div>

    <div class="preferencias-card" style="width: 100%; max-width: 700px; margin: 60px auto; background: var(--bg-nav); border: 2px solid var(--color-primario);">
        <h2 style="color: var(--texto-principal); text-align: center; margin-bottom: 25px;">
            <i class="fa-solid fa-headset" style="color: var(--color-primario);"></i> <?php echo t('asistencia.abrir_ticket'); ?>
        </h2>
        
        <form action="asistencia.php" method="POST">
            
            <div class="input-group" style="margin-bottom: 20px;">
                <label style="color: var(--texto-secundario); font-size: 14px; margin-bottom: 5px; display: block;"><?php echo t('asistencia.problema'); ?></label>
                <select name="categoria" class="input-ajustes" required>
                    <option value="" disabled selected><?php echo t('asistencia.selecciona_categoria'); ?></option>
                    <option value="<?php echo t('asistencia.cat1'); ?>"><?php echo t('asistencia.cat1'); ?></option>
                    <option value="<?php echo t('asistencia.cat2'); ?>"><?php echo t('asistencia.cat2'); ?></option>
                    <option value="<?php echo t('asistencia.cat3'); ?>"><?php echo t('asistencia.cat3'); ?></option>
                    <option value="<?php echo t('asistencia.cat4'); ?>"><?php echo t('asistencia.cat4'); ?></option>
                </select>
            </div>

            <div class="input-group" style="margin-bottom: 20px;">
                <label style="color: var(--texto-secundario); font-size: 14px; margin-bottom: 5px; display: block;"><?php echo t('asistencia.descripcion'); ?></label>
                <textarea name="descripcion" rows="6" class="input-ajustes" placeholder="<?php echo t('asistencia.ph_desc'); ?>" style="resize: vertical;" required></textarea>
            </div>

            <button type="submit" class="btn-blizzard" style="margin-top: 10px; font-size: 18px;">
                <i class="fa-solid fa-paper-plane"></i> <?php echo t('asistencia.enviar'); ?>
            </button>
        </form>
    </div>
</main>

<script>
    // Lanzo una alerta visual si ha ocurrido algún error al validar el ticket en PHP.
    <?php if ($error): ?>
        Swal.fire({
            title: '<?php echo t('mensajes.atencion'); ?>',
            text: '<?php echo $error; ?>',
            icon: 'error',
            background: 'var(--bg-nav)',
            color: 'var(--texto-principal)',
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<?php echo t('common.revisar'); ?>'
        });
    <?php endif; ?>

    // Si todo ha ido bien, muestro el éxito y recargo la página limpia.
    <?php if ($exito): ?>
        Swal.fire({
            title: '<?php echo t('asistencia.ticket_enviado'); ?>',
            text: '<?php echo $exito; ?>',
            icon: 'success',
            background: 'var(--bg-nav)',
            color: 'var(--texto-principal)',
            confirmButtonColor: 'var(--color-primario)',
            confirmButtonText: '<?php echo t('ajustes.aceptar'); ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirijo a la misma página para vaciar el formulario y evitar reenvíos al pulsar F5.
                window.location.href = 'asistencia.php';
            }
        });
    <?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>