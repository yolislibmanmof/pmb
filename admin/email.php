<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Template Email";
$breadcrumbs = [['label' => 'Dashboard', 'url' => 'index.php'], ['label' => 'Template Email']];

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
                $stmt = $pdo->prepare("INSERT INTO email_templates (template_key, subject_id, subject_en, body_id, body_en, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([sanitize($_POST['template_key']), sanitize($_POST['subject_id']), sanitize($_POST['subject_en']), sanitize($_POST['body_id']), sanitize($_POST['body_en']), $is_active]);
                $rec_id = $pdo->lastInsertId(); $log_desc = "Menambah template: " . sanitize($_POST['template_key']);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE email_templates SET subject_id=?, subject_en=?, body_id=?, body_en=?, is_active=? WHERE id=?");
                $stmt->execute([sanitize($_POST['subject_id']), sanitize($_POST['subject_en']), sanitize($_POST['body_id']), sanitize($_POST['body_en']), $is_active, $id]);
                $rec_id = $id; $log_desc = "Mengupdate template ID: {$id}";
            }
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, 'email_templates', ?, ?, ?)")->execute([$user_id, strtoupper($action), $rec_id, $log_desc, $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Template email berhasil ' . ($action === 'add' ? 'ditambahkan' : 'diperbarui')];
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id']; $pdo->prepare("DELETE FROM email_templates WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'email_templates', ?, ?, ?)")->execute([$user_id, $id, "Menghapus template ID: {$id}", $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Template email berhasil dihapus'];
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id']; $pdo->prepare("UPDATE email_templates SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status template berhasil diubah'];
        }
    } catch (Exception $e) { $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]; }
    header('Location: email.php'); exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA & RENDER HTML
// ======================================================================
$stmt = $pdo->query("SELECT * FROM email_templates ORDER BY template_key ASC");
$email_list = $stmt->fetchAll();
$total = count($email_list); $aktif = count(array_filter($email_list, fn($e) => $e['is_active'] == 1));

