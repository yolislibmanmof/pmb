<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Testimoni";
$breadcrumbs = [['label' => 'Dashboard', 'url' => 'index.php'], ['label' => 'Kelola Testimoni']];

// ======================================================================
// 2. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF Error');
    $action = $_POST['action'] ?? ''; $user_id = $_SESSION['user_id'] ?? null; $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    try {
        if ($action === 'add' || $action === 'edit') {
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $rating = max(1, min(5, (int)$_POST['rating']));
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO testimonials (name, program, year, photo_url, content_id, content_en, rating, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([sanitize($_POST['name']), sanitize($_POST['program']), sanitize($_POST['year']), sanitize($_POST['photo_url']), sanitize($_POST['content_id']), sanitize($_POST['content_en']), $rating, $is_active, (int)$_POST['sort_order']]);
                $rec_id = $pdo->lastInsertId(); $log_desc = "Menambah testimoni: " . sanitize($_POST['name']);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE testimonials SET name=?, program=?, year=?, photo_url=?, content_id=?, content_en=?, rating=?, is_active=?, sort_order=? WHERE id=?");
                $stmt->execute([sanitize($_POST['name']), sanitize($_POST['program']), sanitize($_POST['year']), sanitize($_POST['photo_url']), sanitize($_POST['content_id']), sanitize($_POST['content_en']), $rating, $is_active, (int)$_POST['sort_order'], $id]);
                $rec_id = $id; $log_desc = "Mengupdate testimoni ID: {$id}";
            }
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, 'testimonials', ?, ?, ?)")->execute([$user_id, strtoupper($action), $rec_id, $log_desc, $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Testimoni berhasil ' . ($action === 'add' ? 'ditambahkan' : 'diperbarui')];
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id']; $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'testimonials', ?, ?, ?)")->execute([$user_id, $id, "Menghapus testimoni ID: {$id}", $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Testimoni berhasil dihapus'];
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id']; $pdo->prepare("UPDATE testimonials SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status testimoni berhasil diubah'];
        }
    } catch (Exception $e) { $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]; }
    header('Location: testimoni.php'); exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA
// ======================================================================
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
$testi_list = $stmt->fetchAll();
$total = count($testi_list); $aktif = count(array_filter($testi_list, fn($t) => $t['is_active'] == 1));
$avg_rating = $total > 0 ? array_sum(array_column($testi_list, 'rating')) / $total : 0;

