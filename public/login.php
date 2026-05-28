<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

$role = $_SESSION['login_role'] ?? ($_GET['role'] ?? null);
if (!$role || !in_array($role, ['student', 'teacher', 'admin'], true)) {
    redirect('/index.php');
}
$_SESSION['login_role'] = $role;

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = User::findByEmail($email);

    if ($user && $user['role'] === $role && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        unset($_SESSION['login_role']);
        redirect(dashboard_path($user['role']));
    }

    flash('error', 'Credenciales incorrectas para el tipo de usuario seleccionado.');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIGEA – Inicia sesion en el sistema de evaluaciones.">
    <title>Login | SIGEA</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-body">
    <form class="auth-card" method="post">
        <div class="brand-inline">
            <img class="brand-logo" src="/logo.png" alt="SIGEA Logo">
            <div>
                <h1>Acceso <?= e(role_label($role)) ?></h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
        </div>
        <?php render('flash'); ?>
        <label>Correo electronico
            <input type="email" name="email" required autocomplete="email" placeholder="usuario@institucion.edu"
                   value="<?= e($_POST['email'] ?? '') ?>">
        </label>
        <label>Contrasena
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </label>
        <button class="button button-primary full" type="submit" style="margin-top:8px;">
            <span class="material-symbols-rounded">login</span>
            Iniciar sesion
        </button>
        <a class="button button-ghost full" href="/forgot-password.php">
            <span class="material-symbols-rounded">lock_reset</span>
            Recuperar contrasena
        </a>
        <p style="text-align:center;margin:18px 0 0;">
            <a class="back-link" href="/index.php">
                <span class="material-symbols-rounded" style="font-size:14px;vertical-align:middle;">arrow_back</span>
                Cambiar tipo de usuario
            </a>
        </p>
    </form>
</body>
</html>
