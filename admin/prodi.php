<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Prodi";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Prodi']
];

// ======================================================================
// 1.5 HELPER FUNCTIONS (WAJIB DIDEFINISIKAN SEBELUM POST HANDLER)
// ======================================================================
if (!function_exists('formatRupiah')) {
    function formatRupiah($num) {
        return 'Rp ' . number_format((float)$num, 0, ',', '.');
    }
}

if (!function_exists('uploadProdiImage')) {
    function uploadProdiImage($file) {
        $uploadDir = __DIR__ . '/../uploads/prodi/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Gagal upload'];
        if ($file['size'] > 5 * 1024 * 1024) return ['success' => false, 'error' => 'Maksimal 5MB'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return ['success' => false, 'error' => 'Format tidak diizinkan'];
        
        $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $uniqueName)) {
            // Kembalikan path relatif terhadap ROOT project (konsisten dengan DB)
            return ['success' => true, 'url' => 'uploads/prodi/' . $uniqueName];
        }
        return ['success' => false, 'error' => 'Gagal menyimpan file'];
    }
}

// ======================================================================
// 2. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            // ===== PROSES UPLOAD GAMBAR (JIKA ADA) =====
            $final_image_url = sanitize($_POST['image_url'] ?? '');
            if (!empty($_FILES['prodi_image_upload']['name'])) {
                $upload_result = uploadProdiImage($_FILES['prodi_image_upload']);
                if ($upload_result['success']) {
                    // Simpan path RELATIF TERHADAP ROOT PROJECT (tanpa ../)
                    $final_image_url = 'uploads/prodi/' . basename($upload_result['url']);
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'title' => 'Upload Gagal', 'message' => $upload_result['error']];
                    header('Location: prodi.php');
                    exit;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO prodi (
                kode_prodi, jenjang, nama_prodi, sistem_kuliah, akreditasi, gelar_lulusan,
                kuota, biaya_spp, biaya_pangkal, jalur_count,
                description_id, description_en, image_url, is_active, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                sanitize($_POST['kode_prodi']),
                sanitize($_POST['jenjang']),
                sanitize($_POST['nama_prodi']),
                sanitize($_POST['sistem_kuliah']),
                sanitize($_POST['akreditasi']),
                sanitize($_POST['gelar_lulusan']),
                (int)$_POST['kuota'],
                (float)$_POST['biaya_spp'],
                (float)$_POST['biaya_pangkal'],
                (int)$_POST['jalur_count'],
                sanitize($_POST['description_id']),
                sanitize($_POST['description_en']),
                $final_image_url,
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0)
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, description, ip_address) VALUES (?, 'CREATE', 'prodi', ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, "Menambah prodi: " . sanitize($_POST['nama_prodi']), $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Program studi berhasil ditambahkan'];
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            
            // ===== PROSES UPLOAD GAMBAR (JIKA ADA) =====
            $final_image_url = sanitize($_POST['image_url'] ?? '');
            if (!empty($_FILES['prodi_image_upload']['name'])) {
                $upload_result = uploadProdiImage($_FILES['prodi_image_upload']);
                if ($upload_result['success']) {
                    $final_image_url = 'uploads/prodi/' . basename($upload_result['url']);
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'title' => 'Upload Gagal', 'message' => $upload_result['error']];
                    header('Location: prodi.php');
                    exit;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE prodi SET 
                kode_prodi=?, jenjang=?, nama_prodi=?, sistem_kuliah=?, akreditasi=?, gelar_lulusan=?,
                kuota=?, biaya_spp=?, biaya_pangkal=?, jalur_count=?,
                description_id=?, description_en=?, image_url=?, is_active=?, sort_order=?
                WHERE id=?");
            
            $stmt->execute([
                sanitize($_POST['kode_prodi']),
                sanitize($_POST['jenjang']),
                sanitize($_POST['nama_prodi']),
                sanitize($_POST['sistem_kuliah']),
                sanitize($_POST['akreditasi']),
                sanitize($_POST['gelar_lulusan']),
                (int)$_POST['kuota'],
                (float)$_POST['biaya_spp'],
                (float)$_POST['biaya_pangkal'],
                (int)$_POST['jalur_count'],
                sanitize($_POST['description_id']),
                sanitize($_POST['description_en']),
                $final_image_url,
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0),
                $id
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'prodi', ?, ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, $id, "Mengupdate prodi ID: {$id}", $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Program studi berhasil diperbarui'];
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Cek apakah ada pendaftar
            $check = $pdo->prepare("SELECT COUNT(*) FROM applicants WHERE prodi_id = ?");
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Tidak dapat menghapus prodi yang memiliki pendaftar. Nonaktifkan saja statusnya.'];
            } else {
                $stmt = $pdo->prepare("DELETE FROM prodi WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Program studi berhasil dihapus'];
            }
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE prodi SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status berhasil diubah'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    // ======================================================================
    // 3. REDIRECT SETELAH POST (WAJIB SEBELUM HTML DICETAK)
    // ======================================================================
    header('Location: prodi.php');
    exit;
}

// ======================================================================
// 4. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ==========================================
// FETCH DATA WITH STATISTICS
// ==========================================
$stmt = $pdo->query("SELECT p.*, 
    (SELECT COUNT(*) FROM applicants WHERE prodi_id = p.id) AS total_pendaftar,
    (SELECT COUNT(*) FROM applicants WHERE prodi_id = p.id AND status_daftar = 'Diverifikasi') AS pendaftar_diterima
    FROM prodi p 
    ORDER BY p.sort_order ASC, FIELD(p.jenjang, 'S2', 'S1', 'Prof', 'D3'), p.nama_prodi ASC");
$prodi_list = $stmt->fetchAll();

// Stats
$total = count($prodi_list);
$active = count(array_filter($prodi_list, fn($p) => $p['is_active'] == 1));
$inactive = $total - $active;
$total_applicants = array_sum(array_column($prodi_list, 'total_pendaftar'));

// Get edit data if requested
$edit_prodi = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM prodi WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_prodi = $stmt->fetch();
}
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= $total ?></h3>
            <p>Total Program Studi</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $active ?>"><?= $active ?></h3>
            <p>Aktif</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $inactive ?>"><?= $inactive ?></h3>
            <p>Nonaktif</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total_applicants ?>"><?= number_format($total_applicants) ?></h3>
            <p>Total Pendaftar</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                Daftar Program Studi (<?= $total ?>)
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola program studi, kuota, dan biaya kuliah</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari program studi..." data-table-search="prodi-table">
            </div>
            <button class="btn btn-primary btn-sm" data-modal-open="prodi-modal" onclick="resetForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Prodi
            </button>
        </div>
    </div>
    
<div class="table-wrapper">
    <table class="admin-table" id="prodi-table" data-sortable>
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th data-sort="kode" style="width: 80px;">Kode</th>
                <th data-sort="nama" style="min-width: 200px;">Program Studi</th>
                <th data-sort="jenjang" style="width: 80px;">Jenjang</th>
                <th data-sort="akreditasi" style="width: 90px;">Akreditasi</th>
                <th data-sort="kuota" style="width: 120px;">Kuota</th>
                <th data-sort="biaya" style="width: 150px;">Biaya</th>
                <th style="width: 100px;">Pendaftar</th>
                <th style="width: 100px;">Status</th>
                <th style="width: 120px; text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($prodi_list) > 0): ?>
                <?php foreach($prodi_list as $p): 
                    // Badge warna berdasarkan akreditasi
                    $akred_badge = match($p['akreditasi'] ?? '') {
                        'Unggul', 'A' => 'badge-success',
                        'B', 'Baik Sekali' => 'badge-info',
                        'C', 'Baik' => 'badge-warning',
                        default => 'badge-danger'
                    };
                    
                    // Hitung persentase kuota
                    $kuota_pct = $p['kuota'] > 0 ? round(($p['total_pendaftar'] / $p['kuota']) * 100) : 0;
                    $kuota_color = $kuota_pct >= 90 ? 'var(--danger)' : ($kuota_pct >= 70 ? 'var(--warning)' : 'var(--success)');
                ?>
                <tr>
                    <td>
                        <span style="font-weight: 600; color: var(--text-muted);"><?= $p['sort_order'] ?: '-' ?></span>
                    </td>
                    <td data-column="kode">
                        <code style="background: var(--bg); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; color: var(--primary);">
                            <?= sanitize($p['kode_prodi'] ?: '-') ?>
                        </code>
                    </td>
                    <td data-column="nama">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="<?= !empty($p['image_url']) ? (strpos($p['image_url'], 'http') === 0 ? sanitize($p['image_url']) : '../' . sanitize($p['image_url'])) : '' ?>" alt="" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex-shrink: 0;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23e2e8f0%22%3E%3Crect width=%2224%22 height=%2224%22/%3E%3C/svg%3E'">
                            <div style="min-width: 0;">
                                <div style="font-weight: 600; font-size: 0.9rem; line-height: 1.3;"><?= sanitize($p['nama_prodi']) ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);"><?= sanitize($p['gelar_lulusan'] ?: '-') ?> • <?= sanitize($p['sistem_kuliah']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-column="jenjang">
                        <span class="badge badge-info" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;"><?= sanitize($p['jenjang']) ?></span>
                    </td>
                    <td data-column="akreditasi">
                        <span class="badge <?= $akred_badge ?>" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;"><?= sanitize($p['akreditasi'] ?: '-') ?></span>
                    </td>
                    <td data-column="kuota">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= $p['total_pendaftar'] ?> / <?= $p['kuota'] ?></div>
                            <div style="width: 100%; height: 3px; background: var(--border); border-radius: 50px; overflow: hidden;">
                                <div style="width: <?= min($kuota_pct, 100) ?>%; height: 100%; background: <?= $kuota_color ?>; border-radius: 50px;"></div>
                            </div>
                            <div style="font-size: 0.65rem; color: var(--text-muted);"><?= $kuota_pct ?>% terisi</div>
                        </div>
                    </td>
                    <td data-column="biaya">
                        <div style="font-size: 0.8rem;">
                            <div style="font-weight: 600; color: var(--text);"><?= formatRupiah($p['biaya_spp']) ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);"><?= formatRupiah($p['biaya_pangkal']) ?></div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 1px; font-size: 0.8rem;">
                            <span style="font-weight: 600;"><?= $p['total_pendaftar'] ?></span>
                            <span style="color: var(--success); font-size: 0.7rem;"><?= $p['pendaftar_diterima'] ?> diterima</span>
                        </div>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content: flex-end; gap: 0.25rem;">
                            <button class="action-btn" onclick="viewProdi(<?= $p['id'] ?>)" title="Lihat Detail" style="width: 28px; height: 28px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
   <button class="action-btn" onclick="editProdiById(<?= $p['id'] ?>)" title="Edit" style="width: 28px; height: 28px;">
       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
   </button>
                            <button class="action-btn danger" onclick="deleteProdi(<?= $p['id'] ?>, '<?= addslashes($p['nama_prodi']) ?>')" title="Hapus" style="width: 28px; height: 28px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                            <form id="delete-form-<?= $p['id'] ?>" method="POST" style="display: none;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                        <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--text-muted);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        </div>
                        <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Program Studi</p>
                        <button class="btn btn-primary btn-sm" onclick="resetForm(); openModal('prodi-modal')">Tambah Program Studi Pertama</button>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal (Bertab) -->
