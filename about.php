<?php
$page_title = 'Tentang Kami';
require_once __DIR__ . '/includes/header.php';

$anggota = [
    ['nama' => 'Jeremy J. Pandey',     'nim' => 'NIM 240211060088', 'foto' => 'images/jeremy.jpeg'],
    ['nama' => 'Josua P. Ramaino',     'nim' => 'NIM 240211060090', 'foto' => 'images/josua.jpeg'],
    ['nama' => 'Nishinia Q. Aruperes', 'nim' => 'NIM 240211060102', 'foto' => 'images/nishi.jpeg'],
    ['nama' => 'Ivan J. Legi',         'nim' => 'NIM 240211060108', 'foto' => 'images/ivan.jpeg'],
];

function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim($nama)) ?: [];
    $i = mb_strtoupper(mb_substr($kata[0] ?? '', 0, 1));
    if (count($kata) > 1) {
        $i .= mb_strtoupper(mb_substr(end($kata), 0, 1));
    }
    return $i ?: '?';
}
?>

<section class="hero">
    <div class="ornament">&#10086; &mdash;&mdash;&mdash;&mdash;&mdash; &#10086;</div>
    <h1>Tentang Torang Baca</h1>
</section>

<div class="panel">
    <h2 class="section-title">Tentang Proyek</h2>
    <div class="about-text">
        <p><strong>Torang Baca</strong> dari bahasa Manado yang berarti &ldquo;kita membaca&rdquo;
        adalah website library management sederhana bertema klasik. Pengunjung dapat menelusuri
        katalog buku, sedangkan member yang terdaftar dapat meminjam buku serta memberikan
        rating bintang dan ulasan. </p>
    </div>
</div>

<div class="panel">
    <h2 class="section-title">Anggota Kelompok</h2>
    <div class="member-grid">
        <?php foreach ($anggota as $a): ?>
            <div class="member-card">
                <div class="member-avatar">
                    <?php if (!empty($a['foto'])): ?>
                        <img src="<?= e(base_url($a['foto'])) ?>" alt="Foto <?= e($a['nama']) ?>">
                    <?php else: ?>
                        <?= e(inisial($a['nama'])) ?>
                    <?php endif; ?>
                </div>
                <h3><?= e($a['nama']) ?></h3>
                <p class="nim"><?= e($a['nim']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
