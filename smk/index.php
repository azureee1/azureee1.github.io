<?php
require_once 'config.php';

// Ambil artikel terbaru (published)
$artikelQuery = $conn->query("SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug, u.nama as penulis 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.status = 'published' 
    ORDER BY a.created_at DESC 
    LIMIT 6");

// Ambil artikel populer
$popularQuery = $conn->query("SELECT a.*, k.nama as kategori_nama 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    WHERE a.status = 'published' 
    ORDER BY a.views DESC 
    LIMIT 4");

// Ambil semua kategori
$kategoriQuery = $conn->query("SELECT k.*, COUNT(a.id) as total_artikel 
    FROM kategori k 
    LEFT JOIN artikel a ON k.id = a.kategori_id AND a.status = 'published' 
    GROUP BY k.id 
    ORDER BY total_artikel DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK Bangun Nusa Bangsa — Smart Digital Campus</title>
    <meta name="description" content="Website Resmi SMK Bangun Nusa Bangsa. Lembaga pendidikan terakreditasi A yang memadukan inovasi teknologi, integritas akhlak, dan kurikulum industri.">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════ -->
<!-- NAVBAR -->
<!-- ═══════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <div class="brand-icon">
                <img src="assets/img/logo-smk.jpg" alt="Logo SMK Bangun Nusa Bangsa">
            </div>
            <div>
                <div class="brand-name">SMK Bangun Nusa Bangsa</div>
                <div class="brand-sub">SMK • Kabupaten Bogor • Akreditasi A</div>
            </div>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#jurusan">Jurusan</a></li>
                <li class="nav-item"><a class="nav-link" href="artikel.php">Berita</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="admin/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Portal
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════ -->
<!-- HERO SECTION -->
<!-- ═══════════════════════════════════════════ -->
<section class="hero-section" id="beranda">
    <div class="hero-bg-effects">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
        <div class="hero-grid-pattern"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-7">
                <div class="hero-badge animate-fade-in">
                    <span class="badge-dot"></span>
                    <span>SMART DIGITAL CAMPUS 2026</span>
                </div>
                
                <h1 class="hero-title animate-fade-in-up">
                    Membangun Generasi <span class="text-gradient">Unggul & Terampil</span> di Era Digital
                </h1>
                
                <p class="hero-desc animate-fade-in-up delay-1">
                    Selamat datang di portal resmi <strong>SMK Bangun Nusa Bangsa</strong>. 
                    Lembaga pendidikan terakreditasi A yang memadukan inovasi teknologi, 
                    integritas akhlak, dan kurikulum industri.
                </p>
                
                <div class="hero-cta animate-fade-in-up delay-2">
                    <a href="artikel.php" class="btn btn-warning btn-lg rounded-pill px-4">
                        <i class="fas fa-newspaper me-2"></i> Baca Artikel
                    </a>
                    <a href="#jurusan" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="fas fa-compass me-2"></i> Jelajahi Jurusan
                    </a>
                </div>
                
                <div class="hero-trust animate-fade-in-up delay-3">
                    <div class="trust-item"><i class="fas fa-circle-check text-success"></i> Akreditasi A BAN-SM</div>
                    <span class="trust-sep">•</span>
                    <div class="trust-item"><i class="fas fa-handshake text-info"></i> Kemitraan DUDI</div>
                    <span class="trust-sep">•</span>
                    <div class="trust-item"><i class="fas fa-certificate text-warning"></i> Sertifikasi BNSP</div>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-card animate-fade-in-right">
                    <div class="hero-card-header">
                        <span><i class="fas fa-newspaper text-warning me-1"></i> Artikel Terbaru</span>
                        <span class="badge bg-success bg-opacity-25 text-success">
                            <i class="fas fa-circle fa-2xs me-1"></i> LIVE
                        </span>
                    </div>
                    <div class="hero-card-body">
                        <?php 
                        $heroArtikel = $conn->query("SELECT judul, slug, created_at FROM artikel WHERE status='published' ORDER BY created_at DESC LIMIT 3");
                        while($ha = $heroArtikel->fetch_assoc()): 
                        ?>
                        <a href="detail.php?slug=<?= $ha['slug'] ?>" class="hero-article-item">
                            <div class="hero-article-dot"></div>
                            <div>
                                <div class="hero-article-title"><?= htmlspecialchars($ha['judul']) ?></div>
                                <div class="hero-article-date"><?= timeAgo($ha['created_at']) ?></div>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                    <div class="hero-card-footer">
                        <a href="artikel.php" class="text-decoration-none"><i class="fas fa-arrow-right me-1"></i> Lihat Semua Artikel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ -->
<!-- STATS COUNTER -->
<!-- ═══════════════════════════════════════════ -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="343">0</div>
                    <div class="stat-label">Peserta Didik Aktif</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="30">0</div>
                    <div class="stat-label">Guru & Tenaga Pendidik</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="3">0</div>
                    <div class="stat-label">Program Keahlian</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number-text">Grade A</div>
                    <div class="stat-label">Akreditasi BAN-SM</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ -->
<!-- TENTANG / SAMBUTAN KEPALA SEKOLAH -->
<!-- ═══════════════════════════════════════════ -->
<section class="section-about" id="tentang">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="about-image-frame">
                    <img src="assets/img/kepala-sekolah.jpg" alt="Muhammad Yunus, S.E., M.Pd. - Kepala SMK Bangun Nusa Bangsa">
                    <div class="about-badge">
                        <i class="fas fa-award me-1"></i> Kepala Sekolah
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="section-tag">Sambutan Pimpinan Lembaga</div>
                <h2 class="section-title text-start">
                    Mempersiapkan Generasi Berkarakter & Terampil di Era Digital
                </h2>
                
                <blockquote class="about-quote">
                    "Pendidikan bermutu adalah perpaduan harmonis antara keteguhan akhlak mulia, 
                    disiplin kerja profesional, dan penguasaan teknologi mutakhir."
                </blockquote>
                
                <p class="text-muted mb-4" style="line-height: 1.8;">
                    Selamat datang di portal resmi <strong>SMK Bangun Nusa Bangsa</strong>. 
                    Kami berkomitmen untuk terus berinovasi dalam menghadirkan ekosistem pembelajaran 
                    modern yang menumbuhkan potensi terbaik setiap peserta didik agar siap berkarier, 
                    berwirausaha, maupun melanjutkan studi.
                </p>
                
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">Muhammad Yunus, S.E., M.Pd.</h5>
                    <small class="text-muted">Kepala SMK Bangun Nusa Bangsa</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ -->
<!-- PROGRAM KEAHLIAN / JURUSAN -->
<!-- ═══════════════════════════════════════════ -->
<section class="section-jurusan" id="jurusan">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Program Kejuruan & Vokasi</div>
            <h2 class="section-title">Pilihan Program Keahlian Unggulan</h2>
            <p class="section-subtitle">Kurikulum berbasis industri dan sertifikasi profesi untuk mencetak lulusan terampil dan siap kerja.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="jurusan-card">
                    <div class="jurusan-icon">
                        <img src="assets/img/jurusan-tkj.jpg" alt="Badge Jurusan Teknik Komputer dan Jaringan">
                    </div>
                    <span class="jurusan-badge">JURUSAN 01</span>
                    <h3 class="jurusan-title">Teknik Komputer dan Jaringan (TKJ)</h3>
                    <p class="jurusan-desc">Mempelajari arsitektur jaringan komputer, administrasi server, keamanan siber, komputasi awan, dan konfigurasi router.</p>
                    <div class="jurusan-skills">
                        <span>MikroTik & Cisco</span>
                        <span>Server Linux</span>
                        <span>Cyber Security</span>
                        <span>Cloud Computing</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="jurusan-card">
                    <div class="jurusan-icon">
                        <img src="assets/img/jurusan-ak.jpg" alt="Badge Jurusan Akuntansi">
                    </div>
                    <span class="jurusan-badge">JURUSAN 02</span>
                    <h3 class="jurusan-title">Akuntansi dan Keuangan Lembaga (AK)</h3>
                    <p class="jurusan-desc">Mempelajari siklus akuntansi terkomputerisasi, perpajakan digital, spreadsheet bisnis, dan software akuntansi.</p>
                    <div class="jurusan-skills">
                        <span>MYOB & Accurate</span>
                        <span>Perpajakan</span>
                        <span>Laporan Keuangan</span>
                        <span>Audit Dasar</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="jurusan-card">
                    <div class="jurusan-icon">
                        <img src="assets/img/jurusan-tkr.jpg" alt="Badge Jurusan Teknik Kendaraan Ringan">
                    </div>
                    <span class="jurusan-badge">JURUSAN 03</span>
                    <h3 class="jurusan-title">Teknik Kendaraan Ringan (TKR)</h3>
                    <p class="jurusan-desc">Mempelajari diagnosis sistem mesin EFI, kelistrikan otomotif, servis transmisi, chassis, dan manajemen bengkel modern.</p>
                    <div class="jurusan-skills">
                        <span>Engine Management</span>
                        <span>EFI Diagnostic</span>
                        <span>Kelistrikan Bodi</span>
                        <span>Safety K3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ -->
<!-- ARTIKEL TERBARU -->
<!-- ═══════════════════════════════════════════ -->
<section class="section-artikel" id="artikel">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Berita & Informasi</div>
            <h2 class="section-title">Artikel Terbaru</h2>
            <p class="section-subtitle">Informasi terkini seputar kegiatan, prestasi, dan berita dari SMK Bangun Nusa Bangsa.</p>
        </div>
        
        <div class="row g-4">
            <?php while($artikel = $artikelQuery->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <article class="artikel-card">
                    <div class="artikel-thumbnail">
                        <?php if($artikel['thumbnail']): ?>
                            <img src="uploads/thumbnails/<?= $artikel['thumbnail'] ?>" alt="<?= htmlspecialchars($artikel['judul']) ?>">
                        <?php else: ?>
                            <div class="artikel-thumb-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        <?php endif; ?>
                        <span class="artikel-kategori"><?= htmlspecialchars($artikel['kategori_nama']) ?></span>
                    </div>
                    <div class="artikel-body">
                        <div class="artikel-meta">
                            <span><i class="fas fa-user me-1"></i> <?= htmlspecialchars($artikel['penulis']) ?></span>
                            <span><i class="fas fa-clock me-1"></i> <?= tanggalIndo($artikel['created_at']) ?></span>
                        </div>
                        <h3 class="artikel-title">
                            <a href="detail.php?slug=<?= $artikel['slug'] ?>"><?= htmlspecialchars($artikel['judul']) ?></a>
                        </h3>
                        <p class="artikel-excerpt"><?= truncateText($artikel['ringkasan'] ?? $artikel['isi'], 120) ?></p>
                    </div>
                    <div class="artikel-footer">
                        <a href="detail.php?slug=<?= $artikel['slug'] ?>" class="btn-read-more">
                            Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                        <span class="artikel-views"><i class="fas fa-eye me-1"></i> <?= $artikel['views'] ?></span>
                    </div>
                </article>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="artikel.php" class="btn btn-outline-primary btn-lg rounded-pill px-5">
                <i class="fas fa-th-list me-2"></i> Lihat Semua Artikel
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ -->
<!-- KONTAK & FOOTER -->
<!-- ═══════════════════════════════════════════ -->
<section class="section-kontak" id="kontak">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Hubungi Kami</div>
            <h2 class="section-title">Lokasi & Kontak</h2>
            <p class="section-subtitle">Kunjungi kami atau hubungi melalui saluran komunikasi berikut.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="kontak-card">
                    <div class="kontak-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <h4>Alamat</h4>
                    <p>Jl. Roda Pembangunan No.45, Karadenan, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16913</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kontak-card">
                    <div class="kontak-icon"><i class="fas fa-phone"></i></div>
                    <h4>Telepon</h4>
                    <p>(0251) 755-5982</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kontak-card">
                    <div class="kontak-icon"><i class="fas fa-envelope"></i></div>
                    <h4>Email</h4>
                    <p>info@smk-bnb.sch.id</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="main-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-icon">
                            <img src="assets/img/logo-smk.jpg" alt="Logo SMK Bangun Nusa Bangsa">
                        </div>
                        <div>
                            <div class="fw-bold">SMK Bangun Nusa Bangsa</div>
                            <small class="text-muted">Smart Digital Campus</small>
                        </div>
                    </div>
                    <p class="text-muted small">Lembaga pendidikan vokasi terakreditasi A yang berkomitmen mencetak lulusan unggul, berkarakter, dan siap bersaing di era global.</p>
                </div>
            </div>
            <div class="col-lg-2">
                <h6 class="footer-heading">Navigasi</h6>
                <ul class="footer-links">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="artikel.php">Artikel</a></li>
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#jurusan">Jurusan</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="footer-heading">Program Keahlian</h6>
                <ul class="footer-links">
                    <li><a href="#">Teknik Komputer & Jaringan</a></li>
                    <li><a href="#">Akuntansi & Keuangan</a></li>
                    <li><a href="#">Teknik Kendaraan Ringan</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="footer-heading">Kontak</h6>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt me-2"></i> Cibinong, Kab. Bogor</li>
                    <li><i class="fas fa-phone me-2"></i> (0251) 755-5982</li>
                    <li><i class="fas fa-envelope me-2"></i> info@smk-bnb.sch.id</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> SMK Bangun Nusa Bangsa. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
