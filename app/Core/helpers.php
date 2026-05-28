<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return User::find((int) $_SESSION['user_id']);
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('/login.php');
    }

    return $user;
}

function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        redirect('/dashboard.php');
    }

    return $user;
}

function password_is_valid(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function role_label(string $role): string
{
    return [
        'admin' => 'Administrador',
        'teacher' => 'Docente',
        'student' => 'Estudiante',
    ][$role] ?? $role;
}

function dashboard_path(string $role): string
{
    return [
        'admin' => '/admin/dashboard.php',
        'teacher' => '/teacher/dashboard.php',
        'student' => '/student/exams.php',
    ][$role] ?? '/dashboard.php';
}

function render(string $component, array $data = []): void
{
    extract($data);
    require APP_PATH . '/Components/' . $component . '.php';
}
