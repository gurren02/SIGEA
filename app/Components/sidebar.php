<?php
// Compute initials from user name (max 2 chars)
$nameParts = array_filter(explode(' ', $user['name'] ?? 'U'), fn($w) => strlen(trim($w)) > 0);
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($nameParts, 0, 2))));

$links = [
    'admin' => [
        ['/admin/dashboard.php',   'dashboard',      'Panel'],
        ['/admin/users.php',       'group',           'Usuarios'],
        ['/admin/teachers.php',    'school',          'Docentes'],
    ],
    'teacher' => [
        ['/teacher/dashboard.php', 'dashboard',      'Panel'],
        ['/teacher/exams.php',     'description',    'Examenes'],
        ['/teacher/questions.php', 'quiz',           'Preguntas'],
        ['/teacher/results.php',   'bar_chart',      'Resultados'],
    ],
    'student' => [
        ['/student/exams.php',     'assignment',     'Examenes'],
        ['/student/results.php',   'bar_chart',      'Resultados'],
    ],
][$user['role']];
?>
<aside class="sidebar">
    <!-- Brand -->
    <a class="brand" href="/dashboard.php">
        <img class="sidebar-logo" src="/logo.png" alt="SIGEA Logo">
        <span>
            <strong>SIGEA</strong>
            <small><?= e(role_label($user['role'])) ?></small>
        </span>
    </a>

    <!-- Navigation -->
    <p class="nav-section-label">Menu</p>
    <nav>
        <?php foreach ($links as [$href, $icon, $label]): ?>
            <a class="<?= $_SERVER['PHP_SELF'] === $href ? 'active' : '' ?>" href="<?= e($href) ?>">
                <span class="material-symbols-rounded"><?= $icon ?></span>
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Spacer pushes user card to bottom -->
    <div class="sidebar-spacer"></div>

    <!-- User card + popover -->
    <div class="sidebar-user-wrap">
        <!-- Popover (renders above the card) -->
        <div class="user-popover" id="user-popover" role="menu">
            <a class="user-popover-item" href="/profile.php" role="menuitem">
                <span class="material-symbols-rounded">manage_accounts</span>
                Editar perfil
            </a>
            <div class="user-popover-divider"></div>
            <a class="user-popover-item user-popover-item--danger" href="/logout.php" role="menuitem">
                <span class="material-symbols-rounded">logout</span>
                Cerrar sesion
            </a>
        </div>

        <!-- Trigger card -->
        <button class="sidebar-user" id="sidebar-user-btn" type="button" aria-haspopup="true" aria-expanded="false">
            <span class="sidebar-user-avatar"><?= e($initials) ?></span>
            <span class="sidebar-user-info">
                <strong><?= e($user['name']) ?></strong>
                <small><?= e(role_label($user['role'])) ?></small>
            </span>
            <span class="material-symbols-rounded sidebar-user-chevron">expand_less</span>
        </button>
    </div>
</aside>
