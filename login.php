<?php
session_start();
require_once 'config/conexion.php';
require_once 'includes/i18n.php';

// Si el usuario ya está logueado, no tiene sentido que esté aquí, así que lo mando a la tienda.
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$exito = '';
$accion = $_POST['accion'] ?? 'login';

// Compruebo si el usuario ha pulsado el botón de enviar algún formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si el formulario que envió fue el de "Registro", proceso sus datos para crear una cuenta nueva.
    if ($accion === 'registro') {
        $nuevo_user = trim($_POST['reg_usuario'] ?? '');
        $nueva_pass = $_POST['reg_password'] ?? '';
        $confirm_pass = $_POST['reg_password_confirm'] ?? '';

        // Hago varias comprobaciones de seguridad: que no deje campos vacíos, que el nombre sea válido y que las contraseñas coincidan y sean largas.
        if (empty($nuevo_user) || empty($nueva_pass)) {
            $error = t('login.err_campos');
        } 
        elseif (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $nuevo_user)) {
            $error = t('login.err_usuario_formato');
        }
        elseif (strlen($nueva_pass) < 4) {
            $error = t('ajustes.err_pass_length');
        } 
        elseif ($nueva_pass !== $confirm_pass) {
            $error = t('ajustes.err_pass_match');
        } 
        else {
            // Compruebo en la base de datos si alguien ya se ha registrado con ese mismo nombre.
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$nuevo_user]);
            
            if ($stmt->rowCount() > 0) {
                $error = t('login.err_usuario_uso');
            } else {
                // Si todo está correcto, cifro la contraseña y guardo al nuevo usuario en la base de datos con rol normal ('user').
                $pass_cifrada = password_hash($nueva_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, 'user')");
                if ($stmt->execute([$nuevo_user, $pass_cifrada])) {
                    $exito = t('login.registro_ok');
                    $accion = 'login'; 
                } else {
                    $error = t('login.err_registro');
                }
            }
        }
    }

    // Si el formulario que envió fue el de "Login", compruebo sus credenciales.
    if ($accion === 'login' && empty($exito)) { 
        $usuario_form = trim($_POST["usuario"] ?? "");
        $password_form = trim($_POST["password"] ?? "");

        if ($usuario_form === '' || $password_form === '') {
            $error = t('login.err_campos') ?: "Campos obligatorios vacíos.";
        } else {
            // Busco al usuario en la base de datos.
            $sql = "SELECT * FROM usuarios WHERE username = :username LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $usuario_form]);
            $user_db = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifico que el usuario exista y que la contraseña coincida.
            if ($user_db && ($password_form === $user_db['password'] || password_verify($password_form, $user_db['password']))) {            
                
                // Si la contraseña es correcta, le doy acceso y guardo sus datos en la sesión.
                $_SESSION['usuario'] = $user_db['username'];
                $_SESSION['rol'] = $user_db['rol'];

                $avatar_guardado = false; 
                // Reviso si el usuario ha subido una foto de perfil personalizada.
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    
                    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    $nombre_archivo = $_FILES['avatar']['name'];
                    $ext = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                    $tamano_maximo = 2 * 1024 * 1024; // 2 MB

                    // Valido que la imagen sea segura (tamaño y formato correcto) antes de guardarla.
                    if (!in_array($ext, $extensiones_permitidas)) {
                        $error = t('ajustes.err_avatar_formato');
                    } elseif ($_FILES['avatar']['size'] > $tamano_maximo) {
                        $error = t('login.err_avatar_tamano_2mb');
                    } else {
                        $nombre_seguro = uniqid('avatar_') . '.' . $ext;
                        $ruta_destino = 'img/' . $nombre_seguro; 
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_destino)) {
                            $_SESSION['avatar'] = $ruta_destino; 
                            $avatar_guardado = true; 
                        }
                    }
                } 
                
                if (empty($error)) {
                    // Si no subió foto propia o hubo un error, le asigno aleatoriamente el escudo de la Horda o la Alianza.
                    if (!$avatar_guardado) {
                        $escudos = ['img/alianza.png', 'img/horda.png'];
                        $_SESSION['avatar'] = $escudos[array_rand($escudos)]; 
                    }
                    // Le doy la bienvenida y lo mando a la portada de la tienda.
                    header('Location: index.php');
                    exit;
                }

            } else {
                $error = t('login.err_login') ?: t('login.err_login');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('login.titulo') ?: 'Iniciar Sesión'; ?></title>
    <link rel="stylesheet" href="css/variables.css?v=8">
    <link rel="stylesheet" href="css/layout.css?v=8">
    <link rel="stylesheet" href="css/componentes.css?v=8">
    <link rel="stylesheet" href="css/admin.css?v=8">
    <link rel="stylesheet" href="css/paginas.css?v=8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-fullscreen-body">

    <div class="login-fullscreen">
        <div class="blizzard-login-container">
            <img src="img/Logo.png" class="blizzard-logo" alt="Logo">
            
            <h2 class="auth-title" id="titulo-auth"><?php echo t('login.titulo') ?: 'Iniciar Sesión'; ?></h2>

            <?php if ($error): ?>
                <div class="mensaje-alerta error-alerta">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="mensaje-alerta exito-alerta">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $exito; ?>
                </div>
            <?php endif; ?>

            <div id="form-login" class="auth-form <?php echo ($accion == 'registro') ? 'oculto' : ''; ?>">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="login">
                    
                    <div class="input-group">
                        <input type="text" name="usuario" placeholder="<?php echo t('login.usuario') ?: 'Usuario'; ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" name="password" placeholder="<?php echo t('login.password') ?: 'Contraseña'; ?>" required>
                    </div>
                    
                    <div class="input-avatar">
                        <label for="avatar"><strong><?php echo t('login.foto_perfil'); ?></strong></label>
                        <input type="file" name="avatar" id="avatar" accept="image/*">
                    </div>

                    <button type="submit" class="btn-blizzard"><?php echo t('login.entrar'); ?></button>
                </form>

                <div class="auth-links">
                    <p><?php echo t('login.no_cuenta'); ?></p>
                    <button type="button" class="btn-text" onclick="toggleForms('registro')"><?php echo t('login.crear_cuenta'); ?></button>
                </div>
                
                <div class="chuleta-mini">
                    <span><?php echo t('login.test_access'); ?></span>
                </div>
            </div>

            <div id="form-registro" class="auth-form <?php echo ($accion == 'registro') ? '' : 'oculto'; ?>">
                <form method="post">
                    <input type="hidden" name="accion" value="registro">
                    
                    <div class="input-group">
                        <input type="text" name="reg_usuario" placeholder="<?php echo t('login.ph_reg_usuario'); ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" name="reg_password" placeholder="<?php echo t('login.ph_reg_pass'); ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" name="reg_password_confirm" placeholder="<?php echo t('login.ph_reg_pass_conf'); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-blizzard"><?php echo t('login.inscribirse'); ?></button>
                </form>

                <div class="auth-links">
                    <p><?php echo t('login.ya_cuenta'); ?></p>
                    <button type="button" class="btn-text" onclick="toggleForms('login')"><?php echo t('login.volver_login'); ?></button>
                </div>
            </div>

            <a href="index.php" class="volver-tienda"><?php echo t('login.volver'); ?></a>
        </div>
    </div>

    <script>
        // Con esta función escondo el formulario de login y enseño el de registro (y viceversa).
        function toggleForms(target) {
            const formLogin = document.getElementById('form-login');
            const formRegistro = document.getElementById('form-registro');
            const tituloAuth = document.getElementById('titulo-auth');
            
            if (target === 'registro') {
                formLogin.style.opacity = '0';
                setTimeout(() => {
                    formLogin.classList.add('oculto');
                    formRegistro.classList.remove('oculto');
                    tituloAuth.textContent = '<?php echo t('login.crear_cuenta'); ?>'; 
                    setTimeout(() => formRegistro.style.opacity = '1', 50);
                }, 300);
            } else {
                formRegistro.style.opacity = '0';
                setTimeout(() => {
                    formRegistro.classList.add('oculto');
                    formLogin.classList.remove('oculto');
                    tituloAuth.textContent = '<?php echo t('login.titulo'); ?>'; 
                    setTimeout(() => formLogin.style.opacity = '1', 50);
                }, 300);
            }
        }
    </script>
</body>
</html>