<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);
$mode_edit = $id > 0;

$book = [
    'id' => 0, 'judul' => '', 'penulis' => '', 'tahun' => date('Y'),
    'kategori' => '', 'deskripsi' => '', 'cover_warna' => '5b3a1a', 'cover_gambar' => '', 'stok' => 1,
];

if ($mode_edit) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Buku tidak ditemukan.');
        header('Location: ' . base_url('admin/books.php'));
        exit;
    }
    $book = $found;
}

if (!empty($_SESSION['old_input'])) {
    $book = array_merge($book, $_SESSION['old_input']);
    unset($_SESSION['old_input']);
}

$page_title = $mode_edit ? 'Edit Buku' : 'Tambah Buku';
require_once __DIR__ . '/../includes/header.php';
?>

<p class="mt-2"><a href="<?= e(base_url('admin/books.php')) ?>">&larr; Kembali ke Daftar Buku</a></p>

<div class="panel">
    <h2 class="section-title"><?= $mode_edit ? 'Edit Buku' : 'Tambah Buku Baru' ?></h2>

    <div class="book-detail">
        <div class="cover-wrap">
            <img src="<?= e(cover_url($book)) ?>" alt="Pratinjau sampul">
            <p class="form-hint text-center mt-2">Pratinjau sampul</p>
        </div>
        <form method="post" action="<?= e(base_url('admin/book_save.php')) ?>" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">

            <div class="form-group">
                <label for="judul">Judul</label>
                <input id="judul" class="form-control" type="text" name="judul" value="<?= e($book['judul']) ?>" required>
            </div>
            <div class="form-group">
                <label for="penulis">Penulis</label>
                <input id="penulis" class="form-control" type="text" name="penulis" value="<?= e($book['penulis']) ?>" required>
            </div>
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <input id="kategori" class="form-control" type="text" name="kategori"
                       value="<?= e($book['kategori']) ?>" placeholder="mis. Fiksi, Pengembangan Diri" required>
            </div>
            <div class="form-group">
                <label for="tahun">Tahun Terbit</label>
                <input id="tahun" class="form-control" type="number" name="tahun" min="1" max="<?= date('Y') ?>"
                       value="<?= e((string) $book['tahun']) ?>" required>
            </div>
            <div class="form-group">
                <label for="stok">Stok</label>
                <input id="stok" class="form-control" type="number" name="stok" min="0"
                       value="<?= e((string) $book['stok']) ?>" required>
            </div>
            <div class="form-group">
                <label for="cover_gambar">URL Gambar Sampul</label>
                <input id="cover_gambar" class="form-control" type="url"
                       name="cover_gambar" value="<?= e($book['cover_gambar'] ?? '') ?>"
                       placeholder="https://...">
                <span class="form-hint">Kosongkan untuk menggunakan sampul warna otomatis.</span>
            </div>
            <div class="form-group">
                <label for="cover_warna">Warna Sampul (Fallback)</label>
                <input id="cover_warna" class="form-control" type="color"
                       name="cover_warna" value="#<?= e($book['cover_warna']) ?>"
                       style="height:44px;padding:4px;max-width:120px">
                <span class="form-hint">Dipakai jika URL gambar di atas dikosongkan.</span>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" class="form-control" name="deskripsi" rows="5" required><?= e($book['deskripsi']) ?></textarea>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit"><?= $mode_edit ? 'Simpan Perubahan' : 'Tambah Buku' ?></button>
                <a class="btn btn-secondary" href="<?= e(base_url('admin/books.php')) ?>">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const urlInput = document.getElementById('cover_gambar');
    const preview  = document.querySelector('.cover-wrap img');
    const fallback = preview.src;
    urlInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val) {
            preview.src = val;
            preview.onerror = function () { preview.src = fallback; preview.onerror = null; };
        } else {
            preview.src = fallback;
            preview.onerror = null;
        }
    });
}());
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
