<?php
require_once 'config.php';

// Pagination
$per_page = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Filter kategori
$kategori_filter = isset($_GET['kategori']) ? clean($_GET['kategori']) : '';
$search = isset($_GET['q']) ? clean($_GET['q']) : '';

// Build query
$where = "WHERE a.status = 'published'";
$params = [];
$types = '';

if ($kategori_filter) {
    $where .= " AND k.slug = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

if ($search) {
    $where .= " AND (a.judul LIKE ? OR a.isi LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Count total
$countSql = "SELECT COUNT(*) as total FROM artikel a JOIN kategori k ON a.kategori_id = k.id $where";
$countStmt = $conn->prepare($countSql);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $per_page);

// Get articles
$sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug, u.nama as penulis 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    JOIN users u ON a.user_id = u.id 
    $where 
    ORDER BY a.created_at DESC 
    LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$artikelList = $stmt->get_result();

// Get categories for sidebar
$kategoriQuery = $conn->query("SELECT k.*, COUNT(a.id) as total_artikel 
    FROM kategori k 
    LEFT JOIN artikel a ON k.id = a.kategori_id AND a.status = 'published' 
    GROUP BY k.id 
    ORDER BY total_artikel DESC");

// Get popular articles for sidebar
$popularQuery = $conn->query("SELECT a.*, k.nama as kategori_nama 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    WHERE a.status = 'published' 
    ORDER BY a.views DESC 
    LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel & Berita — SMK Bangun Nusa Bangsa</title>
    <meta name="description" content="Kumpulan berita, artikel, dan informasi terbaru dari SMK Bangun Nusa Bangsa.">
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
            <span class="active">Artikel & Berita</span>
            <?php if($kategori_filter): ?>
                / <span class="active"><?= htmlspecialchars($kategori_filter) ?></span>
            <?php endif; ?>
        </nav>
        <h1>Artikel & Berita</h1>
        <p>Informasi terkini seputar kegiatan, prestasi, dan berita SMK Bangun Nusa Bangsa</p>
    </div>
</section>

<!-- MAIN CONTENT -->
<section style="padding: 60px 0; background: var(--bg-body);">
    <div class="container">
        <div class="row g-4">
            <!-- Artikel List -->
            <div class="col-lg-8">
                <?php if($search): ?>
                    <div class="mb-4">
                        <p class="text-muted">Hasil pencarian untuk: <strong class="text-primary">"<?= htmlspecialchars($search) ?>"</strong> (<?= $total ?> artikel ditemukan)</p>
                    </div>
                <?php endif; ?>
                
                <?php if($artikelList->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php while($artikel = $artikelList->fetch_assoc()): ?>
                        <div class="col-md-6">
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
                                    <p class="artikel-excerpt"><?= truncateText($artikel['ringkasan'] ?? $artikel['isi'], 100) ?></p>
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
                    
                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination pagination-custom justify-content-center">
                            <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page-1 ?><?= $kategori_filter ? '&kategori='.$kategori_filter : '' ?><?= $search ? '&q='.$search : '' ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $kategori_filter ? '&kategori='.$kategori_filter : '' ?><?= $search ? '&q='.$search : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page+1 ?><?= $kategori_filter ? '&kategori='.$kategori_filter : '' ?><?= $search ? '&q='.$search : '' ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3 d-block" style="opacity:0.3;"></i>
                        <h4 class="text-muted">Belum ada artikel</h4>
                        <p class="text-muted">Artikel yang dipublikasikan akan muncul di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Search -->
                <div class="sidebar-card">
                    <h5><i class="fas fa-search me-2"></i> Cari Artikel</h5>
                    <form action="artikel.php" method="GET">
                        <div class="search-box form-dark">
                            <input type="text" name="q" class="form-control" placeholder="Ketik kata kunci..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
                
                <!-- Kategori -->
                <div class="sidebar-card">
                    <h5><i class="fas fa-folder me-2"></i> Kategori</h5>
                    <?php while($kat = $kategoriQuery->fetch_assoc()): ?>
                    <a href="artikel.php?kategori=<?= $kat['slug'] ?>" class="sidebar-kategori-item <?= $kategori_filter == $kat['slug'] ? 'text-primary' : '' ?>">
                        <span><?= htmlspecialchars($kat['nama']) ?></span>
                        <span class="sidebar-kategori-count"><?= $kat['total_artikel'] ?></span>
                    </a>
                    <?php endwhile; ?>
                    <?php if($kategori_filter): ?>
                        <a href="artikel.php" class="d-block mt-3 text-center text-muted small">
                            <i class="fas fa-times me-1"></i> Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Artikel Populer -->
                <div class="sidebar-card">
                    <h5><i class="fas fa-fire me-2"></i> Artikel Populer</h5>
                    <?php $num = 1; while($pop = $popularQuery->fetch_assoc()): ?>
                    <a href="detail.php?slug=<?= $pop['slug'] ?>" class="sidebar-popular-item">
                        <div class="sidebar-popular-num"><?= $num++ ?></div>
                        <div>
                            <div class="sidebar-popular-title"><?= htmlspecialchars($pop['judul']) ?></div>
                            <div class="sidebar-popular-views"><i class="fas fa-eye me-1"></i> <?= $pop['views'] ?> views</div>
                        </div>
                    </a>
                    <?php endwhile; ?>
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
