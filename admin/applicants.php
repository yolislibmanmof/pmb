<?php
$page_title = "Data Pendaftar";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Data Pendaftar']
];
require_once 'includes/header.php';

// ==========================================
// HANDLE POST ACTIONS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'];
    $id = (int)($_POST['id'] ?? 0);
    
    try {
        if ($action === 'update_status' && $id > 0) {
            $new_status = sanitize($_POST['status_daftar'] ?? 'Pending');
            $payment_status = sanitize($_POST['status_pembayaran'] ?? 'Belum Bayar');
            $catatan = sanitize($_POST['catatan_admin'] ?? '');
            
            $stmt = $pdo->prepare("UPDATE applicants SET status_daftar = ?, status_pembayaran = ?, catatan_admin = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $payment_status, $catatan, $_SESSION['user_id'] ?? null, $id]);
            
            // Log audit trail
            $log = $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, 'UPDATE', 'applicants', ?, ?, ?)");
            $log->execute([$_SESSION['user_id'] ?? null, $id, "Status diubah ke {$new_status}, Pembayaran: {$payment_status}", $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Status pendaftar berhasil diperbarui'];
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $pdo->prepare("DELETE FROM applicants WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Data pendaftar berhasil dihapus'];
        } elseif ($action === 'bulk_delete' && !empty($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM applicants WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Bulk Delete', 'message' => count($ids) . ' data berhasil dihapus'];
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()];
    }
    
    header('Location: applicants.php' . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
    exit;
}

// ==========================================
// FILTERS
// ==========================================
$where_clauses = [];
$params = [];

if (!empty($_GET['status']) && in_array($_GET['status'], ['Draft','Pending','Diverifikasi','Ditolak','Lulus','Tidak Lulus','Registrasi Ulang'])) {
    $where_clauses[] = "a.status_daftar = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['payment']) && in_array($_GET['payment'], ['Belum Bayar','Menunggu Verifikasi','Lunas','Refund'])) {
    $where_clauses[] = "a.status_pembayaran = ?";
    $params[] = $_GET['payment'];
}
if (!empty($_GET['prodi'])) {
    $where_clauses[] = "a.prodi_id = ?";
    $params[] = (int)$_GET['prodi'];
}
if (!empty($_GET['gelombang'])) {
    $where_clauses[] = "a.gelombang_id = ?";
    $params[] = (int)$_GET['gelombang'];
}
if (!empty($_GET['jalur'])) {
    $where_clauses[] = "a.jalur_id = ?";
    $params[] = (int)$_GET['jalur'];
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// ==========================================
// FETCH DATA WITH JOIN
// ==========================================
$sql = "SELECT a.*, 
               p.nama_prodi, p.jenjang, p.kode_prodi,
               g.nama AS gelombang_nama,
               j.nama_jalur,
               (SELECT COUNT(*) FROM applicant_documents WHERE applicant_id = a.id) AS doc_count,
               (SELECT COUNT(*) FROM payments WHERE applicant_id = a.id) AS payment_count
        FROM applicants a 
        LEFT JOIN prodi p ON a.prodi_id = p.id 
        LEFT JOIN gelombang g ON a.gelombang_id = g.id
        LEFT JOIN jalur_pendaftaran j ON a.jalur_id = j.id
        {$where_sql}
        ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applicants = $stmt->fetchAll();

// Master data for filters
$prodi_list = $pdo->query("SELECT id, nama_prodi, jenjang FROM prodi ORDER BY nama_prodi")->fetchAll();
$gelombang_list = $pdo->query("SELECT id, nama, tahun_akademik FROM gelombang ORDER BY tanggal_mulai DESC")->fetchAll();
$jalur_list = $pdo->query("SELECT id, nama_jalur FROM jalur_pendaftaran ORDER BY sort_order")->fetchAll();

// ==========================================
// STATS
// ==========================================
$total = count($applicants);
$pending = count(array_filter($applicants, fn($a) => $a['status_daftar'] === 'Pending'));
$verified = count(array_filter($applicants, fn($a) => $a['status_daftar'] === 'Diverifikasi'));
$lunas = count(array_filter($applicants, fn($a) => $a['status_pembayaran'] === 'Lunas'));

// Total revenue
$stmt_rev = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'Verified'");
$total_revenue = (float)$stmt_rev->fetchColumn();
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $total ?>"><?= number_format($total) ?></h3>
            <p>Total Pendaftar</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $pending ?>"><?= number_format($pending) ?></h3>
            <p>Menunggu Verifikasi</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info">
            <h3 data-counter="<?= $verified ?>"><?= number_format($verified) ?></h3>
            <p>Terverifikasi</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <h3>Rp <?= number_format($total_revenue / 1000000, 1) ?>M</h3>
            <p>Total Pendapatan</p>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="data-card" style="margin-bottom: 1.5rem;">
    <form method="GET" style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Status Daftar</label>
            <select name="status" class="form-select" style="padding: 0.5rem 0.75rem;">
                <option value="">Semua Status</option>
                <?php foreach (['Draft','Pending','Diverifikasi','Ditolak','Lulus','Tidak Lulus','Registrasi Ulang'] as $s): ?>
                <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Status Bayar</label>
            <select name="payment" class="form-select" style="padding: 0.5rem 0.75rem;">
                <option value="">Semua</option>
                <?php foreach (['Belum Bayar','Menunggu Verifikasi','Lunas','Refund'] as $p): ?>
                <option value="<?= $p ?>" <?= ($_GET['payment'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Gelombang</label>
            <select name="gelombang" class="form-select" style="padding: 0.5rem 0.75rem;">
                <option value="">Semua</option>
                <?php foreach ($gelombang_list as $g): ?>
                <option value="<?= $g['id'] ?>" <?= ($_GET['gelombang'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= sanitize($g['nama']) ?> (<?= $g['tahun_akademik'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Program Studi</label>
            <select name="prodi" class="form-select" style="padding: 0.5rem 0.75rem;">
                <option value="">Semua</option>
                <?php foreach ($prodi_list as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($_GET['prodi'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= sanitize($p['jenjang'] . ' - ' . $p['nama_prodi']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Jalur</label>
            <select name="jalur" class="form-select" style="padding: 0.5rem 0.75rem;">
                <option value="">Semua</option>
                <?php foreach ($jalur_list as $j): ?>
                <option value="<?= $j['id'] ?>" <?= ($_GET['jalur'] ?? '') == $j['id'] ? 'selected' : '' ?>><?= sanitize($j['nama_jalur']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            <a href="applicants.php" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                Daftar Pendaftar (<?= $total ?>)
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Klik baris atau tombol <strong>👁</strong> untuk lihat detail lengkap & verifikasi</p>
        </div>
        <div class="table-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari nama, email, kode..." data-table-search="applicants-table">
            </div>
            <button class="btn btn-secondary btn-sm" onclick="exportCSV()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="applicants-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"></th>
                    <th data-sort="kode">Kode</th>
                    <th data-sort="name">Pendaftar</th>
                    <th data-sort="prodi">Program Studi</th>
                    <th data-sort="status">Status</th>
                    <th data-sort="payment">Pembayaran</th>
                    <th data-sort="date" data-sort-type="date">Tanggal</th>
                    <th style="text-align: right; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($applicants) > 0): ?>
                    <?php foreach($applicants as $app): 
                        $status_badge = match($app['status_daftar']) {
                            'Diverifikasi', 'Lulus', 'Registrasi Ulang' => 'badge-success',
                            'Ditolak', 'Tidak Lulus' => 'badge-danger',
                            'Pending' => 'badge-warning',
                            default => 'badge-info'
                        };
                        $payment_badge = match($app['status_pembayaran']) {
                            'Lunas' => 'badge-success',
                            'Belum Bayar' => 'badge-danger',
                            'Menunggu Verifikasi' => 'badge-warning',
                            default => 'badge-info'
                        };
                        $initial = strtoupper(substr($app['nama_lengkap'], 0, 1));
                        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];
                        $avatar_color = $colors[crc32($app['nama_lengkap']) % count($colors)];
                    ?>
                    <tr style="cursor: pointer;" onclick="openDetailModal(<?= $app['id'] ?>)">
                        <td onclick="event.stopPropagation()"><input type="checkbox" class="row-checkbox" value="<?= $app['id'] ?>"></td>
                        <td data-column="kode">
                            <code style="background: var(--bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?= sanitize($app['kode_pendaftaran'] ?? '-') ?></code>
                        </td>
                        <td data-column="name" onclick="event.stopPropagation()">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: <?= $avatar_color ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">
                                    <?= $initial ?>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 600;"><?= sanitize($app['nama_lengkap']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= sanitize($app['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-column="prodi">
                            <div style="font-weight: 500;"><?= sanitize($app['nama_prodi'] ?? '-') ?></div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($app['gelombang_nama'] ?? '-') ?></span>
                        </td>
                        <td data-column="status">
                            <span class="badge <?= $status_badge ?>"><?= sanitize($app['status_daftar']) ?></span>
                            <?php if ($app['doc_count'] > 0): ?>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">📎 <?= $app['doc_count'] ?> berkas</div>
                            <?php endif; ?>
                        </td>
                        <td data-column="payment">
                            <span class="badge <?= $payment_badge ?>"><?= sanitize($app['status_pembayaran']) ?></span>
                        </td>
                        <td data-column="date" style="color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;">
                            <?= date('d M Y', strtotime($app['created_at'])) ?>
                            <div style="font-size: 0.75rem;"><?= date('H:i', strtotime($app['created_at'])) ?></div>
                        </td>
<td onclick="event.stopPropagation()">
    <div class="table-actions" style="justify-content: flex-end;">
        <?php 
        $wa_number = !empty($app['no_wa']) ? $app['no_wa'] : $app['no_hp'];
        $clean_wa = preg_replace('/[^0-9]/', '', $wa_number);
        if (!empty($clean_wa)): 
        ?>
        <a href="https://wa.me/<?= $clean_wa ?>?text=Halo%20<?= urlencode($app['nama_lengkap']) ?>,%20ini%20dari%20PMB%20UKJP." target="_blank" class="action-btn" style="color: #25D366;" title="Chat WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
        <?php endif; ?>
        
        <button class="action-btn" onclick="openDetailModal(<?= $app['id'] ?>)" title="Detail & Verifikasi">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="action-btn danger" onclick="event.stopPropagation(); if(confirm('Hapus data ini?')) { document.getElementById('delete-form-<?= $app['id'] ?>').submit(); }" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $app['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div style="text-align: center; padding: 4rem 2rem;">
                                <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                </div>
                                <p style="font-weight: 600; margin-bottom: 0.5rem;">Tidak Ada Data</p>
                                <p style="font-size: 0.85rem; color: var(--text-muted);"><?= !empty($_GET) ? 'Coba ubah filter atau reset pencarian.' : 'Belum ada pendaftar yang mendaftar.' ?></p>
                                <?php if (!empty($_GET)): ?>
                                <a href="applicants.php" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Reset Filter</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==========================================
     DETAIL MODAL (Bertab)
     ========================================== -->
<div class="modal-overlay" id="detail-modal">
    <div class="modal" style="max-width: 900px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 0;">
                <div id="modal-avatar" style="width: 48px; height: 48px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.25rem; flex-shrink: 0;">A</div>
                <div style="min-width: 0;">
                    <h3 id="modal-name" style="margin: 0; font-size: 1.1rem;">Nama Pendaftar</h3>
                    <div id="modal-kode" style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">PMB-XXXX-XXXXX</div>
                </div>
            </div>
            <button class="modal-close" data-modal-close>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        
        <!-- Tabs -->
        <div style="display: flex; border-bottom: 1px solid var(--border); padding: 0 1.5rem; overflow-x: auto;" id="detail-tabs">
            <button class="tab-link active" onclick="switchTab(this, 'tab-data')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; font-size: 0.875rem; cursor: pointer;">Data Pribadi</button>
            <button class="tab-link" onclick="switchTab(this, 'tab-edu')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.875rem; cursor: pointer;">Pendidikan</button>
            <button class="tab-link" onclick="switchTab(this, 'tab-parents')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.875rem; cursor: pointer;">Orang Tua</button>
            <button class="tab-link" onclick="switchTab(this, 'tab-payment')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.875rem; cursor: pointer;">Pembayaran</button>
<button class="tab-link" onclick="switchTab(this, 'tab-docs')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.875rem; cursor: pointer;">
    Dokumen <span id="doc-badge" style="background: var(--primary); color: white; padding: 0.1rem 0.4rem; border-radius: 50px; font-size: 0.7rem; margin-left: 0.25rem;">0</span>
</button>
            <button class="tab-link" onclick="switchTab(this, 'tab-action')" style="padding: 0.875rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; font-size: 0.875rem; cursor: pointer;">Aksi Verifikasi</button>
        </div>
        
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
            <!-- Tab: Data Pribadi -->
            <div id="tab-data" class="tab-pane" style="display: block;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">NIK</label><div id="d-nik" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Jenis Kelamin</label><div id="d-gender" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tempat Lahir</label><div id="d-birthplace" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tanggal Lahir</label><div id="d-birthdate" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Agama</label><div id="d-religion" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Email</label><div id="d-email" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">No. HP</label><div id="d-phone" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">No. WhatsApp</label><div id="d-wa" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div style="grid-column: 1 / -1;"><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Alamat Lengkap</label><div id="d-address" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Kota</label><div id="d-city" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Provinsi</label><div id="d-province" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                </div>
            </div>
            
            <!-- Tab: Pendidikan -->
            <div id="tab-edu" class="tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div style="grid-column: 1 / -1;"><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Asal Sekolah</label><div id="d-school" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Jurusan</label><div id="d-major" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tahun Lulus</label><div id="d-year" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Nilai Rapor Rata-rata</label><div id="d-grade" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">No. Ijazah</label><div id="d-ijazah" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Program Studi Dipilih</label><div id="d-prodi" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Jalur Pendaftaran</label><div id="d-jalur" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                </div>
            </div>
            
            <!-- Tab: Orang Tua -->
            <div id="tab-parents" class="tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Nama Ayah</label><div id="d-father" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Pekerjaan Ayah</label><div id="d-father-job" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Nama Ibu</label><div id="d-mother" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Pekerjaan Ibu</label><div id="d-mother-job" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                    <div style="grid-column: 1 / -1;"><label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Penghasilan Orang Tua</label><div id="d-income" style="font-weight: 600; margin-top: 0.25rem;">-</div></div>
                </div>
            </div>

                        
            <!-- Tab: Dokumen -->
            <div id="tab-docs" class="tab-pane" style="display: none;">
                <div id="docs-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted); grid-column: 1 / -1;">Memuat dokumen...</div>
                </div>
            </div>
            
            <!-- Tab: Pembayaran -->
            <div id="tab-payment" class="tab-pane" style="display: none;">
                <div id="payment-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Injected by JS -->
                </div>
            </div>
            
            <!-- Tab: Aksi Verifikasi -->
            <div id="tab-action" class="tab-pane" style="display: none;">
                <form method="POST" id="action-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" id="action-id">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; display: block;">Status Pendaftaran <span style="color: var(--danger);">*</span></label>
                            <select name="status_daftar" class="form-select">
                                <option value="Draft">Draft</option>
                                <option value="Pending">Pending</option>
                                <option value="Diverifikasi">Diverifikasi (Lulus Seleksi)</option>
                                <option value="Ditolak">Ditolak</option>
                                <option value="Lulus">Lulus (Final)</option>
                                <option value="Tidak Lulus">Tidak Lulus</option>
                                <option value="Registrasi Ulang">Registrasi Ulang</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; display: block;">Status Pembayaran</label>
                            <select name="status_pembayaran" class="form-select">
                                <option value="Belum Bayar">Belum Bayar</option>
                                <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                                <option value="Lunas">Lunas</option>
                                <option value="Refund">Refund</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; display: block;">Catatan Admin</label>
                        <textarea name="catatan_admin" class="form-textarea" rows="3" placeholder="Tulis catatan untuk pendaftar (opsional)..."></textarea>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-pane { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .tab-link { transition: all 0.2s ease; }
    .tab-link:hover { color: var(--primary) !important; }
    .tab-link.active { color: var(--primary) !important; border-bottom-color: var(--primary) !important; }
</style>

<script>
// Data store untuk detail applicant
const applicantsData = <?= json_encode($applicants) ?>;

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

function switchTab(btn, tabId) {
    document.querySelectorAll('#detail-tabs .tab-link').forEach(b => {
        b.classList.remove('active');
        b.style.borderBottomColor = 'transparent';
        b.style.color = 'var(--text-muted)';
    });
    btn.classList.add('active');
    btn.style.borderBottomColor = 'var(--primary)';
    btn.style.color = 'var(--primary)';
    
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
}

function openDetailModal(id) {
    const data = applicantsData.find(a => parseInt(a.id) === parseInt(id));
    if (!data) return;
    
    // Populate header
    document.getElementById('modal-avatar').textContent = data.nama_lengkap.charAt(0).toUpperCase();
    document.getElementById('modal-name').textContent = data.nama_lengkap;
    document.getElementById('modal-kode').textContent = data.kode_pendaftaran || 'PMB-XXXX-XXXXX';
    document.getElementById('action-id').value = data.id;
    
    // Form defaults
    const form = document.getElementById('action-form');
    form.querySelector('[name="status_daftar"]').value = data.status_daftar || 'Pending';
    form.querySelector('[name="status_pembayaran"]').value = data.status_pembayaran || 'Belum Bayar';
    form.querySelector('[name="catatan_admin"]').value = data.catatan_admin || '';
    
    // Tab: Data Pribadi
    document.getElementById('d-nik').textContent = data.nik || '-';
    document.getElementById('d-gender').textContent = data.jenis_kelamin || '-';
    document.getElementById('d-birthplace').textContent = data.tempat_lahir || '-';
    document.getElementById('d-birthdate').textContent = data.tanggal_lahir ? new Date(data.tanggal_lahir).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
    document.getElementById('d-religion').textContent = data.agama || '-';
    document.getElementById('d-email').textContent = data.email || '-';
    document.getElementById('d-phone').textContent = data.no_hp || '-';
    document.getElementById('d-wa').textContent = data.no_wa || '-';
    const fullAddress = [data.alamat, data.kota, data.provinsi, data.kode_pos].filter(Boolean).join(', ');
    document.getElementById('d-address').textContent = fullAddress || '-';
    document.getElementById('d-city').textContent = data.kota || '-';
    document.getElementById('d-province').textContent = data.provinsi || '-';
    
    // Tab: Pendidikan
    document.getElementById('d-school').textContent = data.asal_sekolah || '-';
    document.getElementById('d-major').textContent = data.jurusan_sekolah || '-';
    document.getElementById('d-year').textContent = data.tahun_lulus || '-';
    document.getElementById('d-grade').textContent = data.nilai_rapor ? data.nilai_rapor + ' / 100' : '-';
    document.getElementById('d-ijazah').textContent = data.no_ijazah || '-';
    document.getElementById('d-prodi').textContent = (data.nama_prodi || '-') + ' (' + (data.jenjang || '-') + ')';
    document.getElementById('d-jalur').textContent = data.jalur_pendaftaran || data.nama_jalur || '-';
    
    // Tab: Orang Tua
    document.getElementById('d-father').textContent = data.nama_ayah || '-';
    document.getElementById('d-father-job').textContent = data.pekerjaan_ayah || '-';
    document.getElementById('d-mother').textContent = data.nama_ibu || '-';
    document.getElementById('d-mother-job').textContent = data.pekerjaan_ibu || '-';
    document.getElementById('d-income').textContent = data.penghasilan_ortu || '-';
    
    // Tab: Dokumen (Fetch via AJAX)
    const docsList = document.getElementById('docs-list');
    const docBadge = document.getElementById('doc-badge');
    docBadge.textContent = data.doc_count || 0;
    docsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted); grid-column: 1 / -1;">Memuat dokumen...</div>';
    
    if (data.doc_count > 0) {
        fetch(`documents_api.php?applicant_id=${data.id}`)
            .then(r => r.ok ? r.json() : [])
            .then(docs => {
                if (docs.length === 0) {
                    docsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted); grid-column: 1 / -1;">Belum ada dokumen diupload.</div>';
                    return;
                }
                docsList.innerHTML = docs.map(doc => `
                    <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 1rem; text-align: center;">
                        <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem; text-transform: capitalize;">${doc.document_type.replace('_', ' ')}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">${(doc.file_size / 1024).toFixed(1)} KB • ${doc.verification_status}</div>
                        <a href="${doc.file_path}" target="_blank" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.25rem;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Lihat File
                        </a>
                    </div>
                `).join('');
            })
            .catch(() => {
                docsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--danger); grid-column: 1 / -1;">Gagal memuat dokumen.</div>';
            });
    } else {
        docsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted); grid-column: 1 / -1;">Pendaftar belum mengunggah dokumen.</div>';
    }

    // Tab: Pembayaran (fetch via AJAX)
    const paymentList = document.getElementById('payment-list');
    paymentList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted);">Memuat data pembayaran...</div>';
    
    fetch(`payments_api.php?applicant_id=${data.id}`)
        .then(r => r.ok ? r.json() : [])
        .then(payments => {
            if (payments.length === 0) {
                paymentList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada transaksi pembayaran.</div>';
                return;
            }
            paymentList.innerHTML = payments.map(p => `
                <div style="background: var(--bg); padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong style="font-family: monospace; font-size: 0.9rem;">${p.invoice_number}</strong>
                        <span class="badge ${p.status === 'Verified' ? 'badge-success' : 'badge-warning'}">${p.status}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
                        <div><span style="color: var(--text-muted);">Tipe:</span> ${p.payment_type}</div>
                        <div><span style="color: var(--text-muted);">Jumlah:</span> <strong>Rp ${parseInt(p.amount).toLocaleString('id-ID')}</strong></div>
                        <div><span style="color: var(--text-muted);">Metode:</span> ${p.payment_method || '-'}</div>
                        <div><span style="color: var(--text-muted);">Tanggal:</span> ${p.payment_date || '-'}</div>
                    </div>
                    ${p.proof_file ? `<a href="${p.proof_file}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.5rem; font-size: 0.8rem; color: var(--primary);">📎 Lihat Bukti Transfer</a>` : ''}
                </div>
            `).join('');
        })
        .catch(() => {
            paymentList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted);">Tidak dapat memuat data pembayaran.</div>';
        });
    
    // Reset to first tab
    document.querySelector('#detail-tabs .tab-link').click();
    openModal('detail-modal');
}

function exportCSV() {
    const rows = document.querySelectorAll('#applicants-table tbody tr');
    if (rows.length === 0 || !rows[0].querySelector('[data-column]')) {
        if (typeof showToast === 'function') showToast('Info', 'Tidak ada data untuk di-export', 'warning');
        return;
    }
    
    let csv = 'Kode,Nama,Email,No HP,Program Studi,Gelombang,Status Daftar,Status Bayar,Tanggal\n';
    rows.forEach(row => {
        if (!row.querySelector('[data-column]')) return;
        const kode = row.querySelector('[data-column="kode"]')?.innerText.trim() || '';
        const name = row.querySelector('[data-column="name"]')?.querySelector('div[style*="font-weight: 600"]')?.innerText || '';
        const email = row.querySelector('[data-column="name"]')?.querySelector('div[style*="font-size: 0.8rem"]')?.innerText || '';
        const prodi = row.querySelector('[data-column="prodi"]')?.querySelector('div[style*="font-weight: 500"]')?.innerText || '';
        const gelombang = row.querySelector('[data-column="prodi"]')?.querySelector('span')?.innerText || '';
        const status = row.querySelector('[data-column="status"] .badge')?.innerText || '';
        const payment = row.querySelector('[data-column="payment"] .badge')?.innerText || '';
        const date = row.querySelector('[data-column="date"]')?.innerText.trim().replace(/\n/g, ' ') || '';
        
        csv += `"${kode}","${name}","${email}","${email}","${prodi}","${gelombang}","${status}","${payment}","${date}"\n`;
    });
    
    const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `pendaftar_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    
    if (typeof showToast === 'function') showToast('Berhasil', 'Data sedang diunduh', 'success');
}

<?php if (isset($_SESSION['toast'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    showToast('<?= $_SESSION['toast']['title'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>', '<?= $_SESSION['toast']['type'] ?>');
});
<?php unset($_SESSION['toast']); endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>