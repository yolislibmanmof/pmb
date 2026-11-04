<?php
$page_title = "Kelola Virtual Tour";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Virtual Tour']
];
require_once 'includes/header.php';

// ==========================================
// UPLOAD HELPER
// ==========================================
function uploadTourImage($file) {
    $uploadDir = __DIR__ . '/../uploads/tour/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Gagal upload'];
    if ($file['size'] > 10 * 1024 * 1024) return ['success' => false, 'error' => 'Maksimal 10MB untuk foto 360°'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) return ['success' => false, 'error' => 'Format harus JPG/PNG'];
    
    $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $uniqueName)) {
        return ['success' => true, 'url' => '../uploads/tour/' . $uniqueName];
    }
    return ['success' => false, 'error' => 'Gagal menyimpan file'];
}

// ==========================================
// HANDLE CRUD ACTIONS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            // Handle image upload
            $image_url = sanitize($_POST['image_url'] ?? '');
            if (!empty($_FILES['image_upload']['name'])) {
                $up = uploadTourImage($_FILES['image_upload']);
                if ($up['success']) {
                    $image_url = $up['url'];
                } else {
                    throw new Exception($up['error']);
                }
            }
            
            $thumbnail_url = sanitize($_POST['thumbnail_url'] ?? '');
            if (!empty($_FILES['thumbnail_upload']['name'])) {
                $up = uploadTourImage($_FILES['thumbnail_upload']);
                if ($up['success']) {
                    $thumbnail_url = $up['url'];
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO virtual_tour_spots (
                title_id, title_en, description_id, description_en,
                image_url, thumbnail_url, hfov, vfov, haov, vaov, pitch, yaw,
                hotspots_json, facilities_json, sort_order, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                sanitize($_POST['title_id']),
                sanitize($_POST['title_en']),
                sanitize($_POST['description_id'] ?? ''),
                sanitize($_POST['description_en'] ?? ''),
                $image_url,
                $thumbnail_url,
                (int)$_POST['hfov'],
                $_POST['vfov'] ? (int)$_POST['vfov'] : null,
                $_POST['haov'] ? (int)$_POST['haov'] : null,
                $_POST['vaov'] ? (int)$_POST['vaov'] : null,
                (int)$_POST['pitch'],
                (int)$_POST['yaw'],
                $_POST['hotspots_json'] ?? '[]',
                $_POST['facilities_json'] ?? '[]',
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'CREATE', 'virtual_tour_spots', ?, ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, $pdo->lastInsertId(), "Menambah lokasi tour: " . sanitize($_POST['title_id']), $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Lokasi tur berhasil ditambahkan'];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            
            // Handle image upload
            $image_url = sanitize($_POST['image_url'] ?? '');
            if (!empty($_FILES['image_upload']['name'])) {
                $up = uploadTourImage($_FILES['image_upload']);
                if ($up['success']) {
                    $image_url = $up['url'];
                } else {
                    throw new Exception($up['error']);
                }
            }
            
            $thumbnail_url = sanitize($_POST['thumbnail_url'] ?? '');
            if (!empty($_FILES['thumbnail_upload']['name'])) {
                $up = uploadTourImage($_FILES['thumbnail_upload']);
                if ($up['success']) {
                    $thumbnail_url = $up['url'];
                }
            }
            
            $stmt = $pdo->prepare("UPDATE virtual_tour_spots SET 
                title_id=?, title_en=?, description_id=?, description_en=?,
                image_url=?, thumbnail_url=?, hfov=?, vfov=?, haov=?, vaov=?, pitch=?, yaw=?,
                hotspots_json=?, facilities_json=?, sort_order=?, is_active=?
                WHERE id=?");
            
            $stmt->execute([
                sanitize($_POST['title_id']),
                sanitize($_POST['title_en']),
                sanitize($_POST['description_id'] ?? ''),
                sanitize($_POST['description_en'] ?? ''),
                $image_url,
                $thumbnail_url,
                (int)$_POST['hfov'],
                $_POST['vfov'] ? (int)$_POST['vfov'] : null,
                $_POST['haov'] ? (int)$_POST['haov'] : null,
                $_POST['vaov'] ? (int)$_POST['vaov'] : null,
                (int)$_POST['pitch'],
                (int)$_POST['yaw'],
                $_POST['hotspots_json'] ?? '[]',
                $_POST['facilities_json'] ?? '[]',
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'virtual_tour_spots', ?, ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, $id, "Mengupdate lokasi tour ID: {$id}", $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Lokasi tur berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM virtual_tour_spots WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Lokasi tur berhasil dihapus'];
            
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE virtual_tour_spots SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status berhasil diubah'];
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()];
    }
    
    header('Location: virtual-tour.php');
    exit;
}

// ==========================================
// FETCH DATA
// ==========================================
$stmt = $pdo->query("SELECT * FROM virtual_tour_spots ORDER BY sort_order ASC, created_at DESC");
$spots = $stmt->fetchAll();

$total = count($spots);
$active = count(array_filter($spots, fn($s) => $s['is_active'] == 1));
$inactive = $total - $active;

// Get edit data if requested
$edit_spot = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM virtual_tour_spots WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_spot = $stmt->fetch();
}
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= $total ?></h3>
            <p>Total Lokasi</p>
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
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-info">
            <h3>360°</h3>
            <p>Immersive View</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                Kelola Lokasi Virtual Tour (<?= $total ?>)
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Tambah, edit, dan atur urutan lokasi tur 360° kampus</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari lokasi..." data-table-search="tour-table">
            </div>
            <button class="btn btn-primary btn-sm" data-modal-open="tour-modal" onclick="resetForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Lokasi
            </button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="tour-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th data-sort="title">Lokasi</th>
                    <th data-sort="image">Gambar</th>
                    <th data-sort="hfov">Field View</th>
                    <th data-sort="hotspots">Hotspots</th>
                    <th style="width: 80px;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($spots) > 0): ?>
                    <?php foreach($spots as $idx => $s): 
                        $hotspots = json_decode($s['hotspots_json'] ?? '[]', true) ?: [];
                        $facilities = json_decode($s['facilities_json'] ?? '[]', true) ?: [];
                    ?>
                    <tr>
                        <td>
                            <span style="font-weight: 600; color: var(--text-muted);"><?= $s['sort_order'] ?: ($idx + 1) ?></span>
                        </td>
                        <td data-column="title">
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($s['title_id']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($s['title_en']) ?></div>
                            <?php if (count($facilities) > 0): ?>
                            <div style="font-size: 0.7rem; color: var(--primary); margin-top: 0.25rem;">🏛️ <?= count($facilities) ?> fasilitas</div>
                            <?php endif; ?>
                        </td>
                        <td data-column="image">
                            <img src="<?= sanitize($s['thumbnail_url'] ?: $s['image_url']) ?>" alt="" style="width: 60px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border);" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23e2e8f0%22%3E%3Crect width=%2224%22 height=%2224%22/%3E%3C/svg%3E'">
                        </td>
                        <td data-column="hfov">
                            <div style="font-size: 0.85rem;">
                                <div>HFOV: <strong><?= $s['hfov'] ?>°</strong></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Pitch: <?= $s['pitch'] ?>° • Yaw: <?= $s['yaw'] ?>°</div>
                            </div>
                        </td>
                        <td data-column="hotspots">
                            <span class="badge badge-info" style="font-size: 0.75rem;"><?= count($hotspots) ?> hotspot</span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="badge <?= $s['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                    <?= $s['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end; gap: 0.25rem;">
                                <button class="action-btn" onclick="previewSpot(<?= $s['id'] ?>)" title="Preview 360°">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="action-btn" onclick="editSpot(<?= htmlspecialchars(json_encode($s)) ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn danger" onclick="deleteSpot(<?= $s['id'] ?>, '<?= addslashes($s['title_id']) ?>')" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $s['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Lokasi Tur</p>
                            <button class="btn btn-primary btn-sm" onclick="resetForm(); openModal('tour-modal')">Tambah Lokasi Pertama</button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="tour-modal">
    <div class="modal" style="max-width: 900px;">
        <form method="POST" id="tour-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            <input type="hidden" name="hotspots_json" id="hotspots_json" value="[]">
            <input type="hidden" name="facilities_json" id="facilities_json" value="[]">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Lokasi Tur</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Tab Navigation -->
                <div style="display: flex; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem; overflow-x: auto;">
                    <button type="button" class="tab-link-tour active" onclick="switchTourTab(this, 'ttab-basic')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Informasi Dasar</button>
                    <button type="button" class="tab-link-tour" onclick="switchTourTab(this, 'ttab-image')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Gambar 360°</button>
                    <button type="button" class="tab-link-tour" onclick="switchTourTab(this, 'ttab-hotspot')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Hotspots</button>
                    <button type="button" class="tab-link-tour" onclick="switchTourTab(this, 'ttab-facility')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">Fasilitas</button>
                </div>
                
                <!-- Tab 1: Basic Info -->
                <div id="ttab-basic" class="ttab-pane" style="display: block;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Judul (Indonesia) <span class="required">*</span></label>
                            <input type="text" name="title_id" id="title_id" class="form-input" required placeholder="Contoh: Gerbang Utama">
                        </div>
                        <div class="form-group">
                            <label>Title (English) <span class="required">*</span></label>
                            <input type="text" name="title_en" id="title_en" class="form-input" required placeholder="Example: Main Gate">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Deskripsi (Indonesia)</label>
                            <textarea name="description_id" id="description_id" class="form-textarea" rows="3" placeholder="Deskripsi lokasi..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Description (English)</label>
                            <textarea name="description_en" id="description_en" class="form-textarea" rows="3" placeholder="Location description..."></textarea>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Urutan Tampil</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label>HFOV (Horizontal Field of View)</label>
                            <input type="number" name="hfov" id="hfov" class="form-input" min="50" max="120" value="110">
                            <div class="form-hint">Default: 110°</div>
                        </div>
                        <div class="form-group">
                            <label>Pitch (Vertikal)</label>
                            <input type="number" name="pitch" id="pitch" class="form-input" min="-90" max="90" value="0">
                            <div class="form-hint">Default: 0°</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Yaw (Horizontal)</label>
                        <input type="number" name="yaw" id="yaw" class="form-input" min="-180" max="180" value="0" style="max-width: 300px;">
                        <div class="form-hint">Default: 0°</div>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="is_active" checked>
                            <span>Aktif (tampil di website)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Tab 2: Image Upload -->
                <div id="ttab-image" class="ttab-pane" style="display: none;">
                    <div class="form-group">
                        <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <span>Gambar 360° (Equirectangular) <span class="required">*</span></span>
                            <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                                <button type="button" class="mode-btn active" data-target="image" data-mode="upload" onclick="switchMode('image', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Upload</button>
                                <button type="button" class="mode-btn" data-target="image" data-mode="url" onclick="switchMode('image', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">URL</button>
                            </div>
                        </label>
                        
                        <div id="image-upload-mode" class="upload-mode">
                            <div class="upload-dropzone" onclick="document.getElementById('image_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'image_upload')">
                                <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png" style="display: none;" onchange="handleFileSelect(this, 'image-preview-local', 'image-file-info')">
                                <div class="upload-content" id="image-upload-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Klik atau drag & drop foto 360°</p>
                                    <p style="font-size: 0.8rem; color: var(--text-muted);">JPG/PNG, maksimal 10MB, rasio 2:1 ideal</p>
                                </div>
                                <div class="upload-preview" id="image-preview-local" style="display: none;">
                                    <img src="" alt="Preview" style="max-width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px;">
                                    <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('image_upload', 'image-preview-local', 'image-file-info')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div id="image-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted);">
                                <span class="file-name"></span> • <span class="file-size"></span>
                            </div>
                        </div>
                        
                        <div id="image-url-mode" class="url-mode" style="display: none;">
                            <input type="url" name="image_url" id="image_url" class="form-input" placeholder="https://...">
                        </div>
                        <div class="form-hint" style="margin-top: 0.5rem;">Foto equirectangular 360° dengan rasio 2:1 (contoh: 4096×2048px)</div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <span>Thumbnail (Opsional)</span>
                            <div style="display: flex; gap: 0.25rem; background: var(--bg); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border);">
                                <button type="button" class="mode-btn active" data-target="thumbnail" data-mode="upload" onclick="switchMode('thumbnail', 'upload', this)" style="padding: 0.375rem 0.75rem; border: none; background: var(--primary); color: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Upload</button>
                                <button type="button" class="mode-btn" data-target="thumbnail" data-mode="url" onclick="switchMode('thumbnail', 'url', this)" style="padding: 0.375rem 0.75rem; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">URL</button>
                            </div>
                        </label>
                        
                        <div id="thumbnail-upload-mode" class="upload-mode">
                            <div class="upload-dropzone" onclick="document.getElementById('thumbnail_upload').click()" ondragover="event.preventDefault(); this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')" ondrop="handleDrop(event, 'thumbnail_upload')">
                                <input type="file" id="thumbnail_upload" name="thumbnail_upload" accept="image/jpeg,image/png" style="display: none;" onchange="handleFileSelect(this, 'thumbnail-preview-local', 'thumbnail-file-info')">
                                <div class="upload-content" id="thumbnail-upload-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 0.75rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Upload thumbnail</p>
                                    <p style="font-size: 0.8rem; color: var(--text-muted);">JPG/PNG, ideal 400×250px</p>
                                </div>
                                <div class="upload-preview" id="thumbnail-preview-local" style="display: none;">
                                    <img src="" alt="Preview" style="max-width: 100%; max-height: 150px; object-fit: cover; border-radius: 8px;">
                                    <button type="button" class="remove-file-btn" onclick="event.stopPropagation(); removeFile('thumbnail_upload', 'thumbnail-preview-local', 'thumbnail-file-info')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div id="thumbnail-file-info" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem; color: var(--text-muted);">
                                <span class="file-name"></span> • <span class="file-size"></span>
                            </div>
                        </div>
                        
                        <div id="thumbnail-url-mode" class="url-mode" style="display: none;">
                            <input type="url" name="thumbnail_url" id="thumbnail_url" class="form-input" placeholder="https://...">
                        </div>
                    </div>
                </div>
                
                <!-- Tab 3: Hotspots -->
                <div id="ttab-hotspot" class="ttab-pane" style="display: none;">
                    <div style="background: var(--bg); padding: 1rem; border-radius: 10px; margin-bottom: 1rem; border: 1px solid var(--border);">
                        <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem;">📍 Hotspot Editor</h4>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Hotspot adalah titik interaktif di foto 360° yang bisa diklik untuk navigasi atau info.</p>
                    </div>
                    
                    <div id="hotspot-list" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem;">
                        <!-- Hotspots will be added here -->
                    </div>
                    
                    <button type="button" onclick="addHotspot()" class="btn btn-secondary btn-sm" style="width: 100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Hotspot
                    </button>
                    
                    <div style="margin-top: 1rem; padding: 1rem; background: rgba(245, 158, 11, 0.08); border-radius: 8px; border-left: 3px solid var(--warning); font-size: 0.85rem; color: var(--text);">
                        <strong>💡 Tips:</strong> Untuk hotspot navigasi antar lokasi, gunakan <code>targetIndex</code> (index lokasi tujuan, mulai dari 0).
                    </div>
                </div>
                
                <!-- Tab 4: Facilities -->
                <div id="ttab-facility" class="ttab-pane" style="display: none;">
                    <div style="background: var(--bg); padding: 1rem; border-radius: 10px; margin-bottom: 1rem; border: 1px solid var(--border);">
                        <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem;">🏛️ Fasilitas di Lokasi Ini</h4>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Daftar fasilitas yang tersedia di lokasi ini (AC, WiFi, dll).</p>
                    </div>
                    
                    <div id="facility-list" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem;">
                        <!-- Facilities will be added here -->
                    </div>
                    
                    <button type="button" onclick="addFacility()" class="btn btn-secondary btn-sm" style="width: 100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Fasilitas
                    </button>
                </div>
            </div>
            
            <div class="modal-footer" style="justify-content: space-between;">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" id="prev-tab-btn" class="btn btn-secondary" onclick="changeTourTab(-1)" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Sebelumnya
                    </button>
                    <button type="button" id="next-tab-btn" class="btn btn-secondary" onclick="changeTourTab(1)">
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

<!-- Preview Modal -->
<div class="modal-overlay" id="preview-modal">
    <div class="modal" style="max-width: 1000px; width: 95%;">
        <div class="modal-header">
            <h3 id="preview-title">Preview 360°</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('preview-modal'))">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div id="preview-panorama" style="width: 100%; height: 500px; background: #000;"></div>
        </div>
    </div>
</div>

<style>
    .ttab-pane { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .tab-link-tour { transition: all 0.2s ease; }
    .tab-link-tour:hover { color: var(--primary) !important; }
    
    .hotspot-item, .facility-item {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        position: relative;
    }
    
    .hotspot-item .remove-btn, .facility-item .remove-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 28px;
        height: 28px;
        background: var(--danger);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hotspot-item .remove-btn:hover, .facility-item .remove-btn:hover {
        background: #dc2626;
    }
</style>

<script>
const spotsData = <?= json_encode($spots) ?>;
let currentTourTab = 0;
const tourTabOrder = ['ttab-basic', 'ttab-image', 'ttab-hotspot', 'ttab-facility'];
let hotspots = [];
let facilities = [];

function switchTourTab(btn, tabId) {
    document.querySelectorAll('#tour-modal .tab-link-tour').forEach(b => {
        b.classList.remove('active');
        b.style.borderBottomColor = 'transparent';
        b.style.color = 'var(--text-muted)';
    });
    btn.classList.add('active');
    btn.style.borderBottomColor = 'var(--primary)';
    btn.style.color = 'var(--primary)';
    
    document.querySelectorAll('.ttab-pane').forEach(p => p.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    currentTourTab = tourTabOrder.indexOf(tabId);
    updateTourTabButtons();
}

function changeTourTab(dir) {
    const newIndex = currentTourTab + dir;
    if (newIndex >= 0 && newIndex < tourTabOrder.length) {
        const btn = document.querySelectorAll('#tour-modal .tab-link-tour')[newIndex];
        switchTourTab(btn, tourTabOrder[newIndex]);
    }
}

function updateTourTabButtons() {
    document.getElementById('prev-tab-btn').style.display = currentTourTab === 0 ? 'none' : 'inline-flex';
    document.getElementById('next-tab-btn').style.display = currentTourTab === tourTabOrder.length - 1 ? 'none' : 'inline-flex';
}

function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Lokasi Tur';
    document.getElementById('tour-form').reset();
    document.getElementById('is_active').checked = true;
    hotspots = [];
    facilities = [];
    renderHotspots();
    renderFacilities();
    
    // Reset upload zones
    ['image', 'thumbnail'].forEach(type => {
        const pv = document.getElementById(type + '-preview-local');
        if (pv) pv.style.display = 'none';
        const uc = document.getElementById(type + '-upload-content');
        if (uc) uc.style.display = 'block';
        const fi = document.getElementById(type + '-file-info');
        if (fi) fi.style.display = 'none';
        const fu = document.getElementById(type + '_upload');
        if (fu) fu.value = '';
    });
    
    const firstTab = document.querySelector('#tour-modal .tab-link-tour');
    switchTourTab(firstTab, 'ttab-basic');
}

function editSpot(data) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit Lokasi Tur';
    
    document.getElementById('title_id').value = data.title_id || '';
    document.getElementById('title_en').value = data.title_en || '';
    document.getElementById('description_id').value = data.description_id || '';
    document.getElementById('description_en').value = data.description_en || '';
    document.getElementById('sort_order').value = data.sort_order || 0;
    document.getElementById('hfov').value = data.hfov || 110;
    document.getElementById('pitch').value = data.pitch || 0;
    document.getElementById('yaw').value = data.yaw || 0;
    document.getElementById('image_url').value = data.image_url || '';
    document.getElementById('thumbnail_url').value = data.thumbnail_url || '';
    document.getElementById('is_active').checked = data.is_active == 1;
    
    hotspots = JSON.parse(data.hotspots_json || '[]');
    facilities = JSON.parse(data.facilities_json || '[]');
    renderHotspots();
    renderFacilities();
    
    const firstTab = document.querySelector('#tour-modal .tab-link-tour');
    switchTourTab(firstTab, 'ttab-basic');
    openModal('tour-modal');
}

function deleteSpot(id, title) {
    if (confirmDelete(`Hapus lokasi "${title}"? Tindakan ini tidak dapat dibatalkan.`)) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function previewSpot(id) {
    const spot = spotsData.find(s => parseInt(s.id) === parseInt(id));
    if (!spot) return;
    
    document.getElementById('preview-title').textContent = spot.title_id;
    openModal('preview-modal');
    
    setTimeout(() => {
        if (typeof pannellum !== 'undefined') {
            pannellum.viewer('preview-panorama', {
                type: 'equirectangular',
                panorama: spot.image_url,
                autoLoad: true,
                hfov: spot.hfov || 110,
                pitch: spot.pitch || 0,
                yaw: spot.yaw || 0
            });
        }
    }, 300);
}

// Hotspot functions
function addHotspot() {
    hotspots.push({ pitch: 0, yaw: 0, text: '', type: 'info', targetIndex: null });
    renderHotspots();
}

function removeHotspot(index) {
    hotspots.splice(index, 1);
    renderHotspots();
}

function renderHotspots() {
    const list = document.getElementById('hotspot-list');
    list.innerHTML = hotspots.map((h, i) => `
        <div class="hotspot-item">
            <button type="button" class="remove-btn" onclick="removeHotspot(${i})">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div style="grid-column: 1 / -1; font-weight: 600; font-size: 0.85rem; color: var(--primary);">Hotspot #${i + 1}</div>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Pitch</label>
                <input type="number" class="form-input" value="${h.pitch}" onchange="hotspots[${i}].pitch = parseInt(this.value)" style="padding: 0.5rem;">
            </div>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Yaw</label>
                <input type="number" class="form-input" value="${h.yaw}" onchange="hotspots[${i}].yaw = parseInt(this.value)" style="padding: 0.5rem;">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Teks/Label</label>
                <input type="text" class="form-input" value="${h.text}" onchange="hotspots[${i}].text = this.value" placeholder="Contoh: Ke Lobby">
            </div>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Tipe</label>
                <select class="form-select" onchange="hotspots[${i}].type = this.value" style="padding: 0.5rem;">
                    <option value="info" ${h.type === 'info' ? 'selected' : ''}>Info</option>
                    <option value="scene" ${h.type === 'scene' ? 'selected' : ''}>Navigasi</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Target Index (opsional)</label>
                <input type="number" class="form-input" value="${h.targetIndex || ''}" onchange="hotspots[${i}].targetIndex = this.value ? parseInt(this.value) : null" placeholder="Index lokasi tujuan">
            </div>
        </div>
    `).join('');
    
    document.getElementById('hotspots_json').value = JSON.stringify(hotspots);
}

// Facility functions
function addFacility() {
    facilities.push({ name_id: '', name_en: '', icon: '🏛️' });
    renderFacilities();
}

function removeFacility(index) {
    facilities.splice(index, 1);
    renderFacilities();
}

function renderFacilities() {
    const list = document.getElementById('facility-list');
    list.innerHTML = facilities.map((f, i) => `
        <div class="facility-item">
            <button type="button" class="remove-btn" onclick="removeFacility(${i})">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Nama (ID)</label>
                <input type="text" class="form-input" value="${f.name_id}" onchange="facilities[${i}].name_id = this.value" placeholder="Contoh: AC Sentral">
            </div>
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Name (EN)</label>
                <input type="text" class="form-input" value="${f.name_en}" onchange="facilities[${i}].name_en = this.value" placeholder="Example: Central AC">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Icon (Emoji)</label>
                <input type="text" class="form-input" value="${f.icon}" onchange="facilities[${i}].icon = this.value" placeholder="🏛️" style="max-width: 100px;">
            </div>
        </div>
    `).join('');
    
    document.getElementById('facilities_json').value = JSON.stringify(facilities);
}

// Mode switching
function switchMode(target, mode, btn) {
    document.getElementById(target + '-upload-mode').style.display = mode === 'upload' ? 'block' : 'none';
    document.getElementById(target + '-url-mode').style.display = mode === 'url' ? 'block' : 'none';
    const container = btn.parentElement;
    container.querySelectorAll('.mode-btn').forEach(b => { b.style.background = 'transparent'; b.style.color = 'var(--text-muted)'; });
    btn.style.background = 'var(--primary)';
    btn.style.color = 'white';
}

function handleFileSelect(input, previewId, infoId) {
    const file = input.files[0]; if (!file) return;
    if (file.size > 10 * 1024 * 1024) { alert('Maksimal 10MB!'); input.value = ''; return; }
    const reader = new FileReader();
    reader.onload = (e) => {
        document.querySelector(`#${previewId} img`).src = e.target.result;
        document.getElementById(previewId).style.display = 'flex';
        document.getElementById(previewId.replace('-local', '-content')).style.display = 'none';
        const info = document.getElementById(infoId);
        info.querySelector('.file-name').textContent = file.name;
        info.querySelector('.file-size').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        info.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function handleDrop(event, inputId) {
    event.preventDefault(); event.currentTarget.classList.remove('dragover');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById(inputId);
        input.files = files;
        handleFileSelect(input, inputId.replace('_upload', '-preview-local'), inputId.replace('_upload', '-file-info'));
    }
}

function removeFile(inputId, previewId, infoId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).style.display = 'none';
    document.getElementById(previewId.replace('-local', '-content')).style.display = 'block';
    document.getElementById(infoId).style.display = 'none';
}

<?php if (isset($_SESSION['toast'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
});
<?php unset($_SESSION['toast']); endif; ?>

<?php if ($edit_spot): ?>
document.addEventListener('DOMContentLoaded', () => {
    editSpot(<?= json_encode($edit_spot) ?>);
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>