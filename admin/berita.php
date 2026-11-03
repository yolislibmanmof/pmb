<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Berita";
$breadcrumbs = [['label' => 'Dashboard', 'url' => 'index.php'], ['label' => 'Kelola Berita']];

// ======================================================================
// 2. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF Error');
    
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title_id'])));
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            $published_at = $is_published && empty($_POST['published_at']) ? date('Y-m-d H:i:s') : ($_POST['published_at'] ?: null);
            
            // Handle Image Upload
            $image_url = sanitize($_POST['thumbnail_url']);
            if (!empty($_FILES['thumbnail_file']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/berita/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['thumbnail_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && $_FILES['thumbnail_file']['size'] <= 5 * 1024 * 1024) {
                    $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $uploadDir . $newName);
                    $image_url = '../uploads/berita/' . $newName;
                }
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO news (title_id, title_en, slug, content_id, content_en, thumbnail_url, category, author_id, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([sanitize($_POST['title_id']), sanitize($_POST['title_en']), $slug, sanitize($_POST['content_id']), sanitize($_POST['content_en']), $image_url, sanitize($_POST['category']), $user_id, $is_published, $published_at]);
                $rec_id = $pdo->lastInsertId();
                $log_desc = "Menambah berita: " . sanitize($_POST['title_id']);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE news SET title_id=?, title_en=?, slug=?, content_id=?, content_en=?, thumbnail_url=?, category=?, is_published=?, published_at=? WHERE id=?");
                $stmt->execute([sanitize($_POST['title_id']), sanitize($_POST['title_en']), $slug, sanitize($_POST['content_id']), sanitize($_POST['content_en']), $image_url, sanitize($_POST['category']), $is_published, $published_at, $id]);
                $rec_id = $id;
                $log_desc = "Mengupdate berita ID: {$id}";
            }
            
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, 'news', ?, ?, ?)")->execute([$user_id, strtoupper($action), $rec_id, $log_desc, $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Berita berhasil ' . ($action === 'add' ? 'ditambahkan' : 'diperbarui')];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'news', ?, ?, ?)")->execute([$user_id, $id, "Menghapus berita ID: {$id}", $ip]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Berita berhasil dihapus'];
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $pdo->prepare("UPDATE news SET is_published = NOT is_published WHERE id = ?")->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status publikasi berhasil diubah'];
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()];
    }
    header('Location: berita.php'); 
    exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA & RENDER HTML
// ======================================================================
$stmt = $pdo->query("SELECT n.*, u.full_name as author_name FROM news n LEFT JOIN users u ON n.author_id = u.id ORDER BY n.created_at DESC");
$news_list = $stmt->fetchAll();
$total = count($news_list);
$published = count(array_filter($news_list, fn($n) => $n['is_published'] == 1));
$draft = $total - $published;

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg></div><div class="stat-info"><h3 data-counter="<?= $total ?>"><?= $total ?></h3><p>Total Berita</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="stat-info"><h3 data-counter="<?= $published ?>"><?= $published ?></h3><p>Dipublikasi</p></div></div>
    <div class="stat-card"><div class="stat-icon orange"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></div><div class="stat-info"><h3 data-counter="<?= $draft ?>"><?= $draft ?></h3><p>Draft</p></div></div>
</div>

