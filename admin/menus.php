<?php
$page_title = "Kelola Menu";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Menu']
];
require_once 'includes/header.php';

// Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO menus (label_id, label_en, url, target, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                sanitize($_POST['label_id']),
                sanitize($_POST['label_en']),
                sanitize($_POST['url']),
                sanitize($_POST['target']),
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0
            ]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Menu berhasil ditambahkan'];
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE menus SET label_id=?, label_en=?, url=?, target=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([
                sanitize($_POST['label_id']),
                sanitize($_POST['label_en']),
                sanitize($_POST['url']),
                sanitize($_POST['target']),
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Menu berhasil diperbarui'];
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Menu berhasil dihapus'];
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE menus SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status berhasil diubah'];
        } elseif ($action === 'move') {
            $id = (int)$_POST['id'];
            $direction = $_POST['direction'];
            
            $stmt = $pdo->prepare("SELECT sort_order FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();
            
            if ($direction === 'up') {
                $stmt = $pdo->prepare("UPDATE menus SET sort_order = sort_order + 1 WHERE sort_order = ? AND id != ?");
                $stmt->execute([$current - 1, $id]);
                $stmt = $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?");
                $stmt->execute([$current - 1, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE menus SET sort_order = sort_order - 1 WHERE sort_order = ? AND id != ?");
                $stmt->execute([$current + 1, $id]);
                $stmt = $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?");
                $stmt->execute([$current + 1, $id]);
            }
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Urutan berhasil diubah'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    header('Location: menus.php');
    exit;
}

// Fetch Data
$stmt = $pdo->query("SELECT * FROM menus ORDER BY sort_order ASC");
$menus = $stmt->fetchAll();
?>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Menu Navigasi Website
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola menu yang tampil di navbar website publik</p>
        </div>
        <button class="btn btn-primary btn-sm" data-modal-open="menu-modal" onclick="resetForm()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Menu
        </button>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="menus-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Urutan</th>
                    <th>Label (ID)</th>
                    <th>Label (EN)</th>
                    <th>URL</th>
                    <th>Target</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($menus) > 0): ?>
                    <?php foreach($menus as $index => $m): ?>
                    <tr>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="move">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="action-btn btn-sm" style="width: 24px; height: 24px; padding: 0;" title="Naik" <?= $index === 0 ? 'disabled' : '' ?>>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="move">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="action-btn btn-sm" style="width: 24px; height: 24px; padding: 0;" title="Turun" <?= $index === count($menus) - 1 ? 'disabled' : '' ?>>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td style="font-weight: 600;"><?= sanitize($m['label_id']) ?></td>
                        <td><?= sanitize($m['label_en']) ?></td>
                        <td><code style="background: var(--bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?= sanitize($m['url']) ?></code></td>
                        <td><span class="badge badge-info"><?= sanitize($m['target']) ?></span></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="badge <?= $m['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                    <?= $m['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end;">
                                <button class="action-btn" onclick="editMenu(<?= htmlspecialchars(json_encode($m)) ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Hapus menu ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="action-btn danger" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">Belum ada menu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="menu-modal">
    <div class="modal">
        <form method="POST" id="menu-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Menu</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Label (Indonesia) <span class="required">*</span></label>
                        <input type="text" name="label_id" id="label_id" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Label (English) <span class="required">*</span></label>
                        <input type="text" name="label_en" id="label_en" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>URL / Link <span class="required">*</span></label>
                    <input type="text" name="url" id="url" class="form-input" required placeholder="index.php, prodi.php, atau https://...">
                    <div class="form-hint">Contoh: index.php, prodi.php, alur.php, atau URL lengkap</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Target <span class="required">*</span></label>
                        <select name="target" id="target" class="form-select" required>
                            <option value="_self">_self (Same Tab)</option>
                            <option value="_blank">_blank (New Tab)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Urutan <span class="required">*</span></label>
                        <input type="number" name="sort_order" id="sort_order" class="form-input" min="1" value="<?= count($menus) + 1 ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span>Aktif (tampil di navbar)</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function resetForm() {
        document.getElementById('form-action').value = 'add';
        document.getElementById('form-id').value = '';
        document.getElementById('modal-title').textContent = 'Tambah Menu';
        document.getElementById('menu-form').reset();
        document.getElementById('is_active').checked = true;
        document.getElementById('sort_order').value = <?= count($menus) + 1 ?>;
    }
    
    function editMenu(data) {
        document.getElementById('form-action').value = 'edit';
        document.getElementById('form-id').value = data.id;
        document.getElementById('modal-title').textContent = 'Edit Menu';
        
        document.getElementById('label_id').value = data.label_id;
        document.getElementById('label_en').value = data.label_en;
        document.getElementById('url').value = data.url;
        document.getElementById('target').value = data.target;
        document.getElementById('sort_order').value = data.sort_order;
        document.getElementById('is_active').checked = data.is_active == 1;
        
        openModal('menu-modal');
    }
    
    <?php if (isset($_SESSION['toast'])): ?>
    document.addEventListener('DOMContentLoaded', () => {
        showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
    });
    <?php unset($_SESSION['toast']); endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>