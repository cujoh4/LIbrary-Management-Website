<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'Torang Baca';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> &mdash; Torang Baca</title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
    <link rel="icon" href="<?= e(base_url('cover.php?judul=TB&warna=5b3a1a&w=64&h=64')) ?>">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= e(base_url('index.php')) ?>">
            <span class="brand-mark">&#10086;</span>
            <span class="brand-text">
                <span class="brand-title">Torang Baca</span>
                <span class="brand-sub">Perpustakaan Digital</span>
            </span>
        </a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Buka/tutup menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav">
            <a href="<?= e(base_url('index.php')) ?>">Katalog</a>
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="<?= e(base_url('admin/dashboard.php')) ?>">Panel Admin</a>
                <?php else: ?>
                    <a href="<?= e(base_url('member/dashboard.php')) ?>">Riwayat Saya</a>
                <?php endif; ?>
                <span class="nav-user">Halo, <?= e($user['nama']) ?></span>
                <a class="btn-nav" href="<?= e(base_url('logout.php')) ?>">Keluar</a>
            <?php else: ?>
                <a href="<?= e(base_url('login.php')) ?>">Masuk</a>
                <a class="btn-nav" href="<?= e(base_url('register.php')) ?>">Daftar</a>
            <?php endif; ?>
            <a href="<?= e(base_url('about.php')) ?>">Tentang Kami</a>
        </nav>
    </div>
</header>
<main class="container">
<?php foreach (get_flash() as $flash): ?>
    <div class="flash flash-<?= e($flash['tipe']) ?>"><?= e($flash['pesan']) ?></div>
<?php endforeach; ?>
