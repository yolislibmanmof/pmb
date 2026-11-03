<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Pengaturan Situs";

// ======================================================================
// 2. UPLOAD HELPER FUNCTION
// ======================================================================
function uploadImage($file, $folder = 'settings', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico']) {
    global $pdo;
    
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (max PHP upload size)',
            UPLOAD_ERR_FORM_SIZE => 'File melebihi batas form',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Error upload tidak diketahui'];
    }
    
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran file maksimal 5MB. File Anda: ' . round($file['size'] / 1024 / 1024, 2) . 'MB'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan. Gunakan: ' . implode(', ', $allowedTypes)];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
    if (!in_array($mime, $allowedMimes) && $ext !== 'svg') {
        return ['success' => false, 'error' => 'File bukan gambar yang valid (MIME: ' . $mime . ')'];
    }
    
    $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $uniqueName;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Gagal memindahkan file. Periksa permission folder uploads/'];
    }
    
    $relativeUrl = '../uploads/' . $folder . '/' . $uniqueName;
    
    return ['success' => true, 'url' => $relativeUrl, 'filename' => $uniqueName, 'size' => $file['size']];
}

function deleteOldFile($url) {
    if (empty($url) || strpos($url, 'http') === 0) return;
    $path = __DIR__ . '/' . ltrim($url, '/');
    $path = realpath(__DIR__ . '/..') . '/' . ltrim($url, '../');
    if (file_exists($path) && is_file($path)) {
        @unlink($path);
    }
}

$success = '';
$error = '';

// ======================================================================
// 3. HANDLE POST (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmtOld = $pdo->query("SELECT setting_key, value_id FROM settings WHERE setting_key IN ('logo_url', 'favicon_url', 'hero_image')");
            $oldUrls = [];
            foreach ($stmtOld->fetchAll() as $row) {
                $oldUrls[$row['setting_key']] = $row['value_id'];
            }
            
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0 && !is_array($value)) {
                    $clean_key = str_replace(['setting_', '_id', '_en'], '', $key);
                    $col = strpos($key, '_en') !== false ? 'value_en' : 'value_id';
                    
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, $col) VALUES (?, ?) ON DUPLICATE KEY UPDATE $col = VALUES($col)");
                    $stmt->execute([$clean_key, sanitize($value)]);
                }
            }
            
            $uploadFields = [
                'logo_upload' => 'logo_url',
                'favicon_upload' => 'favicon_url',
                'hero_upload' => 'hero_image'
            ];
            
            foreach ($uploadFields as $fileKey => $settingKey) {
                if (!empty($_FILES[$fileKey]['name'])) {
                    $result = uploadImage($_FILES[$fileKey], 'settings');
                    
                    if ($result['success']) {
                        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, value_id, value_en) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value_id = VALUES(value_id), value_en = VALUES(value_en)");
                        $stmt->execute([$settingKey, $result['url'], $result['url']]);
                        
                        if (!empty($oldUrls[$settingKey]) && $oldUrls[$settingKey] !== $result['url']) {
                            deleteOldFile($oldUrls[$settingKey]);
                        }
                    } else {
                        throw new Exception("Upload {$settingKey} gagal: " . $result['error']);
                    }
                }
            }
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, description, ip_address) VALUES (?, 'UPDATE', 'settings', ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, "Memperbarui pengaturan situs", $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $pdo->commit();
            $success = "Pengaturan situs berhasil diperbarui! Perubahan langsung terlihat di website publik.";
            
            header('Location: settings.php?success=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// ======================================================================
// 4. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// Handle success dari redirect
if (isset($_GET['success'])) {
    $success = "Pengaturan situs berhasil diperbarui! Perubahan langsung terlihat di website publik.";
}

// Ambil semua settings
$stmt = $pdo->query("SELECT setting_key, value_id, value_en FROM settings");
$settings_list = $stmt->fetchAll();
$settings = [];
foreach ($settings_list as $s) {
    $settings[$s['setting_key']] = ['id' => $s['value_id'], 'en' => $s['value_en']];
}

function getVal($settings, $key, $lang = 'id', $default = '') {
    return $settings[$key][$lang] ?? $default;
}

function isLocalFile($url) {
    return !empty($url) && strpos($url, 'http') !== 0 && strpos($url, '//') !== 0;
}
?>

<!-- Page Header -->
<div style="margin-bottom: 2rem;">
    <div class="breadcrumb">
        <a href="index.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            Dashboard
        </a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span style="color: var(--primary); font-weight: 600;">Pengaturan Situs</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.5px;">Pengaturan Situs & Branding</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Kelola identitas visual, konten hero, dan informasi kontak website publik.</p>
        </div>
        <a href="../public/index.php" target="_blank" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Lihat Perubahan
        </a>
    </div>
</div>

<!-- Info Banner -->
<div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05)); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: var(--radius); padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 40px; height: 40px; background: var(--info); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    </div>
    <div style="flex: 1;">
        <strong style="display: block; margin-bottom: 0.25rem; color: var(--info);">Perubahan Instan + Upload File</strong>
        <span style="font-size: 0.9rem; color: var(--text);">Upload gambar langsung dari komputer atau paste URL. File disimpan aman di folder <code style="background: var(--bg); padding: 0.125rem 0.375rem; border-radius: 4px;">/uploads/settings/</code></span>
    </div>
