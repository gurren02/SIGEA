<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';
$user = require_login();

if (is_post()) {
    if (($_POST['action'] ?? '') === 'profile') {
        User::updateProfile($user['id'], $_POST);
        flash('success', 'Perfil actualizado correctamente.');
        redirect('/profile.php');
    }

    if (($_POST['action'] ?? '') === 'password') {
        $password = $_POST['password'] ?? '';
        if (!password_is_valid($password)) {
            flash('error', 'La contrasena debe tener 8 caracteres, una mayuscula, un numero y un signo.');
        } elseif ($password !== ($_POST['password_confirmation'] ?? '')) {
            flash('error', 'La confirmacion no coincide.');
        } else {
            User::updatePassword($user['id'], $password);
            flash('success', 'Contrasena actualizada.');
        }
        redirect('/profile.php');
    }
}

$pageTitle = 'Perfil';
render('header', compact('pageTitle'));
?>
<section class="content-grid two">
    <form class="panel" method="post">
        <input type="hidden" name="action" value="profile">
        <h2>Datos personales</h2>
        <label>Nombre
            <input name="name" value="<?= e($user['name']) ?>" required>
        </label>
        <label>Correo
            <input value="<?= e($user['email']) ?>" disabled>
        </label>
        <label>Telefono
            <input name="phone" value="<?= e($user['phone']) ?>">
        </label>
        <label>Matricula o clave institucional
            <input name="institutional_id" value="<?= e($user['institutional_id']) ?>">
        </label>
        <button class="button button-primary" type="submit">Guardar perfil</button>
    </form>
    <form class="panel" method="post">
        <input type="hidden" name="action" value="password">
        <h2>Cambiar contrasena</h2>
        <p class="muted">Minimo 8 caracteres, una mayuscula, un numero y un signo.</p>
        <label>Nueva contrasena
            <input type="password" name="password" required>
        </label>
        <label>Confirmar contrasena
            <input type="password" name="password_confirmation" required>
        </label>
        <button class="button button-warning" type="submit">Actualizar contrasena</button>
    </form>
</section>
<?php render('footer'); ?>
