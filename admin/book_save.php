<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/books.php'));
    exit;
}

verify_csrf();

$id        = (int) ($_POST['id'] ?? 0);
$judul     = trim((string) ($_POST['judul'] ?? ''));
$penulis   = trim((string) ($_POST['penulis'] ?? ''));
$kategori  = trim((string) ($_POST['kategori'] ?? '')) ?: 'Umum';
$tahun     = (int) ($_POST['tahun'] ?? 0);
$stok      = max(0, (int) ($_POST['stok'] ?? 0));
$deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
$warna     = ltrim((string) ($_POST['cover_warna'] ?? '#5b3a1a'), '#');
$warna     = preg_replace('/[^0-9a-fA-F]/', '', $warna);
if (strlen($warna) !== 6) {
    $warna = '5b3a1a';
}
$cover_gambar = trim((string) ($_POST['cover_gambar'] ?? ''));
if ($cover_gambar !== '' && !filter_var($cover_gambar, FILTER_VALIDATE_URL)) {
    $cover_gambar = '';
}

$errors = [];
if ($judul === '')   { $errors[] = 'Judul wajib diisi.'; }
if ($penulis === '') { $errors[] = 'Penulis wajib diisi.'; }
if ($deskripsi === '') { $errors[] = 'Deskripsi wajib diisi.'; }
if ($tahun < 1 || $tahun > (int) date('Y')) { $errors[] = 'Tahun terbit tidak valid.'; }

if ($errors) {
    set_flash('error', implode(' ', $errors));
    $_SESSION['old_input'] = compact('judul', 'penulis', 'kategori', 'tahun', 'stok', 'deskripsi', 'cover_gambar') + ['cover_warna' => $warna];
    $tujuan = $id > 0 ? 'admin/book_form.php?id=' . $id : 'admin/book_form.php';
    header('Location: ' . base_url($tujuan));
    exit;
}

if ($id > 0) {
    $stmt = $pdo->prepare('
        UPDATE books
        SET judul = ?, penulis = ?, kategori = ?, tahun = ?, stok = ?, deskripsi = ?, cover_warna = ?, cover_gambar = ?
        WHERE id = ?
    ');
    $stmt->execute([$judul, $penulis, $kategori, $tahun, $stok, $deskripsi, $warna, $cover_gambar ?: null, $id]);
    set_flash('sukses', 'Buku "' . $judul . '" berhasil diperbarui.');
} else {
    $stmt = $pdo->prepare('
        INSERT INTO books (judul, penulis, kategori, tahun, stok, deskripsi, cover_warna, cover_gambar)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$judul, $penulis, $kategori, $tahun, $stok, $deskripsi, $warna, $cover_gambar ?: null]);
    set_flash('sukses', 'Buku "' . $judul . '" berhasil ditambahkan.');
}

header('Location: ' . base_url('admin/books.php'));
exit;