$edit_data = null;
if (isset($_GET['edit'])) { $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?"); $stmt->execute([(int)$_GET['edit']]); $edit_data = $stmt->fetch(); }
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><div class="stat-info"><h3 data-counter="<?= $total ?>"><?= $total ?></h3><p>Total Testimoni</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="stat-info"><h3 data-counter="<?= $aktif ?>"><?= $aktif ?></h3><p>Testimoni Aktif</p></div></div>
    <div class="stat-card"><div class="stat-icon orange"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div><div class="stat-info"><h3 data-counter="<?= round($avg_rating, 1) ?>"><?= round($avg_rating, 1) ?></h3><p>Rata-rata Rating</p></div></div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div><h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Daftar Testimoni Alumni</h3><p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola ulasan dan rating dari lulusan</p></div>
        <div class="table-controls">
            <div class="search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Cari testimoni..." data-table-search="testi-table"></div>
            <button class="btn btn-primary btn-sm" data-modal-open="testi-modal" onclick="resetForm()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Testimoni</button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="admin-table" id="testi-table" data-sortable>
            <thead><tr><th style="width: 50px;">#</th><th data-sort="nama">Nama & Program</th><th data-sort="rating">Rating</th><th data-sort="tahun">Tahun</th><th style="width: 100px;">Status</th><th style="width: 120px; text-align: right;">Aksi</th></tr></thead>
            <tbody>
                <?php if (count($testi_list) > 0): foreach($testi_list as $i => $t): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--text-muted);"><?= $i + 1 ?></span></td>
                    <td data-column="nama"><div style="display: flex; align-items: center; gap: 0.75rem;"><img src="<?= sanitize($t['photo_url']) ?: 'https://via.placeholder.com/40' ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/40'"><div><div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($t['name']) ?></div><div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($t['program'] ?: '-') ?></div></div></div></td>
                    <td data-column="rating"><div style="color: var(--accent); font-size: 0.9rem;"><?= str_repeat('★', $t['rating']) ?><span style="color: var(--border);"><?= str_repeat('★', 5 - $t['rating']) ?></span></div></td>
                    <td data-column="tahun"><span class="badge badge-info"><?= sanitize($t['year'] ?: '-') ?></span></td>
                    <td><form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;"><?= $t['is_active'] ? 'Aktif' : 'Nonaktif' ?></button></form></td>
                    <td><div class="table-actions" style="justify-content: flex-end;"><button class="action-btn" onclick="editTestiById(<?= $t['id'] ?>)" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn danger" onclick="deleteTesti(<?= $t['id'] ?>, '<?= addslashes($t['name']) ?>')" title="Hapus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button><form id="delete-form-<?= $t['id'] ?>" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>"></form></div></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="6" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);"><p style="font-weight: 600;">Belum Ada Testimoni</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="testi-modal">
    <div class="modal" style="max-width: 700px;">
        <form method="POST" id="testi-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            <div class="modal-header"><h3 id="modal-title">Tambah Testimoni</h3><button type="button" class="modal-close" data-modal-close><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group"><label>Nama Lengkap <span class="required">*</span></label><input type="text" name="name" id="name" class="form-input" required></div>
                    <div class="form-group"><label>Program Studi / Angkatan</label><input type="text" name="program" id="program" class="form-input" placeholder="S1 Bisnis Digital - 2023"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group"><label>Tahun Lulus</label><input type="text" name="year" id="year" class="form-input" placeholder="2023"></div>
                    <div class="form-group"><label>Rating (1-5) <span class="required">*</span></label><select name="rating" id="rating" class="form-select" required><option value="5">5 - Sangat Baik</option><option value="4">4 - Baik</option><option value="3">3 - Cukup</option><option value="2">2 - Kurang</option><option value="1">1 - Buruk</option></select></div>
                    <div class="form-group"><label>Urutan</label><input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0"></div>
                </div>
                <div class="form-group"><label>URL Foto Profil</label><input type="url" name="photo_url" id="photo_url" class="form-input" placeholder="https://..."></div>
                <div class="form-group"><label>Testimoni (Bahasa Indonesia) <span class="required">*</span></label><textarea name="content_id" id="content_id" class="form-textarea" rows="4" required></textarea></div>
                <div class="form-group"><label>Testimoni (English)</label><textarea name="content_en" id="content_en" class="form-textarea" rows="4"></textarea></div>
                <div class="form-group"><label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;"><input type="checkbox" name="is_active" id="is_active" checked><span style="font-size: 0.9rem; font-weight: 600;">Tampilkan di Website</span></label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Batal</button><button type="submit" class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>Simpan</button></div>
        </form>
    </div>
</div>

<script>
const testiData = <?= json_encode($testi_list) ?>;
function resetForm() { document.getElementById('form-action').value = 'add'; document.getElementById('form-id').value = ''; document.getElementById('modal-title').textContent = 'Tambah Testimoni'; document.getElementById('testi-form').reset(); document.getElementById('is_active').checked = true; document.getElementById('rating').value = '5'; }
function editTestiById(id) {
    const data = testiData.find(t => parseInt(t.id) === parseInt(id)); if (!data) return;
    document.getElementById('form-action').value = 'edit'; document.getElementById('form-id').value = data.id; document.getElementById('modal-title').textContent = 'Edit Testimoni';
    document.getElementById('name').value = data.name || ''; document.getElementById('program').value = data.program || ''; document.getElementById('year').value = data.year || '';
    document.getElementById('photo_url').value = data.photo_url || ''; document.getElementById('content_id').value = data.content_id || ''; document.getElementById('content_en').value = data.content_en || '';
    document.getElementById('rating').value = data.rating || 5; document.getElementById('sort_order').value = data.sort_order || 0; document.getElementById('is_active').checked = (data.is_active == 1);
    const modal = document.getElementById('testi-modal'); if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function deleteTesti(id, name) { if (typeof confirmDelete === 'function') { confirmDelete(`Hapus testimoni dari <strong>${name}</strong>?`, () => { document.getElementById('delete-form-' + id).submit(); }); } else { if (confirm(`Hapus testimoni?`)) { document.getElementById('delete-form-' + id).submit(); } } }
<?php if (isset($_SESSION['toast'])): ?> document.addEventListener('DOMContentLoaded', () => { if (typeof showToast === 'function') showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>'); }); <?php unset($_SESSION['toast']); endif; ?>
<?php if ($edit_data): ?> document.addEventListener('DOMContentLoaded', () => { editTestiById(<?= (int)$edit_data['id'] ?>); }); <?php endif; ?>
</script>
<?php require_once 'includes/footer.php'; ?>