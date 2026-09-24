<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle approve
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE komentar SET status='approved' WHERE id=$id");
    setFlash('success', 'Komentar berhasil di-approve.');
    redirect('komentar.php');
}

// Handle reject
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $conn->query("UPDATE komentar SET status='rejected' WHERE id=$id");
    setFlash('info', 'Komentar telah ditolak.');
    redirect('komentar.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM komentar WHERE id=$id");
    setFlash('success', 'Komentar berhasil dihapus.');
    redirect('komentar.php');
}

// Filter
$statusFilter = isset($_GET['status']) ? clean($_GET['status']) : '';
$where = '';
if ($statusFilter) {
    $where = "WHERE ko.status = '$statusFilter'";
}

// Pagination
$per_page = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$total = $conn->query("SELECT COUNT(*) as total FROM komentar ko $where")->fetch_assoc()['total'];
$totalPages = ceil($total / $per_page);

$komentarList = $conn->query("SELECT ko.*, a.judul as artikel_judul, a.slug as artikel_slug 
    FROM komentar ko 
    JOIN artikel a ON ko.artikel_id = a.id 
    $where 
    ORDER BY ko.created_at DESC 
    LIMIT $per_page OFFSET $offset");

$totalPending = $conn->query("SELECT COUNT(*) as t FROM komentar WHERE status='pending'")->fetch_assoc()['t'];
$totalApproved = $conn->query("SELECT COUNT(*) as t FROM komentar WHERE status='approved'")->fetch_assoc()['t'];
$totalRejected = $conn->query("SELECT COUNT(*) as t FROM komentar WHERE status='rejected'")->fetch_assoc()['t'];
$totalAll = $conn->query("SELECT COUNT(*) as t FROM komentar")->fetch_assoc()['t'];
$totalArtikelAll = $conn->query("SELECT COUNT(*) as total FROM artikel")->fetch_assoc()['total'];

$flash = getFlash();
$currentPage = 'komentar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Komentar — Dashboard SMK BNB</title>
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
                <a href="artikel.php" class="sidebar-link"><i class="fas fa-newspaper"></i> Artikel <span class="badge bg-primary"><?= $totalArtikelAll ?></span></a>
                <a href="kategori.php" class="sidebar-link"><i class="fas fa-folder"></i> Kategori</a>
                <a href="komentar.php" class="sidebar-link active"><i class="fas fa-comments"></i> Komentar <?php if($totalPending > 0): ?><span class="badge bg-warning text-dark"><?= $totalPending ?></span><?php endif; ?></a>
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
                        <div class="topbar-title">Kelola Komentar</div>
                        <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Komentar</div>
                    </div>
                </div>
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
                    <a href="komentar.php" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= !$statusFilter ? 'background: var(--primary-light); color: var(--primary); border-color: var(--primary);' : '' ?>">
                        Semua (<?= $totalAll ?>)
                    </a>
                    <a href="komentar.php?status=pending" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= $statusFilter=='pending' ? 'background: var(--accent-light); color: var(--accent); border-color: var(--accent);' : '' ?>">
                        ⏳ Pending (<?= $totalPending ?>)
                    </a>
                    <a href="komentar.php?status=approved" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= $statusFilter=='approved' ? 'background: var(--emerald-light); color: var(--emerald); border-color: var(--emerald);' : '' ?>">
                        ✅ Approved (<?= $totalApproved ?>)
                    </a>
                    <a href="komentar.php?status=rejected" class="btn-admin-outline" style="padding: 6px 16px; font-size: 0.82rem; <?= $statusFilter=='rejected' ? 'background: var(--rose-light); color: var(--rose); border-color: var(--rose);' : '' ?>">
                        ❌ Rejected (<?= $totalRejected ?>)
                    </a>
                </div>
                
                <!-- Comments Table -->
                <div class="admin-card">
                    <div class="admin-card-body">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Komentar</th>
                                    <th>Artikel</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($komentarList->num_rows > 0): ?>
                                    <?php while($kom = $komentarList->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;">
                                                <?= htmlspecialchars($kom['nama']) ?>
                                            </div>
                                            <small style="color: var(--text-muted);"><?= htmlspecialchars($kom['email']) ?></small>
                                        </td>
                                        <td>
                                            <div class="komentar-preview"><?= htmlspecialchars(truncateText($kom['isi'], 100)) ?></div>
                                        </td>
                                        <td>
                                            <a href="../detail.php?slug=<?= $kom['artikel_slug'] ?>" target="_blank" class="artikel-link">
                                                <?= htmlspecialchars(truncateText($kom['artikel_judul'], 40)) ?>
                                            </a>
                                        </td>
                                        <td><span class="status-badge status-<?= $kom['status'] ?>"><?= $kom['status'] ?></span></td>
                                        <td><span style="font-size: 0.78rem; color: var(--text-muted);"><?= timeAgo($kom['created_at']) ?></span></td>
                                        <td>
                                            <div class="action-btns">
                                                <?php if($kom['status'] !== 'approved'): ?>
                                                    <a href="komentar.php?approve=<?= $kom['id'] ?>" class="btn-action btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($kom['status'] !== 'rejected'): ?>
                                                    <a href="komentar.php?reject=<?= $kom['id'] ?>" class="btn-action" title="Reject">
                                                        <i class="fas fa-ban"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="komentar.php?delete=<?= $kom['id'] ?>" class="btn-action btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus komentar ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-comments d-block"></i>
                                                <h4>Belum ada komentar</h4>
                                                <p>Komentar dari pengunjung website akan muncul di sini.</p>
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
