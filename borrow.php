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
    set_flash('error', 'Admin tidak meminjam buku.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$user    = current_user();
$book_id = (int) ($_POST['book_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    set_flash('error', 'Buku tidak ditemukan.');
    header('Location: ' . base_url('index.php'));
    exit;
}

if ((int) $book['stok'] <= 0) {
    set_flash('error', 'Maaf, stok buku ini sedang habis.');
    header('Location: ' . base_url('book.php?id=' . $book_id));
    exit;
}

$dup = $pdo->prepare('SELECT id FROM loans WHERE book_id = ? AND user_id = ? AND tgl_kembali IS NULL');
$dup->execute([$book_id, $user['id']]);
if ($dup->fetch()) {
    set_flash('info', 'Anda masih meminjam buku ini dan belum mengembalikannya.');
    header('Location: ' . base_url('member/dashboard.php'));
    exit;
}

// Buat peminjaman dalam transaksi agar stok & loan konsisten.
try {
    $pdo->beginTransaction();

    $tgl_pinjam = date('Y-m-d');
    $jatuh_tempo = date('Y-m-d', strtotime('+' . LAMA_PINJAM_HARI . ' days'));

    $ins = $pdo->prepare('
        INSERT INTO loans (book_id, user_id, tgl_pinjam, tgl_jatuh_tempo)
        VALUES (?, ?, ?, ?)
    ');
    $ins->execute([$book_id, $user['id'], $tgl_pinjam, $jatuh_tempo]);

    $pdo->prepare('UPDATE books SET stok = stok - 1 WHERE id = ?')->execute([$book_id]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    set_flash('error', 'Gagal memproses peminjaman. Coba lagi.');
    header('Location: ' . base_url('book.php?id=' . $book_id));
    exit;
}

set_flash('sukses', 'Berhasil meminjam "' . $book['judul'] . '". Harap kembalikan sebelum '
    . date('d M Y', strtotime($jatuh_tempo)) . '.');
header('Location: ' . base_url('member/dashboard.php'));
exit;
