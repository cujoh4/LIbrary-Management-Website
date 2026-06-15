<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$errors = [];
$nama = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nama     = trim((string) ($_POST['nama'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $konfirm  = (string) ($_POST['konfirmasi'] ?? '');

    if ($nama === '') {
        $errors['nama'] = 'Nama wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Kata sandi minimal 6 karakter.';
    }
    if ($password !== $konfirm) {
        $errors['konfirmasi'] = 'Konfirmasi kata sandi tidak cocok.';
    }

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $errors['email'] = 'Email sudah terdaftar.';
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "member")'
            );
            $ins->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT)]);
            set_flash('sukses', 'Pendaftaran berhasil! Silakan masuk dengan akun Anda.');
            header('Location: ' . base_url('login.php'));
            exit;
        }
    }
}

$page_title = 'Daftar';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel panel-narrow">
    <h2 class="section-title">Daftar Member</h2>

    <form method="post" action="<?= e(base_url('register.php')) ?>" data-validate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input id="nama" class="form-control" type="text" name="nama" value="<?= e($nama) ?>" required>
            <?php if (isset($errors['nama'])): ?><div class="field-error"><?= e($errors['nama']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" class="form-control" type="email" name="email" value="<?= e($email) ?>" required>
            <?php if (isset($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input id="password" class="form-control" type="password" name="password" data-min="6" required>
            <span class="form-hint">Minimal 6 karakter.</span>
            <?php if (isset($errors['password'])): ?><div class="field-error"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="konfirmasi">Konfirmasi Kata Sandi</label>
            <input id="konfirmasi" class="form-control" type="password" name="konfirmasi" required>
            <?php if (isset($errors['konfirmasi'])): ?><div class="field-error"><?= e($errors['konfirmasi']) ?></div><?php endif; ?>
        </div>
        <button class="btn btn-block" type="submit">Daftar</button>
    </form>

    <p class="text-center mt-2 muted">Sudah punya akun?
        <a href="<?= e(base_url('login.php')) ?>">Masuk di sini</a>.</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
