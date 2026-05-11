<?php
session_start();
require_once 'config/conexion.php'; 
require_once 'includes/i18n.php';

// Si no está logueado, al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$exito = '';
$usuario_actual = $_SESSION['usuario'];

// PROCESAMIENTO REAL DE LOS DATOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recojo los datos del formulario
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    $nueva_pass = $_POST['nueva_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // LÓGICA DE LA FOTO DE PERFIL
    if (isset($_FILES['nuevo_avatar']) && $_FILES['nuevo_avatar']['error'] === UPLOAD_ERR_OK) {
        
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $nombre_archivo = $_FILES['nuevo_avatar']['name'];
        $ext = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $tamano_maximo = 5 * 1024 * 1024;

        if (!in_array($ext, $extensiones_permitidas)) {
            $error = t('ajustes.err_avatar_formato');
        } elseif ($_FILES['nuevo_avatar']['size'] > $tamano_maximo) {
            $error = t('ajustes.err_avatar_tamano');
        } else {
            $nombre_seguro = uniqid('avatar_') . '.' . $ext;
            $ruta_destino = 'img/' . $nombre_seguro;
            
            if (move_uploaded_file($_FILES['nuevo_avatar']['tmp_name'], $ruta_destino)) {
                $_SESSION['avatar'] = $ruta_destino;
            }
        }
    }

    // LÓGICA DEL NOMBRE DE USUARIO
    if (!empty($nuevo_usuario) && $nuevo_usuario !== $usuario_actual) {
        // Compruebo si el nombre ya existe para evitar duplicados
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$nuevo_usuario]);
        
        if ($stmt->rowCount() > 0) {
            $error = t('ajustes.err_user_existe');
        } else {
            // Si está libre, lo actualizo en la base de datos
            $stmt = $pdo->prepare("UPDATE usuarios SET username = ? WHERE username = ?");
            if ($stmt->execute([$nuevo_usuario, $usuario_actual])) {
                $_SESSION['usuario'] = $nuevo_usuario; 
                $usuario_actual = $nuevo_usuario; 
            } else {
                $error = t('ajustes.err_user_update');
            }
        }
    }

    // LÓGICA DE LA CONTRASEÑA
    if (empty($error) && !empty($nueva_pass)) {
        if ($nueva_pass !== $confirm_pass) {
            $error = t('ajustes.err_pass_match');
        } elseif (strlen($nueva_pass) < 4) {
            $error = t('ajustes.err_pass_length');
        } else {
            // Cifro la contraseña  
            $pass_cifrada = password_hash($nueva_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
            if (!$stmt->execute([$pass_cifrada, $usuario_actual])) {
                $error = t('ajustes.err_pass_update');
            }
        }
    }

    if (empty($error) && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
        if (!empty($nuevo_usuario) || !empty($nueva_pass) || (isset($_FILES['nuevo_avatar']) && $_FILES['nuevo_avatar']['error'] === UPLOAD_ERR_OK)) {
            $exito = t('ajustes.msg_exito'); 
        }
    }
}

// Uso la traducción para el título de la página
$titulo_pagina = t('perfil.ajustes') . " - Tienda WoW";
require_once 'includes/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="contenedor-principal" style="flex-grow: 1;">
    <div class="hero-titulo">
        <h1><?php echo t('ajustes.titulo'); ?></h1>
        <p><?php echo t('ajustes.subtitulo'); ?></p>
    </div>

    <div class="preferencias-card" style="width: 100%; max-width: 500px; margin: 40px auto; background: var(--bg-nav); box-shadow: var(--sombra); border: 2px solid var(--color-primario);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <div class="avatar-edit-container" onclick="document.getElementById('input-avatar-oculto').click()" title="Cambiar foto de perfil">
                <img src="<?php echo $_SESSION['avatar'] ?? 'img/horda.png'; ?>" alt="Avatar" class="avatar-ajustes" id="avatar-preview">
                <div class="avatar-overlay">
                    <i class="fa-solid fa-pencil"></i>
                </div>
            </div>
            <h2 style="color: var(--texto-principal); font-size: 24px; text-transform: uppercase;"><?php echo $_SESSION['usuario']; ?></h2>
            <p style="color: var(--texto-secundario); font-size: 14px;"><?php echo t('ajustes.miembro'); ?></p>
        </div>

        <form action="ajustes.php" method="POST" enctype="multipart/form-data">
            
            <input type="file" id="input-avatar-oculto" name="nuevo_avatar" accept="image/*" style="display: none;" onchange="previsualizarImagen(event)">

            <div class="input-group" style="margin-bottom: 20px;">
                <label style="color: var(--texto-secundario); font-size: 14px; margin-bottom: 5px; display: block;"><?php echo t('ajustes.nuevo_usuario'); ?></label>
                <input type="text" name="nuevo_usuario" placeholder="<?php echo t('ajustes.ph_usuario'); ?>" class="input-ajustes">
            </div>

            <div class="input-group" style="margin-bottom: 20px;">
                <label style="color: var(--texto-secundario); font-size: 14px; margin-bottom: 5px; display: block;"><?php echo t('ajustes.password'); ?></label>
                <input type="password" name="nueva_password" placeholder="<?php echo t('ajustes.ph_pass'); ?>" class="input-ajustes">
            </div>

            <div class="input-group" style="margin-bottom: 20px;">
                <label style="color: var(--texto-secundario); font-size: 14px; margin-bottom: 5px; display: block;"><?php echo t('ajustes.confirm_password'); ?></label>
                <input type="password" name="confirm_password" placeholder="<?php echo t('ajustes.ph_pass_conf'); ?>" class="input-ajustes">
            </div>

            <button type="submit" class="btn-blizzard" style="margin-top: 15px;"><?php echo t('ajustes.btn_guardar'); ?></button>
        </form>
    </div>
</main>

<script>
    // Muestro la nueva foto de perfil al instante en pantalla antes de que el usuario le dé a guardar
    function previsualizarImagen(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const imgElement = document.getElementById('avatar-preview');
            imgElement.src = reader.result;
        }
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // Si ha ocurrido algún error al procesar los datos en PHP, lanzo una alerta visual de error.
    <?php if ($error): ?>
        Swal.fire({
            title: '¡Algo ha fallado!',
            text: '<?php echo $error; ?>',
            icon: 'error',
            background: 'var(--bg-nav)',
            color: 'var(--texto-principal)',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar'
        });
    <?php endif; ?>

    // Si todo se ha guardado correctamente, muestro una alerta de éxito y recargo la página para actualizar los datos.
    <?php if ($exito): ?>
        Swal.fire({
            title: '<?php echo t('ajustes.exito_titulo'); ?>',
            text: '<?php echo $exito; ?>',
            icon: 'success',
            background: 'var(--bg-nav)',
            color: 'var(--texto-principal)',
            confirmButtonColor: 'var(--color-primario)',
            confirmButtonText: '<?php echo t('ajustes.aceptar'); ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'ajustes.php';
            }
        });
    <?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>