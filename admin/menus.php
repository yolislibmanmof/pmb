<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Kelola Menu";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Kelola Menu']
];

// ======================================================================
// 1.5 HELPER FUNCTION (Tambahan untuk admin)
// ======================================================================
if (!function_exists('renderMenuIcon')) {
    function renderMenuIcon($icon_name, $url = '', $label = '') {
        // Jika icon kosong/null, coba tebak dari URL atau label
        if (empty($icon_name)) {
            $url = strtolower($url);
            $label = strtolower($label);
            
            if (str_contains($url, 'index') || str_contains($label, 'beranda') || str_contains($label, 'home')) {
                $icon_name = 'home';
            } elseif (str_contains($url, 'prodi') || str_contains($label, 'program')) {
                $icon_name = 'book';
            } elseif (str_contains($url, 'alur') || str_contains($label, 'guide') || str_contains($label, 'flow')) {
                $icon_name = 'guide';
            } elseif (str_contains($url, 'kontak') || str_contains($label, 'contact')) {
                $icon_name = 'phone';
            } elseif (str_contains($url, 'daftar') || str_contains($label, 'register')) {
                $icon_name = 'user';
            } else {
                $icon_name = 'default';
            }
        }
        
        $icons = [
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
            'guide' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/>',
            'news' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>',
            'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
            'user' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
            'default' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>'
        ];
        
        $icon_key = strtolower(trim($icon_name));
        $path = $icons[$icon_key] ?? $icons['default'];
        
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}

// ==========================================
// HELPER: Build options parent bertingkat (dengan indentasi)
// ==========================================
function build_parent_options($menus, $parent_id = 0, $level = 0) {
    $out = '';
    foreach ($menus as $m) {
        if ((int)$m['parent_id'] !== (int)$parent_id) continue;
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $arrow  = $level > 0 ? '↳ ' : '';
        $out .= '<option value="' . (int)$m['id'] . '">' . $indent . $arrow . sanitize($m['label_id']) . ' [' . sanitize($m['menu_position']) . ']</option>';
        $out .= build_parent_options($menus, $m['id'], $level + 1);
    }
    return $out;
}

// ==========================================
// HELPER: Urutkan menu secara hierarchical (untuk tabel nested)
// ==========================================
function flatten_menu_tree($menus, $parent_id, $level, &$result) {
    foreach ($menus as $m) {
        if ((int)$m['parent_id'] === (int)$parent_id) {
            $m['_level'] = $level;
            $result[] = $m;
            flatten_menu_tree($menus, $m['id'], $level + 1, $result);
        }
    }
}

// ==========================================
// HELPER: Ambil semua ID turunan (cegah circular reference)
// ==========================================
function get_descendant_ids($menus, $id) {
    $ids = [(int)$id];
    foreach ($menus as $m) {
        if ((int)$m['parent_id'] === (int)$id) {
            $ids = array_merge($ids, get_descendant_ids($menus, $m['id']));
        }
    }
    return $ids;
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
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO menus (parent_id, label_id, label_en, url, icon, target, menu_position, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                (int)($_POST['parent_id'] ?? 0),
                sanitize($_POST['label_id']),
                sanitize($_POST['label_en']),
                sanitize($_POST['url']),
                sanitize($_POST['icon']),
                sanitize($_POST['target']),
                sanitize($_POST['menu_position']),
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'CREATE', 'menus', ?, ?, ?)");
            $log->execute([$user_id, $pdo->lastInsertId(), "Menambah menu: " . sanitize($_POST['label_id']), $ip]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Menu berhasil ditambahkan'];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            // Cegah circular: parent tidak boleh diri sendiri / turunannya
            $new_parent = (int)($_POST['parent_id'] ?? 0);
            if ($new_parent !== 0) {
                $all = $pdo->query("SELECT id, parent_id FROM menus")->fetchAll();
                if ($new_parent === $id || in_array($new_parent, get_descendant_ids($all, $id))) {
                    $_SESSION['toast'] = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Menu tidak boleh menjadi child dari dirinya sendiri atau turunannya.'];
                    header('Location: menus.php');
                    exit;
                }
            }
            $stmt = $pdo->prepare("UPDATE menus SET parent_id=?, label_id=?, label_en=?, url=?, icon=?, target=?, menu_position=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([
                (int)($_POST['parent_id'] ?? 0),
                sanitize($_POST['label_id']),
                sanitize($_POST['label_en']),
                sanitize($_POST['url']),
                sanitize($_POST['icon']),
                sanitize($_POST['target']),
                sanitize($_POST['menu_position']),
                (int)$_POST['sort_order'],
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'menus', ?, ?, ?)");
            $log->execute([$user_id, $id, "Mengupdate menu ID: {$id}", $ip]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Menu berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Cek apakah ada child menu (sub-menu)
            $check = $pdo->prepare("SELECT COUNT(*) FROM menus WHERE parent_id = ?");
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Tidak dapat menghapus menu yang memiliki sub-menu. Hapus sub-menu terlebih dahulu.'];
            } else {
                $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
                $stmt->execute([$id]);
                
                $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'menus', ?, ?, ?)");
                $log->execute([$user_id, $id, "Menghapus menu ID: {$id}", $ip]);
                
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Menu berhasil dihapus'];
            }
            
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
                $pdo->prepare("UPDATE menus SET sort_order = sort_order + 1 WHERE sort_order = ? AND id != ?")->execute([$current - 1, $id]);
                $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?")->execute([$current - 1, $id]);
            } else {
                $pdo->prepare("UPDATE menus SET sort_order = sort_order - 1 WHERE sort_order = ? AND id != ?")->execute([$current + 1, $id]);
                $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?")->execute([$current + 1, $id]);
            }
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Urutan berhasil diubah'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    header('Location: menus.php');
    exit;
}