<div class="modal-overlay" id="prodi-modal">
    <div class="modal" style="max-width: 800px;">
        <form method="POST" id="prodi-form" data-validate enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Program Studi</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <!-- Tabs -->
            <div style="display: flex; border-bottom: 1px solid var(--border); padding: 0 1.5rem; overflow-x: auto;" id="prodi-tabs">
                <button type="button" class="tab-link-prodi active" onclick="switchProdiTab(this, 'ptab-info')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Informasi Dasar</button>
                <button type="button" class="tab-link-prodi" onclick="switchProdiTab(this, 'ptab-academic')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Akademik & Biaya</button>
                <button type="button" class="tab-link-prodi" onclick="switchProdiTab(this, 'ptab-desc')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Deskripsi & Media</button>
            </div>
            
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <!-- Tab 1: Informasi Dasar -->
                <div id="ptab-info" class="ptab-pane" style="display: block;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Kode Prodi <span class="required">*</span></label>
                            <input type="text" name="kode_prodi" id="kode_prodi" class="form-input" required placeholder="S1-BD, S1-IK, PROF-NERS" maxlength="20">
                            <div class="form-hint">Kode unik, maksimal 20 karakter</div>
                        </div>
                        <div class="form-group">
                            <label>Jenjang <span class="required">*</span></label>
                            <select name="jenjang" id="jenjang" class="form-select" required>
                                <option value="">Pilih Jenjang</option>
                                <option value="S2">S2 - Strata 2</option>
                                <option value="S1">S1 - Strata 1</option>
                                <option value="Prof">Prof - Profesi</option>
                                <option value="D3">D3 - Diploma 3</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Nama Program Studi <span class="required">*</span></label>
                            <input type="text" name="nama_prodi" id="nama_prodi" class="form-input" required placeholder="Contoh: Bisnis Digital">
                        </div>
                        <div class="form-group">
                            <label>Gelar Lulusan</label>
                            <input type="text" name="gelar_lulusan" id="gelar_lulusan" class="form-input" placeholder="S.Kom, S.M., Ners, dll">
                        </div>
                        <div class="form-group">
                            <label>Sistem Kuliah <span class="required">*</span></label>
                            <select name="sistem_kuliah" id="sistem_kuliah" class="form-select" required>
                                <option value="Reguler">Reguler</option>
                                <option value="RPL Transfer">RPL Transfer</option>
                                <option value="Progsus">Progsus</option>
                                <option value="Karyawan">Karyawan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Akreditasi <span class="required">*</span></label>
                            <select name="akreditasi" id="akreditasi" class="form-select" required>
                                <option value="">Pilih Akreditasi</option>
                                <option value="Unggul">Unggul</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="Baik Sekali">Baik Sekali</option>
                                <option value="C">C</option>
                                <option value="Baik">Baik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Urutan Tampil</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0" placeholder="0 = paling awal">
                            <div class="form-hint">Angka kecil tampil duluan</div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 2: Akademik & Biaya -->
                <div id="ptab-academic" class="ptab-pane" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Kuota Mahasiswa <span class="required">*</span></label>
                            <input type="number" name="kuota" id="kuota" class="form-input" min="1" required value="100">
                            <div class="form-hint">Kapasitas maksimal per angkatan</div>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Jalur Pendaftaran</label>
                            <input type="number" name="jalur_count" id="jalur_count" class="form-input" min="1" value="1">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1; padding: 1.25rem; background: rgba(59, 130, 246, 0.05); border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.1);">
                            <label style="font-size: 0.9rem; color: var(--primary); margin-bottom: 1rem; display: block; font-weight: 700;">💰 Informasi Biaya Kuliah</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem; display: block;">Biaya SPP / Semester <span class="required">*</span></label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;">Rp</span>
                                        <input type="number" name="biaya_spp" id="biaya_spp" class="form-input" style="padding-left: 3rem;" min="0" step="50000" required value="0" oninput="updateCurrencyDisplay(this, 'spp-display')">
                                    </div>
                                    <div id="spp-display" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Rp 0</div>
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem; display: block;">Biaya Pangkal / Awal <span class="required">*</span></label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;">Rp</span>
                                        <input type="number" name="biaya_pangkal" id="biaya_pangkal" class="form-input" style="padding-left: 3rem;" min="0" step="100000" required value="0" oninput="updateCurrencyDisplay(this, 'pangkal-display')">
                                    </div>
                                    <div id="pangkal-display" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 3: Deskripsi & Media -->
