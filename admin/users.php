<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Manajemen Pengguna";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Manajemen Pengguna']
];

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
            $password = $_POST['password'] ?? '';
            if (strlen($password) < 6) throw new Exception("Password minimal 6 karakter.");
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                sanitize($_POST['username']),
                sanitize($_POST['email']),
                password_hash($password, PASSWORD_DEFAULT),
                sanitize($_POST['full_name']),
                sanitize($_POST['role']),
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'CREATE', 'users', ?, ?, ?)");
            $log->execute([$user_id, $pdo->lastInsertId(), "Menambah user: " . sanitize($_POST['username']), $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Pengguna berhasil ditambahkan'];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $new_password = $_POST['new_password'] ?? '';
            
            $sql = "UPDATE users SET username=?, email=?, full_name=?, role=?, is_active=?";
            $params = [
                sanitize($_POST['username']),
                sanitize($_POST['email']),
                sanitize($_POST['full_name']),
                sanitize($_POST['role']),
                isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Update password hanya jika diisi
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) throw new Exception("Password baru minimal 6 karakter.");
                if ($new_password !== ($_POST['confirm_password'] ?? '')) throw new Exception("Konfirmasi password tidak cocok.");
                
                $sql .= ", password=?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'users', ?, ?, ?)");
            $log->execute([$user_id, $id, "Mengupdate user ID: {$id}", $ip_address]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Data pengguna berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Cegah admin menghapus akun sendiri
            if ($id == $user_id) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Ditolak', 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'];
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'DELETE', 'users', ?, ?, ?)");
                $log->execute([$user_id, $id, "Menghapus user ID: {$id}", $ip_address]);
                
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Pengguna berhasil dihapus'];
            }
        } elseif ($action === 'toggle') {
            $id = (int)$_POST['id'];
            if ($id == $user_id) {
                $_SESSION['toast'] = ['type' => 'error', 'title' => 'Ditolak', 'message' => 'Tidak dapat menonaktifkan akun sendiri.'];
            } else {
                $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
                $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status pengguna berhasil diubah'];
            }
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Database Error', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    
    // ======================================================================
    // 3. REDIRECT SETELAH POST (WAJIB SEBELUM HTML DICETAK)
    // ======================================================================
    header('Location: users.php');
    exit;
}

// ======================================================================
// 4. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 5. FETCH DATA
// ======================================================================
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users_list = $stmt->fetchAll();

