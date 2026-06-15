<?php
require_once __DIR__ . '/includes/header.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    http_response_code(404);
    echo '<div class="panel text-center"><h1>Buku tidak ditemukan</h1>'
       . '<p class="muted">Mungkin buku telah dihapus.</p>'
       . '<a class="btn" href="' . e(base_url('index.php')) . '">Kembali ke Katalog</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$agg = $pdo->prepare('SELECT COALESCE(AVG(bintang),0) AS rata, COUNT(*) AS jml FROM ratings WHERE book_id = ?');
$agg->execute([$id]);
$rating = $agg->fetch();

$rev = $pdo->prepare('
    SELECT r.*, u.nama
    FROM ratings r
    JOIN users u ON u.id = r.user_id
    WHERE r.book_id = ?
    ORDER BY r.created_at DESC
');
$rev->execute([$id]);
$reviews = $rev->fetchAll();

$rating_saya = null;
$user = current_user();
if ($user) {
    $mine = $pdo->prepare('SELECT * FROM ratings WHERE book_id = ? AND user_id = ?');
    $mine->execute([$id, $user['id']]);
    $rating_saya = $mine->fetch() ?: null;
}

$page_title = $book['judul'];
?>

<p class="mt-2"><a href="<?= e(base_url('index.php')) ?>">&larr; Kembali ke Katalog</a></p>

<div class="panel">
    <div class="book-detail">
        <div class="cover-wrap">
            <img src="<?= e(cover_url($book)) ?>" alt="Sampul <?= e($book['judul']) ?>">
        </div>
        <div class="book-info">
            <h1><?= e($book['judul']) ?></h1>
            <p class="penulis">oleh <?= e($book['penulis']) ?></p>

            <?= render_stars((float) $rating['rata']) ?>
            <span class="rating-line">
                <?php if ((int) $rating['jml'] > 0): ?>
                    <?= number_format((float) $rating['rata'], 1) ?>/5 dari <?= (int) $rating['jml'] ?> ulasan
                <?php else: ?>
                    Belum ada ulasan
                <?php endif; ?>
            </span>

            <ul class="meta-list">
                <li><strong>Kategori</strong> <?= e($book['kategori']) ?></li>
                <li><strong>Tahun Terbit</strong> <?= e((string) $book['tahun']) ?></li>
                <li><strong>Stok Tersedia</strong>
                    <?php if ((int) $book['stok'] > 0): ?>
                        <?= (int) $book['stok'] ?> eksemplar
                    <?php else: ?>
                        <span class="badge badge-belum">Stok habis</span>
                    <?php endif; ?>
                </li>
            </ul>

            <p><?= nl2br(e($book['deskripsi'])) ?></p>

            <?php if ($user && !is_admin()): ?>
                <form method="post" action="<?= e(base_url('borrow.php')) ?>"
                      data-confirm="Pinjam buku ini selama <?= LAMA_PINJAM_HARI ?> hari?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
                    <button class="btn" type="submit" <?= (int) $book['stok'] <= 0 ? 'disabled' : '' ?>>
                        Pinjam Buku
                    </button>
                    <span class="form-hint">Lama pinjam <?= LAMA_PINJAM_HARI ?> hari &middot;
                        denda <?= rupiah(DENDA_PER_HARI) ?>/hari bila terlambat.</span>
                </form>
            <?php elseif (!$user): ?>
                <p class="muted"><a href="<?= e(base_url('login.php')) ?>">Masuk</a> sebagai member untuk meminjam buku ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel">
    <h2 class="section-title">Ulasan Pembaca</h2>

    <?php if ($user && !is_admin()): ?>
        <h3><?= $rating_saya ? 'Perbarui Ulasan Anda' : 'Beri Rating &amp; Ulasan' ?></h3>
        <form method="post" action="<?= e(base_url('rate.php')) ?>" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
            <div class="form-group">
                <label>Rating Anda</label>
                <div class="star-input">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="bintang" value="<?= $i ?>"
                               <?= ($rating_saya && (int) $rating_saya['bintang'] === $i) ? 'checked' : '' ?>>
                        <label for="star<?= $i ?>" data-value="<?= $i ?>" title="<?= $i ?> bintang">&#9733;</label>
                    <?php endfor; ?>
                </div>
                <div class="star-value form-hint"></div>
            </div>
            <div class="form-group">
                <label for="komentar">Komentar</label>
                <textarea id="komentar" class="form-control" name="komentar"
                          placeholder="Bagikan pendapatmu tentang buku ini&hellip;"><?= e($rating_saya['komentar'] ?? '') ?></textarea>
            </div>
            <button class="btn" type="submit"><?= $rating_saya ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?></button>
        </form>
        <div class="divider">&#10086; &#10086; &#10086;</div>
    <?php elseif (!$user): ?>
        <p class="muted"><a href="<?= e(base_url('login.php')) ?>">Masuk</a> sebagai member untuk memberi rating &amp; ulasan.</p>
    <?php endif; ?>

    <?php if (!$reviews): ?>
        <p class="muted">Belum ada ulasan. Jadilah yang pertama memberi ulasan!</p>
    <?php else: ?>
        <ul class="review-list">
            <?php foreach ($reviews as $r): ?>
                <li class="review-item">
                    <div class="review-head">
                        <span class="review-author"><?= e($r['nama']) ?></span>
                        <span class="review-date"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                    </div>
                    <?= render_stars((float) $r['bintang']) ?>
                    <?php if (trim((string) $r['komentar']) !== ''): ?>
                        <p><?= nl2br(e($r['komentar'])) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