<div id="ptab-desc" class="ptab-pane" style="display: none;">
    <div class="form-group">
        <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <span>Gambar Thumbnail Prodi</span>
            <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                <button type="button" class="mode-btn active" data-target="prodi" data-mode="upload" onclick="switchMode('prodi', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Upload</button>
                <button type="button" class="mode-btn" data-target="prodi" data-mode="url" onclick="switchMode('prodi', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">URL</button>
            </div>
        </label>
        
        <!-- Upload Mode -->
        <div id="prodi-upload-mode" class="upload-mode">
            <div class="upload-dropzone" onclick="document.getElementById('prodi_image_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'prodi_image_upload')">
                <input type="file" id="prodi_image_upload" name="prodi_image_upload" accept="image/png,image/jpeg,image/webp" style="display: none;" onchange="handleFileSelect(this, 'prodi-preview-local', 'prodi-file-info')">
                <div class="upload-content" id="prodi-upload-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Klik atau drag & drop gambar</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">JPG, PNG, WebP (Maks 5MB, rasio 16:9)</p>
                </div>
                <div class="upload-preview" id="prodi-preview-local" style="display: none;">
                    <img src="" alt="Preview" style="max-width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px;">
                    <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('prodi_image_upload', 'prodi-preview-local', 'prodi-file-info')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div id="prodi-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted);">
                <span class="file-name"></span> • <span class="file-size"></span>
            </div>
        </div>
        
        <!-- URL Mode -->
        <div id="prodi-url-mode" class="url-mode" style="display: none;">
            <input type="url" name="image_url" id="image_url" class="form-input" placeholder="https://..." oninput="previewProdiImage()">
        </div>
        <div class="form-hint" style="margin-top: 0.5rem;">Direkomendasikan: rasio 16:9, minimal 800x450px</div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label>Deskripsi (Bahasa Indonesia)</label>
            <textarea name="description_id" id="description_id" class="form-textarea" rows="4" maxlength="500" data-counter="desc_id"></textarea>
            <div class="form-hint"><span id="counter_desc_id">0</span>/500 karakter</div>
        </div>
        <div class="form-group">
            <label>Description (English)</label>
            <textarea name="description_en" id="description_en" class="form-textarea" rows="4" maxlength="500" data-counter="desc_en"></textarea>
            <div class="form-hint"><span id="counter_desc_en">0</span>/500 karakter</div>
        </div>
    </div>
    
    <div class="form-group" style="margin-top: 1rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" name="is_active" id="is_active" checked>
            <span>Aktif (tampil di website publik)</span>
        </label>
    </div>
