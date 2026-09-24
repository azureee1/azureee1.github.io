<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$isEdit = false;
$artikel = [
    'id' => '',
    'judul' => '',
    'kategori_id' => '',
    'isi' => '',
    'ringkasan' => '',
    'thumbnail' => '',
    'status' => 'draft'
];

// Edit mode
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $artikel = $result;
        $isEdit = true;
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean($_POST['judul']);
    $kategori_id = (int)$_POST['kategori_id'];
    $isi = $_POST['isi']; // Allow HTML content
    $ringkasan = clean($_POST['ringkasan']);
    $status = clean($_POST['status']);
    $slug = createSlug($judul);
    $thumbnail = $artikel['thumbnail'];
    
    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $upload = uploadImage($_FILES['thumbnail']);
        if (isset($upload['success'])) {
            // Delete old thumbnail if editing
            if ($isEdit && $artikel['thumbnail'] && file_exists(THUMBNAIL_DIR . $artikel['thumbnail'])) {
                unlink(THUMBNAIL_DIR . $artikel['thumbnail']);
            }
            $thumbnail = $upload['success'];
        } else {
            setFlash('danger', $upload['error']);
            redirect('artikel-form.php' . ($isEdit ? '?id=' . $artikel['id'] : ''));
        }
    }
    
    if ($isEdit) {
        // Update
        $stmt = $conn->prepare("UPDATE artikel SET judul=?, slug=?, kategori_id=?, isi=?, ringkasan=?, thumbnail=?, status=?, updated_at=NOW() WHERE id=?");
        $isiClean = $conn->real_escape_string($isi);
        $stmt->bind_param('ssissssi', $judul, $slug, $kategori_id, $isi, $ringkasan, $thumbnail, $status, $artikel['id']);
        
        if ($stmt->execute()) {
            setFlash('success', 'Artikel berhasil diperbarui.');
            redirect('artikel.php');
        } else {
            setFlash('danger', 'Gagal memperbarui artikel.');
        }
    } else {
        // Create
        $user_id = $_SESSION['user_id'];
        
        // Make slug unique
        $origSlug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM artikel WHERE slug = '$slug'")->num_rows > 0) {
            $slug = $origSlug . '-' . $counter;
            $counter++;
        }
        
        $stmt = $conn->prepare("INSERT INTO artikel (user_id, kategori_id, judul, slug, isi, ringkasan, thumbnail, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissssss', $user_id, $kategori_id, $judul, $slug, $isi, $ringkasan, $thumbnail, $status);
        
        if ($stmt->execute()) {
            setFlash('success', 'Artikel baru berhasil ditambahkan.');
            redirect('artikel.php');
        } else {
            setFlash('danger', 'Gagal menambahkan artikel.');
        }
    }
}

// Get categories
$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama ASC");

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
    <title><?= $isEdit ? 'Edit' : 'Tulis' ?> Artikel — Dashboard SMK BNB</title>
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
                        <div class="topbar-title"><?= $isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru' ?></div>
                        <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / <a href="artikel.php">Artikel</a> / <?= $isEdit ? 'Edit' : 'Baru' ?></div>
                    </div>
                </div>
                <a href="artikel.php" class="btn-admin-outline">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </header>
            
            <div class="admin-content">
                <?php if($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="form-admin">
                    <div class="row g-4">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <span class="admin-card-title"><i class="fas fa-pen-fancy me-2"></i> Konten Artikel</span>
                                </div>
                                <div class="admin-card-body with-padding">
                                    <div class="mb-4">
                                        <label class="form-label">Judul Artikel *</label>
                                        <input type="text" name="judul" class="form-control" 
                                               value="<?= htmlspecialchars($artikel['judul']) ?>" 
                                               placeholder="Masukkan judul artikel yang menarik..." required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">Ringkasan</label>
                                        <textarea name="ringkasan" class="form-control" rows="3" 
                                                  placeholder="Tulis ringkasan singkat artikel (opsional)..."
                                                  style="min-height: 80px;"><?= htmlspecialchars($artikel['ringkasan']) ?></textarea>
                                        <div class="form-text">Ringkasan akan ditampilkan di halaman daftar artikel.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Isi Artikel *</label>
                                        <textarea name="isi" class="form-control" rows="15" 
                                                  placeholder="Tulis isi artikel Anda di sini... (mendukung tag HTML)"
                                                  required style="min-height: 350px;"><?= htmlspecialchars($artikel['isi']) ?></textarea>
                                        <div class="form-text">Anda dapat menggunakan tag HTML seperti &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;br&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;img&gt;, dll.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sidebar Options -->
                        <div class="col-lg-4">
                            <!-- Publish Settings -->
                            <div class="admin-card mb-4">
                                <div class="admin-card-header">
                                    <span class="admin-card-title"><i class="fas fa-cog me-2"></i> Pengaturan</span>
                                </div>
                                <div class="admin-card-body with-padding">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="draft" <?= $artikel['status'] == 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="published" <?= $artikel['status'] == 'published' ? 'selected' : '' ?>>✅ Published</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Kategori *</label>
                                        <select name="kategori_id" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php while($kat = $kategoriList->fetch_assoc()): ?>
                                                <option value="<?= $kat['id'] ?>" <?= $artikel['kategori_id'] == $kat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kat['nama']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn-admin-primary justify-content-center">
                                            <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui Artikel' : 'Simpan Artikel' ?>
                                        </button>
                                        <a href="artikel.php" class="btn-admin-outline justify-content-center">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thumbnail -->
                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <span class="admin-card-title"><i class="fas fa-image me-2"></i> Thumbnail</span>
                                </div>
                                <div class="admin-card-body with-padding">
                                    <?php if($isEdit && $artikel['thumbnail']): ?>
                                        <div class="mb-3">
                                            <img src="../uploads/thumbnails/<?= $artikel['thumbnail'] ?>" 
                                                 alt="Current Thumbnail" 
                                                 style="width: 100%; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                            <small class="text-muted d-block mt-2">Thumbnail saat ini</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <label class="form-label"><?= $isEdit ? 'Ganti Thumbnail' : 'Upload Thumbnail' ?></label>
                                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    </div>
                                    <div class="form-text">Format: JPG, PNG, GIF, WebP. Maks: 5MB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
