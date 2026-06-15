<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
if (is_admin()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$user = current_user();

$stmt = $pdo->prepare('
    SELECT l.*, b.judul, b.penulis, b.cover_warna
    FROM loans l
    JOIN books b ON b.id = l.book_id
    WHERE l.user_id = ?
    ORDER BY l.tgl_pinjam DESC, l.id DESC
');
$stmt->execute([$user['id']]);
$loans = $stmt->fetchAll();

$total_tunggakan = 0;
foreach ($loans as $l) {
    if (!$l['lunas']) {
        $total_tunggakan += hitung_denda($l['tgl_jatuh_tempo'], $l['tgl_kembali']);
    }
}

$page_title = 'Riwayat Saya';
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Riwayat Peminjaman</h2>
<p class="text-center muted">Halo <strong><?= e($user['nama']) ?></strong>, berikut riwayat peminjaman buku Anda.</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-num"><?= count($loans) ?></div>
        <div class="stat-label">Total Pinjam</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= count(array_filter($loans, fn($l) => $l['tgl_kembali'] === null)) ?></div>
        <div class="stat-label">Sedang Dipinjam</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= rupiah($total_tunggakan) ?></div>
        <div class="stat-label">Tunggakan Denda</div>
    </div>
</div>

<div class="panel">
    <?php if (!$loans): ?>
        <p class="muted text-center">Belum ada peminjaman. <a href="<?= e(base_url('index.php')) ?>">Jelajahi katalog</a> untuk mulai meminjam.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Dikembalikan</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $l): ?>
                        <?php
                        $denda = $l['lunas'] ? (int) $l['denda'] : hitung_denda($l['tgl_jatuh_tempo'], $l['tgl_kembali']);
                        $belum_kembali = $l['tgl_kembali'] === null;
                        $telat = $belum_kembali && strtotime($l['tgl_jatuh_tempo']) < strtotime(date('Y-m-d'));
                        ?>
                        <tr>
                            <td>
                                <a href="<?= e(base_url('book.php?id=' . $l['book_id'])) ?>"><?= e($l['judul']) ?></a>
                                <div class="muted" style="font-size:12px"><?= e($l['penulis']) ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($l['tgl_pinjam'])) ?></td>
                            <td>
                                <?= date('d M Y', strtotime($l['tgl_jatuh_tempo'])) ?>
                                <?php if ($telat): ?><br><span class="badge badge-belum">Terlambat</span><?php endif; ?>
                            </td>
                            <td><?= $l['tgl_kembali'] ? date('d M Y', strtotime($l['tgl_kembali'])) : '&mdash;' ?></td>
                            <td><?= $denda > 0 ? rupiah($denda) : '&mdash;' ?></td>
                            <td>
                                <?php if ($denda <= 0): ?>
                                    <span class="badge badge-lunas">Tanpa Denda</span>
                                <?php elseif ($l['lunas']): ?>
                                    <span class="badge badge-lunas">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-belum">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="form-hint mt-2">Denda keterlambatan <?= rupiah(DENDA_PER_HARI) ?>/hari.
            Pelunasan dikonfirmasi oleh admin di perpustakaan.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
