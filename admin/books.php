<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$books = $pdo->query('
    SELECT b.*, COUNT(r.id) AS jumlah_rating, COALESCE(AVG(r.bintang),0) AS rata
    FROM books b
    LEFT JOIN ratings r ON r.book_id = b.id
    GROUP BY b.id
    ORDER BY b.judul ASC
')->fetchAll();

$page_title = 'Kelola Buku';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="toolbar">
    <h2 class="section-title" style="margin:0">Kelola Buku</h2>
    <a class="btn" href="<?= e(base_url('admin/book_form.php')) ?>">+ Tambah Buku</a>
</div>

<div class="panel">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Tahun</th>
                    <th>Stok</th>
                    <th>Rating</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <tr>
                        <td><img src="<?= e(cover_url($b)) ?>" alt="" width="40" height="55" style="border:1px solid #5b3a1a"></td>
                        <td><a href="<?= e(base_url('book.php?id=' . $b['id'])) ?>"><?= e($b['judul']) ?></a></td>
                        <td><?= e($b['penulis']) ?></td>
                        <td><?= e($b['kategori']) ?></td>
                        <td><?= e((string) $b['tahun']) ?></td>
                        <td><?= (int) $b['stok'] ?></td>
                        <td>
                            <?php if ((int) $b['jumlah_rating'] > 0): ?>
                                <?= number_format((float) $b['rata'], 1) ?> &#9733; (<?= (int) $b['jumlah_rating'] ?>)
                            <?php else: ?>
                                <span class="muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                <a class="btn btn-secondary btn-sm" href="<?= e(base_url('admin/book_form.php?id=' . $b['id'])) ?>">Edit</a>
                                <form method="post" action="<?= e(base_url('admin/book_delete.php')) ?>"
                                      data-confirm="Hapus buku &quot;<?= e($b['judul']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
