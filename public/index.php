<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

if (isset($_GET['role'])) {
    $role = $_GET['role'];
    if (in_array($role, ['student', 'teacher', 'admin'], true)) {
        $_SESSION['login_role'] = $role;
        redirect('/login.php');
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIGEA – Selecciona tu tipo de acceso al sistema de evaluaciones.">
    <title>SIGEA | Acceso</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-body">
    <section class="auth-card role-card">
        <div class="brand-inline">
            <img class="brand-logo" src="/logo.png" alt="SIGEA Logo">
            <div>
                <h1>SIGEA</h1>
                <p>Sistema de Generacion y Evaluacion Automatica</p>
            </div>
        </div>
        <div class="role-grid">
            <a class="role-option" href="/index.php?role=student">
                <strong>
                    <span class="material-symbols-rounded" style="font-size:22px;">school</span>
                    Estudiantes
                </strong>
                <span>Consulta examenes asignados y resultados.</span>
            </a>
            <a class="role-option" href="/index.php?role=teacher">
                <strong>
                    <span class="material-symbols-rounded" style="font-size:22px;">edit_document</span>
                    Docente
                </strong>
                <span>Genera examenes y valida resultados.</span>
            </a>
        </div>
        <p class="admin-question">Eres administrador? <a href="/index.php?role=admin">Entrar como administrador</a></p>
    </section>
</body>
</html>
