-- ============================================
-- DATABASE: smk_bnb
-- Website Sekolah SMK Bangun Nusa Bangsa
-- ============================================

CREATE DATABASE IF NOT EXISTS smk_bnb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smk_bnb;

-- ─── TABEL USERS (Admin / Author) ───
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TABEL KATEGORI ARTIKEL ───
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    deskripsi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TABEL ARTIKEL ───
CREATE TABLE IF NOT EXISTS artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kategori_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    thumbnail VARCHAR(255) DEFAULT NULL,
    isi LONGTEXT NOT NULL,
    ringkasan TEXT DEFAULT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── TABEL KOMENTAR ───
CREATE TABLE IF NOT EXISTS komentar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artikel_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    isi TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── TABEL PENGATURAN WEBSITE ───
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_key VARCHAR(100) NOT NULL UNIQUE,
    nilai TEXT DEFAULT NULL
) ENGINE=InnoDB;

-- ─── DATA AWAL ───

-- Admin default (password: admin123)
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@smkbnb.sch.id', '$2y$10$V4Q97VCwTwXlpYEpKkF1z.8d2YiigCkPvkskne7MoplFyS19fubVu', 'admin');

-- Kategori default
INSERT INTO kategori (nama, slug, deskripsi) VALUES
('Berita Sekolah', 'berita-sekolah', 'Berita dan informasi terbaru dari SMK Bangun Nusa Bangsa'),
('Kegiatan', 'kegiatan', 'Dokumentasi kegiatan dan acara sekolah'),
('Prestasi', 'prestasi', 'Prestasi siswa dan guru SMK BNB'),
('Akademik', 'akademik', 'Informasi akademik dan kurikulum'),
('PPDB', 'ppdb', 'Informasi Penerimaan Peserta Didik Baru');

-- Artikel sampel
INSERT INTO artikel (user_id, kategori_id, judul, slug, isi, ringkasan, status) VALUES
(1, 1, 'Selamat Datang di Website Resmi SMK Bangun Nusa Bangsa', 'selamat-datang-di-website-resmi-smk-bnb', 
'<p>Dengan bangga kami memperkenalkan website resmi <strong>SMK Bangun Nusa Bangsa</strong> yang baru. Website ini dirancang untuk menjadi pusat informasi digital bagi seluruh civitas akademika, orang tua/wali siswa, dan masyarakat umum.</p><p>Melalui website ini, Anda dapat mengakses berbagai informasi terkini mengenai kegiatan sekolah, program keahlian, pendaftaran siswa baru (PPDB), serta berita dan artikel edukatif lainnya.</p><p>Kami berkomitmen untuk terus memperbarui konten website ini agar selalu relevan dan bermanfaat. Terima kasih atas kunjungan Anda!</p>',
'Website resmi SMK Bangun Nusa Bangsa kini hadir dengan tampilan baru dan fitur yang lebih lengkap untuk melayani kebutuhan informasi civitas akademika.',
'published'),

(1, 2, 'MPLS 2026/2027 - Masa Pengenalan Lingkungan Sekolah', 'mpls-2026-2027-masa-pengenalan-lingkungan-sekolah',
'<p>Kegiatan <strong>Masa Pengenalan Lingkungan Sekolah (MPLS)</strong> tahun ajaran 2026/2027 telah sukses dilaksanakan pada tanggal 14-16 Juli 2026. Kegiatan ini diikuti oleh seluruh siswa baru kelas X dari semua program keahlian.</p><p>MPLS bertujuan untuk memperkenalkan lingkungan sekolah, budaya sekolah, serta membangun karakter siswa sejak awal masa pendidikan mereka di SMK Bangun Nusa Bangsa.</p><p>Berbagai kegiatan menarik dilaksanakan selama MPLS, antara lain:<br>- Pengenalan program keahlian<br>- Tour fasilitas sekolah<br>- Kegiatan team building<br>- Sosialisasi tata tertib sekolah<br>- Penanaman nilai-nilai karakter</p>',
'Kegiatan MPLS 2026/2027 sukses dilaksanakan dengan berbagai kegiatan menarik untuk memperkenalkan lingkungan sekolah kepada siswa baru.',
'published'),

(1, 3, 'Siswa SMK BNB Raih Juara di Lomba Kompetensi Siswa', 'siswa-smk-bnb-raih-juara-lks',
'<p>Kebanggaan besar bagi keluarga besar <strong>SMK Bangun Nusa Bangsa</strong>! Siswa kami berhasil meraih prestasi membanggakan dalam Lomba Kompetensi Siswa (LKS) tingkat Kabupaten Bogor tahun 2026.</p><p>Berikut daftar prestasi yang diraih:<br>- <strong>Juara 1</strong> - IT Network Systems Administration<br>- <strong>Juara 2</strong> - Web Technologies<br>- <strong>Juara 3</strong> - Accounting</p><p>Prestasi ini membuktikan kualitas pendidikan vokasi di SMK BNB yang terus berkembang dan mampu bersaing di tingkat regional maupun nasional.</p>',
'Siswa SMK BNB meraih berbagai juara dalam Lomba Kompetensi Siswa tingkat Kabupaten Bogor tahun 2026.',
'published'),

(1, 5, 'Informasi PPDB 2026/2027 - Pendaftaran Dibuka!', 'informasi-ppdb-2026-2027',
'<p><strong>SMK Bangun Nusa Bangsa</strong> membuka pendaftaran Penerimaan Peserta Didik Baru (PPDB) untuk tahun ajaran 2026/2027. Pendaftaran dapat dilakukan secara online maupun langsung ke sekolah.</p><p><strong>Program Keahlian yang Tersedia:</strong><br>1. Teknik Komputer dan Jaringan (TKJ)<br>2. Akuntansi dan Keuangan Lembaga (AK)<br>3. Teknik Kendaraan Ringan (TKR)</p><p><strong>Persyaratan Umum:</strong><br>- Lulusan SMP/MTs sederajat<br>- Fotokopi ijazah dan SKHUN<br>- Pas foto 3x4 (4 lembar)<br>- Fotokopi Kartu Keluarga<br>- Fotokopi Akta Kelahiran</p>',
'Pendaftaran PPDB 2026/2027 SMK Bangun Nusa Bangsa telah dibuka! Segera daftarkan diri Anda di program keahlian pilihan.',
'published');

-- Settings default
INSERT INTO settings (nama_key, nilai) VALUES
('nama_sekolah', 'SMK Bangun Nusa Bangsa'),
('tagline', 'Smart Digital Campus'),
('alamat', 'Jl. Roda Pembangunan No.45, Karadenan, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16913'),
('telepon', '(0251) 755-5982'),
('email', 'info@smk-bnb.sch.id'),
('npsn', '12345678'),
('akreditasi', 'A'),
('kepala_sekolah', 'Muhammad Yunus, S.E., M.Pd.');
