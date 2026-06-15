# 📚 Torang Baca — Perpustakaan Klasik Digital

Proyek tugas kuliah: website manajemen perpustakaan bertema **klasik / perpustakaan lama**,
dibangun **murni** dengan HTML, CSS, JavaScript, PHP, dan MySQL **tanpa library/framework apa pun**
(tanpa jQuery, Bootstrap, CodeIgniter, dll).

---

## ✨ Fitur

- **Katalog dinamis** 20 buku modern dari database MySQL + pencarian judul/penulis/kategori.
- **Cover buku otomatis** dirender sebagai SVG bergaya klasik (`cover.php`) — tanpa file gambar.
- **Rating 5 bintang + ulasan** dari member (1 ulasan per buku, bisa diperbarui).
- **Login admin & member** dengan halaman khusus masing-masing.
- **Manajemen konten buku** (Tambah / Edit / Hapus) oleh admin.
- **Peminjaman + denda keterlambatan** dengan mekanisme **"Tandai Lunas"** untuk admin.
- **Halaman statis** "Tentang Kami" berisi profil anggota kelompok.
- Keamanan: PDO *prepared statements*, `password_hash`, escaping output, dan token CSRF.

---

## 🛠️ Kebutuhan

- **XAMPP** (Apache + MySQL/MariaDB) — atau server PHP 8 + MySQL lainnya.
- PHP 8.0 atau lebih baru.

---

## 🚀 Cara Menjalankan (XAMPP)

1. **Salin folder** `perpustakaan_torang_baca` ke dalam `C:\xampp\htdocs\`.
2. Buka **XAMPP Control Panel** → klik **Start** pada **Apache** dan **MySQL**.
3. **Impor database**:
   - Buka <http://localhost/phpmyadmin>.
   - (Opsional) buat database baru bernama `torang_baca`.
   - Pilih tab **Import** → pilih berkas `sql/torang_baca.sql` → **Go**.
     Skrip ini otomatis membuat database `torang_baca` beserta seluruh tabel & data awal.

   Atau lewat terminal:
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < sql\torang_baca.sql
   ```
4. Buka aplikasi di browser:
   <http://localhost/perpustakaan_torang_baca/>

> Jika konfigurasi MySQL Anda memakai password atau host berbeda,
> ubah nilainya di [`config/db.php`](config/db.php).

---

## 🔑 Akun Demo

| Peran  | Email                     | Kata Sandi |
|--------|---------------------------|------------|
| Admin  | `admin@torangbaca.test`   | `admin123` |
| Member | `member@torangbaca.test`  | `member123`|

---

## 🧭 Alur Penggunaan

**Sebagai member:**
1. Daftar / login → telusuri katalog → buka detail buku.
2. Beri **rating bintang & ulasan**, atau **Pinjam Buku** (lama pinjam 7 hari).
3. Lihat **Riwayat Saya** untuk memantau jatuh tempo & status denda.

**Sebagai admin:**
1. Login → **Panel Admin**.
2. **Kelola Buku**: Tambah / Edit / Hapus.
3. **Kelola Peminjaman & Denda**: denda keterlambatan dihitung otomatis
   (Rp 1.000/hari); klik **"Tandai Lunas"** untuk konfirmasi pelunasan
   (sekaligus mengembalikan stok buku).

---

## 🗂️ Struktur Proyek

```
perpustakaan_torang_baca/
├── config/db.php           # Koneksi database (PDO)
├── sql/torang_baca.sql     # Skema + data awal (20 buku, user, loan)
├── cover.php               # Generator cover SVG dinamis
├── includes/               # header, footer, fungsi, autentikasi
├── assets/css/style.css    # Tema klasik
├── assets/js/main.js       # Rating bintang & validasi (JS murni)
├── index.php               # Katalog (dinamis)
├── book.php                # Detail buku + rating
├── about.php               # Tentang Kami (statis)
├── login / register / logout
├── rate.php  borrow.php    # Proses rating & peminjaman
├── member/dashboard.php    # Halaman khusus member
└── admin/                  # Panel admin: dashboard, CRUD buku, loans
```

---

## ⚙️ Kustomisasi

- **Tarif denda & lama pinjam**: konstanta `DENDA_PER_HARI` dan `LAMA_PINJAM_HARI`
  di [`includes/functions.php`](includes/functions.php).
- **Data anggota kelompok**: array `$anggota` di [`about.php`](about.php).
- **Koneksi database**: [`config/db.php`](config/db.php).

---

## 📋 Pemenuhan Rubrik Tugas

| Persyaratan | Implementasi |
|---|---|
| HTML, CSS, JS, PHP, MySQL | Seluruh proyek |
| Halaman dinamis dari MySQL | `index.php`, `book.php` |
| Halaman statis info anggota | `about.php` |
| Login admin/member | `login.php`, `auth.php` |
| Manajemen konten (CRUD) | `admin/books.php`, `book_form.php`, `book_save.php`, `book_delete.php` |
| Halaman konten kreatif | Rating bintang + sistem denda & "Tandai Lunas" |
| Tanpa framework/library | Semua kode ditulis vanilla |