</div>

<!-- Settings Form -->
<form method="POST" id="settings-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border); overflow-x: auto; padding-bottom: 0;" class="tab-nav">
        <button type="button" class="tab-btn active" data-tab="branding" onclick="switchTab('branding', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>Branding & Identitas</span>
        </button>
        <button type="button" class="tab-btn" data-tab="hero" onclick="switchTab('hero', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>Hero Section</span>
        </button>
        <button type="button" class="tab-btn" data-tab="contact" onclick="switchTab('contact', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>Informasi Kontak</span>
        </button>
        <button type="button" class="tab-btn" data-tab="seo" onclick="switchTab('seo', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span>SEO & Meta</span>
        </button>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;" class="settings-layout">
        <div>
            <!-- TAB 1: BRANDING -->
            <div class="tab-content active" id="tab-branding">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                                    <circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/>
                                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
                                </svg>
                                Identitas Visual
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Logo, favicon, dan nama situs Anda</p>
                        </div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <!-- Site Name -->
                        <div class="form-group">
                            <label>Nama Situs</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2M4 7h16M4 7v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7M9 12h6"/></svg>
                                        Bahasa Indonesia
                                    </label>
                                    <input type="text" name="setting_site_name_id" class="form-input" value="<?= sanitize(getVal($settings, 'site_name', 'id')) ?>" maxlength="100" data-counter="site_name_id">
                                    <div class="form-hint"><span id="counter_site_name_id">0</span>/100 karakter</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        English
                                    </label>
                                    <input type="text" name="setting_site_name_en" class="form-input" value="<?= sanitize(getVal($settings, 'site_name', 'en')) ?>" maxlength="100" data-counter="site_name_en">
                                    <div class="form-hint"><span id="counter_site_name_en">0</span>/100 karakter</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tagline -->
                        <div class="form-group">
                            <label>Tagline / Slogan</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Bahasa Indonesia</label>
                                    <input type="text" name="setting_tagline_id" class="form-input" value="<?= sanitize(getVal($settings, 'tagline', 'id')) ?>" maxlength="150" data-counter="tagline_id">
                                    <div class="form-hint"><span id="counter_tagline_id">0</span>/150 karakter</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">English</label>
                                    <input type="text" name="setting_tagline_en" class="form-input" value="<?= sanitize(getVal($settings, 'tagline', 'en')) ?>" maxlength="150" data-counter="tagline_en">
                                    <div class="form-hint"><span id="counter_tagline_en">0</span>/150 karakter</div>
                                </div>
                            </div>
                        </div>

                        <!-- Logo Upload -->
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <span>Logo Situs <span class="required">*</span></span>
                                <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                                    <button type="button" class="mode-btn active" data-target="logo" data-mode="upload" onclick="switchMode('logo', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        Upload
                                    </button>
                                    <button type="button" class="mode-btn" data-target="logo" data-mode="url" onclick="switchMode('logo', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        URL
                                    </button>
                                </div>
                            </label>
                            
                            <div id="logo-upload-mode" class="upload-mode">
                                <div class="upload-dropzone" data-input="logo_upload" onclick="document.getElementById('logo_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'logo_upload')">
                                    <input type="file" id="logo_upload" name="logo_upload" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display: none;" onchange="handleFileSelect(this, 'logo-preview-local', 'logo-file-info')">
                                    <div class="upload-content" id="logo-upload-content">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        <p style="font-weight: 600; margin-bottom: 0.25rem;">Klik atau drag & drop logo di sini</p>
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">PNG, JPG, SVG, WebP (Maks 5MB)</p>
                                    </div>
                                    <div class="upload-preview" id="logo-preview-local" style="display: none;">
                                        <img src="" alt="Preview" style="max-width: 200px; max-height: 100px; object-fit: contain;">
                                        <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('logo_upload', 'logo-preview-local', 'logo-file-info')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div id="logo-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted); display: none;">
                                    <span class="file-name"></span> • <span class="file-size"></span>
                                </div>
                            </div>
                            
                            <div id="logo-url-mode" class="url-mode" style="display: none;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="url" name="setting_logo_url_id" id="logo_url_input" class="form-input" value="<?= sanitize(getVal($settings, 'logo_url', 'id')) ?>" oninput="previewImage('logo_url_input', 'logo_preview')" placeholder="https://example.com/logo.png">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyToClipboard('logo_url_input')" title="Copy URL">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-hint" style="margin-top: 0.5rem;">
                                <?php if (isLocalFile(getVal($settings, 'logo_url', 'id'))): ?>
                                    <span style="color: var(--success);">📁 File lokal:</span> <code style="background: var(--bg); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;"><?= sanitize(getVal($settings, 'logo_url', 'id')) ?></code>
                                <?php else: ?>
                                    Direkomendasikan: PNG transparan, tinggi minimal 80px
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Favicon Upload -->
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <span>Favicon (Ikon Tab Browser)</span>
                                <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                                    <button type="button" class="mode-btn active" data-target="favicon" data-mode="upload" onclick="switchMode('favicon', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        Upload
                                    </button>
                                    <button type="button" class="mode-btn" data-target="favicon" data-mode="url" onclick="switchMode('favicon', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        URL
                                    </button>
                                </div>
                            </label>
                            
                            <div id="favicon-upload-mode" class="upload-mode">
                                <div class="upload-dropzone" data-input="favicon_upload" onclick="document.getElementById('favicon_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'favicon_upload')">
                                    <input type="file" id="favicon_upload" name="favicon_upload" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/svg+xml" style="display: none;" onchange="handleFileSelect(this, 'favicon-preview-local', 'favicon-file-info')">
                                    <div class="upload-content" id="favicon-upload-content">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <p style="font-weight: 600; margin-bottom: 0.25rem;">Klik atau drag & drop favicon</p>
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">ICO, PNG, SVG (Maks 5MB, ideal 32x32px)</p>
                                    </div>
                                    <div class="upload-preview" id="favicon-preview-local" style="display: none;">
                                        <img src="" alt="Preview" style="max-width: 64px; max-height: 64px; object-fit: contain;">
                                        <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('favicon_upload', 'favicon-preview-local', 'favicon-file-info')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div id="favicon-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted);">
                                    <span class="file-name"></span> • <span class="file-size"></span>
                                </div>
                            </div>
                            
                            <div id="favicon-url-mode" class="url-mode" style="display: none;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="url" name="setting_favicon_url_id" id="favicon_url_input" class="form-input" value="<?= sanitize(getVal($settings, 'favicon_url', 'id')) ?>" oninput="previewImage('favicon_url_input', 'favicon_preview')" placeholder="https://example.com/favicon.ico">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyToClipboard('favicon_url_input')" title="Copy URL">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-hint" style="margin-top: 0.5rem;">
                                <?php if (isLocalFile(getVal($settings, 'favicon_url', 'id'))): ?>
                                    <span style="color: var(--success);">📁 File lokal:</span> <code style="background: var(--bg); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;"><?= sanitize(getVal($settings, 'favicon_url', 'id')) ?></code>
                                <?php else: ?>
                                    Ukuran ideal: 32x32px atau 64x64px, format ICO/PNG
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: HERO SECTION (Disingkat untuk menghemat ruang) -->
            <div class="tab-content" id="tab-hero" style="display: none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                </svg>
                                Konten Hero Section
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Judul, subtitle, dan gambar utama halaman beranda</p>
                        </div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <!-- Hero Title -->
                        <div class="form-group">
                            <label>Judul Hero (Headline Utama)</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Bahasa Indonesia</label>
                                    <input type="text" name="setting_hero_title_id" class="form-input" value="<?= sanitize(getVal($settings, 'hero_title', 'id')) ?>" maxlength="80" data-counter="hero_title_id">
                                    <div class="form-hint"><span id="counter_hero_title_id">0</span>/80 karakter</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">English</label>
                                    <input type="text" name="setting_hero_title_en" class="form-input" value="<?= sanitize(getVal($settings, 'hero_title', 'en')) ?>" maxlength="80" data-counter="hero_title_en">
                                    <div class="form-hint"><span id="counter_hero_title_en">0</span>/80 karakter</div>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Subtitle -->
                        <div class="form-group">
                            <label>Subtitle Hero</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Bahasa Indonesia</label>
                                    <textarea name="setting_hero_subtitle_id" class="form-textarea" rows="3" maxlength="200" data-counter="hero_subtitle_id"><?= sanitize(getVal($settings, 'hero_subtitle', 'id')) ?></textarea>
                                    <div class="form-hint"><span id="counter_hero_subtitle_id">0</span>/200 karakter</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">English</label>
                                    <textarea name="setting_hero_subtitle_en" class="form-textarea" rows="3" maxlength="200" data-counter="hero_subtitle_en"><?= sanitize(getVal($settings, 'hero_subtitle', 'en')) ?></textarea>
                                    <div class="form-hint"><span id="counter_hero_subtitle_en">0</span>/200 karakter</div>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Image Upload -->
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <span>Gambar Hero</span>
                                <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                                    <button type="button" class="mode-btn active" data-target="hero" data-mode="upload" onclick="switchMode('hero', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        Upload
                                    </button>
                                    <button type="button" class="mode-btn" data-target="hero" data-mode="url" onclick="switchMode('hero', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        URL
                                    </button>
                                </div>
                            </label>
                            
                            <div id="hero-upload-mode" class="upload-mode">
                                <div class="upload-dropzone" data-input="hero_upload" onclick="document.getElementById('hero_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'hero_upload')">
                                    <input type="file" id="hero_upload" name="hero_upload" accept="image/*" style="display: none;" onchange="handleFileSelect(this, 'hero-preview-local', 'hero-file-info')">
                                    <div class="upload-content" id="hero-upload-content">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <p style="font-weight: 600; margin-bottom: 0.25rem;">Klik atau drag & drop gambar hero</p>
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">JPG, PNG, WebP (Maks 5MB, rasio 4:3 ideal)</p>
                                    </div>
                                    <div class="upload-preview" id="hero-preview-local" style="display: none;">
                                        <img src="" alt="Preview" style="max-width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px;">
                                        <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('hero_upload', 'hero-preview-local', 'hero-file-info')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div id="hero-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted);">
                                    <span class="file-name"></span> • <span class="file-size"></span>
                                </div>
                            </div>
                            
                            <div id="hero-url-mode" class="url-mode" style="display: none;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="url" name="setting_hero_image_id" id="hero_image_input" class="form-input" value="<?= sanitize(getVal($settings, 'hero_image', 'id')) ?>" oninput="previewImage('hero_image_input', 'hero_preview')" placeholder="https://example.com/hero.jpg">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyToClipboard('hero_image_input')" title="Copy URL">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-hint" style="margin-top: 0.5rem;">
                                <?php if (isLocalFile(getVal($settings, 'hero_image', 'id'))): ?>
                                    <span style="color: var(--success);">📁 File lokal:</span> <code style="background: var(--bg); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;"><?= sanitize(getVal($settings, 'hero_image', 'id')) ?></code>
                                <?php else: ?>
                                    Direkomendasikan: Landscape, rasio 4:3, resolusi minimal 1920x1440px
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: CONTACT (Disingkat) -->
            <div class="tab-content" id="tab-contact" style="display: none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                Informasi Kontak
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Tampil di footer dan halaman kontak website</p>
                        </div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="setting_contact_phone_id" class="form-input" value="<?= sanitize(getVal($settings, 'contact_phone', 'id')) ?>" placeholder="+62 851-7427-0172">
                        </div>
                        <div class="form-group">
                            <label>Nomor WhatsApp</label>
                            <input type="text" name="setting_contact_wa_id" class="form-input" value="<?= sanitize(getVal($settings, 'contact_wa', 'id')) ?>" placeholder="6285174270172">
                            <div class="form-hint">Contoh: 6285174270172 (tanpa + atau spasi)</div>
                        </div>
                        <div class="form-group">
                            <label>Email Resmi</label>
                            <input type="email" name="setting_contact_email_id" class="form-input" value="<?= sanitize(getVal($settings, 'contact_email', 'id')) ?>" placeholder="info@ukjp.ac.id">
                        </div>
                        <div class="form-group">
                            <label>Alamat Kampus</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Bahasa Indonesia</label>
                                    <textarea name="setting_contact_address_id" class="form-textarea" rows="3" maxlength="250" data-counter="contact_address_id"><?= sanitize(getVal($settings, 'contact_address', 'id')) ?></textarea>
                                    <div class="form-hint"><span id="counter_contact_address_id">0</span>/250 karakter</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">English</label>
                                    <textarea name="setting_contact_address_en" class="form-textarea" rows="3" maxlength="250" data-counter="contact_address_en"><?= sanitize(getVal($settings, 'contact_address', 'en')) ?></textarea>
                                    <div class="form-hint"><span id="counter_contact_address_en">0</span>/250 karakter</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: SEO (Disingkat) -->
            <div class="tab-content" id="tab-seo" style="display: none;">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                SEO & Meta Data
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Optimasi untuk mesin pencari (Google)</p>
                        </div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="form-group">
                            <label>Meta Description</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Bahasa Indonesia</label>
                                    <textarea name="setting_meta_description_id" class="form-textarea" rows="4" maxlength="160" data-counter="meta_description_id" placeholder="Deskripsi singkat website..."><?= sanitize(getVal($settings, 'meta_description', 'id')) ?></textarea>
                                    <div class="form-hint"><span id="counter_meta_description_id">0</span>/160 karakter (ideal: 150-160)</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">English</label>
                                    <textarea name="setting_meta_description_en" class="form-textarea" rows="4" maxlength="160" data-counter="meta_description_en" placeholder="Short website description..."><?= sanitize(getVal($settings, 'meta_description', 'en')) ?></textarea>
                                    <div class="form-hint"><span id="counter_meta_description_en">0</span>/160 karakter</div>
                                </div>
                            </div>
                        </div>
                        <div style="background: var(--bg); border-radius: var(--radius); padding: 1.5rem; margin-top: 1rem;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem;">Preview di Google Search</div>
                            <div style="background: white; padding: 1.25rem; border-radius: 8px;">
                                <div style="color: #1a0dab; font-size: 1.125rem; font-weight: 500; margin-bottom: 0.25rem;"><?= sanitize(getVal($settings, 'site_name', 'id', 'PMB UKJP')) ?></div>
                                <div style="color: #006621; font-size: 0.875rem; margin-bottom: 0.5rem;">https://spmb.ukjp.ac.id/</div>
                                <div style="color: #545454; font-size: 0.875rem; line-height: 1.5;" id="seo-preview"><?= sanitize(getVal($settings, 'meta_description', 'id', 'Portal Penerimaan Mahasiswa Baru...')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Reset
                </button>
                <button type="submit" class="btn btn-primary" id="save-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span id="save-btn-text">Simpan Pengaturan</span>
                </button>
            </div>
        </div>

        <!-- RIGHT: Live Preview Sidebar -->
        <div style="position: sticky; top: 2rem; align-self: start;">
            <div class="data-card" style="margin-bottom: 1.5rem;">
                <div class="data-card-header" style="padding: 1rem 1.5rem;">
                    <h3 style="font-size: 1rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        Live Preview
                    </h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Logo</div>
                        <div style="background: var(--bg); border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 1.5rem; display: flex; align-items: center; justify-content: center; min-height: 100px;">
                            <img id="logo_preview" src="<?= sanitize(getVal($settings, 'logo_url', 'id')) ?>" alt="Logo" style="max-width: 100%; max-height: 80px; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; flex-direction: column; align-items: center; gap: 0.5rem; color: var(--text-muted);">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span style="font-size: 0.8rem;">Logo tidak valid</span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Favicon</div>
                        <div style="background: var(--bg); border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                            <div style="background: white; padding: 0.5rem 0.75rem; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; border: 1px solid var(--border);">
                                <img id="favicon_preview" src="<?= sanitize(getVal($settings, 'favicon_url', 'id')) ?>" alt="Favicon" style="width: 16px; height: 16px; object-fit: contain;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Crect fill=%22%231e40af%22 width=%2224%22 height=%2224%22 rx=%224%22/%3E%3C/svg%3E'">
                                <span style="color: var(--text);"><?= sanitize(getVal($settings, 'site_name', 'id', 'PMB')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Hero Image</div>
                        <div style="background: var(--bg); border: 2px dashed var(--border); border-radius: var(--radius-sm); overflow: hidden; aspect-ratio: 4/3; display: flex; align-items: center; justify-content: center;">
                            <img id="hero_preview" src="<?= sanitize(getVal($settings, 'hero_image', 'id')) ?>" alt="Hero" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; flex-direction: column; align-items: center; gap: 0.5rem; color: var(--text-muted); padding: 2rem;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span style="font-size: 0.8rem; text-align: center;">URL gambar tidak valid</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="data-card">
                <div style="padding: 1.5rem;">
                    <h4 style="font-size: 0.95rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
                            <path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.9V17h8v-2.1A7 7 0 0 0 12 2z"/>
                        </svg>
                        Tips Branding
                    </h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <li style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>File lokal lebih cepat & tidak bergantung server eksternal.</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Logo PNG transparan terlihat profesional di semua tema.</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Optimalkan gambar sebelum upload untuk loading cepat.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .tab-nav { scrollbar-width: thin; }
    .tab-nav::-webkit-scrollbar { height: 4px; }
    .tab-nav::-webkit-scrollbar-track { background: transparent; }
    .tab-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    
    .tab-btn {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        background: transparent; border: none;
        color: var(--text-muted); font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all 0.3s ease;
        border-bottom: 3px solid transparent; margin-bottom: -2px;
        white-space: nowrap; font-family: inherit;
    }
    .tab-btn:hover { color: var(--primary); }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    .tab-btn svg { width: 18px; height: 18px; }
    
    .upload-dropzone {
        position: relative;
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--bg);
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .upload-dropzone:hover {
        border-color: var(--primary);
        background: rgba(30, 64, 175, 0.03);
    }
    .upload-dropzone.dragover {
        border-color: var(--primary);
        background: rgba(30, 64, 175, 0.08);
        transform: scale(1.01);
    }
    .upload-preview {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .remove-file-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 28px;
        height: 28px;
        background: var(--danger);
        color: white;
        border: 2px solid var(--surface);
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        padding: 0;
    }
    .remove-file-btn:hover {
        transform: scale(1.1);
        background: #dc2626;
    }
    
    @media (max-width: 1024px) {
        .settings-layout { grid-template-columns: 1fr !important; }
        .settings-layout > div:last-child { position: static !important; }
    }
    @media (max-width: 640px) {
        .tab-btn span { display: none; }
        .tab-btn { padding: 0.875rem; }
    }
</style>

<script>
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + tabId).style.display = 'block';
        document.getElementById('tab-' + tabId).classList.add('active');
        btnElement.classList.add('active');
        window.scrollTo({ top: 200, behavior: 'smooth' });
    }

    function switchMode(target, mode, btn) {
        const uploadMode = document.getElementById(target + '-upload-mode');
        const urlMode = document.getElementById(target + '-url-mode');
        
        if (mode === 'upload') {
            uploadMode.style.display = 'block';
            urlMode.style.display = 'none';
        } else {
            uploadMode.style.display = 'none';
            urlMode.style.display = 'block';
        }
        
        const container = btn.parentElement;
        container.querySelectorAll('.mode-btn').forEach(b => {
            b.style.background = 'transparent';
            b.style.color = 'var(--text-muted)';
        });
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
    }

    function handleFileSelect(input, previewId, infoId) {
        const file = input.files[0];
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) {
            if (typeof showToast === 'function') {
                showToast('File Terlalu Besar', 'Ukuran maksimal 5MB. File Anda: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB', 'error');
            } else {
                alert('File terlalu besar! Maksimal 5MB.');
            }
            input.value = '';
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            if (typeof showToast === 'function') {
                showToast('File Tidak Valid', 'Hanya file gambar yang diizinkan', 'error');
            } else {
                alert('File harus berupa gambar!');
            }
            input.value = '';
            return;
        }
        
        const preview = document.getElementById(previewId);
        const img = preview.querySelector('img');
        const content = preview.parentElement.querySelector('.upload-content');
        const reader = new FileReader();
        
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'flex';
            if (content) content.style.display = 'none';
            
            const info = document.getElementById(infoId);
            if (info) {
                info.querySelector('.file-name').textContent = file.name;
                info.querySelector('.file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
                info.style.display = 'block';
            }
            
            updateSidebarPreview(input.id, e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function handleDrop(event, inputId) {
        event.preventDefault();
        event.currentTarget.classList.remove('dragover');
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById(inputId);
            input.files = files;
            const previewId = inputId.replace('_upload', '-preview-local');
            const infoId = inputId.replace('_upload', '-file-info');
            handleFileSelect(input, previewId, infoId);
        }
    }

    function removeFile(inputId, previewId, infoId) {
        document.getElementById(inputId).value = '';
        const preview = document.getElementById(previewId);
        preview.style.display = 'none';
        preview.querySelector('img').src = '';
        const content = preview.parentElement.querySelector('.upload-content');
        if (content) content.style.display = 'block';
        const info = document.getElementById(infoId);
        if (info) info.style.display = 'none';
    }

    function updateSidebarPreview(inputId, dataUrl) {
        if (inputId === 'logo_upload') {
            const preview = document.getElementById('logo_preview');
            preview.src = dataUrl;
            preview.style.display = 'block';
            if (preview.nextElementSibling) preview.nextElementSibling.style.display = 'none';
        } else if (inputId === 'favicon_upload') {
            const preview = document.getElementById('favicon_preview');
            preview.src = dataUrl;
        } else if (inputId === 'hero_upload') {
            const preview = document.getElementById('hero_preview');
            preview.src = dataUrl;
            preview.style.display = 'block';
            if (preview.nextElementSibling) preview.nextElementSibling.style.display = 'none';
        }
    }

    function previewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const url = input.value.trim();
        
        if (url) {
            preview.src = url;
            preview.style.display = 'block';
            if (preview.nextElementSibling) preview.nextElementSibling.style.display = 'none';
        }
    }

    function copyToClipboard(inputId) {
        const input = document.getElementById(inputId);
        if (!input.value) {
            if (typeof showToast === 'function') showToast('Gagal', 'URL kosong', 'warning');
            return;
        }
        navigator.clipboard.writeText(input.value).then(() => {
            if (typeof showToast === 'function') showToast('Disalin', 'URL berhasil disalin ke clipboard', 'success');
        });
    }

    function initCounters() {
        document.querySelectorAll('[data-counter]').forEach(input => {
            const counterId = input.getAttribute('data-counter');
            const counterEl = document.getElementById('counter_' + counterId);
            const max = parseInt(input.getAttribute('maxlength')) || 999;
            function updateCounter() {
                const length = input.value.length;
                if (counterEl) {
                    counterEl.textContent = length;
                    counterEl.style.color = length > max * 0.9 ? 'var(--danger)' : length > max * 0.7 ? 'var(--warning)' : 'var(--text-muted)';
                }
            }
            updateCounter();
            input.addEventListener('input', updateCounter);
        });
    }

    function initSeoPreview() {
        const metaInput = document.querySelector('[name="setting_meta_description_id"]');
        const preview = document.getElementById('seo-preview');
        if (metaInput && preview) {
            metaInput.addEventListener('input', () => {
                preview.textContent = metaInput.value || 'Deskripsi akan muncul di sini...';
            });
        }
    }

    document.getElementById('settings-form').addEventListener('submit', function(e) {
        const btn = document.getElementById('save-btn');
        const btnText = document.getElementById('save-btn-text');
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
    });

    document.addEventListener('DOMContentLoaded', () => {
        initCounters();
        initSeoPreview();
        
        <?php if ($success): ?>
        if (typeof showToast === 'function') showToast('Berhasil Disimpan', '<?= addslashes($success) ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error): ?>
        if (typeof showToast === 'function') showToast('Error', '<?= addslashes($error) ?>', 'error');
        <?php endif; ?>
    });
</script>

<?php require_once 'includes/footer.php'; ?>