<?php
/**
 * Konfigurasi Database & Aplikasi
 * SMK Bangun Nusa Bangsa
 */

// ─── Database Configuration ───
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smk_bnb');

// ─── Application Configuration ───
define('BASE_URL', 'http://localhost/smk');
define('SITE_NAME', 'SMK Bangun Nusa Bangsa');
define('SITE_TAGLINE', 'Smart Digital Campus');

// ─── Upload Configuration ───
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('THUMBNAIL_DIR', __DIR__ . '/uploads/thumbnails/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ─── Database Connection ───
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ─── Session Start ───
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ─── Helper Functions ───

/**
 * Membersihkan input dari XSS
 */
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Generate slug dari judul
 */
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Format tanggal Indonesia
 */
function tanggalIndo($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
}

/**
 * Format waktu yang lalu
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}

/**
 * Potong teks
 */
function truncateText($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Tampilkan flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Upload file gambar
 */
function uploadImage($file, $dir = null) {
    if ($dir === null) $dir = THUMBNAIL_DIR;
    
    // Buat direktori jika belum ada
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Format file tidak diizinkan. Gunakan: ' . implode(', ', $allowed)];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
    }
    
    $filename = uniqid('img_') . '_' . time() . '.' . $ext;
    $filepath = $dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => $filename];
    }
    
    return ['error' => 'Gagal mengupload file.'];
}