</div>
            
            <div class="modal-footer" style="justify-content: space-between;">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" id="prev-tab-btn" class="btn btn-secondary" onclick="changeProdiTab(-1)" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Sebelumnya
                    </button>
                    <button type="button" id="next-tab-btn" class="btn btn-secondary" onclick="changeProdiTab(1)">
                        Selanjutnya
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- View Detail Modal -->
<div class="modal-overlay" id="view-modal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Detail Program Studi</h3>
            <button class="modal-close" data-modal-close>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" id="view-content"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-modal-close>Tutup</button>
            <button class="btn btn-primary" id="view-edit-btn">Edit Prodi</button>
        </div>
    </div>
</div>

   <style>
       .ptab-pane { animation: fadeIn 0.3s ease; }
       @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
       .tab-link-prodi { transition: all 0.2s ease; }
       .tab-link-prodi:hover { color: var(--primary) !important; }
       
       /* PERBAIKAN LAYOUT TABEL */
       .table-wrapper {
           overflow-x: auto;
           -webkit-overflow-scrolling: touch;
           width: 100%;
       }
       
       .admin-table {
           width: 100%;
           min-width: 900px; /* Dikecilkan agar tidak terlalu memaksa */
           border-collapse: collapse;
       }
       
       .admin-table th, .admin-table td {
           white-space: nowrap;
           padding: 0.75rem 1rem;
           vertical-align: middle;
       }
       
       /* Pengaturan lebar kolom yang proporsional */
       .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 40px; text-align: center; }
       .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 90px; }
       .admin-table th:nth-child(3), .admin-table td:nth-child(3) { min-width: 180px; white-space: normal; }
       .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 70px; }
       .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 80px; }
       .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 110px; }
       .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 130px; }
       .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 90px; }
       .admin-table th:nth-child(9), .admin-table td:nth-child(9) { width: 90px; }
       .admin-table th:nth-child(10), .admin-table td:nth-child(10) { width: 110px; text-align: right; }
       
       .admin-table td { font-size: 0.85rem; }
       
       /* Responsif: Sembunyikan kolom kurang penting di layar kecil */
       @media (max-width: 1400px) {
           .admin-table th:nth-child(2), .admin-table td:nth-child(2) { display: none; } /* Kode */
       }
       @media (max-width: 1200px) {
           .admin-table th:nth-child(4), .admin-table td:nth-child(4) { display: none; } /* Jenjang */
           .admin-table th:nth-child(5), .admin-table td:nth-child(5) { display: none; } /* Akreditasi */
       }
       @media (max-width: 1024px) {
           .admin-table th:nth-child(8), .admin-table td:nth-child(8) { display: none; } /* Detail Pendaftar */
       }
   </style>