// ======================================================================
// 3. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 4. FETCH DATA
// ======================================================================
// Ambil semua menu untuk ditampilkan di tabel
$stmt = $pdo->query("SELECT * FROM menus ORDER BY menu_position ASC, sort_order ASC");
$menus = $stmt->fetchAll();

// Build urutan hierarchical (nested) untuk tabel, per posisi
$ordered_menus = [];
foreach (['header', 'footer'] as $pos) {
    $subset = array_values(array_filter($menus, fn($m) => $m['menu_position'] === $pos));
    flatten_menu_tree($subset, 0, 0, $ordered_menus);
}

// Opsi Icon
$icon_options = [
    '' => '-- Pilih Icon (Opsional) --',
    'home' => 'Home / Beranda',
    'book' => 'Book / Program Studi',
    'guide' => 'Guide / Alur',
    'news' => 'News / Berita',
    'help' => 'Help / FAQ',
    'phone' => 'Phone / Kontak',
    'user' => 'User / Daftar',
    'search' => 'Search / Cek Status',
    'default' => 'Default (Titik Tiga)'
];
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
                Kelola Menu Navigasi
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Atur menu Header, Footer, dan Dropdown tanpa sentuh database</p>
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
                    <th>Icon</th>
                    <th>Posisi</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($menus) > 0): ?>
<?php foreach($ordered_menus as $index => $m): 
    $level = (int)($m['_level'] ?? 0);
    $is_child = $level > 0;
?>
<tr style="<?= $is_child ? 'background: rgba(30, 64, 175, ' . (0.03 * $level) . ');' : '' ?>">
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
                          <td style="font-weight: 600; padding-left: <?= 1.5 + ($level * 1.75) ?>rem;">
    <?= $is_child ? '↳ ' : '' ?><?= sanitize($m['label_id']) ?>
    <?php if ($is_child): ?>
        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">Level <?= $level + 1 ?> (sub-menu)</div>
    <?php endif; ?>
</td>
                        <td><?= sanitize($m['label_en']) ?></td>
                        <td><code style="background: var(--bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?= sanitize($m['url']) ?></code></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; background: var(--bg); border-radius: 6px; font-size: 0.8rem;">
                                <?= renderMenuIcon($m['icon'], $m['url'], $m['label_id']) ?>
                                <?= sanitize($m['icon'] ?: 'default') ?>
                            </span>
                        </td>
                        <td><span class="badge badge-info"><?= sanitize($m['menu_position']) ?></span></td>
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
                                <button class="action-btn danger" onclick="deleteMenu(<?= $m['id'] ?>, '<?= addslashes($m['label_id']) ?>')" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $m['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">Belum ada menu. Klik tombol "Tambah Menu" untuk memulai.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="menu-modal">
    <div class="modal" style="max-width: 650px;">
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
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Label (Indonesia) <span class="required">*</span></label>
                        <input type="text" name="label_id" id="label_id" class="form-input" required placeholder="Contoh: Cek Status">
                    </div>
                    <div class="form-group">
                        <label>Label (English) <span class="required">*</span></label>
                        <input type="text" name="label_en" id="label_en" class="form-input" required placeholder="Example: Check Status">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>URL / Link <span class="required">*</span></label>
                    <input type="text" name="url" id="url" class="form-input" required placeholder="cek-status.php, prodi.php, atau #">
                    <div class="form-hint">Gunakan <code>#</code> jika menu ini hanya sebagai induk dropdown.</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Parent Menu (Untuk Dropdown)</label>
<select name="parent_id" id="parent_id" class="form-select">
    <option value="0">-- Menu Utama (Tidak Ada Parent) --</option>
    <?= build_parent_options($menus) ?>
</select>
                        <div class="form-hint">Pilih menu lain untuk menjadikannya sub-menu.</div>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon" id="icon" class="form-select">
                            <?php foreach($icon_options as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Target</label>
                        <select name="target" id="target" class="form-select">
                            <option value="_self">_self (Tab Sama)</option>
                            <option value="_blank">_blank (Tab Baru)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Posisi Tampil</label>
                        <select name="menu_position" id="menu_position" class="form-select">
                            <option value="header">Header (Navigasi Atas)</option>
                            <option value="footer">Footer (Bagian Bawah)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-input" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span>Aktif (tampil di website)</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Menu</button>
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
        document.getElementById('sort_order').value = '0';
    }
    
    function editMenu(data) {
        document.getElementById('form-action').value = 'edit';
        document.getElementById('form-id').value = data.id;
        document.getElementById('modal-title').textContent = 'Edit Menu';
        
        document.getElementById('label_id').value = data.label_id || '';
        document.getElementById('label_en').value = data.label_en || '';
        document.getElementById('url').value = data.url || '';
        document.getElementById('parent_id').value = data.parent_id || '0';
        document.getElementById('icon').value = data.icon || '';
        document.getElementById('target').value = data.target || '_self';
        document.getElementById('menu_position').value = data.menu_position || 'header';
        document.getElementById('sort_order').value = data.sort_order || '0';
        document.getElementById('is_active').checked = data.is_active == 1;
        
        openModal('menu-modal');
    }
    
    function deleteMenu(id, label) {
        if (confirm(`Hapus menu "${label}"? Jika ini menu induk, pastikan sub-menu sudah dihapus dulu.`)) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
    
    <?php if (isset($_SESSION['toast'])): ?>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof showToast === 'function') {
            showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
        }
    });
    <?php unset($_SESSION['toast']); endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>