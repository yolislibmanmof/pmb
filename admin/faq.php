<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola FAQ";
$breadcrumbs = [['label' => 'Dashboard', 'url' => 'index.php'], ['label' => 'Kelola FAQ']];

// ======================================================================
// 2. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF Error');
    $action = $_POST['action'] ?? ''; $user_id = $_SESSION['user_id'] ?? null; $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    try {
        if ($action === 'add' || $action === 'edit') {
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO faq (question_id, question_en, answer_id, answer_en, category, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([sanitize($_POST['question_id']), sanitize($_POST['question_en']), sanitize($_POST['answer_id']), sanitize($_POST['answer_en']), sanitize($_POST['category']), (int)$_POST['sort_order'], $is_active]);
                $rec_id = $pdo->lastInsertId(); $log_desc = "Menambah FAQ: " . sanitize($_POST['question_id']);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE faq SET question_id=?, question_en=?, answer_id=?, answer_en=?, category=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([sanitize($_POST['question_id']), sanitize($_POST['question_en']), sanitize($_POST['answer_id']), sanitize($_POST['answer_en']), sanitize($_POST['category']), (int)$_POST['sort_order'], $is_active, $id]);
                $rec_id = $id; $log_desc = "Mengupdate FAQ ID: {$id}";
            }
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, 'faq', ?, ?, ?)")->execute([$user_id, strtoupper($action), $rec_id, $log_desc, $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'FAQ berhasil ' . ($action === 'add' ? 'ditambahkan' : 'diperbarui')];
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id']; $pdo->prepare("DELETE FROM faq WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'faq', ?, ?, ?)")->execute([$user_id, $id, "Menghapus FAQ ID: {$id}", $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'FAQ berhasil dihapus'];
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id']; $pdo->prepare("UPDATE faq SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status FAQ berhasil diubah'];
        }
    } catch (Exception $e) { $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]; }
    header('Location: faq.php'); exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA & RENDER HTML
// ======================================================================
$stmt = $pdo->query("SELECT * FROM faq ORDER BY category ASC, sort_order ASC");
$faq_list = $stmt->fetchAll();
$total = count($faq_list); $aktif = count(array_filter($faq_list, fn($f) => $f['is_active'] == 1));