<script>
const prodiData = <?= json_encode($prodi_list) ?>;
let currentProdiTab = 0;
const prodiTabOrder = ['ptab-info', 'ptab-academic', 'ptab-desc'];

// ==========================================
// 1. TAB NAVIGATION
// ==========================================
function switchProdiTab(btn, tabId) {
    document.querySelectorAll('#prodi-tabs .tab-link-prodi').forEach(b => {
        b.classList.remove('active');
        b.style.borderBottomColor = 'transparent';
        b.style.color = 'var(--text-muted)';
    });
    btn.classList.add('active');
    btn.style.borderBottomColor = 'var(--primary)';
    btn.style.color = 'var(--primary)';
    
    document.querySelectorAll('.ptab-pane').forEach(p => p.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    currentProdiTab = prodiTabOrder.indexOf(tabId);
    updateProdiTabButtons();
}

function changeProdiTab(dir) {
    const newIndex = currentProdiTab + dir;
    if (newIndex >= 0 && newIndex < prodiTabOrder.length) {
        const btn = document.querySelectorAll('#prodi-tabs .tab-link-prodi')[newIndex];
        switchProdiTab(btn, prodiTabOrder[newIndex]);
    }
}

function updateProdiTabButtons() {
    const prevBtn = document.getElementById('prev-tab-btn');
    const nextBtn = document.getElementById('next-tab-btn');
    if (prevBtn) prevBtn.style.display = currentProdiTab === 0 ? 'none' : 'inline-flex';
    if (nextBtn) nextBtn.style.display = currentProdiTab === prodiTabOrder.length - 1 ? 'none' : 'inline-flex';
}

// ==========================================
// 2. FORM HELPERS
// ==========================================
function updateCurrencyDisplay(input, displayId) {
    const value = parseInt(input.value) || 0;
    const formatted = 'Rp ' + value.toLocaleString('id-ID');
    const display = document.getElementById(displayId);
    if (display) display.textContent = formatted;
}

function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Program Studi';
    document.getElementById('prodi-form').reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('spp-display').textContent = 'Rp 0';
    document.getElementById('pangkal-display').textContent = 'Rp 0';
    
    // FIX: Reset image preview elements sesuai ID HTML yang sebenarnya
    const previewLocal = document.getElementById('prodi-preview-local');
    const uploadContent = document.getElementById('prodi-upload-content');
    const fileInfo = document.getElementById('prodi-file-info');
    const fileInput = document.getElementById('prodi_image_upload');
    const urlInput = document.getElementById('image_url');
    
    if (previewLocal) previewLocal.style.display = 'none';
    if (uploadContent) uploadContent.style.display = 'block';
    if (fileInfo) fileInfo.style.display = 'none';
    if (fileInput) fileInput.value = '';
    if (urlInput) urlInput.value = '';

    const firstTab = document.querySelector('#prodi-tabs .tab-link-prodi');
    if (firstTab) switchProdiTab(firstTab, 'ptab-info');
}