<!-- Table -->
<div class="data-card">
    <div class="data-card-header">
        <div><h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>Daftar Berita & Pengumuman</h3><p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola konten artikel untuk halaman publik</p></div>
        <div class="table-controls">
            <div class="search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Cari berita..." data-table-search="berita-table"></div>
            <button class="btn btn-primary btn-sm" data-modal-open="berita-modal" onclick="resetForm()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Berita</button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="admin-table" id="berita-table" data-sortable>
            <thead><tr><th style="width: 50px;">#</th><th data-sort="judul">Judul Berita</th><th data-sort="kategori">Kategori</th><th data-sort="penulis">Penulis</th><th data-sort="views">Views</th><th style="width: 100px;">Status</th><th style="width: 120px; text-align: right;">Aksi</th></tr></thead>
            <tbody>
                <?php if (count($news_list) > 0): foreach($news_list as $i => $n): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--text-muted);"><?= $i + 1 ?></span></td>
                    <td data-column="judul"><div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($n['title_id']) ?></div><div style="font-size: 0.7rem; color: var(--text-muted);">Slug: <?= sanitize($n['slug']) ?></div></td>
                    <td data-column="kategori"><span class="badge badge-info"><?= sanitize($n['category'] ?: 'Umum') ?></span></td>
                    <td data-column="penulis"><div style="font-size: 0.85rem;"><?= sanitize($n['author_name'] ?: 'Admin') ?></div></td>
                    <td data-column="views"><span style="font-weight: 600;"><?= number_format($n['views']) ?></span></td>
                    <td><form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $n['id'] ?>"><button type="submit" class="badge <?= $n['is_published'] ? 'badge-success' : 'badge-warning' ?>" style="cursor: pointer; border: none;"><?= $n['is_published'] ? 'Published' : 'Draft' ?></button></form></td>
                    <td><div class="table-actions" style="justify-content: flex-end;"><button class="action-btn" onclick="editBeritaById(<?= $n['id'] ?>)" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn danger" onclick="deleteBerita(<?= $n['id'] ?>, '<?= addslashes($n['title_id']) ?>')" title="Hapus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button><form id="delete-form-<?= $n['id'] ?>" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $n['id'] ?>"></form></div></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);"><p style="font-weight: 600;">Belum Ada Berita</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="berita-modal">
    <div class="modal" style="max-width: 800px;">
        <form method="POST" id="berita-form" data-validate enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            <div class="modal-header"><h3 id="modal-title">Tambah Berita</h3><button type="button" class="modal-close" data-modal-close><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: flex; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem;">
                    <button type="button" class="tab-link-berita active" onclick="switchBeritaTab(this, 'btab-id')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; cursor: pointer;">Bahasa Indonesia</button>
                    <button type="button" class="tab-link-berita" onclick="switchBeritaTab(this, 'btab-en')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer;">English</button>
                    <button type="button" class="tab-link-berita" onclick="switchBeritaTab(this, 'btab-meta')" style="padding: 0.75rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer;">Meta & Media</button>
                </div>
                
                <div id="btab-id" class="btab-pane">
                    <div class="form-group"><label>Judul (ID) <span class="required">*</span></label><input type="text" name="title_id" id="title_id" class="form-input" required oninput="generateSlug()"></div>
                    <div class="form-group"><label>Konten (ID) <span class="required">*</span></label><textarea name="content_id" id="content_id" class="form-textarea" rows="8" required></textarea></div>
                </div>
                <div id="btab-en" class="btab-pane" style="display: none;">
                    <div class="form-group"><label>Title (EN)</label><input type="text" name="title_en" id="title_en" class="form-input"></div>
                    <div class="form-group"><label>Content (EN)</label><textarea name="content_en" id="content_en" class="form-textarea" rows="8"></textarea></div>
                </div>
                <div id="btab-meta" class="btab-pane" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group"><label>Kategori</label><input type="text" name="category" id="category" class="form-input" placeholder="pengumuman, berita, event"></div>
                        <div class="form-group"><label>Tanggal Publikasi</label><input type="datetime-local" name="published_at" id="published_at" class="form-input"></div>
                    </div>
                    <div class="form-group"><label>Thumbnail URL / Upload</label><input type="url" name="thumbnail_url" id="thumbnail_url" class="form-input" placeholder="https://..."><input type="file" name="thumbnail_file" accept="image/*" style="margin-top: 0.5rem;"></div>
                    <div class="form-group"><label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;"><input type="checkbox" name="is_published" id="is_published" checked><span style="font-size: 0.9rem; font-weight: 600;">Publikasikan Sekarang</span></label></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Batal</button><button type="submit" class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>Simpan</button></div>
        </form>
    </div>
</div>

<script>
const beritaData = <?= json_encode($news_list) ?>;
function switchBeritaTab(btn, tabId) {
    document.querySelectorAll('.tab-link-berita').forEach(b => { b.style.borderBottomColor = 'transparent'; b.style.color = 'var(--text-muted)'; });
    btn.style.borderBottomColor = 'var(--primary)'; btn.style.color = 'var(--primary)';
    document.querySelectorAll('.btab-pane').forEach(p => p.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
}
function generateSlug() {
    const title = document.getElementById('title_id').value;
    // Auto slug logic can be added here if needed
}
function resetForm() {
    document.getElementById('form-action').value = 'add'; document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Berita'; document.getElementById('berita-form').reset();
    document.getElementById('is_published').checked = true;
    const firstTab = document.querySelector('.tab-link-berita'); if(firstTab) switchBeritaTab(firstTab, 'btab-id');
}
function editBeritaById(id) {
    const data = beritaData.find(n => parseInt(n.id) === parseInt(id)); if (!data) return;
    document.getElementById('form-action').value = 'edit'; document.getElementById('form-id').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit Berita';
    document.getElementById('title_id').value = data.title_id || ''; document.getElementById('title_en').value = data.title_en || '';
    document.getElementById('content_id').value = data.content_id || ''; document.getElementById('content_en').value = data.content_en || '';
    document.getElementById('category').value = data.category || ''; document.getElementById('thumbnail_url').value = data.thumbnail_url || '';
    document.getElementById('published_at').value = data.published_at ? data.published_at.substring(0, 16) : '';
    document.getElementById('is_published').checked = (data.is_published == 1);
    const firstTab = document.querySelector('.tab-link-berita'); if(firstTab) switchBeritaTab(firstTab, 'btab-id');
    const modal = document.getElementById('berita-modal'); if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function deleteBerita(id, title) {
    if (typeof confirmDelete === 'function') { confirmDelete(`Hapus berita <strong>${title}</strong>?`, () => { document.getElementById('delete-form-' + id).submit(); }); } 
    else { if (confirm(`Hapus berita ${title}?`)) { document.getElementById('delete-form-' + id).submit(); } }
}
<?php if (isset($_SESSION['toast'])): ?> document.addEventListener('DOMContentLoaded', () => { if (typeof showToast === 'function') showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>'); }); <?php unset($_SESSION['toast']); endif; ?>
<?php if ($edit_data): ?> document.addEventListener('DOMContentLoaded', () => { editBeritaById(<?= (int)$edit_data['id'] ?>); }); <?php endif; ?>
</script>
<?php require_once 'includes/footer.php'; ?>