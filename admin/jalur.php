<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Jalur Pendaftaran";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Jalur']
];

// ======================================================================
// 1.5 HELPER FUNCTIONS
// ======================================================================
if (!function_exists('formatRupiah')) {
    function formatRupiah($num) {
        $num = (float)$num;
        $prefix = $num < 0 ? '- ' : '';
        return $prefix . 'Rp ' . number_format(abs($num), 0, ',', '.');
    }
}

// ======================================================================
// 2. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO jalur_pendaftaran (nama_jalur, kode_jalur, biaya_tambahan, deskripsi_id, deskripsi_en, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                sanitize($_POST['nama_jalur']),
                strtoupper(sanitize($_POST['kode_jalur'])),
                (float)$_POST['biaya_tambahan'],
                sanitize($_POST['deskripsi_id']),
                sanitize($_POST['deskripsi_en']),
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0)
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'CREATE', 'jalur_pendaftaran', ?, ?, ?)");
            $log->execute([$user_id, $pdo->lastInsertId(), "Menambah jalur: " . sanitize($_POST['nama_jalur']), $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Jalur pendaftaran berhasil ditambahkan'];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE jalur_pendaftaran SET nama_jalur=?, kode_jalur=?, biaya_tambahan=?, deskripsi_id=?, deskripsi_en=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([
                sanitize($_POST['nama_jalur']),
                strtoupper(sanitize($_POST['kode_jalur'])),
                (float)$_POST['biaya_tambahan'],
                sanitize($_POST['deskripsi_id']),
                sanitize($_POST['deskripsi_en']),
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0),
                $id
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'jalur_pendaftaran', ?, ?, ?)");
            $log->execute([$user_id, $id, "Mengupdate jalur ID: {$id}", $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Jalur pendaftaran berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            $check = $pdo->prepare("SELECT COUNT(*) FROM applicants WHERE jalur_id = ?");
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Tidak dapat menghapus jalur yang sudah digunakan oleh pendaftar.'];
            } else {
                $stmt = $pdo->prepare("DELETE FROM jalur_pendaftaran WHERE id = ?");
                $stmt->execute([$id]);
                
                $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'jalur_pendaftaran', ?, ?, ?)");
                $log->execute([$user_id, $id, "Menghapus jalur ID: {$id}", $ip_address]);
                
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Jalur pendaftaran berhasil dihapus'];
            }
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $pdo->prepare("UPDATE jalur_pendaftaran SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status jalur berhasil diubah'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    // ======================================================================
    // 3. REDIRECT SETELAH POST (WAJIB SEBELUM HTML DICETAK)
    // ======================================================================
    header('Location: jalur.php');
    exit;
}

// ======================================================================
// 4. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 5. FETCH DATA
// ======================================================================
$stmt = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY sort_order ASC, nama_jalur ASC");
$jalur_list = $stmt->fetchAll();

