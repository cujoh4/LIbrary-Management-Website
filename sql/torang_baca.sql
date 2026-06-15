-- Hapus tabel lama (urutan menghormati foreign key) agar import dapat diulang.
DROP TABLE IF EXISTS loans;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
--  Tabel users
-- ------------------------------------------------------------
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)        NOT NULL,
    email      VARCHAR(150)        NOT NULL UNIQUE,
    password   VARCHAR(255)        NOT NULL,
    role       ENUM('admin','member') NOT NULL DEFAULT 'member',
    created_at TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabel books
-- ------------------------------------------------------------
CREATE TABLE books (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    judul       VARCHAR(200)  NOT NULL,
    penulis     VARCHAR(150)  NOT NULL,
    tahun       SMALLINT      NOT NULL,
    kategori    VARCHAR(80)   NOT NULL DEFAULT 'Umum',
    deskripsi   TEXT          NOT NULL,
    cover_warna  CHAR(6)       NOT NULL DEFAULT '5b3a1a',
    cover_gambar VARCHAR(500)  NULL     DEFAULT NULL,
    stok         INT           NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabel ratings (1 rating per member per buku)
-- ------------------------------------------------------------
CREATE TABLE ratings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    book_id    INT       NOT NULL,
    user_id    INT       NOT NULL,
    bintang    TINYINT   NOT NULL,
    komentar   TEXT      NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_book_user (book_id, user_id),
    CONSTRAINT fk_rating_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_bintang CHECK (bintang BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabel loans (peminjaman + denda + status lunas)
-- ------------------------------------------------------------
CREATE TABLE loans (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    book_id         INT       NOT NULL,
    user_id         INT       NOT NULL,
    tgl_pinjam      DATE      NOT NULL,
    tgl_jatuh_tempo DATE      NOT NULL,
    tgl_kembali     DATE      NULL,
    denda           INT       NOT NULL DEFAULT 0,
    lunas           TINYINT   NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_loan_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    CONSTRAINT fk_loan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DATA AWAL
-- ============================================================

INSERT INTO users (id, nama, email, password, role) VALUES
(1, 'Administrator', 'admin@torangbaca.test',  '$2y$10$5/CuM8IndfurbFhJMZ2Kg.icpdN7dODmxakMrU.UpI8YPLsSLyWAC', 'admin'),
(2, 'Budi Pembaca',  'member@torangbaca.test', '$2y$10$b870zKSkJT37ycYreMfsxuaJVhJ5ggKTU/EQ9eX2gYMSh1iNeOM3W', 'member');

-- 20 buku modern beserta warna cover & URL gambar asli.
INSERT INTO books (judul, penulis, tahun, kategori, deskripsi, cover_warna, cover_gambar, stok) VALUES
('Laut Bercerita', 'Leila S. Chudori', 2017, 'Fiksi Sejarah', 'Kisah Biru Laut, seorang aktivis mahasiswa yang hilang pada masa Orde Baru, dan keluarga yang menanti dalam penantian tak berujung.', '2f4f4f', 'https://covers.openlibrary.org/b/id/10648285-L.jpg', 4),
('Filosofi Teras', 'Henry Manampiring', 2018, 'Pengembangan Diri', 'Pengantar populer filsafat Stoa untuk mengelola emosi negatif dan hidup lebih tenang di tengah dunia yang penuh tekanan.', '7a3b2e', 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1548033656i/42861019.jpg', 6),
('Atomic Habits', 'James Clear', 2018, 'Pengembangan Diri', 'Panduan membangun kebiasaan baik dan menghapus kebiasaan buruk lewat perubahan kecil satu persen yang konsisten.', '2c3e50', 'https://covers.openlibrary.org/b/id/12539702-L.jpg', 5),
('Sapiens', 'Yuval Noah Harari', 2011, 'Sejarah', 'Sejarah ringkas umat manusia, dari revolusi kognitif hingga revolusi sains yang membentuk dunia modern.', '6b4226', 'https://covers.openlibrary.org/b/id/8634250-L.jpg', 3),
('The Midnight Library', 'Matt Haig', 2020, 'Fiksi', 'Di antara hidup dan mati terdapat perpustakaan berisi kemungkinan hidup yang berbeda. Sebuah renungan tentang penyesalan dan harapan.', '274060', 'https://covers.openlibrary.org/b/id/10313767-L.jpg', 4),
('Bumi', 'Tere Liye', 2014, 'Fantasi', 'Petualangan Raib, Seli, dan Ali menembus dunia paralel dalam seri fantasi populer karya Tere Liye.', '1f6f4a', 'https://covers.openlibrary.org/b/id/12810708-L.jpg', 7),
('Hujan', 'Tere Liye', 2016, 'Fiksi', 'Tentang persahabatan, cinta, dan melupakan, berlatar dunia masa depan yang dilanda bencana iklim.', '35506b', 'https://covers.openlibrary.org/b/id/10872657-L.jpg', 5),
('Pulang', 'Leila S. Chudori', 2012, 'Fiksi Sejarah', 'Kisah para eksil politik 1965 yang terdampar di Paris dan kerinduan mereka untuk pulang ke tanah air.', '5a2a3a', 'https://covers.openlibrary.org/b/id/15094936-L.jpg', 3),
('Cantik Itu Luka', 'Eka Kurniawan', 2002, 'Fiksi', 'Saga keluarga penuh tragedi, mitos, dan sejarah Indonesia lewat sosok Dewi Ayu dan keturunannya.', '7a1f2b', 'https://covers.openlibrary.org/b/id/13903116-L.jpg', 2),
('Educated', 'Tara Westover', 2018, 'Memoar', 'Memoar seorang perempuan yang tumbuh tanpa sekolah formal hingga meraih gelar doktor di Cambridge.', '3a5a5a', 'https://covers.openlibrary.org/b/id/8314077-L.jpg', 3),
('Laskar Pelangi', 'Andrea Hirata', 2005, 'Fiksi', 'Semangat sepuluh anak Belitung mengejar pendidikan di tengah keterbatasan—kisah inspiratif yang melegenda.', '2e6b5e', 'https://covers.openlibrary.org/b/id/7079796-L.jpg', 6),
('Pergi', 'Tere Liye', 2018, 'Fiksi', 'Kelanjutan kisah Bujang dalam dunia penuh intrik kekuasaan, pencarian jati diri, dan makna pulang.', '4a3b6b', 'https://covers.openlibrary.org/b/id/12905873-L.jpg', 4),
('Dilan 1990', 'Pidi Baiq', 2014, 'Roman', 'Kisah cinta remaja Dilan dan Milea di Bandung tahun 1990 yang manis, lucu, dan ikonik.', '7a4a1f', 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1459959274i/29830096.jpg', 8),
('Negeri 5 Menara', 'Ahmad Fuadi', 2009, 'Fiksi', 'Perjuangan Alif dan sahabat-sahabatnya di pondok pesantren dengan mantra "man jadda wajada".', '1f4a6b', 'https://covers.openlibrary.org/b/id/14303993-L.jpg', 5),
('The Psychology of Money', 'Morgan Housel', 2020, 'Keuangan', 'Pelajaran tentang kekayaan, keserakahan, dan kebahagiaan—mengapa perilaku lebih penting daripada rumus keuangan.', '2c5530', 'https://covers.openlibrary.org/b/id/10389354-L.jpg', 4),
('Norwegian Wood', 'Haruki Murakami', 1987, 'Fiksi', 'Nostalgia, cinta, dan kehilangan di Tokyo akhir 1960-an lewat kenangan Toru Watanabe.', '3a4a2b', 'https://covers.openlibrary.org/b/id/2237620-L.jpg', 3),
('Sebuah Seni untuk Bersikap Bodo Amat', 'Mark Manson', 2016, 'Pengembangan Diri', 'Pendekatan kontra-intuitif untuk menjalani hidup yang baik dengan memilih hal yang benar-benar penting.', '6b2a2a', 'https://covers.openlibrary.org/b/id/8231990-L.jpg', 5),
('Aroma Karsa', 'Dee Lestari', 2018, 'Fiksi', 'Perburuan bunga legendaris Puspa Karsa lewat indra penciuman luar biasa milik Jati Wesi dan Tanaya Suma.', '4a5a2a', 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1519064063i/37830526.jpg', 3),
('Garis Waktu', 'Fiersa Besari', 2016, 'Puisi', 'Catatan dan puisi tentang perjalanan hati, jarak, dan waktu yang menyertai sebuah perpisahan.', '5a3a5a', 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1471933385i/31604219.jpg', 6),
('Tentang Kamu', 'Tere Liye', 2016, 'Fiksi', 'Pengacara muda menelusuri kisah hidup luar biasa seorang perempuan sederhana bernama Sri Ningsih.', '2a3a5a', 'https://covers.openlibrary.org/b/id/14541664-L.jpg', 5);

-- Beberapa rating contoh dari member demo (Budi).
INSERT INTO ratings (book_id, user_id, bintang, komentar) VALUES
(1, 2, 5, 'Menyentuh dan memilukan. Salah satu novel Indonesia terbaik yang pernah saya baca.'),
(3, 2, 4, 'Praktis dan mudah diterapkan, walau beberapa bagian terasa berulang.'),
(6, 2, 5, 'Seru sekali! Imajinasi Tere Liye benar-benar liar.');

-- Contoh peminjaman untuk menguji fitur denda & "Tandai Lunas".
--   1) Terlambat & belum lunas  -> denda dihitung admin, tombol "Tandai Lunas" aktif
--   2) Masih berjalan (belum jatuh tempo)
--   3) Sudah kembali & lunas (riwayat)
INSERT INTO loans (book_id, user_id, tgl_pinjam, tgl_jatuh_tempo, tgl_kembali, denda, lunas) VALUES
(1, 2, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 13 DAY), NULL, 0, 0),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  DATE_ADD(CURDATE(), INTERVAL 4 DAY),  NULL, 0, 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 23 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 3000, 1);

-- ============================================================
--  UPDATE cover_gambar untuk database yang sudah ada
--  (jalankan di phpMyAdmin jika tidak ingin import ulang)
-- ============================================================
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/10648285-L.jpg' WHERE judul = 'Laut Bercerita';
UPDATE books SET cover_gambar = 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1548033656i/42861019.jpg' WHERE judul = 'Filosofi Teras';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/12539702-L.jpg' WHERE judul = 'Atomic Habits';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/8634250-L.jpg' WHERE judul = 'Sapiens';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/10313767-L.jpg' WHERE judul = 'The Midnight Library';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/12810708-L.jpg' WHERE judul = 'Bumi';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/10872657-L.jpg' WHERE judul = 'Hujan';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/15094936-L.jpg' WHERE judul = 'Pulang';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/13903116-L.jpg' WHERE judul = 'Cantik Itu Luka';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/8314077-L.jpg' WHERE judul = 'Educated';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/7079796-L.jpg' WHERE judul = 'Laskar Pelangi';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/12905873-L.jpg' WHERE judul = 'Pergi';
UPDATE books SET cover_gambar = 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1459959274i/29830096.jpg' WHERE judul = 'Dilan 1990';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/14303993-L.jpg' WHERE judul = 'Negeri 5 Menara';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/10389354-L.jpg' WHERE judul = 'The Psychology of Money';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/2237620-L.jpg' WHERE judul = 'Norwegian Wood';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/8231990-L.jpg' WHERE judul = 'Sebuah Seni untuk Bersikap Bodo Amat';
UPDATE books SET cover_gambar = 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1519064063i/37830526.jpg' WHERE judul = 'Aroma Karsa';
UPDATE books SET cover_gambar = 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1471933385i/31604219.jpg' WHERE judul = 'Garis Waktu';
UPDATE books SET cover_gambar = 'https://covers.openlibrary.org/b/id/14541664-L.jpg' WHERE judul = 'Tentang Kamu';
