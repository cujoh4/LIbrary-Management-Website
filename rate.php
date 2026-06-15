<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('index.php'));
    exit;
}

verify_csrf();

if (is_admin()) {
    set_flash('error', 'Admin tidak dapat memberi rating.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$user    = current_user();
$book_id = (int) ($_POST['book_id'] ?? 0);
$bintang = (int) ($_POST['bintang'] ?? 0);
$komentar = trim((string) ($_POST['komentar'] ?? ''));

$cek = $pdo->prepare('SELECT id FROM books WHERE id = ?');
$cek->execute([$book_id]);
if (!$cek->fetch()) {
    set_flash('error', 'Buku tidak ditemukan.');
    header('Location: ' . base_url('index.php'));
    exit;
}

if ($bintang < 1 || $bintang > 5) {
    set_flash('error', 'Silakan pilih rating antara 1 sampai 5 bintang.');
    header('Location: ' . base_url('book.php?id=' . $book_id));
    exit;
}

$sql = '
    INSERT INTO ratings (book_id, user_id, bintang, komentar)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE bintang = VALUES(bintang), komentar = VALUES(komentar)
';
$stmt = $pdo->prepare($sql);
$stmt->execute([$book_id, $user['id'], $bintang, $komentar !== '' ? $komentar : null]);

set_flash('sukses', 'Terima kasih! Ulasan Anda telah disimpan.');
header('Location: ' . base_url('book.php?id=' . $book_id));
exit;
