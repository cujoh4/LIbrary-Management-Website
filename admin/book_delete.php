<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/books.php'));
    exit;
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT judul FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    set_flash('error', 'Buku tidak ditemukan.');
} else {
    $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([$id]);
    set_flash('sukses', 'Buku "' . $book['judul'] . '" telah dihapus.');
}

header('Location: ' . base_url('admin/books.php'));
exit;
