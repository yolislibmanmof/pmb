<?php
// ======================================================================
// 1. LOAD KONFIGURASI & FUNGSI (WAJIB DI PALING ATAS)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan user sudah login
require_login();

$page_title = "Jadwal & Kartu Ujian";
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php'],
    ['label' => 'Jadwal & Kartu Ujian']
];

// ======================================================================
// 2. HELPER FUNCTIONS
// ======================================================================

// Auto-generate nomor peserta unik
function generateNomorPeserta($pdo, $gelombang_id) {
    $tahun = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM exam_schedules WHERE gelombang_id = ?");
    $stmt->execute([$gelombang_id]);
    $count = (int)$stmt->fetchColumn() + 1;
    return sprintf('%d/%s/%04d', $gelombang_id, substr($tahun, -2), $count);
}

// Generate QR Code data URL
function generateQRCode($data) {
    $qr_data = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$qr_data}";
}

// ======================================================================
// 3. HANDLE CRUD ACTIONS (SEBELUM ADA OUTPUT HTML)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF Error');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $applicant_id = (int)$_POST['applicant_id'];
            $gelombang_id = (int)$_POST['gelombang_id'];
            
            // Check if applicant already has schedule
            $check = $pdo->prepare("SELECT COUNT(*) FROM exam_schedules WHERE applicant_id = ?");
            $check->execute([$applicant_id]);
            if ((int)$check->fetchColumn() > 0) {
                throw new Exception("Peserta sudah memiliki jadwal ujian");
            }
            
            $nomor_peserta = generateNomorPeserta($pdo, $gelombang_id);
            
            $stmt = $pdo->prepare("INSERT INTO exam_schedules (
                applicant_id, gelombang_id, nomor_peserta, tanggal_ujian, 
                waktu_ujian, ruang_ujian, lokasi_ujian, jenis_ujian, keterangan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $applicant_id,
                $gelombang_id,
                $nomor_peserta,
                sanitize($_POST['tanggal_ujian']),
                sanitize($_POST['waktu_ujian']),
                sanitize($_POST['ruang_ujian']),
                sanitize($_POST['lokasi_ujian']),
                sanitize($_POST['jenis_ujian']),
                sanitize($_POST['keterangan'])
            ]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Jadwal ujian berhasil ditambahkan'];
            
        } elseif ($action === 'bulk_generate') {
            // Bulk generate for all applicants in a wave
            $gelombang_id = (int)$_POST['gelombang_id'];
            $tanggal = sanitize($_POST['tanggal_ujian']);
            $ruang_prefix = sanitize($_POST['ruang_prefix']);
            
            // Get all verified applicants in this wave
            $stmt = $pdo->prepare("
                SELECT a.id, a.kode_pendaftaran 
                FROM applicants a 
                WHERE a.gelombang_id = ? 
                AND a.status_daftar IN ('Diverifikasi', 'Lulus')
                AND NOT EXISTS (SELECT 1 FROM exam_schedules es WHERE es.applicant_id = a.id)
            ");
            $stmt->execute([$gelombang_id]);
            $applicants = $stmt->fetchAll();
            
            $counter = 1;
            foreach ($applicants as $app) {
                $nomor_peserta = sprintf('UJ%s-%03d', substr(date('Y'), -2), $counter);
                $ruang = $ruang_prefix . '-' . sprintf('%02d', (($counter - 1) % 5) + 1);
                
                $pdo->prepare("INSERT INTO exam_schedules (
                    applicant_id, gelombang_id, nomor_peserta, tanggal_ujian, 
                    waktu_ujian, ruang_ujian, lokasi_ujian, jenis_ujian
                ) VALUES (?, ?, ?, ?, '08:00:00', ?, 'Kampus 1', 'Tes Tulis')")
                ->execute([
                    $app['id'], $gelombang_id, $nomor_peserta, $tanggal, $ruang
                ]);
                $counter++;
            }
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => "Berhasil generate {$counter} jadwal ujian"];
            
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE exam_schedules SET 
                tanggal_ujian=?, waktu_ujian=?, ruang_ujian=?, 
                lokasi_ujian=?, jenis_ujian=?, keterangan=?
                WHERE id=?");
            
            $stmt->execute([
                sanitize($_POST['tanggal_ujian']),
                sanitize($_POST['waktu_ujian']),
                sanitize($_POST['ruang_ujian']),
                sanitize($_POST['lokasi_ujian']),
                sanitize($_POST['jenis_ujian']),
                sanitize($_POST['keterangan']),
                $id
            ]);
            
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Jadwal ujian berhasil diperbarui'];
            
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM exam_schedules WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = ['type' => 'success', 'title' => 'Dihapus', 'message' => 'Jadwal ujian berhasil dihapus'];
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()];
    }
    
    header('Location: exam-schedules.php');
    exit;
}

// ======================================================================
// 4. BARU PANGGIL HEADER (SETELAH SEMUA LOGIC REDIRECT SELESAI)
// ======================================================================
require_once 'includes/header.php';

