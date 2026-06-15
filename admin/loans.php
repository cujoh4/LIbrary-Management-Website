<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'lunas') {
    verify_csrf();
    $loan_id = (int) ($_POST['loan_id'] ?? 0);

    $stmt = $pdo->prepare('SELECT * FROM loans WHERE id = ?');
    $stmt->execute([$loan_id]);
    $loan = $stmt->fetch();

    if (!$loan) {
        set_flash('error', 'Data peminjaman tidak ditemukan.');
    } elseif ($loan['lunas']) {
        set_flash('info', 'Peminjaman ini sudah lunas.');
    } else {
        try {
            $pdo->beginTransaction();

            $tgl_kembali = $loan['tgl_kembali'] ?: date('Y-m-d');
            $denda = hitung_denda($loan['tgl_jatuh_tempo'], $tgl_kembali);

            $pdo->prepare('
                UPDATE loans SET tgl_kembali = ?, denda = ?, lunas = 1 WHERE id = ?
            ')->execute([$tgl_kembali, $denda, $loan_id]);

            // Kembalikan stok bila buku baru dikembalikan saat ini.
            if ($loan['tgl_kembali'] === null) {
                $pdo->prepare('UPDATE books SET stok = stok + 1 WHERE id = ?')->execute([$loan['book_id']]);
            }

            $pdo->commit();
            set_flash('sukses', 'Peminjaman ditandai LUNAS. Denda tercatat: ' . rupiah($denda) . '.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            set_flash('error', 'Gagal memproses pelunasan. Coba lagi.');
        }
    }

    header('Location: ' . base_url('admin/loans.php'));
    exit;
}

$loans = $pdo->query('
    SELECT l.*, b.judul, u.nama
    FROM loans l
    JOIN books b ON b.id = l.book_id
    JOIN users u ON u.id = l.user_id
    ORDER BY l.lunas ASC, l.tgl_jatuh_tempo ASC
')->fetchAll();

$page_title = 'Kelola Peminjaman';
require_once __DIR__ . '/../includes/header.php';
?>

<p class="mt-2"><a href="<?= e(base_url('admin/dashboard.php')) ?>">&larr; Kembali ke Panel Admin</a></p>

<h2 class="section-title">Kelola Peminjaman &amp; Denda</h2>

<div class="panel">
    <?php if (!$loans): ?>
        <p class="muted text-center">Belum ada data peminjaman.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Buku</th>
                        <th>Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $l): ?>
                        <?php
                        $denda = $l['lunas'] ? (int) $l['denda'] : hitung_denda($l['tgl_jatuh_tempo'], $l['tgl_kembali']);
                        $telat = !$l['lunas'] && $l['tgl_kembali'] === null
                                 && strtotime($l['tgl_jatuh_tempo']) < strtotime(date('Y-m-d'));
                        ?>
                        <tr>
                            <td><?= e($l['nama']) ?></td>
                            <td><?= e($l['judul']) ?></td>
                            <td><?= date('d M Y', strtotime($l['tgl_pinjam'])) ?></td>
                            <td>
                                <?= date('d M Y', strtotime($l['tgl_jatuh_tempo'])) ?>
                                <?php if ($telat): ?><br><span class="badge badge-belum">Terlambat</span><?php endif; ?>
                            </td>
                            <td><?= $l['tgl_kembali'] ? date('d M Y', strtotime($l['tgl_kembali'])) : '<span class="badge badge-tempo">Dipinjam</span>' ?></td>
                            <td><?= $denda > 0 ? rupiah($denda) : '&mdash;' ?></td>
                            <td>
                                <?php if ($l['lunas']): ?>
                                    <span class="badge badge-lunas">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-belum">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$l['lunas']): ?>
                                    <form method="post" action="<?= e(base_url('admin/loans.php')) ?>"
                                          data-confirm="Tandai peminjaman ini sebagai LUNAS?<?= $denda > 0 ? ' Denda ' . rupiah($denda) . ' dianggap telah dibayar.' : '' ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="lunas">
                                        <input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>">
                                        <button class="btn btn-success btn-sm" type="submit">Tandai Lunas</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="form-hint mt-2">Denda dihitung otomatis: <?= rupiah(DENDA_PER_HARI) ?> &times; jumlah hari keterlambatan.
            Menekan <strong>Tandai Lunas</strong> sekaligus mengembalikan buku (stok bertambah) bila belum dikembalikan.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
