<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $nama = clean($_POST['nama']);
    $deskripsi = clean($_POST['deskripsi']);
    $slug = createSlug($nama);
    
    // Make slug unique
    $origSlug = $slug;
    $counter = 1;
    while ($conn->query("SELECT id FROM kategori WHERE slug = '$slug'")->num_rows > 0) {
        $slug = $origSlug . '-' . $counter;
        $counter++;
    }
    
    $stmt = $conn->prepare("INSERT INTO kategori (nama, slug, deskripsi) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $nama, $slug, $deskripsi);
    
    if ($stmt->execute()) {
        setFlash('success', 'Kategori baru berhasil ditambahkan.');
    } else {
        setFlash('danger', 'Gagal menambahkan kategori.');
    }
    redirect('kategori.php');
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = clean($_POST['nama']);
    $deskripsi = clean($_POST['deskripsi']);
    $slug = createSlug($nama);
    
    $stmt = $conn->prepare("UPDATE kategori SET nama=?, slug=?, deskripsi=? WHERE id=?");
    $stmt->bind_param('sssi', $nama, $slug, $deskripsi, $id);
    
    if ($stmt->execute()) {
        setFlash('success', 'Kategori berhasil diperbarui.');
    } else {
        setFlash('danger', 'Gagal memperbarui kategori.');
    }
    redirect('kategori.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category has articles
    $artCount = $conn->query("SELECT COUNT(*) as t FROM artikel WHERE kategori_id = $id")->fetch_assoc()['t'];
    if ($artCount > 0) {
        setFlash('warning', "Kategori tidak dapat dihapus karena masih memiliki $artCount artikel.");
    } else {
        $conn->query("DELETE FROM kategori WHERE id=$id");
        setFlash('success', 'Kategori berhasil dihapus.');
    }
    redirect('kategori.php');
}

// Get all categories with article count
$kategoriList = $conn->query("SELECT k.*, COUNT(a.id) as total_artikel 
    FROM kategori k 
    LEFT JOIN artikel a ON k.id = a.kategori_id 
    GROUP BY k.id 
    ORDER BY k.created_at DESC");

$totalKomentarPending = $conn->query("SELECT COUNT(*) as total FROM komentar WHERE status='pending'")->fetch_assoc()['total'];
$totalArtikelAll = $conn->query("SELECT COUNT(*) as total FROM artikel")->fetch_assoc()['total'];

$flash = getFlash();
$currentPage = 'kategori';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori — Dashboard SMK BNB</title>
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
                <a href="kategori.php" class="sidebar-link active"><i class="fas fa-folder"></i> Kategori</a>
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
                        <div class="topbar-title">Kelola Kategori</div>
                        <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Kategori</div>
                    </div>
                </div>
                <button class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </button>
            </header>
            
            <div class="admin-content">
                <?php if($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="admin-card-body">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Kategori</th>
                                    <th>Slug</th>
                                    <th>Deskripsi</th>
                                    <th>Total Artikel</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($kategoriList->num_rows > 0): ?>
                                    <?php $no = 1; while($kat = $kategoriList->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong style="color: var(--text-main);"><?= htmlspecialchars($kat['nama']) ?></strong></td>
                                        <td><code style="color: var(--secondary); font-size: 0.82rem;"><?= $kat['slug'] ?></code></td>
                                        <td style="font-size: 0.82rem; max-width: 250px;"><?= htmlspecialchars(truncateText($kat['deskripsi'] ?? '-', 80)) ?></td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-25 text-primary" style="font-size: 0.78rem; padding: 4px 10px;">
                                                <?= $kat['total_artikel'] ?> artikel
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="btn-action" title="Edit" onclick="editKategori(<?= $kat['id'] ?>, '<?= htmlspecialchars($kat['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($kat['deskripsi'] ?? '', ENT_QUOTES) ?>')">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <a href="kategori.php?delete=<?= $kat['id'] ?>" class="btn-action btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
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
                                                <i class="fas fa-folder d-block"></i>
                                                <h4>Belum ada kategori</h4>
                                                <p>Tambahkan kategori pertama untuk mengorganisir artikel.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h5 class="modal-title" style="font-family: var(--font-display); font-weight: 800;">
                        <i class="fas fa-plus me-2"></i> Tambah Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="form-admin">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Berita Sekolah" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat kategori (opsional)" style="min-height: 80px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border);">
                        <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add" class="btn-admin-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h5 class="modal-title" style="font-family: var(--font-display); font-weight: 800;">
                        <i class="fas fa-pen me-2"></i> Edit Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="form-admin">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3" style="min-height: 80px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border);">
                        <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit" class="btn-admin-primary">
                            <i class="fas fa-save"></i> Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
            document.getElementById('sidebarBackdrop').classList.toggle('show');
        }
        
        function editKategori(id, nama, deskripsi) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editDeskripsi').value = deskripsi;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
