<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$jml_buku    = (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$jml_member  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$jml_ulasan  = (int) $pdo->query('SELECT COUNT(*) FROM ratings')->fetchColumn();
$jml_pinjam  = (int) $pdo->query('SELECT COUNT(*) FROM loans WHERE tgl_kembali IS NULL')->fetchColumn();

// Hitung denda yang belum lunas (dihitung di PHP agar konsisten dengan logika denda).
$rows = $pdo->query('SELECT tgl_jatuh_tempo, tgl_kembali FROM loans WHERE lunas = 0')->fetchAll();
$denda_tertunggak = 0;
foreach ($rows as $r) {
    $denda_tertunggak += hitung_denda($r['tgl_jatuh_tempo'], $r['tgl_kembali']);
}

$page_title = 'Panel Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Panel Admin</h2>
<p class="text-center muted">Kelola koleksi buku dan peminjaman Torang Baca.</p>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-num"><?= $jml_buku ?></div><div class="stat-label">Buku</div></div>
    <div class="stat-card"><div class="stat-num"><?= $jml_member ?></div><div class="stat-label">Member</div></div>
    <div class="stat-card"><div class="stat-num"><?= $jml_pinjam ?></div><div class="stat-label">Sedang Dipinjam</div></div>
    <div class="stat-card"><div class="stat-num"><?= $jml_ulasan ?></div><div class="stat-label">Ulasan</div></div>
    <div class="stat-card"><div class="stat-num" style="font-size:24px"><?= rupiah($denda_tertunggak) ?></div><div class="stat-label">Denda Tertunggak</div></div>
</div>

<div class="panel">
    <h3>Menu Pengelolaan</h3>
    <div class="form-actions">
        <a class="btn" href="<?= e(base_url('admin/books.php')) ?>">Kelola Buku</a>
        <a class="btn" href="<?= e(base_url('admin/book_form.php')) ?>">+ Tambah Buku</a>
        <a class="btn btn-secondary" href="<?= e(base_url('admin/loans.php')) ?>">Kelola Peminjaman &amp; Denda</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