$total = count($users_list);
$active = count(array_filter($users_list, fn($u) => $u['is_active'] == 1));
$inactive = $total - $active;
$admins = count(array_filter($users_list, fn($u) => in_array($u['role'], ['admin', 'superadmin'])));

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= $total ?></h3>
            <p>Total Pengguna</p>
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
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $admins ?>"><?= $admins ?></h3>
            <p>Admin & Superadmin</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Daftar Pengguna Sistem
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola akses admin, operator, dan superadmin</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari pengguna..." data-table-search="users-table">
            </div>
            <button class="btn btn-primary btn-sm" data-modal-open="users-modal" onclick="resetForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pengguna
            </button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="users-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th data-sort="nama">Pengguna</th>
                    <th data-sort="username">Username</th>
                    <th data-sort="role">Role</th>
                    <th data-sort="login">Terakhir Login</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users_list) > 0): ?>
                    <?php foreach($users_list as $index => $u): 
                        $is_self = ($u['id'] == ($_SESSION['user_id'] ?? 0));
                        $role_badge = match($u['role']) {
                            'superadmin' => 'badge-danger',
                            'admin' => 'badge-warning',
                            default => 'badge-info'
                        };
                    ?>
                    <tr>
                        <td><span style="font-weight: 600; color: var(--text-muted);"><?= $index + 1 ?></span></td>
                        <td data-column="nama">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($u['full_name'] ?: $u['username'], 0, 1)) ?>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($u['full_name'] ?: $u['username']) ?> <?= $is_self ? '<span style="font-size: 0.7rem; color: var(--primary);">(Anda)</span>' : '' ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($u['email'] ?: '-') ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-column="username">
                            <code style="background: var(--bg); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?= sanitize($u['username']) ?></code>
                        </td>
                        <td data-column="role">
                            <span class="badge <?= $role_badge ?>" style="font-size: 0.7rem; text-transform: capitalize;"><?= sanitize($u['role']) ?></span>
                        </td>
                        <td data-column="login">
                            <div style="font-size: 0.85rem;"><?= $u['last_login'] ? date('d M Y', strtotime($u['last_login'])) : '-' ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);"><?= $u['last_login'] ? date('H:i', strtotime($u['last_login'])) : 'Belum pernah' ?></div>
                        </td>
                        <td>
                            <?php if ($is_self): ?>
                                <span class="badge badge-success" style="cursor: default;">Aktif</span>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                        <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end;">
                                <button class="action-btn" onclick="editUserById(<?= $u['id'] ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <?php if (!$is_self): ?>
                                <button class="action-btn danger" onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes($u['username']) ?>')" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $u['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Pengguna</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="users-modal">
    <div class="modal" style="max-width: 600px;">
        <form method="POST" id="users-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="modal-header">
                <h3 id="modal-title">Tambah Pengguna</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" name="username" id="username" class="form-input" required minlength="4" placeholder="username_unik">
                    </div>
                    <div class="form-group">
                        <label>Role <span class="required">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" id="full_name" class="form-input" placeholder="Nama lengkap pengguna">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-input" placeholder="email@domain.com">
                </div>

                <!-- Password Fields -->
                <div id="password-section">
                    <div class="form-group">
                        <label id="password-label">Password <span class="required">*</span></label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="Minimal 6 karakter">
                        <div class="form-hint" id="password-hint">Wajib diisi untuk pengguna baru.</div>
                    </div>
                    <div class="form-group" id="confirm-password-group" style="display: none;">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Ulangi password baru">
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span style="font-size: 0.9rem; font-weight: 600;">Akun Aktif</span>
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
const usersData = <?= json_encode($users_list) ?>;

function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Pengguna';
    document.getElementById('users-form').reset();
    document.getElementById('is_active').checked = true;
    
    // Reset password logic untuk mode Add
    document.getElementById('password').required = true;
    document.getElementById('password-label').innerHTML = 'Password <span class="required">*</span>';
    document.getElementById('password-hint').textContent = 'Wajib diisi untuk pengguna baru.';
    document.getElementById('confirm-password-group').style.display = 'none';
    document.getElementById('password').name = 'password';
}

function editUserById(id) {
    const data = usersData.find(u => parseInt(u.id) === parseInt(id));
    if (!data) return;

    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit Pengguna';
    
    document.getElementById('username').value = data.username || '';
    document.getElementById('email').value = data.email || '';
    document.getElementById('full_name').value = data.full_name || '';
    document.getElementById('role').value = data.role || 'admin';
    document.getElementById('is_active').checked = (data.is_active == 1);
    
    // Ubah logika password untuk mode Edit
    document.getElementById('password').required = false;
    document.getElementById('password-label').innerHTML = 'Password Baru <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(Kosongkan jika tidak ingin mengubah)</span>';
    document.getElementById('password-hint').textContent = 'Biarkan kosong untuk mempertahankan password lama.';
    document.getElementById('confirm-password-group').style.display = 'block';
    document.getElementById('password').name = 'new_password';
    document.getElementById('password').value = '';
    document.getElementById('confirm_password').value = '';
    
    const modal = document.getElementById('users-modal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function deleteUser(id, username) {
    if (typeof confirmDelete === 'function') {
        confirmDelete(`Hapus pengguna <strong>${username}</strong>? Tindakan ini akan menghapus akses login mereka secara permanen.`, () => {
            document.getElementById('delete-form-' + id).submit();
        });
    } else {
        if (confirm(`Hapus pengguna ${username}?`)) {
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
    editUserById(<?= (int)$edit_data['id'] ?>);
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>