<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            login_user($u);
            set_flash('sukses', 'Selamat datang kembali, ' . $u['nama'] . '!');
            $tujuan = $u['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php';
            header('Location: ' . base_url($tujuan));
            exit;
        }
        $error = 'Email atau kata sandi salah.';
    }
}

$page_title = 'Masuk';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel panel-narrow">
    <h2 class="section-title">Masuk</h2>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('login.php')) ?>" data-validate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" class="form-control" type="email" name="email"
                   value="<?= e($email) ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input id="password" class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-block" type="submit">Masuk</button>
    </form>

    <p class="text-center mt-2 muted">Belum punya akun?
        <a href="<?= e(base_url('register.php')) ?>">Daftar di sini</a>.</p>
  
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
