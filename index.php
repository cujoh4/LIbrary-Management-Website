<?php
require_once __DIR__ . '/includes/header.php';
?>
<?php if (!is_logged_in()): ?>
<div id="landing-overlay" style="display:none">
    <div id="landing-box">
        <div class="landing-ornament">&#10022; &nbsp; &#10022; &nbsp; &#10022;</div>
        <span class="landing-icon">&#10087;</span>
        <h1 class="landing-title">Torang Baca</h1>
        <p class="landing-sub">Perpustakaan Digital</p>
        <div class="landing-actions">
            <a href="<?= e(base_url('login.php')) ?>" class="btn landing-btn-login">Masuk</a>
            <button id="btn-guest" class="landing-btn-guest">Jelajahi sebagai Tamu</button>
        </div>
        <p class="landing-hint">Belum punya akun? <a href="<?= e(base_url('register.php')) ?>">Daftar di sini</a></p>
    </div>
</div>
<?php endif; ?>
<?php

$q = trim((string) ($_GET['q'] ?? ''));

$sql = '
    SELECT b.*,
           COALESCE(AVG(r.bintang), 0) AS rata_rating,
           COUNT(r.id)                 AS jumlah_rating
    FROM books b
    LEFT JOIN ratings r ON r.book_id = b.id
';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE b.judul LIKE ? OR b.penulis LIKE ? OR b.kategori LIKE ? ';
    $q_escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    $like = '%' . $q_escaped . '%';
    $params = [$like, $like, $like];
}
$sql .= ' GROUP BY b.id ORDER BY b.judul ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();
$total = (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
?>

<section class="hero hero-fullscreen">
    <div class="ornament">&#10086; &mdash;&mdash;&mdash;&mdash;&mdash; &#10086;</div>
    <h1>Selamat Datang di Torang Baca</h1>
    <p>Telusuri koleksi <strong><?= $total ?></strong> buku pilihan, beri rating, dan bagikan ulasanmu.</p>
    <div class="ornament">&#10086; &mdash;&mdash;&mdash;&mdash;&mdash; &#10086;</div>
    <div class="hero-scroll-hint" title="Gulir ke bawah">&#8964;</div>
</section>

<div class="toolbar">
    <form class="search-form" method="get" action="<?= e(base_url('index.php')) ?>">
        <input type="search" name="q" placeholder="Cari judul, penulis, atau kategori&hellip;"
               value="<?= e($q) ?>" aria-label="Pencarian buku">
        <button class="btn" type="submit">Cari</button>
    </form>
    <?php if ($q !== ''): ?>
        <a class="btn btn-secondary" href="<?= e(base_url('index.php')) ?>">Tampilkan Semua</a>
    <?php endif; ?>
</div>

<h2 class="section-title"><?= $q !== '' ? 'Hasil Pencarian' : 'Koleksi Buku' ?></h2>

<?php if (!$books): ?>
    <p class="text-center muted">Tidak ada buku yang cocok dengan pencarian &ldquo;<?= e($q) ?>&rdquo;.</p>
<?php else: ?>
    <div class="book-grid">
        <?php foreach ($books as $b): ?>
            <article class="book-card">
                <a href="<?= e(base_url('book.php?id=' . $b['id'])) ?>">
                    <img class="book-cover" loading="lazy"
                         src="<?= e(cover_url($b)) ?>"
                         alt="Sampul <?= e($b['judul']) ?>">
                </a>
                <h3><a href="<?= e(base_url('book.php?id=' . $b['id'])) ?>"><?= e($b['judul']) ?></a></h3>
                <p class="penulis"><?= e($b['penulis']) ?></p>
                <span class="kategori"><?= e($b['kategori']) ?></span>
                <div class="card-foot">
                    <?= render_stars((float) $b['rata_rating']) ?>
                    <div class="rating-line">
                        <?php if ((int) $b['jumlah_rating'] > 0): ?>
                            <?= number_format((float) $b['rata_rating'], 1) ?>/5
                            (<?= (int) $b['jumlah_rating'] ?> ulasan)
                        <?php else: ?>
                            Belum ada ulasan
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
