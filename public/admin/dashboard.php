<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('admin');
$counts = User::countsByRole();
$pageTitle = 'Panel administrativo';
render('header', compact('pageTitle'));
?>
<section class="metric-grid">
    <article class="metric"><span>Administradores</span><strong><?= (int) ($counts['admin'] ?? 0) ?></strong></article>
    <article class="metric"><span>Docentes</span><strong><?= (int) ($counts['teacher'] ?? 0) ?></strong></article>
    <article class="metric"><span>Estudiantes</span><strong><?= (int) ($counts['student'] ?? 0) ?></strong></article>
</section>
<section class="panel">
    <h2>Gestion institucional</h2>
    <p class="muted">Desde este panel puedes crear usuarios y asignar estudiantes a docentes.</p>
    <div class="actions">
        <a class="button button-primary" href="/admin/users.php">Crear usuarios</a>
        <a class="button button-warning" href="/admin/teachers.php">Asignar alumnos</a>
    </div>
</section>
<?php render('footer'); ?>