$total = count($jalur_list);
$aktif = count(array_filter($jalur_list, fn($j) => $j['is_active'] == 1));
$nonaktif = $total - $aktif;

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jalur_pendaftaran WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= $total ?></h3>
            <p>Total Jalur</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $aktif ?>"><?= $aktif ?></h3>
            <p>Jalur Aktif</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $nonaktif ?>"><?= $nonaktif ?></h3>
            <p>Jalur Nonaktif</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Daftar Jalur Pendaftaran
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Atur jenis jalur, kode, dan biaya tambahan/potongan</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari jalur..." data-table-search="jalur-table">
            </div>
            <button class="btn btn-primary btn-sm" data-modal-open="jalur-modal" onclick="resetForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Jalur
            </button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="jalur-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th data-sort="nama">Nama Jalur</th>
                    <th data-sort="kode">Kode</th>
                    <th data-sort="biaya">Biaya Tambahan</th>
                    <th data-sort="urutan">Urutan</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($jalur_list) > 0): ?>
                    <?php foreach($jalur_list as $index => $j): ?>
                    <tr>
                        <td><span style="font-weight: 600; color: var(--text-muted);"><?= $index + 1 ?></span></td>
                        <td data-column="nama">
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($j['nama_jalur']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 300px; white-space: normal;"><?= sanitize($j['deskripsi_id'] ?: 'Tidak ada deskripsi') ?></div>
                        </td>
                        <td data-column="kode">
                            <code style="background: var(--bg); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; color: var(--primary);"><?= sanitize($j['kode_jalur']) ?></code>
                        </td>
                        <td data-column="biaya" style="font-weight: 600; font-size: 0.9rem;">
                            <span style="color: <?= (float)$j['biaya_tambahan'] < 0 ? 'var(--success)' : 'var(--text)' ?>;">
                                <?= formatRupiah($j['biaya_tambahan']) ?>
                            </span>
                            <?php if ((float)$j['biaya_tambahan'] < 0): ?>
                                <div style="font-size: 0.7rem; color: var(--success); font-weight: 500;">(Potongan)</div>
                            <?php endif; ?>
                        </td>
                        <td data-column="urutan" style="text-align: center;">
                            <span class="badge badge-info"><?= $j['sort_order'] ?></span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                <button type="submit" class="badge <?= $j['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                    <?= $j['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end;">
                                <button class="action-btn" onclick="editJalurById(<?= $j['id'] ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn danger" onclick="deleteJalur(<?= $j['id'] ?>, '<?= addslashes($j['nama_jalur']) ?>')" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $j['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Jalur Pendaftaran</p>
                            <button class="btn btn-primary btn-sm" onclick="resetForm(); openModal('jalur-modal')">Tambah Jalur Pertama</button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="jalur-modal">
    <div class="modal" style="max-width: 600px;">
        <form method="POST" id="jalur-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Jalur Pendaftaran</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Nama Jalur <span class="required">*</span></label>
                        <input type="text" name="nama_jalur" id="nama_jalur" class="form-input" required placeholder="Contoh: Beasiswa Prestasi">
                    </div>
                    <div class="form-group">
                        <label>Kode Jalur <span class="required">*</span></label>
                        <input type="text" name="kode_jalur" id="kode_jalur" class="form-input" required maxlength="10" placeholder="PRESTASI" style="text-transform: uppercase;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Biaya Tambahan (Rp) <span class="required">*</span></label>
                        <input type="number" name="biaya_tambahan" id="biaya_tambahan" class="form-input" required step="10000" value="0">
                        <div class="form-hint">Gunakan nilai negatif (-) untuk potongan/discount.</div>
                    </div>
                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi (Bahasa Indonesia)</label>
                    <textarea name="deskripsi_id" id="deskripsi_id" class="form-textarea" rows="3" placeholder="Penjelasan singkat jalur ini..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Description (English)</label>
                    <textarea name="deskripsi_en" id="deskripsi_en" class="form-textarea" rows="3" placeholder="Brief description of this path..."></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span style="font-size: 0.9rem; font-weight: 600;">Jalur Aktif (Tampil di Formulir Publik)</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const jalurData = <?= json_encode($jalur_list) ?>;

function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Jalur Pendaftaran';
    document.getElementById('jalur-form').reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('biaya_tambahan').value = '0';
    document.getElementById('sort_order').value = '0';
}

function editJalurById(id) {
    const data = jalurData.find(j => parseInt(j.id) === parseInt(id));
    if (!data) return;

    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit Jalur Pendaftaran';
    
    document.getElementById('nama_jalur').value = data.nama_jalur || '';
    document.getElementById('kode_jalur').value = data.kode_jalur || '';
    document.getElementById('biaya_tambahan').value = data.biaya_tambahan || 0;
    document.getElementById('sort_order').value = data.sort_order || 0;
    document.getElementById('deskripsi_id').value = data.deskripsi_id || '';
    document.getElementById('deskripsi_en').value = data.deskripsi_en || '';
    document.getElementById('is_active').checked = (data.is_active == 1);
    
    const modal = document.getElementById('jalur-modal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function deleteJalur(id, nama) {
    if (typeof confirmDelete === 'function') {
        confirmDelete(`Hapus jalur <strong>${nama}</strong>? Pendaftar yang menggunakan jalur ini akan kehilangan referensi jalur.`, () => {
            document.getElementById('delete-form-' + id).submit();
        });
    } else {
        if (confirm(`Hapus jalur ${nama}?`)) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
}

<?php if (isset($_SESSION['toast'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof showToast === 'function') {
        showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
    }
});
<?php unset($_SESSION['toast']); endif; ?>

<?php if ($edit_data): ?>
document.addEventListener('DOMContentLoaded', () => {
    editJalurById(<?= (int)$edit_data['id'] ?>);
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>