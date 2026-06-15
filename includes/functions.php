<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DENDA_PER_HARI = 1000;
const LAMA_PINJAM_HARI = 7;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah(int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function hitung_denda(string $jatuh_tempo, ?string $tgl_kembali = null): int
{
    $akhir = new DateTime($tgl_kembali ?: date('Y-m-d'));
    $tempo = new DateTime($jatuh_tempo);
    if ($akhir <= $tempo) {
        return 0;
    }
    $hari_telat = (int) $tempo->diff($akhir)->days;
    return $hari_telat * DENDA_PER_HARI;
}

function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function get_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Token CSRF tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

function cover_url(array $buku): string
{
    if (!empty($buku['cover_gambar'])) {
        return $buku['cover_gambar'];
    }
    return base_url('cover.php') . '?' . http_build_query([
        'judul'   => $buku['judul'],
        'penulis' => $buku['penulis'],
        'warna'   => $buku['cover_warna'],
    ]);
}

function render_stars(float $nilai): string
{
    $penuh = (int) round($nilai);
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $penuh ? '&#9733;' : '<span class="empty">&#9734;</span>';
    }
    return $html . '</span>';
}

function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        // Jika skrip berada di sub-folder (mis. /admin), naik satu tingkat.
        // Normalisasi ulang karena dirname() di Windows dapat mengembalikan backslash.
        if (str_ends_with($base, '/admin') || str_ends_with($base, '/member')) {
            $base = str_replace('\\', '/', dirname($base));
        }
        $base = rtrim($base, '/');
        if ($base === '/') {
            $base = '';
        }
    }
    return $base . '/' . ltrim($path, '/');
}