$edit_data = null;
if (isset($_GET['edit'])) { $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE id = ?"); $stmt->execute([(int)$_GET['edit']]); $edit_data = $stmt->fetch(); }
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div><div class="stat-info"><h3 data-counter="<?= $total ?>"><?= $total ?></h3><p>Total Template</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="stat-info"><h3 data-counter="<?= $aktif ?>"><?= $aktif ?></h3><p>Template Aktif</p></div></div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div><h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Daftar Template Email Otomatis</h3><p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola subjek dan isi email sistem (Gunakan {nama}, {kode}, dll sebagai variabel)</p></div>
        <div class="table-controls">
            <div class="search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Cari template..." data-table-search="email-table"></div>
            <button class="btn btn-primary btn-sm" data-modal-open="email-modal" onclick="resetForm()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Template</button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="admin-table" id="email-table" data-sortable>
            <thead><tr><th style="width: 50px;">#</th><th data-sort="key">Template Key</th><th data-sort="subjek">Subjek (ID)</th><th style="width: 100px;">Status</th><th style="width: 120px; text-align: right;">Aksi</th></tr></thead>
            <tbody>
                <?php if (count($email_list) > 0): foreach($email_list as $i => $e): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--text-muted);"><?= $i + 1 ?></span></td>
                    <td data-column="key"><code style="background: var(--bg); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; color: var(--primary);"><?= sanitize($e['template_key']) ?></code></td>
                    <td data-column="subjek"><div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($e['subject_id']) ?></div></td>
                    <td><form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $e['id'] ?>"><button type="submit" class="badge <?= $e['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;"><?= $e['is_active'] ? 'Aktif' : 'Nonaktif' ?></button></form></td>
                    <td><div class="table-actions" style="justify-content: flex-end;"><button class="action-btn" onclick="editEmailById(<?= $e['id'] ?>)" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn danger" onclick="deleteEmail(<?= $e['id'] ?>, '<?= addslashes($e['template_key']) ?>')" title="Hapus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button><form id="delete-form-<?= $e['id'] ?>" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $e['id'] ?>"></form></div></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="5" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);"><p style="font-weight: 600;">Belum Ada Template</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="email-modal">
    <div class="modal" style="max-width: 800px;">
        <form method="POST" id="email-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            <div class="modal-header"><h3 id="modal-title">Tambah Template Email</h3><button type="button" class="modal-close" data-modal-close><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-group"><label>Template Key (Unik) <span class="required">*</span></label><input type="text" name="template_key" id="template_key" class="form-input" required placeholder="registration_success" <?= $edit_data ? 'readonly' : '' ?>></div>
                <div style="display: flex; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem;">
                    <button type="button" class="tab-link-email active" onclick="switchEmailTab(this, 'etab-id')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; cursor: pointer;">Bahasa Indonesia</button>
                    <button type="button" class="tab-link-email" onclick="switchEmailTab(this, 'etab-en')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer;">English</button>
                </div>
                <div id="etab-id" class="etab-pane">
                    <div class="form-group"><label>Subjek Email (ID) <span class="required">*</span></label><input type="text" name="subject_id" id="subject_id" class="form-input" required></div>
                    <div class="form-group"><label>Isi Email (ID) <span class="required">*</span></label><textarea name="body_id" id="body_id" class="form-textarea" rows="8" required></textarea><div class="form-hint">Variabel: {nama}, {kode}, {prodi}, {deadline}</div></div>
                </div>
                <div id="etab-en" class="etab-pane" style="display: none;">
                    <div class="form-group"><label>Subject (EN)</label><input type="text" name="subject_en" id="subject_en" class="form-input"></div>
                    <div class="form-group"><label>Body (EN)</label><textarea name="body_en" id="body_en" class="form-textarea" rows="8"></textarea></div>
                </div>
                <div class="form-group"><label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;"><input type="checkbox" name="is_active" id="is_active" checked><span style="font-size: 0.9rem; font-weight: 600;">Template Aktif</span></label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Batal</button><button type="submit" class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>Simpan</button></div>
        </form>
    </div>
</div>

<script>
const emailData = <?= json_encode($email_list) ?>;
function switchEmailTab(btn, tabId) { document.querySelectorAll('.tab-link-email').forEach(b => { b.style.borderBottomColor = 'transparent'; b.style.color = 'var(--text-muted)'; }); btn.style.borderBottomColor = 'var(--primary)'; btn.style.color = 'var(--primary)'; document.querySelectorAll('.etab-pane').forEach(p => p.style.display = 'none'); document.getElementById(tabId).style.display = 'block'; }
function resetForm() { document.getElementById('form-action').value = 'add'; document.getElementById('form-id').value = ''; document.getElementById('modal-title').textContent = 'Tambah Template Email'; document.getElementById('email-form').reset(); document.getElementById('is_active').checked = true; document.getElementById('template_key').readOnly = false; const firstTab = document.querySelector('.tab-link-email'); if(firstTab) switchEmailTab(firstTab, 'etab-id'); }
function editEmailById(id) {
    const data = emailData.find(e => parseInt(e.id) === parseInt(id)); if (!data) return;
    document.getElementById('form-action').value = 'edit'; document.getElementById('form-id').value = data.id; document.getElementById('modal-title').textContent = 'Edit Template Email';
    document.getElementById('template_key').value = data.template_key || ''; document.getElementById('template_key').readOnly = true;
    document.getElementById('subject_id').value = data.subject_id || ''; document.getElementById('subject_en').value = data.subject_en || '';
    document.getElementById('body_id').value = data.body_id || ''; document.getElementById('body_en').value = data.body_en || '';
    document.getElementById('is_active').checked = (data.is_active == 1);
    const firstTab = document.querySelector('.tab-link-email'); if(firstTab) switchEmailTab(firstTab, 'etab-id');
    const modal = document.getElementById('email-modal'); if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function deleteEmail(id, key) { if (typeof confirmDelete === 'function') { confirmDelete(`Hapus template <strong>${key}</strong>?`, () => { document.getElementById('delete-form-' + id).submit(); }); } else { if (confirm(`Hapus template?`)) { document.getElementById('delete-form-' + id).submit(); } } }
<?php if (isset($_SESSION['toast'])): ?> document.addEventListener('DOMContentLoaded', () => { if (typeof showToast === 'function') showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>'); }); <?php unset($_SESSION['toast']); endif; ?>
<?php if ($edit_data): ?> document.addEventListener('DOMContentLoaded', () => { editEmailById(<?= (int)$edit_data['id'] ?>); }); <?php endif; ?>
</script>
<?php require_once 'includes/footer.php'; ?>