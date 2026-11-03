<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Audit Log Sistem";
$breadcrumbs = [['label' => 'Dashboard', 'url' => 'index.php'], ['label' => 'Audit Log']];

// ======================================================================
// 2. BARU PANGGIL HEADER
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 3. FILTER LOGIC
// ======================================================================
$filter_action = $_GET['action'] ?? '';
$filter_table = $_GET['table'] ?? '';
$sql = "SELECT l.*, u.username FROM admin_logs l LEFT JOIN users u ON l.user_id = u.id WHERE 1=1";
$params = [];
if (!empty($filter_action)) { $sql .= " AND l.action = ?"; $params[] = $filter_action; }
if (!empty($filter_table)) { $sql .= " AND l.table_name = ?"; $params[] = $filter_table; }
$sql .= " ORDER BY l.created_at DESC LIMIT 500"; // Batasi 500 log terbaru untuk performa

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs_list = $stmt->fetchAll();

$total_logs = count($logs_list);
$today_logs = count(array_filter($logs_list, fn($l) => date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')));
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div><div class="stat-info"><h3 data-counter="<?= $total_logs ?>"><?= number_format($total_logs) ?></h3><p>Total Log Tersimpan</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="stat-info"><h3 data-counter="<?= $today_logs ?>"><?= $today_logs ?></h3><p>Aktivitas Hari Ini</p></div></div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div><h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Riwayat Aktivitas Administrator</h3><p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Pantau semua perubahan data dan login sistem</p></div>
        <div class="table-controls">
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <select name="action" class="form-select" style="width: 150px; padding: 0.5rem;">
                    <option value="">Semua Aksi</option>
                    <option value="CREATE" <?= $filter_action === 'CREATE' ? 'selected' : '' ?>>Create</option>
                    <option value="UPDATE" <?= $filter_action === 'UPDATE' ? 'selected' : '' ?>>Update</option>
                    <option value="DELETE" <?= $filter_action === 'DELETE' ? 'selected' : '' ?>>Delete</option>
                    <option value="EXPORT" <?= $filter_action === 'EXPORT' ? 'selected' : '' ?>>Export</option>
                </select>
                <select name="table" class="form-select" style="width: 150px; padding: 0.5rem;">
                    <option value="">Semua Tabel</option>
                    <option value="applicants" <?= $filter_table === 'applicants' ? 'selected' : '' ?>>Applicants</option>
                    <option value="prodi" <?= $filter_table === 'prodi' ? 'selected' : '' ?>>Prodi</option>
                    <option value="users" <?= $filter_table === 'users' ? 'selected' : '' ?>>Users</option>
                    <option value="news" <?= $filter_table === 'news' ? 'selected' : '' ?>>News</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                <a href="logs.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="admin-table" id="logs-table">
            <thead><tr><th style="width: 50px;">#</th><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Tabel</th><th>Deskripsi</th><th>IP Address</th><th style="width: 80px; text-align: right;">Detail</th></tr></thead>
            <tbody>
                <?php if (count($logs_list) > 0): foreach($logs_list as $i => $l): 
                    $action_badge = match($l['action']) { 'CREATE' => 'badge-success', 'UPDATE' => 'badge-info', 'DELETE' => 'badge-danger', 'EXPORT' => 'badge-warning', default => 'badge-info' };
                ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--text-muted);"><?= $i + 1 ?></span></td>
                    <td><div style="font-size: 0.85rem; font-weight: 600;"><?= date('d M Y', strtotime($l['created_at'])) ?></div><div style="font-size: 0.7rem; color: var(--text-muted);"><?= date('H:i:s', strtotime($l['created_at'])) ?></div></td>
                    <td><span style="font-weight: 600; font-size: 0.85rem;"><?= sanitize($l['username'] ?: 'System') ?></span></td>
                    <td><span class="badge <?= $action_badge ?>" style="font-size: 0.7rem;"><?= $l['action'] ?></span></td>
                    <td><code style="background: var(--bg); padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.7rem;"><?= sanitize($l['table_name'] ?: '-') ?></code></td>
                    <td><div style="font-size: 0.85rem; max-width: 300px; white-space: normal;"><?= sanitize($l['description']) ?></div></td>
                    <td><code style="font-size: 0.75rem;"><?= sanitize($l['ip_address']) ?></code></td>
                    <td style="text-align: right;"><button class="action-btn" onclick="viewLogDetail(<?= htmlspecialchars(json_encode($l), ENT_QUOTES) ?>)" title="Lihat Detail JSON"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="8" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);"><p style="font-weight: 600;">Tidak Ada Log Ditemukan</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="log-detail-modal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header"><h3>Detail Data Log</h3><button class="modal-close" onclick="closeLogModal()"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
            <div id="log-detail-content"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" onclick="closeLogModal()">Tutup</button></div>
    </div>
</div>

<script>
function viewLogDetail(log) {
    const formatJson = (data) => {
        if (!data) return '<em style="color: var(--text-muted);">Tidak ada data</em>';
        try {
            const parsed = JSON.parse(data);
            return '<pre style="background: var(--bg); padding: 1rem; border-radius: 8px; font-size: 0.8rem; overflow-x: auto; white-space: pre-wrap;">' + JSON.stringify(parsed, null, 2) + '</pre>';
        } catch (e) { return '<pre style="background: var(--bg); padding: 1rem; border-radius: 8px; font-size: 0.8rem;">' + data + '</pre>'; }
    };

    const html = `
        <div style="margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem;">User Agent</div>
            <div style="font-size: 0.85rem; word-break: break-all;">${log.user_agent || '-'}</div>
        </div>
        <div style="margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem;">Data Lama (Old Data)</div>
            ${formatJson(log.old_data)}
        </div>
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem;">Data Baru (New Data)</div>
            ${formatJson(log.new_data)}
        </div>
    `;
    document.getElementById('log-detail-content').innerHTML = html;
    const modal = document.getElementById('log-detail-modal');
    if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function closeLogModal() {
    const modal = document.getElementById('log-detail-modal');
    if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}
</script>
<?php require_once 'includes/footer.php'; ?>