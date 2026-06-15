<?php
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');

$judul   = trim((string) ($_GET['judul'] ?? 'Tanpa Judul'));
$penulis = trim((string) ($_GET['penulis'] ?? ''));
$warna   = preg_replace('/[^0-9a-fA-F]/', '', (string) ($_GET['warna'] ?? '5b3a1a'));
$w       = max(80, min(800, (int) ($_GET['w'] ?? 320)));
$h       = max(80, min(1100, (int) ($_GET['h'] ?? 440)));

if (strlen($warna) !== 6) {
    $warna = '5b3a1a';
}

function svg_text(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function shade(string $hex, float $factor): string
{
    $r = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
    $g = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
    $b = (int) round(hexdec(substr($hex, 4, 2)) * $factor);
    return sprintf('%02x%02x%02x', min(255, $r), min(255, $g), min(255, $b));
}

function bungkus_baris(string $teks, int $maks_char): array
{
    $kata  = preg_split('/\s+/', $teks) ?: [];
    $baris = [];
    $kini  = '';
    foreach ($kata as $k) {
        $coba = $kini === '' ? $k : $kini . ' ' . $k;
        if (mb_strlen($coba) > $maks_char && $kini !== '') {
            $baris[] = $kini;
            $kini = $k;
        } else {
            $kini = $coba;
        }
    }
    if ($kini !== '') {
        $baris[] = $kini;
    }
    return array_slice($baris, 0, 5);
}

$gelap  = shade($warna, 0.55);
$emas   = 'd9c089';
$krem   = 'f4ecd8';

$baris_judul = bungkus_baris(mb_strtoupper($judul), 14);
$inisial     = mb_strtoupper(mb_substr($judul, 0, 1));

$line_h   = 30;
$mulai_y  = ($h / 2) - (count($baris_judul) - 1) * $line_h / 2;
$cx       = $w / 2;

ob_start();
?>
<svg xmlns="http://www.w3.org/2000/svg" width="<?= $w ?>" height="<?= $h ?>" viewBox="0 0 <?= $w ?> <?= $h ?>" role="img">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#<?= $warna ?>"/>
      <stop offset="100%" stop-color="#<?= $gelap ?>"/>
    </linearGradient>
  </defs>

  <rect width="<?= $w ?>" height="<?= $h ?>" fill="url(#bg)"/>

  <rect x="0" y="0" width="14" height="<?= $h ?>" fill="#<?= $gelap ?>"/>
  <rect x="14" y="0" width="3" height="<?= $h ?>" fill="#<?= $emas ?>" opacity="0.6"/>

  <rect x="26" y="22" width="<?= $w - 48 ?>" height="<?= $h - 44 ?>" fill="none"
        stroke="#<?= $emas ?>" stroke-width="2" opacity="0.85"/>
  <rect x="32" y="28" width="<?= $w - 60 ?>" height="<?= $h - 56 ?>" fill="none"
        stroke="#<?= $emas ?>" stroke-width="1" opacity="0.55"/>

  <text x="<?= $cx ?>" y="70" text-anchor="middle" font-family="Georgia, serif"
        font-size="26" fill="#<?= $emas ?>">&#10086;</text>

  <text x="<?= $cx ?>" y="<?= $h - 96 ?>" text-anchor="middle" font-family="Georgia, serif"
        font-size="120" fill="#<?= $krem ?>" opacity="0.07" font-weight="bold"><?= svg_text($inisial) ?></text>

  <?php foreach ($baris_judul as $i => $baris): ?>
  <text x="<?= $cx ?>" y="<?= (int) round($mulai_y + $i * $line_h) ?>" text-anchor="middle"
        font-family="Georgia, 'Times New Roman', serif" font-size="22" font-weight="bold"
        fill="#<?= $krem ?>" letter-spacing="1"><?= svg_text($baris) ?></text>
  <?php endforeach; ?>

  <line x1="<?= $cx - 40 ?>" y1="<?= $h - 86 ?>" x2="<?= $cx + 40 ?>" y2="<?= $h - 86 ?>"
        stroke="#<?= $emas ?>" stroke-width="1" opacity="0.7"/>

  <?php if ($penulis !== ''): ?>
  <text x="<?= $cx ?>" y="<?= $h - 62 ?>" text-anchor="middle"
        font-family="Georgia, serif" font-size="15" font-style="italic"
        fill="#<?= $krem ?>" opacity="0.92"><?= svg_text($penulis) ?></text>
  <?php endif; ?>

  <text x="<?= $cx ?>" y="<?= $h - 34 ?>" text-anchor="middle"
        font-family="Georgia, serif" font-size="11" letter-spacing="3"
        fill="#<?= $emas ?>">TORANG BACA</text>
</svg>
<?php
echo ob_get_clean();
