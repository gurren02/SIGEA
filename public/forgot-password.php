<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

// Si es una petición GET directa, limpiamos la sesión de recuperación por completo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET['resend'])) {
    unset($_SESSION['recovery']);
}

$step    = $_SESSION['recovery']['step'] ?? 1;
$email   = $_SESSION['recovery']['email'] ?? '';
$code    = $_SESSION['recovery']['code'] ?? '';
$expires = $_SESSION['recovery']['expires'] ?? 0;

if (is_post()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_code') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            flash('error', 'Por favor ingresa tu correo electrónico.');
        } else {
            $user = User::findByEmail($email);
            if ($user) {
                // Generar código numérico de 6 dígitos
                $generatedCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = time() + (15 * 60); // 15 minutos de validez
                
                $db = Database::get();
                // Limpiar base de datos
                $stmt = $db->prepare('DELETE FROM password_resets WHERE email = ?');
                $stmt->execute([$email]);
                
                // Guardar en base de datos
                $stmt = $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
                $stmt->execute([$email, $generatedCode, date('Y-m-d H:i:s', $expiresAt)]);
                
                // Guardar en sesión
                $_SESSION['recovery'] = [
                    'step' => 2,
                    'email' => $email,
                    'code' => $generatedCode,
                    'expires' => $expiresAt
                ];
                
                // Enviar código por correo
                if (Mailer::sendVerificationCode($email, $user['name'], $generatedCode)) {
                    flash('success', 'Hemos enviado un código de verificación a tu correo electrónico.');
                    $step = 2;
                } else {
                    flash('error', 'Ocurrió un error al enviar el correo. Por favor inténtalo de nuevo.');
                }
            } else {
                // Para evitar enumeración, simulamos el paso 2 pero con código inválido
                $_SESSION['recovery'] = [
                    'step' => 2,
                    'email' => $email,
                    'code' => 'dummy-code-123',
                    'expires' => time() + 300
                ];
                flash('success', 'Si el correo electrónico está registrado, recibirás un código de verificación pronto.');
                $step = 2;
            }
        }
    } 
    elseif ($action === 'verify_code') {
        $enteredCode = trim($_POST['verification_code'] ?? '');
        
        if (empty($enteredCode)) {
            flash('error', 'Por favor ingresa el código de verificación.');
        } elseif (time() > $expires) {
            flash('error', 'El código de verificación ha expirado. Por favor, solicita uno nuevo.');
            unset($_SESSION['recovery']);
            $step = 1;
        } else {
            // Verificar contra la base de datos o sesión
            $db = Database::get();
            $stmt = $db->prepare('SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() LIMIT 1');
            $stmt->execute([$email, $enteredCode]);
            $resetRecord = $stmt->fetch();

            if ($resetRecord || ($code !== 'dummy-code-123' && $enteredCode === $code)) {
                $_SESSION['recovery']['step'] = 3;
                $step = 3;
                flash('success', 'Código verificado con éxito. Ingresa tu nueva contraseña.');
            } else {
                flash('error', 'El código de verificación es incorrecto.');
            }
        }
    } 
    elseif ($action === 'reset_password') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            flash('error', 'Las contraseñas no coinciden.');
        } elseif (!password_is_valid($password)) {
            flash('error', 'La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
        } else {
            $user = User::findByEmail($email);
            if ($user) {
                User::updatePassword($user['id'], $password);
                
                // Limpiar base de datos y sesión
                $db = Database::get();
                $stmt = $db->prepare('DELETE FROM password_resets WHERE email = ?');
                $stmt->execute([$email]);
                
                unset($_SESSION['recovery']);
                
                flash('success', 'Tu contraseña ha sido restablecida con éxito. Ya puedes iniciar sesión.');
                redirect('/login.php');
            } else {
                flash('error', 'Error al procesar la solicitud.');
                unset($_SESSION['recovery']);
                $step = 1;
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIGEA – Recupera el acceso a tu cuenta.">
    <title>Recuperar contrasena | SIGEA</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-body">

    <?php if ($step === 1): ?>
        <!-- Paso 1: Ingreso de correo -->
        <form class="auth-card" method="post">
            <input type="hidden" name="action" value="send_code">
            <div class="brand-inline">
                <img class="brand-logo" src="/logo.png" alt="SIGEA Logo">
                <div>
                    <h1>Recuperar contrasena</h1>
                    <p>Te enviaremos un codigo de verificacion</p>
                </div>
            </div>
            
            <?php render('flash'); ?>

            <label>Correo electronico
                <input type="email" name="email" required placeholder="usuario@institucion.edu" value="<?= e($email) ?>">
            </label>
            <button class="button button-primary full" type="submit" style="margin-top:8px;">
                <span class="material-symbols-rounded">send</span>
                Enviar codigo
            </button>
            <p style="text-align:center;margin:18px 0 0;">
                <a class="back-link" href="/login.php">
                    <span class="material-symbols-rounded" style="font-size:14px;vertical-align:middle;">arrow_back</span>
                    Volver al inicio de sesion
                </a>
            </p>
        </form>

    <?php elseif ($step === 2): ?>
        <!-- Paso 2: Ingreso de código -->
        <form class="auth-card" method="post">
            <input type="hidden" name="action" value="verify_code">
            <div class="brand-inline">
                <img class="brand-logo" src="/logo.png" alt="SIGEA Logo">
                <div>
                    <h1>Verificar codigo</h1>
                    <p>Ingresa el codigo de 6 digitos enviado a <strong><?= e($email) ?></strong></p>
                </div>
            </div>
            
            <?php render('flash'); ?>

            <label>Codigo de verificacion
                <input type="text" name="verification_code" required maxlength="6" pattern="\d{6}" placeholder="123456" style="text-align:center;font-size:22px;letter-spacing:6px;font-weight:800;">
            </label>
            <button class="button button-primary full" type="submit" style="margin-top:8px;">
                <span class="material-symbols-rounded">verified</span>
                Verificar codigo
            </button>
            <p style="text-align:center;margin:18px 0 0;">
                <a class="back-link" href="/forgot-password.php">
                    <span class="material-symbols-rounded" style="font-size:14px;vertical-align:middle;">arrow_back</span>
                    Cancelar y volver a empezar
                </a>
            </p>
        </form>

    <?php elseif ($step === 3): ?>
        <!-- Paso 3: Restablecer contraseña -->
        <form class="auth-card" method="post">
            <input type="hidden" name="action" value="reset_password">
            <div class="brand-inline">
                <img class="brand-logo" src="/logo.png" alt="SIGEA Logo">
                <div>
                    <h1>Nueva contrasena</h1>
                    <p>Establece tus nuevas credenciales para <strong><?= e($email) ?></strong></p>
                </div>
            </div>
            
            <?php render('flash'); ?>

            <label>Nueva contrasena
                <input type="password" name="password" required autocomplete="new-password" placeholder="Minimo 8 caracteres (Mays, num, esp)">
            </label>
            <label>Confirmar nueva contrasena
                <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="Repite la contrasena">
            </label>
            <button class="button button-primary full" type="submit" style="margin-top:8px;">
                <span class="material-symbols-rounded">lock_reset</span>
                Restablecer contrasena
            </button>
            <p style="text-align:center;margin:18px 0 0;">
                <a class="back-link" href="/forgot-password.php">
                    <span class="material-symbols-rounded" style="font-size:14px;vertical-align:middle;">arrow_back</span>
                    Cancelar y volver a empezar
                </a>
            </p>
        </form>
    <?php endif; ?>

</body>
</html>
