<?php
require_once 'config.php';

// Get article by slug
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (empty($slug)) {
    redirect('artikel.php');
}

$stmt = $conn->prepare("SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug, u.nama as penulis 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.slug = ? AND a.status = 'published'");
$stmt->bind_param('s', $slug);
$stmt->execute();
$artikel = $stmt->get_result()->fetch_assoc();

if (!$artikel) {
    redirect('artikel.php');
}

// Increment views
$conn->query("UPDATE artikel SET views = views + 1 WHERE id = " . (int)$artikel['id']);

// Handle komentar submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_komentar'])) {
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $isi = clean($_POST['isi']);
    
    if (!empty($nama) && !empty($email) && !empty($isi)) {
        $stmtKomen = $conn->prepare("INSERT INTO komentar (artikel_id, nama, email, isi) VALUES (?, ?, ?, ?)");
        $stmtKomen->bind_param('isss', $artikel['id'], $nama, $email, $isi);
        
        if ($stmtKomen->execute()) {
            setFlash('success', 'Komentar Anda berhasil dikirim dan sedang menunggu moderasi.');
        } else {
            setFlash('danger', 'Gagal mengirim komentar. Silakan coba lagi.');
        }
        redirect('detail.php?slug=' . $slug);
    } else {
        setFlash('warning', 'Semua field harus diisi.');
    }
}

// Get approved comments
$komentarQuery = $conn->prepare("SELECT * FROM komentar WHERE artikel_id = ? AND status = 'approved' ORDER BY created_at DESC");
$komentarQuery->bind_param('i', $artikel['id']);
$komentarQuery->execute();
$komentarList = $komentarQuery->get_result();

// Get related articles
$relatedQuery = $conn->prepare("SELECT a.*, k.nama as kategori_nama 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    WHERE a.kategori_id = ? AND a.id != ? AND a.status = 'published' 
    ORDER BY a.created_at DESC 
    LIMIT 3");
$relatedQuery->bind_param('ii', $artikel['kategori_id'], $artikel['id']);
$relatedQuery->execute();
$relatedList = $relatedQuery->get_result();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['judul']) ?> — SMK Bangun Nusa Bangsa</title>
    <meta name="description" content="<?= htmlspecialchars(truncateText($artikel['ringkasan'] ?? $artikel['isi'], 160)) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($artikel['judul']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(truncateText($artikel['ringkasan'] ?? $artikel['isi'], 160)) ?>">
    <meta property="og:type" content="article">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
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
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#jurusan">Jurusan</a></li>
                <li class="nav-item"><a class="nav-link active" href="artikel.php">Berita</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="admin/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Portal
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container position-relative">
        <nav class="breadcrumb-custom">
            <a href="index.php">Beranda</a> / 
            <a href="artikel.php">Artikel</a> / 
            <a href="artikel.php?kategori=<?= $artikel['kategori_slug'] ?>"><?= htmlspecialchars($artikel['kategori_nama']) ?></a> /
            <span class="active">Detail</span>
        </nav>
    </div>
</section>

<!-- DETAIL CONTENT -->
<section class="detail-content">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Article -->
                <article class="detail-article">
                    <div class="detail-thumbnail">
                        <?php if($artikel['thumbnail']): ?>
                            <img src="uploads/thumbnails/<?= $artikel['thumbnail'] ?>" alt="<?= htmlspecialchars($artikel['judul']) ?>">
                        <?php else: ?>
                            <div class="detail-thumb-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="detail-body">
                        <div class="detail-meta">
                            <span><i class="fas fa-folder"></i> <?= htmlspecialchars($artikel['kategori_nama']) ?></span>
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($artikel['penulis']) ?></span>
                            <span><i class="fas fa-calendar"></i> <?= tanggalIndo($artikel['created_at']) ?></span>
                            <span><i class="fas fa-eye"></i> <?= $artikel['views'] ?> views</span>
                        </div>
                        
                        <h1 class="detail-title"><?= htmlspecialchars($artikel['judul']) ?></h1>
                        
                        <div class="detail-isi">
                            <?= $artikel['isi'] ?>
                        </div>
                    </div>
                </article>
                
                <!-- Share Buttons -->
                <div class="d-flex gap-2 mt-4 mb-4">
                    <span class="text-muted me-2" style="font-weight: 600; font-size: 0.85rem;">Bagikan:</span>
                    <a href="https://wa.me/?text=<?= urlencode($artikel['judul'] . ' - ' . BASE_URL . '/detail.php?slug=' . $artikel['slug']) ?>" 
                       target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/detail.php?slug=' . $artikel['slug']) ?>" 
                       target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fab fa-facebook me-1"></i> Facebook
                    </a>
                </div>
                
                <!-- Komentar Section -->
                <div class="komentar-section">
                    <h4><i class="fas fa-comments me-2"></i> Komentar (<?= $komentarList->num_rows ?>)</h4>
                    
                    <?php if($flash): ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($komentarList->num_rows > 0): ?>
                        <?php while($komentar = $komentarList->fetch_assoc()): ?>
                        <div class="komentar-item">
                            <div class="komentar-avatar">
                                <?= strtoupper(substr($komentar['nama'], 0, 1)) ?>
                            </div>
                            <div class="komentar-content">
                                <h6><?= htmlspecialchars($komentar['nama']) ?></h6>
                                <div class="komentar-date"><?= timeAgo($komentar['created_at']) ?></div>
                                <p class="komentar-text"><?= nl2br(htmlspecialchars($komentar['isi'])) ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-comment-slash me-1"></i> Belum ada komentar. Jadilah yang pertama!
                        </p>
                    <?php endif; ?>
                    
                    <!-- Form Komentar -->
                    <div class="form-komentar">
                        <h5><i class="fas fa-pen me-2"></i> Tulis Komentar</h5>
                        <form method="POST" class="form-dark">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama Anda" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Komentar *</label>
                                    <textarea name="isi" class="form-control" rows="4" placeholder="Tulis komentar Anda di sini..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="kirim_komentar" class="btn btn-primary rounded-pill px-4">
                                        <i class="fas fa-paper-plane me-2"></i> Kirim Komentar
                                    </button>
                                    <small class="text-muted ms-2">Komentar akan ditampilkan setelah dimoderasi.</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Related Articles -->
                <?php if($relatedList->num_rows > 0): ?>
                <div class="sidebar-card">
                    <h5><i class="fas fa-link me-2"></i> Artikel Terkait</h5>
                    <?php $rNum = 1; while($related = $relatedList->fetch_assoc()): ?>
                    <a href="detail.php?slug=<?= $related['slug'] ?>" class="sidebar-popular-item">
                        <div class="sidebar-popular-num"><?= $rNum++ ?></div>
                        <div>
                            <div class="sidebar-popular-title"><?= htmlspecialchars($related['judul']) ?></div>
                            <div class="sidebar-popular-views">
                                <i class="fas fa-clock me-1"></i> <?= timeAgo($related['created_at']) ?>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
                
                <!-- Back to Articles -->
                <div class="sidebar-card text-center">
                    <a href="artikel.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Artikel
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-bottom" style="margin-top: 0; border-top: none;">
            <p>&copy; <?= date('Y') ?> SMK Bangun Nusa Bangsa. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
