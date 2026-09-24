<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    
    // Delete thumbnail file if exists
    $thumb = $conn->query("SELECT thumbnail FROM artikel WHERE id = $delId")->fetch_assoc();
    if ($thumb && $thumb['thumbnail'] && file_exists(THUMBNAIL_DIR . $thumb['thumbnail'])) {
        unlink(THUMBNAIL_DIR . $thumb['thumbnail']);
    }
    
    $conn->query("DELETE FROM artikel WHERE id = $delId");
    setFlash('success', 'Artikel berhasil dihapus.');
    redirect('artikel.php');
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$statusFilter = isset($_GET['status']) ? clean($_GET['status']) : '';
$where = '';
if ($statusFilter) {
    $where = "WHERE a.status = '$statusFilter'";
}

$total = $conn->query("SELECT COUNT(*) as total FROM artikel a $where")->fetch_assoc()['total'];
$totalPages = ceil($total / $per_page);

$artikelList = $conn->query("SELECT a.*, k.nama as kategori_nama, u.nama as penulis 
    FROM artikel a 
    JOIN kategori k ON a.kategori_id = k.id 
    JOIN users u ON a.user_id = u.id 
    $where 
    ORDER BY a.created_at DESC 
    LIMIT $per_page OFFSET $offset");

$totalKomentarPending = $conn->query("SELECT COUNT(*) as total FROM komentar WHERE status='pending'")->fetch_assoc()['total'];
$totalArtikelAll = $conn->query("SELECT COUNT(*) as total FROM artikel")->fetch_assoc()['total'];

$flash = getFlash();
$currentPage = 'artikel';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel — Dashboard SMK BNB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
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
                <a href="index.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="artikel.php" class="sidebar-link active"><i class="fas fa-newspaper"></i> Artikel <span class="badge bg-primary"><?= $totalArtikelAll ?></span></a>
                <a href="kategori.php" class="sidebar-link"><i class="fas fa-folder"></i> Kategori</a>
                <a href="komentar.php" class="sidebar-link"><i class="fas fa-comments"></i> Komentar <?php if($totalKomentarPending > 0): ?><span class="badge bg-warning text-dark"><?= $totalKomentarPending ?></span><?php endif; ?></a>
                <div class="sidebar-label">Lainnya</div>
                <a href="../index.php" target="_blank" class="sidebar-link"><i class="fas fa-external-link-alt"></i> Lihat Website</a>
                <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?></div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_nama']) ?></div>
                        <div class="sidebar-user-role"><?= $_SESSION['role'] ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main -->
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="btn-sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div>
                        <div class="topbar-title">Kelola Artikel</div>
                        <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Artikel</div>
                    </div>
                </div>
                <a href="artikel-form.php" class="btn-admin-primary">
                    <i class="fas fa-plus"></i> Tulis Artikel Baru
                </a>
            </header>
            
            <div class="admin-content">
                <?php if($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Tabs -->
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <a href="artikel.php" class="btn-admin-outline <?= !$statusFilter ? 'active' : '' ?>" style="padding: 6px 16px; font-size: 0.82rem; <?= !$statusFilter ? 'background: var(--primary-light); color: var(--primary); border-color: var(--primary);' : '' ?>">
                        Semua (<?= $conn->query("SELECT COUNT(*) as t FROM artikel")->fetch_assoc()['t'] ?>)
                    </a>
                    <a href="artikel.php?status=published" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= $statusFilter=='published' ? 'background: var(--emerald-light); color: var(--emerald); border-color: var(--emerald);' : '' ?>">
                        Published (<?= $conn->query("SELECT COUNT(*) as t FROM artikel WHERE status='published'")->fetch_assoc()['t'] ?>)
                    </a>
                    <a href="artikel.php?status=draft" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= $statusFilter=='draft' ? 'background: var(--accent-light); color: var(--accent); border-color: var(--accent);' : '' ?>">
                        Draft (<?= $conn->query("SELECT COUNT(*) as t FROM artikel WHERE status='draft'")->fetch_assoc()['t'] ?>)
                    </a>
                </div>
                
                <!-- Articles Table -->
                <div class="admin-card">
                    <div class="admin-card-body">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Penulis</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($artikelList->num_rows > 0): ?>
                                    <?php while($art = $artikelList->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="thumb-preview">
                                                <?php if($art['thumbnail']): ?>
                                                    <img src="../uploads/thumbnails/<?= $art['thumbnail'] ?>" alt="">
                                                <?php else: ?>
                                                    <i class="fas fa-image"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-main); max-width: 250px;">
                                                <?= htmlspecialchars(truncateText($art['judul'], 60)) ?>
                                            </div>
                                        </td>
                                        <td><span style="font-size: 0.82rem;"><?= htmlspecialchars($art['kategori_nama']) ?></span></td>
                                        <td><span style="font-size: 0.82rem;"><?= htmlspecialchars($art['penulis']) ?></span></td>
                                        <td><span class="status-badge status-<?= $art['status'] ?>"><?= $art['status'] ?></span></td>
                                        <td><span style="font-size: 0.82rem;"><?= $art['views'] ?></span></td>
                                        <td><span style="font-size: 0.78rem; color: var(--text-muted);"><?= tanggalIndo($art['created_at']) ?></span></td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="../detail.php?slug=<?= $art['slug'] ?>" target="_blank" class="btn-action" title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="artikel-form.php?id=<?= $art['id'] ?>" class="btn-action" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="artikel.php?delete=<?= $art['id'] ?>" class="btn-action btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus artikel ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="fas fa-newspaper d-block"></i>
                                                <h4>Belum ada artikel</h4>
                                                <p>Mulai tulis artikel pertama Anda.</p>
                                                <a href="artikel-form.php" class="btn-admin-primary mt-3">
                                                    <i class="fas fa-plus"></i> Tulis Artikel
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
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
