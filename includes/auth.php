<?php
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'nama'  => $user['nama'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        set_flash('error', 'Halaman ini khusus untuk admin.');
        header('Location: ' . base_url('index.php'));
        exit;
    }
}
