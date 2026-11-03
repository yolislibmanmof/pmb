<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI SINI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Gelombang";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Gelombang']
];

// ======================================================================
// 2. HANDLE CRUD ACTIONS (Sebelum ada output HTML agar redirect aman)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error: Permintaan tidak valid.');
    }
    
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO gelombang (nama, tahun_akademik, tanggal_mulai, tanggal_selesai, biaya_pendaftaran, is_open, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                sanitize($_POST['nama']), sanitize($_POST['tahun_akademik']), $_POST['tanggal_mulai'], $_POST['tanggal_selesai'],
                (float)$_POST['biaya_pendaftaran'], isset($_POST['is_open']) ? 1 : 0, sanitize($_POST['description'])
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'CREATE', 'gelombang', ?, ?, ?)");
            $log->execute([$user_id, $pdo->lastInsertId(), "Menambah gelombang: " . sanitize($_POST['nama']), $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Gelombang pendaftaran berhasil ditambahkan'];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE gelombang SET nama=?, tahun_akademik=?, tanggal_mulai=?, tanggal_selesai=?, biaya_pendaftaran=?, is_open=?, description=? WHERE id=?");
            $stmt->execute([
                sanitize($_POST['nama']), sanitize($_POST['tahun_akademik']), $_POST['tanggal_mulai'], $_POST['tanggal_selesai'],
                (float)$_POST['biaya_pendaftaran'], isset($_POST['is_open']) ? 1 : 0, sanitize($_POST['description']), $id
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'gelombang', ?, ?, ?)");
            $log->execute([$user_id, $id, "Mengupdate gelombang ID: {$id}", $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Gelombang pendaftaran berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $check = $pdo->prepare("SELECT COUNT(*) FROM applicants WHERE gelombang_id = ?");
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Tidak dapat menghapus gelombang yang sudah memiliki pendaftar.'];
            } else {
                $stmt = $pdo->prepare("DELETE FROM gelombang WHERE id = ?");
                $stmt->execute([$id]);
                
                $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'gelombang', ?, ?, ?)");
                $log->execute([$user_id, $id, "Menghapus gelombang ID: {$id}", $ip_address]);
                
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Gelombang pendaftaran berhasil dihapus'];
            }
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $pdo->prepare("UPDATE gelombang SET is_open = NOT is_open WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status gelombang berhasil diubah'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    // Redirect AMAN karena belum ada HTML yang dicetak
    header('Location: gelombang.php');
    exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (Setelah semua logic redirect selesai)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA & RENDER HTML
// ======================================================================
$stmt = $pdo->query("SELECT * FROM gelombang ORDER BY tahun_akademik DESC, tanggal_mulai DESC");
$gelombang_list = $stmt->fetchAll();

$total = count($gelombang_list);
$aktif = count(array_filter($gelombang_list, fn($g) => $g['is_open'] == 1));
$nonaktif = $total - $aktif;

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM gelombang WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

function formatRupiah($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
}
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= $total ?></h3>
            <p>Total Gelombang</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $aktif ?>"><?= $aktif ?></h3>
            <p>Sedang Dibuka</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $nonaktif ?>"><?= $nonaktif ?></h3>
            <p>Ditutup</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Daftar Gelombang Pendaftaran
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Atur periode dan biaya pendaftaran mahasiswa baru</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari gelombang..." data-table-search="gelombang-table">
            </div>
            <button class="btn btn-primary btn-sm" data-modal-open="gelombang-modal" onclick="resetForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Gelombang
            </button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="gelombang-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th data-sort="nama">Nama Gelombang</th>
                    <th data-sort="tahun">Tahun Akademik</th>
                    <th data-sort="periode">Periode</th>
                    <th data-sort="biaya">Biaya Pendaftaran</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gelombang_list) > 0): ?>
                    <?php foreach($gelombang_list as $index => $g): ?>
                    <tr>
                        <td><span style="font-weight: 600; color: var(--text-muted);"><?= $index + 1 ?></span></td>
                        <td data-column="nama">
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($g['nama']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($g['description'] ?: 'Tidak ada deskripsi') ?></div>
                        </td>
                        <td data-column="tahun">
                            <span class="badge badge-info"><?= sanitize($g['tahun_akademik']) ?></span>
                        </td>
                        <td data-column="periode">
                            <div style="font-size: 0.85rem;"><?= date('d M Y', strtotime($g['tanggal_mulai'])) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">s/d <?= date('d M Y', strtotime($g['tanggal_selesai'])) ?></div>
                        </td>
                        <td data-column="biaya" style="font-weight: 600; color: var(--text);">
                            <?= formatRupiah($g['biaya_pendaftaran']) ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                <button type="submit" class="badge <?= $g['is_open'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                    <?= $g['is_open'] ? 'Dibuka' : 'Ditutup' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end;">
                                <button class="action-btn" onclick="editGelombangById(<?= $g['id'] ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn danger" onclick="deleteGelombang(<?= $g['id'] ?>, '<?= addslashes($g['nama']) ?>')" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $g['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Data Gelombang</p>
                            <button class="btn btn-primary btn-sm" onclick="resetForm(); openModal('gelombang-modal')">Tambah Gelombang Pertama</button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="gelombang-modal">
    <div class="modal" style="max-width: 600px;">
        <form method="POST" id="gelombang-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Gelombang</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-group">
                    <label>Nama Gelombang <span class="required">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-input" required placeholder="Contoh: Gelombang 1, Gelombang Reguler">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Tahun Akademik <span class="required">*</span></label>
                        <input type="text" name="tahun_akademik" id="tahun_akademik" class="form-input" required placeholder="2027/2028">
                    </div>
                    <div class="form-group">
                        <label>Biaya Pendaftaran (Rp) <span class="required">*</span></label>
                        <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-input" required min="0" step="10000" value="200000">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Tanggal Mulai <span class="required">*</span></label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai <span class="required">*</span></label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi / Catatan</label>
                    <textarea name="description" id="description" class="form-textarea" rows="3" placeholder="Potongan biaya, syarat khusus, dll."></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_open" id="is_open" checked>
                        <span style="font-size: 0.9rem; font-weight: 600;">Buka Gelombang Ini (Tampil di Publik)</span>
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
const gelombangData = <?= json_encode($gelombang_list) ?>;

function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Gelombang';
    document.getElementById('gelombang-form').reset();
    document.getElementById('is_open').checked = true;
    document.getElementById('biaya_pendaftaran').value = '200000';
}

function editGelombangById(id) {
    const data = gelombangData.find(g => parseInt(g.id) === parseInt(id));
    if (data) {
        document.getElementById('form-action').value = 'edit';
        document.getElementById('form-id').value = data.id;
        document.getElementById('modal-title').textContent = 'Edit Gelombang';
        
        document.getElementById('nama').value = data.nama || '';
        document.getElementById('tahun_akademik').value = data.tahun_akademik || '';
        document.getElementById('tanggal_mulai').value = data.tanggal_mulai || '';
        document.getElementById('tanggal_selesai').value = data.tanggal_selesai || '';
        document.getElementById('biaya_pendaftaran').value = data.biaya_pendaftaran || 0;
        document.getElementById('description').value = data.description || '';
        document.getElementById('is_open').checked = (data.is_open == 1);
        
        const modal = document.getElementById('gelombang-modal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
}

function deleteGelombang(id, nama) {
    if (typeof confirmDelete === 'function') {
        confirmDelete(`Hapus gelombang <strong>${nama}</strong>? Data pendaftar yang terkait akan kehilangan referensi gelombang.`, () => {
            document.getElementById('delete-form-' + id).submit();
        });
    } else {
        if (confirm(`Hapus gelombang ${nama}?`)) {
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
    editGelombangById(<?= (int)$edit_data['id'] ?>);
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>