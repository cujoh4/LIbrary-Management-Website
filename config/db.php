<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'torang_baca');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die(
        '<div style="font-family:Georgia,serif;max-width:640px;margin:60px auto;'
        . 'padding:24px;border:2px solid #8b6f3a;background:#f4ecd8;color:#5b3a1a">'
        . '<h2>Gagal terhubung ke database</h2>'
        . '<p>Pastikan layanan <strong>MySQL di XAMPP sudah berjalan</strong> dan '
        . 'database <strong>torang_baca</strong> sudah diimpor dari '
        . '<code>sql/torang_baca.sql</code>.</p>'
        . '<p style="color:#7a1f1f">Periksa apakah MySQL sudah berjalan dan database sudah diimpor.</p>'
        . '</div>'
    );
}
