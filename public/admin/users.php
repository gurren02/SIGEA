<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('admin');

if (is_post()) {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId === (int) $user['id']) {
            flash('error', 'No puedes eliminar tu propio usuario.');
        } else {
            try {
                User::delete($userId);
                flash('success', 'Usuario eliminado correctamente.');
            } catch (Throwable $e) {
                flash('error', 'No se pudo eliminar el usuario.');
            }
        }
    } else {
        $password = $_POST['password'] ?? '';
        if (!password_is_valid($password)) {
            flash('error', 'La contrasena debe tener 8 caracteres, una mayuscula, un numero y un signo.');
        } else {
            try {
                User::create($_POST);
                flash('success', 'Usuario creado correctamente.');
            } catch (Throwable $e) {
                flash('error', 'No se pudo crear el usuario. Verifica que el correo no este repetido.');
            }
        }
    }
    redirect('/admin/users.php');
}

$admins   = User::allByRole('admin');
$teachers = User::allByRole('teacher');
$students = User::allByRole('student');
$pageTitle = 'Usuarios';
render('header', compact('pageTitle'));
?>

<section class="panel">
    <div class="section-head">
        <div>
            <h2>Usuarios registrados</h2>
            <p class="muted">Gestiona las cuentas del sistema por rol.</p>
        </div>
        <button class="button button-primary" data-modal-open="create-user">
            <span class="material-symbols-rounded">person_add</span>
            Nuevo usuario
        </button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Clave institucional</th>
                    <th style="width: 80px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_merge($admins, $teachers, $students) as $row): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="table-avatar"><?= strtoupper(substr($row['name'], 0, 1)) ?></span>
                            <?= e($row['name']) ?>
                        </div>
                    </td>
                    <td><?= e($row['email']) ?></td>
                    <td><span class="role-badge role-badge--<?= e($row['role']) ?>"><?= e(role_label($row['role'])) ?></span></td>
                    <td><?= e($row['institutional_id']) ?></td>
                    <td style="text-align: center;">
                        <?php if ((int)$row['id'] !== (int)$user['id']): ?>
                            <form method="post" action="/admin/users.php" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="icon-button" title="Eliminar usuario">
                                    <span class="material-symbols-rounded">delete</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="muted" style="font-size: 12px; font-style: italic;">Actual</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ── Modal: Crear usuario ─────────────────────────────────── -->
<div class="modal-backdrop" id="create-user">
    <form class="modal" method="post" autocomplete="off">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-icon modal-header-icon--blue">
                <span class="material-symbols-rounded">person_add</span>
            </div>
            <div class="modal-header-text">
                <h2>Crear nuevo usuario</h2>
                <p>Completa los datos para registrar la cuenta.</p>
            </div>
            <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body">

            <!-- Seccion: Datos personales -->
            <p class="modal-section-label">
                <span class="material-symbols-rounded">badge</span>
                Datos personales
            </p>
            <div class="form-row">
                <label>Nombre completo
                    <input name="name" placeholder="Ej. Juan Perez" required>
                </label>
                <label>Correo electronico
                    <input type="email" name="email" placeholder="correo@institucion.edu" required>
                </label>
            </div>
            <div class="form-row">
                <label>Telefono
                    <input name="phone" placeholder="+52 000 000 0000">
                </label>
                <label>Matricula o clave institucional
                    <input name="institutional_id" placeholder="Ej. A12345678">
                </label>
            </div>

            <!-- Seccion: Acceso -->
            <p class="modal-section-label" style="margin-top:20px;">
                <span class="material-symbols-rounded">key</span>
                Acceso al sistema
            </p>
            <div class="form-row">
                <label>Rol
                    <select name="role" required>
                        <option value="student">Estudiante</option>
                        <option value="teacher">Docente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </label>
                <label>Contrasena inicial
                    <input type="password" name="password" placeholder="Min. 8 caracteres" required>
                </label>
            </div>
            <p class="muted" style="font-size:12px;margin-top:-6px;">
                La contrasena debe tener al menos 8 caracteres, una mayuscula, un numero y un signo especial.
            </p>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="button button-ghost" type="button" data-modal-close>Cancelar</button>
            <button class="button button-primary" type="submit">
                <span class="material-symbols-rounded">check</span>
                Crear cuenta
            </button>
        </div>

    </form>
</div>

<?php render('footer'); ?>
