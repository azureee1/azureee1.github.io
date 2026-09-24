<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Dashboard statistics
$totalArtikel = $conn->query("SELECT COUNT(*) as total FROM artikel")->fetch_assoc()['total'];
$totalPublished = $conn->query("SELECT COUNT(*) as total FROM artikel WHERE status='published'")->fetch_assoc()['total'];
$totalDraft = $conn->query("SELECT COUNT(*) as total FROM artikel WHERE status='draft'")->fetch_assoc()['total'];
$totalKomentar = $conn->query("SELECT COUNT(*) as total FROM komentar")->fetch_assoc()['total'];
$totalKomentarPending = $conn->query("SELECT COUNT(*) as total FROM komentar WHERE status='pending'")->fetch_assoc()['total'];
$totalKategori = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];
$totalViews = $conn->query("SELECT COALESCE(SUM(views),0) as total FROM artikel")->fetch_assoc()['total'];

// Recent articles
$recentArtikel = $conn->query("SELECT a.*, k.nama as kategori_nama, u.nama as penulis 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5");

// Recent comments
$recentKomentar = $conn->query("SELECT ko.*, a.judul as artikel_judul, a.slug as artikel_slug 
    FROM komentar ko 
    JOIN artikel a ON ko.artikel_id = a.id 
    ORDER BY ko.created_at DESC 
    LIMIT 5");

$flash = getFlash();
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SMK Bangun Nusa Bangsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Backdrop (Mobile) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon"><img src="../assets/img/logo-smk.jpg" alt="Logo SMK BNB"></div>
                <div>
                    <div class="sidebar-brand-name">SMK BNB</div>
                    <div class="sidebar-brand-sub">Dashboard Admin</div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-label">Menu Utama</div>
                <a href="index.php" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="artikel.php" class="sidebar-link">
                    <i class="fas fa-newspaper"></i> Artikel
                    <span class="badge bg-primary"><?= $totalArtikel ?></span>
                </a>
                <a href="kategori.php" class="sidebar-link">
                    <i class="fas fa-folder"></i> Kategori
                </a>
                <a href="komentar.php" class="sidebar-link">
                    <i class="fas fa-comments"></i> Komentar
                    <?php if($totalKomentarPending > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $totalKomentarPending ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="sidebar-label">Lainnya</div>
                <a href="../index.php" target="_blank" class="sidebar-link">
                    <i class="fas fa-external-link-alt"></i> Lihat Website
                </a>
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_nama']) ?></div>
                        <div class="sidebar-user-role"><?= $_SESSION['role'] ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="btn-sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="topbar-title">Dashboard</div>
                        <div class="topbar-breadcrumb">Selamat datang kembali, <?= htmlspecialchars($_SESSION['user_nama']) ?>!</div>
                    </div>
                </div>
                <div>
                    <a href="../index.php" target="_blank" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem;">
                        <i class="fas fa-external-link-alt"></i> Lihat Website
                    </a>
                </div>
            </header>
            
            <div class="admin-content">
                <?php if($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="dash-stats">
                    <div class="dash-stat-card card-primary">
                        <div class="dash-stat-icon icon-primary"><i class="fas fa-newspaper"></i></div>
                        <div class="dash-stat-number"><?= $totalArtikel ?></div>
                        <div class="dash-stat-label">Total Artikel</div>
                    </div>
                    <div class="dash-stat-card card-emerald">
                        <div class="dash-stat-icon icon-emerald"><i class="fas fa-check-circle"></i></div>
                        <div class="dash-stat-number"><?= $totalPublished ?></div>
                        <div class="dash-stat-label">Artikel Published</div>
                    </div>
                    <div class="dash-stat-card card-accent">
                        <div class="dash-stat-icon icon-accent"><i class="fas fa-comments"></i></div>
                        <div class="dash-stat-number"><?= $totalKomentar ?></div>
                        <div class="dash-stat-label">Total Komentar</div>
                    </div>
                    <div class="dash-stat-card card-rose">
                        <div class="dash-stat-icon icon-rose"><i class="fas fa-eye"></i></div>
                        <div class="dash-stat-number"><?= number_format($totalViews) ?></div>
                        <div class="dash-stat-label">Total Views</div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <!-- Recent Articles -->
                    <div class="col-lg-7">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <span class="admin-card-title"><i class="fas fa-newspaper me-2"></i> Artikel Terbaru</span>
                                <a href="artikel.php" class="btn-admin-outline" style="padding: 6px 14px; font-size: 0.78rem;">
                                    Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="admin-card-body">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($recentArtikel->num_rows > 0): ?>
                                            <?php while($ra = $recentArtikel->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.88rem;"><?= htmlspecialchars(truncateText($ra['judul'], 50)) ?></div>
                                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($ra['kategori_nama']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?= $ra['status'] ?>">
                                                        <?= $ra['status'] ?>
                                                    </span>
                                                </td>
                                                <td style="font-size: 0.82rem;"><?= timeAgo($ra['created_at']) ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada artikel</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Comments -->
                    <div class="col-lg-5">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <span class="admin-card-title"><i class="fas fa-comments me-2"></i> Komentar Terbaru</span>
                                <a href="komentar.php" class="btn-admin-outline" style="padding: 6px 14px; font-size: 0.78rem;">
                                    Kelola <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="admin-card-body with-padding">
                                <?php if($recentKomentar->num_rows > 0): ?>
                                    <?php while($rk = $recentKomentar->fetch_assoc()): ?>
                                    <div style="padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <strong style="font-size: 0.85rem;"><?= htmlspecialchars($rk['nama']) ?></strong>
                                            <span class="status-badge status-<?= $rk['status'] ?>"><?= $rk['status'] ?></span>
                                        </div>
                                        <p style="font-size: 0.82rem; color: var(--text-muted); margin: 4px 0;"><?= htmlspecialchars(truncateText($rk['isi'], 80)) ?></p>
                                        <small style="color: var(--text-muted); font-size: 0.72rem;">
                                            pada <a href="../detail.php?slug=<?= $rk['artikel_slug'] ?>" target="_blank" class="artikel-link"><?= htmlspecialchars(truncateText($rk['artikel_judul'], 30)) ?></a>
                                            • <?= timeAgo($rk['created_at']) ?>
                                        </small>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-comments fa-2x text-muted d-block mb-2" style="opacity:0.3;"></i>
                                        <small class="text-muted">Belum ada komentar</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
            document.getElementById('sidebarBackdrop').classList.toggle('show');
        }
    </script>
</body>
</html>