$edit_data = null;
if (isset($_GET['edit'])) { $stmt = $pdo->prepare("SELECT * FROM faq WHERE id = ?"); $stmt->execute([(int)$_GET['edit']]); $edit_data = $stmt->fetch(); }
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="stat-info"><h3 data-counter="<?= $total ?>"><?= $total ?></h3><p>Total FAQ</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="stat-info"><h3 data-counter="<?= $aktif ?>"><?= $aktif ?></h3><p>FAQ Aktif</p></div></div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div><h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Daftar Pertanyaan Umum (FAQ)</h3><p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola pertanyaan yang sering diajukan calon mahasiswa</p></div>
        <div class="table-controls">
            <div class="search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Cari pertanyaan..." data-table-search="faq-table"></div>
            <button class="btn btn-primary btn-sm" data-modal-open="faq-modal" onclick="resetForm()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah FAQ</button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="admin-table" id="faq-table" data-sortable>
            <thead><tr><th style="width: 50px;">#</th><th data-sort="pertanyaan">Pertanyaan</th><th data-sort="kategori">Kategori</th><th data-sort="urutan">Urutan</th><th style="width: 100px;">Status</th><th style="width: 120px; text-align: right;">Aksi</th></tr></thead>
            <tbody>
                <?php if (count($faq_list) > 0): foreach($faq_list as $i => $f): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--text-muted);"><?= $i + 1 ?></span></td>
                    <td data-column="pertanyaan"><div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($f['question_id']) ?></div><div style="font-size: 0.75rem; color: var(--text-muted); max-width: 400px; white-space: normal;"><?= sanitize(substr($f['answer_id'], 0, 60)) ?>...</div></td>
                    <td data-column="kategori"><span class="badge badge-info"><?= sanitize($f['category']) ?></span></td>
                    <td data-column="urutan" style="text-align: center;"><span class="badge badge-warning"><?= $f['sort_order'] ?></span></td>
                    <td><form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button type="submit" class="badge <?= $f['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;"><?= $f['is_active'] ? 'Aktif' : 'Nonaktif' ?></button></form></td>
                    <td><div class="table-actions" style="justify-content: flex-end;"><button class="action-btn" onclick="editFaqById(<?= $f['id'] ?>)" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn danger" onclick="deleteFaq(<?= $f['id'] ?>, '<?= addslashes(substr($f['question_id'], 0, 20)) ?>')" title="Hapus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button><form id="delete-form-<?= $f['id'] ?>" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>"></form></div></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="6" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);"><p style="font-weight: 600;">Belum Ada FAQ</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="faq-modal">
    <div class="modal" style="max-width: 700px;">
        <form method="POST" id="faq-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            <div class="modal-header"><h3 id="modal-title">Tambah FAQ</h3><button type="button" class="modal-close" data-modal-close><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group"><label>Kategori <span class="required">*</span></label><input type="text" name="category" id="category" class="form-input" required placeholder="pendaftaran, biaya, dokumen"></div>
                    <div class="form-group"><label>Urutan Tampil</label><input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0"></div>
                </div>
                <div class="form-group"><label>Pertanyaan (ID) <span class="required">*</span></label><input type="text" name="question_id" id="question_id" class="form-input" required></div>
                <div class="form-group"><label>Pertanyaan (EN)</label><input type="text" name="question_en" id="question_en" class="form-input"></div>
                <div class="form-group"><label>Jawaban (ID) <span class="required">*</span></label><textarea name="answer_id" id="answer_id" class="form-textarea" rows="4" required></textarea></div>
                <div class="form-group"><label>Jawaban (EN)</label><textarea name="answer_en" id="answer_en" class="form-textarea" rows="4"></textarea></div>
                <div class="form-group"><label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;"><input type="checkbox" name="is_active" id="is_active" checked><span style="font-size: 0.9rem; font-weight: 600;">Tampilkan di Halaman Publik</span></label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Batal</button><button type="submit" class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>Simpan</button></div>
        </form>
    </div>
</div>

<script>
const faqData = <?= json_encode($faq_list) ?>;
function resetForm() { document.getElementById('form-action').value = 'add'; document.getElementById('form-id').value = ''; document.getElementById('modal-title').textContent = 'Tambah FAQ'; document.getElementById('faq-form').reset(); document.getElementById('is_active').checked = true; }
function editFaqById(id) {
    const data = faqData.find(f => parseInt(f.id) === parseInt(id)); if (!data) return;
    document.getElementById('form-action').value = 'edit'; document.getElementById('form-id').value = data.id; document.getElementById('modal-title').textContent = 'Edit FAQ';
    document.getElementById('question_id').value = data.question_id || ''; document.getElementById('question_en').value = data.question_en || '';
    document.getElementById('answer_id').value = data.answer_id || ''; document.getElementById('answer_en').value = data.answer_en || '';
    document.getElementById('category').value = data.category || ''; document.getElementById('sort_order').value = data.sort_order || 0;
    document.getElementById('is_active').checked = (data.is_active == 1);
    const modal = document.getElementById('faq-modal'); if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function deleteFaq(id, q) { if (typeof confirmDelete === 'function') { confirmDelete(`Hapus FAQ <strong>${q}...</strong>?`, () => { document.getElementById('delete-form-' + id).submit(); }); } else { if (confirm(`Hapus FAQ?`)) { document.getElementById('delete-form-' + id).submit(); } } }
<?php if (isset($_SESSION['toast'])): ?> document.addEventListener('DOMContentLoaded', () => { if (typeof showToast === 'function') showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>'); }); <?php unset($_SESSION['toast']); endif; ?>
<?php if ($edit_data): ?> document.addEventListener('DOMContentLoaded', () => { editFaqById(<?= (int)$edit_data['id'] ?>); }); <?php endif; ?>
</script>
<?php require_once 'includes/footer.php'; ?>