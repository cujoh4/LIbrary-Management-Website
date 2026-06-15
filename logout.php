<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'Anda telah keluar. Sampai jumpa lagi!');
header('Location: ' . base_url('index.php'));
exit;
