<?php
// ======================================================================
// 1. LOAD KONFIGURASI
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_login();

$page_title = "Cetak Kartu Ujian";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Cetak Kartu Ujian']
];

// ======================================================================
// 2. HANDLE POST (Update status cetak)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF Error');
    
    if (($_POST['action'] ?? '') === 'mark_printed') {
        $ids = $_POST['schedule_ids'] ?? [];
        if (!empty($ids)) {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE exam_schedules SET is_printed = 1, printed_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => count($ids) . ' kartu ditandai sudah dicetak'];
        }
    }
    header('Location: print-card.php');
    exit;
}

require_once 'includes/header.php';

// ======================================================================
// 3. FETCH DATA
// ======================================================================
$selected_gelombang = $_GET['gelombang'] ?? '';
$schedules = [];
$gelombang_list = $pdo->query("SELECT id, nama, tahun_akademik FROM gelombang ORDER BY tahun_akademik DESC")->fetchAll();

if (!empty($selected_gelombang)) {
    $stmt = $pdo->prepare("
        SELECT es.id, es.nomor_peserta, es.tanggal_ujian, es.ruang_ujian, es.is_printed,
               a.nama_lengkap, a.kode_pendaftaran, p.nama_prodi
        FROM exam_schedules es
        JOIN applicants a ON es.applicant_id = a.id
        LEFT JOIN prodi p ON a.prodi_id = p.id
        WHERE es.gelombang_id = ?
        ORDER BY es.ruang_ujian ASC, a.nama_lengkap ASC
    ");
    $stmt->execute([$selected_gelombang]);
    $schedules = $stmt->fetchAll();
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></div>
        <div class="stat-info"><h3><?= count($schedules) ?></h3><p>Total Peserta Terjadwal</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="stat-info"><h3><?= count(array_filter($schedules, fn($s) => $s['is_printed'])) ?></h3><p>Kartu Sudah Dicetak</p></div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>Dashboard Cetak Kartu Ujian</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Pilih gelombang untuk melihat dan mencetak kartu peserta</p>
        </div>
    </div>
    
    <!-- Filter Gelombang -->
    <div style="padding: 1.5rem; background: var(--bg); border-bottom: 1px solid var(--border); display: flex; gap: 1rem; align-items: end;">
        <form method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <div class="form-group" style="margin: 0; flex: 1;">
                <label>Pilih Gelombang</label>
                <select name="gelombang" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Pilih Gelombang --</option>
                    <?php foreach($gelombang_list as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $selected_gelombang == $g['id'] ? 'selected' : '' ?>>
                            <?= sanitize($g['nama']) ?> (<?= sanitize($g['tahun_akademik']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">Tampilkan</button>
        </form>
    </div>

    <?php if (!empty($selected_gelombang) && count($schedules) > 0): ?>
    <!-- Form Cetak Massal -->
    <form method="POST" id="batch-print-form">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="action" value="mark_printed">
        
        <div style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; background: rgba(59, 130, 246, 0.05); border-bottom: 1px solid var(--border);">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                <input type="checkbox" id="select-all" style="width: 18px; height: 18px; accent-color: var(--primary);">
                Pilih Semua untuk Tandai Dicetak
            </label>
            <button type="submit" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><polyline points="20 6 9 17 4 12"/></svg>
                Tandai Terpilih
            </button>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="row-check" style="width: 16px; height: 16px;"></th>
                        <th>No. Peserta</th>
                        <th>Nama Lengkap</th>
                        <th>Program Studi</th>
                        <th>Ruang</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($schedules as $s): ?>
                    <tr>
                        <td><input type="checkbox" name="schedule_ids[]" value="<?= $s['id'] ?>" class="row-check" style="width: 16px; height: 16px;"></td>
                        <td><code style="background: var(--primary); color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.8rem;"><?= sanitize($s['nomor_peserta']) ?></code></td>
                        <td style="font-weight: 600;"><?= sanitize($s['nama_lengkap']) ?></td>
                        <td><?= sanitize($s['nama_prodi']) ?></td>
                        <td><span class="badge badge-info"><?= sanitize($s['ruang_ujian']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($s['tanggal_ujian'])) ?></td>
                        <td>
                            <?php if ($s['is_printed']): ?>
                                <span class="badge badge-success">Sudah Dicetak</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="../public/cetak-kartu-ujian.php?nomor=<?= urlencode($s['nomor_peserta']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.25rem;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Cetak
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php elseif (!empty($selected_gelombang)): ?>
        <div style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
            <p>Tidak ada peserta terjadwal untuk gelombang ini.</p>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
            <p>Silakan pilih gelombang di atas untuk memulai.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Select All Logic
    document.getElementById('select-all')?.addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
</script>

<?php require_once 'includes/footer.php'; ?>