// ======================================================================
// 5. FETCH DATA
// ======================================================================
// Get all schedules with applicant info
$stmt = $pdo->query("
    SELECT es.*, 
           a.nama_lengkap, a.kode_pendaftaran, a.email,
           p.nama_prodi, g.nama as gelombang_nama
    FROM exam_schedules es
    LEFT JOIN applicants a ON es.applicant_id = a.id
    LEFT JOIN prodi p ON a.prodi_id = p.id
    LEFT JOIN gelombang g ON es.gelombang_id = g.id
    ORDER BY es.tanggal_ujian ASC, es.waktu_ujian ASC, es.ruang_ujian ASC
");
$schedules = $stmt->fetchAll();

// Get available applicants (no schedule yet)
$stmt = $pdo->query("
    SELECT a.id, a.nama_lengkap, a.kode_pendaftaran, a.email, 
           p.nama_prodi, g.id as gelombang_id, g.nama as gelombang_nama
    FROM applicants a
    LEFT JOIN prodi p ON a.prodi_id = p.id
    LEFT JOIN gelombang g ON a.gelombang_id = g.id
    WHERE a.status_daftar IN ('Diverifikasi', 'Lulus')
    AND NOT EXISTS (SELECT 1 FROM exam_schedules es WHERE es.applicant_id = a.id)
    ORDER BY g.nama, a.nama_lengkap
");
$available_applicants = $stmt->fetchAll();

// Get gelombang list
$gelombang_list = $pdo->query("SELECT id, nama, tahun_akademik FROM gelombang ORDER BY tanggal_mulai DESC")->fetchAll();

// Stats
$total_schedules = count($schedules);
$today_exams = count(array_filter($schedules, fn($s) => $s['tanggal_ujian'] == date('Y-m-d')));
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-info">
            <h3><?= $total_schedules ?></h3>
            <p>Total Jadwal Ujian</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="stat-info">
            <h3><?= $today_exams ?></h3>
            <p>Ujian Hari Ini</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-info">
            <h3><?= count($available_applicants) ?></h3>
            <p>Belum Ada Jadwal</p>
        </div>
    </div>
</div>

<!-- Bulk Generate Card -->
<?php if (count($available_applicants) > 0): ?>
<div class="data-card" style="margin-bottom: 2rem; border: 2px solid var(--primary);">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="22"/><line x1="12" y1="2" x2="12" y2="6"/>
                </svg>
                Generate Jadwal Massal
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Buat jadwal ujian otomatis untuk semua peserta yang belum memiliki jadwal</p>
        </div>
    </div>
    <div style="padding: 1.5rem;">
        <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="bulk_generate">
            
            <div class="form-group" style="margin: 0;">
                <label>Gelombang</label>
                <select name="gelombang_id" class="form-select" required>
                    <option value="">Pilih Gelombang</option>
                    <?php foreach($gelombang_list as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= sanitize($g['nama']) ?> (<?= sanitize($g['tahun_akademik']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin: 0;">
                <label>Tanggal Ujian</label>
                <input type="date" name="tanggal_ujian" class="form-input" required min="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="form-group" style="margin: 0;">
                <label>Prefix Ruang</label>
                <input type="text" name="ruang_prefix" class="form-input" placeholder="Contoh: A, B, C" value="A" required>
                <div class="form-hint">Ruang akan digenerate otomatis (A-01, A-02, dst)</div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="height: 48px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="12" y1="2" x2="12" y2="6"/></svg>
                Generate <?= count($available_applicants) ?> Jadwal
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Daftar Jadwal Ujian (<?= $total_schedules ?>)
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Kelola jadwal, ruang, dan cetak kartu ujian peserta</p>
        </div>
        <button class="btn btn-primary btn-sm" data-modal-open="add-schedule-modal" onclick="resetAddForm()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Jadwal Manual
        </button>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table" id="schedules-table" data-sortable>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th data-sort="nomor">No. Peserta</th>
                    <th data-sort="nama">Nama Peserta</th>
                    <th data-sort="prodi">Program Studi</th>
                    <th data-sort="tanggal">Tanggal</th>
                    <th data-sort="waktu">Waktu</th>
                    <th data-sort="ruang">Ruang</th>
                    <th data-sort="lokasi">Lokasi</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($schedules) > 0): ?>
                    <?php foreach($schedules as $idx => $s): ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted);"><?= $idx + 1 ?></td>
                        <td>
                            <code style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                <?= sanitize($s['nomor_peserta']) ?>
                            </code>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= sanitize($s['nama_lengkap']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($s['email']) ?></div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem;"><?= sanitize($s['nama_prodi'] ?: '-') ?></span>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= date('d/m/Y', strtotime($s['tanggal_ujian'])) ?></div>
                        </td>
                        <td>
                            <span style="background: var(--bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                <?= date('H:i', strtotime($s['waktu_ujian'])) ?>
                            </span>
                        </td>
                        <td>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                <?= sanitize($s['ruang_ujian']) ?>
                            </span>
                        </td>
                        <td><?= sanitize($s['lokasi_ujian']) ?></td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end; gap: 0.25rem;">
                                <a href="../public/cetak-kartu-ujian.php?nomor=<?= urlencode($s['nomor_peserta']) ?>" target="_blank" class="action-btn" title="Cetak Kartu" style="background: var(--success);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                </a>
                                <button class="action-btn" onclick="editSchedule(<?= htmlspecialchars(json_encode($s)) ?>)" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn danger" onclick="deleteSchedule(<?= $s['id'] ?>)" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                                <form id="delete-form-<?= $s['id'] ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--text-muted);">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Jadwal Ujian</p>
                            <p>Gunakan fitur "Generate Jadwal Massal" untuk membuat jadwal otomatis</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal-overlay" id="add-schedule-modal">
    <div class="modal" style="max-width: 700px;">
        <form method="POST" id="add-schedule-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="modal-header">
                <h3>Tambah Jadwal Ujian Manual</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Pilih Peserta <span class="required">*</span></label>
                    <select name="applicant_id" class="form-select" required>
                        <option value="">-- Pilih Peserta --</option>
                        <?php foreach($available_applicants as $app): ?>
                            <option value="<?= $app['id'] ?>" data-gelombang="<?= $app['gelombang_id'] ?>">
                                <?= sanitize($app['nama_lengkap']) ?> - <?= sanitize($app['nama_prodi']) ?> (<?= sanitize($app['gelombang_nama']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="gelombang_id" id="gelombang_id" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Tanggal Ujian <span class="required">*</span></label>
                        <input type="date" name="tanggal_ujian" class="form-input" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Waktu Ujian <span class="required">*</span></label>
                        <input type="time" name="waktu_ujian" class="form-input" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Ruang Ujian <span class="required">*</span></label>
                        <input type="text" name="ruang_ujian" class="form-input" placeholder="Contoh: A-01" required>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Ujian <span class="required">*</span></label>
                        <select name="lokasi_ujian" class="form-select" required>
                            <option value="Kampus 1">Kampus 1</option>
                            <option value="Kampus 2">Kampus 2</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Jenis Ujian <span class="required">*</span></label>
                    <select name="jenis_ujian" class="form-select" required>
                        <option value="Tes Tulis">Tes Tulis</option>
                        <option value="Wawancara">Wawancara</option>
                        <option value="Tes Kesehatan">Tes Kesehatan</option>
                        <option value="Psikotes">Psikotes</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-textarea" rows="3" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal-overlay" id="edit-schedule-modal">
    <div class="modal" style="max-width: 700px;">
        <form method="POST" id="edit-schedule-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="modal-header">
                <h3>Edit Jadwal Ujian</h3>
                <button type="button" class="modal-close" data-modal-close>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Nomor Peserta</label>
                    <input type="text" id="edit_nomor_peserta" class="form-input" disabled style="background: var(--bg);">
                </div>
                
                <div class="form-group">
                    <label>Nama Peserta</label>
                    <input type="text" id="edit_nama" class="form-input" disabled style="background: var(--bg);">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Tanggal Ujian <span class="required">*</span></label>
                        <input type="date" name="tanggal_ujian" id="edit_tanggal" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu Ujian <span class="required">*</span></label>
                        <input type="time" name="waktu_ujian" id="edit_waktu" class="form-input" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Ruang Ujian <span class="required">*</span></label>
                        <input type="text" name="ruang_ujian" id="edit_ruang" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Ujian <span class="required">*</span></label>
                        <select name="lokasi_ujian" id="edit_lokasi" class="form-select" required>
                            <option value="Kampus 1">Kampus 1</option>
                            <option value="Kampus 2">Kampus 2</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Jenis Ujian <span class="required">*</span></label>
                    <select name="jenis_ujian" id="edit_jenis" class="form-select" required>
                        <option value="Tes Tulis">Tes Tulis</option>
                        <option value="Wawancara">Wawancara</option>
                        <option value="Tes Kesehatan">Tes Kesehatan</option>
                        <option value="Psikotes">Psikotes</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" class="form-textarea" rows="3"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Update Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetAddForm() {
    document.getElementById('add-schedule-form').reset();
}

// Update gelombang_id when applicant changes
document.querySelector('[name="applicant_id"]')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('gelombang_id').value = selectedOption.dataset.gelombang || '';
});

function editSchedule(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nomor_peserta').value = data.nomor_peserta;
    document.getElementById('edit_nama').value = data.nama_lengkap;
    document.getElementById('edit_tanggal').value = data.tanggal_ujian;
    document.getElementById('edit_waktu').value = data.waktu_ujian;
    document.getElementById('edit_ruang').value = data.ruang_ujian;
    document.getElementById('edit_lokasi').value = data.lokasi_ujian;
    document.getElementById('edit_jenis').value = data.jenis_ujian;
    document.getElementById('edit_keterangan').value = data.keterangan || '';
    
    openModal('edit-schedule-modal');
}

function deleteSchedule(id) {
    if (confirm('Hapus jadwal ujian ini?')) {
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