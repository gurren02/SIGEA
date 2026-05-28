<?php $user = current_user(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIGEA – Sistema de Generación y Evaluación Automática de Exámenes">
    <title><?= e(($pageTitle ?? 'Inicio') . ' | SIGEA') ?></title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<?php if ($user): ?>
<div class="app-shell">
    <?php render('sidebar', ['user' => $user]); ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <span class="eyebrow">Sistema de Generacion y Evaluacion Automatica</span>
                <h1><?= e($pageTitle ?? 'SIGEA') ?></h1>
            </div>
        </header>
        <?php render('flash'); ?>
<?php endif; ?>