// ==========================================
// 3. EDIT & VIEW LOGIC (FIXED)
// ==========================================
function editProdiById(id) {
    const data = prodiData.find(p => parseInt(p.id) === parseInt(id));
    if (data) {
        editProdi(data);
    } else {
        console.error("Data tidak ditemukan untuk ID:", id);
        showToast('Error', 'Data program studi tidak ditemukan', 'error');
    }
}

function editProdi(data) {
    try {
        document.getElementById('form-action').value = 'edit';
        document.getElementById('form-id').value = data.id;
        document.getElementById('modal-title').textContent = 'Edit Program Studi';
        
        document.getElementById('kode_prodi').value = data.kode_prodi || '';
        document.getElementById('jenjang').value = data.jenjang || '';
        document.getElementById('nama_prodi').value = data.nama_prodi || '';
        document.getElementById('gelar_lulusan').value = data.gelar_lulusan || '';
        document.getElementById('sistem_kuliah').value = data.sistem_kuliah || 'Reguler';
        document.getElementById('akreditasi').value = data.akreditasi || '';
        document.getElementById('sort_order').value = data.sort_order || 0;
        document.getElementById('kuota').value = data.kuota || 100;
        document.getElementById('jalur_count').value = data.jalur_count || 1;
        document.getElementById('biaya_spp').value = data.biaya_spp || 0;
        document.getElementById('biaya_pangkal').value = data.biaya_pangkal || 0;
        document.getElementById('description_id').value = data.description_id || '';
        document.getElementById('description_en').value = data.description_en || '';
        document.getElementById('image_url').value = data.image_url || '';
        document.getElementById('is_active').checked = (data.is_active == 1);
        
        updateCurrencyDisplay(document.getElementById('biaya_spp'), 'spp-display');
        updateCurrencyDisplay(document.getElementById('biaya_pangkal'), 'pangkal-display');
        
        // FIX: Panggil fungsi preview yang sudah diperbaiki
        previewProdiImage();
        
        const firstTab = document.querySelector('#prodi-tabs .tab-link-prodi');
        if (firstTab) switchProdiTab(firstTab, 'ptab-info');
        
        const modal = document.getElementById('prodi-modal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            console.error("Elemen modal 'prodi-modal' tidak ditemukan!");
        }
    } catch (error) {
        console.error("Error saat mengisi form edit:", error);
        showToast('Error', 'Gagal membuka form edit. Cek Console (F12)', 'error');
    }
}

function viewProdi(id) {
    const data = prodiData.find(p => parseInt(p.id) === parseInt(id));
    if (!data) return;
    
    const formatRp = (n) => 'Rp ' + (parseInt(n) || 0).toLocaleString('id-ID');
    const kuotaPct = data.kuota > 0 ? Math.round((data.total_pendaftar / data.kuota) * 100) : 0;
    
    document.getElementById('view-content').innerHTML = `
        <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem; align-items: flex-start;">
        <img src="${data.image_url ? (data.image_url.startsWith('http') ? data.image_url : '../' + data.image_url) : ''}" alt="" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; flex-shrink: 0;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23e2e8f0%22%3E%3Crect width=%2224%22 height=%2224%22/%3E%3C/svg%3E'">
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                    <span class="badge badge-info">${data.jenjang}</span>
                    <span class="badge ${data.akreditasi === 'Unggul' || data.akreditasi === 'A' ? 'badge-success' : 'badge-warning'}">${data.akreditasi || '-'}</span>
                </div>
                <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">${data.nama_prodi}</h2>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Kode: <code>${data.kode_prodi || '-'}</code> • Gelar: ${data.gelar_lulusan || '-'} • ${data.sistem_kuliah}</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--bg); padding: 1rem; border-radius: 10px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Kuota</div>
                <div style="font-size: 1.25rem; font-weight: 700;">${data.total_pendaftar} / ${data.kuota}</div>
                <div style="margin-top: 0.5rem; width: 100%; height: 4px; background: var(--border); border-radius: 50px; overflow: hidden;">
                    <div style="width: ${Math.min(kuotaPct, 100)}%; height: 100%; background: ${kuotaPct >= 90 ? 'var(--danger)' : kuotaPct >= 70 ? 'var(--warning)' : 'var(--success)'};"></div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">${kuotaPct}% terisi • ${data.pendaftar_diterima} diterima</div>
            </div>
            <div style="background: var(--bg); padding: 1rem; border-radius: 10px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Biaya</div>
                <div style="font-size: 1rem; font-weight: 700;">${formatRp(data.biaya_spp)} / semester</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Pangkal: ${formatRp(data.biaya_pangkal)}</div>
            </div>
        </div>
        <div style="background: var(--bg); padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Deskripsi</div>
            <div style="font-size: 0.9rem; line-height: 1.6;">${data.description_id || '<em style="color: var(--text-muted);">Belum ada deskripsi</em>'}</div>
        </div>
    `;
    
    document.getElementById('view-edit-btn').onclick = () => {
        const viewModal = document.getElementById('view-modal');
        if (typeof closeModal === 'function') {
            closeModal(viewModal);
        } else {
            viewModal.classList.remove('active');
            document.body.style.overflow = '';
        }
        setTimeout(() => editProdi(data), 300);
    };
    
    const viewModal = document.getElementById('view-modal');
    if (viewModal) {
        viewModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function deleteProdi(id, nama) {
    if (typeof confirmDelete === 'function') {
        confirmDelete(`Hapus program studi <strong>${nama}</strong>? Tindakan ini tidak dapat dibatalkan.`, () => {
            document.getElementById('delete-form-' + id).submit();
        });
    } else {
        if (confirm(`Hapus program studi ${nama}?`)) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
}

// ==========================================
// 4. IMAGE & FILE HANDLING (FIXED)
// ==========================================
function previewProdiImage() {
    const urlInput = document.getElementById('image_url');
    if (!urlInput) return;
    const url = urlInput.value;

    // FIX: Menggunakan ID yang sesuai dengan HTML Anda
    const previewContainer = document.getElementById('prodi-preview-local');
    const uploadContent = document.getElementById('prodi-upload-content');
    const previewImg = previewContainer ? previewContainer.querySelector('img') : null;

    let src = url;
    if (url && url.indexOf('http') !== 0) src = '../' + url; // Path lokal butuh awalan ../ dari folder admin

    if (url && previewContainer && previewImg) {
        previewImg.src = src;
        previewContainer.style.display = 'flex';
        if (uploadContent) uploadContent.style.display = 'none';
    } else if (previewContainer) {
        previewContainer.style.display = 'none';
        if (uploadContent) uploadContent.style.display = 'block';
    }
}

function switchMode(target, mode, btn) {
    const uploadMode = document.getElementById(target + '-upload-mode');
    const urlMode = document.getElementById(target + '-url-mode');
    if (uploadMode) uploadMode.style.display = mode === 'upload' ? 'block' : 'none';
    if (urlMode) urlMode.style.display = mode === 'url' ? 'block' : 'none';
    
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
        showToast('Error', 'Ukuran file maksimal 5MB!', 'error'); 
        input.value = ''; 
        return; 
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.querySelector(`#${previewId} img`);
        if (img) img.src = e.target.result;
        
        const previewEl = document.getElementById(previewId);
        const contentEl = document.getElementById(previewId.replace('-local', '-content'));
        if (previewEl) previewEl.style.display = 'flex';
        if (contentEl) contentEl.style.display = 'none';
        
        const info = document.getElementById(infoId);
        if (info) {
            const nameEl = info.querySelector('.file-name');
            const sizeEl = info.querySelector('.file-size');
            if (nameEl) nameEl.textContent = file.name;
            if (sizeEl) sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            info.style.display = 'block';
        }
    };
    reader.readAsDataURL(file);
}

function handleDrop(event, inputId) {
    event.preventDefault(); 
    event.currentTarget.classList.remove('dragover');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById(inputId);
        if (input) {
            input.files = files;
            handleFileSelect(input, inputId.replace('_upload', '-preview-local'), inputId.replace('_upload', '-file-info'));
        }
    }
}

function removeFile(inputId, previewId, infoId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const content = document.getElementById(previewId.replace('-local', '-content'));
    const info = document.getElementById(infoId);
    
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (content) content.style.display = 'block';
    if (info) info.style.display = 'none';
}

// ==========================================
// 5. UTILITIES (FIXED)
// ==========================================
// Character counter dengan null-checking agar tidak error
document.querySelectorAll('[data-counter]').forEach(input => {
    const id = input.getAttribute('data-counter');
    const counter = document.getElementById('counter_' + id);
    const max = parseInt(input.getAttribute('maxlength')) || 500;
    
    const update = () => {
        const val = (input.value !== undefined && input.value !== null) ? input.value : '';
        const len = val.length;
        if (counter) {
            counter.textContent = len;
            counter.style.color = len > max * 0.9 ? 'var(--danger)' : len > max * 0.7 ? 'var(--warning)' : 'var(--text-muted)';
        }
    };
    update();
    input.addEventListener('input', update);
});

// Auto-generate kode prodi dari nama (dengan null-checking)
const namaInput = document.getElementById('nama_prodi');
const kodeInput = document.getElementById('kode_prodi');
if (namaInput && kodeInput) {
    namaInput.addEventListener('input', function() {
        if (!kodeInput.dataset.manualEdit) {
            const jenjangEl = document.getElementById('jenjang');
            const jenjang = jenjangEl ? jenjangEl.value : '';
            const nama = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ' ').split(' ').filter(Boolean);
            const abbrev = nama.map(w => w[0]).join('').substring(0, 4);
            kodeInput.value = jenjang ? jenjang + '-' + abbrev : abbrev;
        }
    });
    kodeInput.addEventListener('input', function() {
        this.dataset.manualEdit = 'true';
    });
}

// ==========================================
// 6. INITIALIZATION
// ==========================================
<?php if (isset($_SESSION['toast'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof showToast === 'function') {
        showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
    }
});
<?php unset($_SESSION['toast']); endif; ?>

<?php if ($edit_prodi): ?>
document.addEventListener('DOMContentLoaded', () => {
    // Gunakan editProdiById agar lebih aman daripada langsung melempar object
    editProdiById(<?= (int)$edit_prodi['id'] ?>);
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>