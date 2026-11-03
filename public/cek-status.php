<?php
$page_title = "Cek Status Pendaftaran";
require_once 'includes/header.php';

// ==========================================
// SECURITY: RATE LIMITING
// ==========================================
function isStatusCheckRateLimited($pdo, $ip, $maxAttempts = 10, $timeWindow = 900) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_logs 
            WHERE action = 'STATUS_CHECK' 
              AND ip_address = ? 
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, $timeWindow]);
        return (int)$stmt->fetchColumn() >= $maxAttempts;
    } catch (Exception $e) {
        return false;
    }
}

function logStatusCheck($pdo, $kode, $status, $ip) {
    try {
        $desc = "Cek status kode: " . substr($kode, 0, 20);
        $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, description, ip_address) 
            VALUES (NULL, ?, 'applicants', ?, ?)")
            ->execute([$status, $desc, $ip]);
    } catch (Exception $e) { /* silent */ }
}

// ==========================================
// HANDLE POST: CARI DATA PENDAFTAR
// ==========================================
$applicant = null;
$error = '';
$searched = false;
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $kode = trim($_POST['kode_pendaftaran'] ?? '');
    $verifier = trim($_POST['verifier'] ?? ''); // email atau no_hp
    
    if (empty($kode) || empty($verifier)) {
        $error = $lang === 'en' ? 'Please fill in all fields.' : 'Mohon lengkapi semua kolom.';
    } elseif (isStatusCheckRateLimited($pdo, $ip)) {
        $error = $lang === 'en' 
            ? 'Too many attempts. Please wait 15 minutes before trying again.' 
            : 'Terlalu banyak percobaan. Silakan tunggu 15 menit sebelum mencoba lagi.';
        logStatusCheck($pdo, $kode, 'RATE_LIMITED', $ip);
    } else {
        // Cari pendaftar berdasarkan kode + (email ATAU no_hp)
        $stmt = $pdo->prepare("SELECT a.*, p.nama_prodi, p.jenjang, g.nama as gelombang_nama, g.tahun_akademik, j.nama_jalur
            FROM applicants a
            LEFT JOIN prodi p ON a.prodi_id = p.id
            LEFT JOIN gelombang g ON a.gelombang_id = g.id
            LEFT JOIN jalur_pendaftaran j ON a.jalur_id = j.id
            WHERE a.kode_pendaftaran = ? 
              AND (a.email = ? OR a.no_hp = ? OR a.no_wa = ?)
            LIMIT 1");
        $stmt->execute([$kode, $verifier, $verifier, $verifier]);
        $applicant = $stmt->fetch();
        
        if ($applicant) {
            logStatusCheck($pdo, $kode, 'STATUS_CHECK_OK', $ip);
            // Ambil dokumen (jika ada)
            $stmt_doc = $pdo->prepare("SELECT document_type, file_path, verification_status, uploaded_at 
                FROM applicant_documents WHERE applicant_id = ? ORDER BY uploaded_at DESC");
            $stmt_doc->execute([$applicant['id']]);
            $applicant['documents'] = $stmt_doc->fetchAll();
            
            // Ambil payments (jika ada)
            $stmt_pay = $pdo->prepare("SELECT * FROM payments WHERE applicant_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt_pay->execute([$applicant['id']]);
            $applicant['payment'] = $stmt_pay->fetch();
        } else {
            logStatusCheck($pdo, $kode, 'STATUS_CHECK_FAILED', $ip);
            $error = $lang === 'en' 
                ? 'No matching application found. Please check your registration code and email/phone.' 
                : 'Data pendaftaran tidak ditemukan. Pastikan kode dan email/no HP sesuai.';
        }
    }
}

// Helper format tanggal
function formatTanggal($dateStr, $lang = 'id') {
    if (empty($dateStr)) return '-';
    $ts = strtotime($dateStr);
    $id = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $en = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $m = (int)date('n', $ts) - 1;
    return date('j', $ts) . ' ' . ($lang === 'en' ? $en[$m] : $id[$m]) . ' ' . date('Y', $ts);
}

// Helper: Hitung stage pendaftaran (0-6)
function getProgressStage($app) {
    if (!$app) return 0;
    if (in_array($app['status_daftar'], ['Lulus', 'Registrasi Ulang'])) return 6;
    if ($app['status_daftar'] === 'Diverifikasi' || $app['status_daftar'] === 'Ditolak' || $app['status_daftar'] === 'Tidak Lulus') return 5;
    if ($app['status_pembayaran'] === 'Lunas' || $app['status_pembayaran'] === 'Menunggu Verifikasi') return 4;
    // Cek dokumen terverifikasi
    if (!empty($app['documents'])) {
        $verified = array_filter($app['documents'], fn($d) => $d['verification_status'] === 'Verified');
        if (count($verified) >= 3) return 3; // Minimal 3 dokumen terverifikasi
    }
    if ($app['status_daftar'] === 'Pending') return 2;
    if (!empty($app['documents']) || !empty($app['payment'])) return 1;
    return 0;
}

// Helper: Label badge status
function statusBadgeClass($status) {
    $map = [
        'Draft' => 'badge-info', 'Pending' => 'badge-warning',
        'Diverifikasi' => 'badge-success', 'Ditolak' => 'badge-danger',
        'Lulus' => 'badge-success', 'Tidak Lulus' => 'badge-danger',
        'Registrasi Ulang' => 'badge-info',
        'Belum Bayar' => 'badge-danger', 'Menunggu Verifikasi' => 'badge-warning',
        'Lunas' => 'badge-success', 'Refund' => 'badge-info',
        'Pending' => 'badge-warning', 'Verified' => 'badge-success', 'Rejected' => 'badge-danger'
    ];
    return $map[$status] ?? 'badge-info';
}

// Cek celebration
$is_celebration = $applicant && in_array($applicant['status_daftar'], ['Lulus', 'Registrasi Ulang']);
?>

<!-- ==========================================
     1. MINI HERO
     ========================================== -->
<section style="padding: 10rem 2rem 3rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%); text-align: center;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none;" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="Cek Status" data-lang-en="Check Status">Cek Status</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Lacak Pendaftaran Anda" data-lang-en="Track Your Application">Lacak Pendaftaran Anda</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 2rem;" data-lang-id="Pantau progres pendaftaran, verifikasi dokumen, pembayaran, dan hasil seleksi Anda secara real-time." data-lang-en="Track your registration progress, document verification, payment, and selection results in real-time.">Pantau progres pendaftaran, verifikasi dokumen, pembayaran, dan hasil seleksi Anda secara real-time.</p>
    </div>
</section>

<!-- ==========================================
     2. FORM PENCARIAN (jika belum submit)
     ========================================== -->
<?php if (!$applicant): ?>
<section class="section" style="padding-top: 1rem;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background: var(--surface); border-radius: 24px; padding: 2.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;" data-lang-id="Masukkan Data Pendaftaran" data-lang-en="Enter Your Registration Data">Masukkan Data Pendaftaran</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Gunakan kode pendaftaran yang dikirim ke email Anda." data-lang-en="Use the registration code sent to your email.">Gunakan kode pendaftaran yang dikirim ke email Anda.</p>
            </div>
            
            <?php if ($error && $searched): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #dc2626; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.625rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <strong style="display: block; margin-bottom: 2px;" data-lang-id="Pencarian Gagal" data-lang-en="Search Failed">Pencarian Gagal</strong>
                        <span style="font-size: 0.875rem;"><?= sanitize($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="status-form">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; display: block;" data-lang-id="Kode Pendaftaran" data-lang-en="Registration Code">Kode Pendaftaran <span style="color: var(--danger);">*</span></label>
                    <div style="position: relative;">
                        <input type="text" name="kode_pendaftaran" class="form-input" style="padding-left: 2.75rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;" required placeholder="PMB-XXXX-XXXXX" value="<?= sanitize($_POST['kode_pendaftaran'] ?? '') ?>" autofocus>
                        <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; display: block;" data-lang-id="Email atau No. HP" data-lang-en="Email or Phone">Email atau No. HP <span style="color: var(--danger);">*</span></label>
                    <div style="position: relative;">
                        <input type="text" name="verifier" class="form-input" style="padding-left: 2.75rem;" required placeholder="email@example.com atau 0812xxxx" value="<?= sanitize($_POST['verifier'] ?? '') ?>">
                        <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.375rem;" data-lang-id="Digunakan untuk verifikasi keamanan data Anda." data-lang-en="Used to verify your data security.">Digunakan untuk verifikasi keamanan data Anda.</div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span data-lang-id="Cek Status Sekarang" data-lang-en="Check Status Now">Cek Status Sekarang</span>
                </button>
            </form>
            
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border); text-align: center;">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;" data-lang-id="Belum mendaftar?" data-lang-en="Haven't registered yet?">Belum mendaftar?</p>
                <a href="register.php" class="btn btn-outline" data-lang-id="Daftar Sekarang" data-lang-en="Register Now">Daftar Sekarang</a>
            </div>
        </div>
        
        <!-- Info Card -->
        <div style="margin-top: 2rem; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 16px; padding: 1.5rem;">
            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </div>
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem;" data-lang-id="Tips Keamanan" data-lang-en="Security Tips">Tips Keamanan</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;" data-lang-id="Jangan pernah membagikan kode pendaftaran dan email/HP Anda kepada siapapun. Data Anda terenkripsi dan aman." data-lang-en="Never share your registration code and email/phone with anyone. Your data is encrypted and secure.">Jangan pernah membagikan kode pendaftaran dan email/HP Anda kepada siapapun. Data Anda terenkripsi dan aman.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php else: ?>

<!-- ==========================================
     3. DASHBOARD STATUS (Setelah berhasil dicari)
     ========================================== -->

<?php if ($is_celebration): ?>
<!-- Celebration Confetti Container -->
<div id="confetti-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>
<?php endif; ?>

<section class="section" style="padding-top: 1rem;">
    <div style="max-width: 1100px; margin: 0 auto;">
        
        <!-- Header Card -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 24px; padding: 2.5rem; color: white; position: relative; overflow: hidden; margin-bottom: 2rem; box-shadow: 0 20px 60px rgba(30, 64, 175, 0.3);">
            <div style="position: absolute; top: -80px; right: -80px; width: 250px; height: 250px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -60px; left: -60px; width: 180px; height: 180px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
            
            <div style="position: relative; z-index: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="font-size: 0.75rem; opacity: 0.85; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; font-weight: 600;" data-lang-id="Kode Pendaftaran" data-lang-en="Registration Code">Kode Pendaftaran</div>
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: 'Courier New', monospace; letter-spacing: 1px;"><?= sanitize($applicant['kode_pendaftaran']) ?></div>
                    </div>
                    <span class="badge <?= statusBadgeClass($applicant['status_daftar']) ?>" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <?= sanitize($applicant['status_daftar']) ?>
                    </span>
                </div>
                
                <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;"><?= sanitize($applicant['nama_lengkap']) ?></h2>
                <div style="opacity: 0.9; font-size: 1.05rem; margin-bottom: 1rem;">
                    <?= sanitize($applicant['jenjang']) ?> <?= sanitize($applicant['nama_prodi']) ?>
                </div>
                
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.875rem; opacity: 0.9;">
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <?= sanitize($applicant['gelombang_nama']) ?> • <?= sanitize($applicant['tahun_akademik']) ?>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <?= sanitize($applicant['nama_jalur'] ?? '-') ?>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?= formatTanggal($applicant['created_at'], $lang) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Progress Timeline -->
        <div style="background: var(--surface); border-radius: 20px; padding: 2rem; border: 1px solid var(--border); margin-bottom: 2rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.75rem;" data-lang-id="Progress Pendaftaran" data-lang-en="Application Progress">Progress Pendaftaran</h3>
            <?php
            $stage = getProgressStage($applicant);
            $steps = [
                ['id' => 'daftar', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>', 'label_id' => 'Pendaftaran', 'label_en' => 'Registration'],
                ['id' => 'dokumen', 'icon' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>', 'label_id' => 'Upload Dokumen', 'label_en' => 'Upload Docs'],
                ['id' => 'verifikasi', 'icon' => '<polyline points="20 6 9 17 4 12"/>', 'label_id' => 'Verifikasi', 'label_en' => 'Verification'],
                ['id' => 'bayar', 'icon' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>', 'label_id' => 'Pembayaran', 'label_en' => 'Payment'],
                ['id' => 'seleksi', 'icon' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>', 'label_id' => 'Seleksi', 'label_en' => 'Selection'],
                ['id' => 'hasil', 'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', 'label_id' => 'Hasil', 'label_en' => 'Result'],
            ];
            ?>
            <div style="display: grid; grid-template-columns: repeat(<?= count($steps) ?>, 1fr); gap: 0; position: relative;">
                <?php foreach ($steps as $i => $step): 
                    $isCompleted = $stage > $i;
                    $isCurrent = $stage === $i;
                    $bgColor = $isCompleted ? 'var(--success)' : ($isCurrent ? 'var(--primary)' : 'var(--bg)');
                    $textColor = ($isCompleted || $isCurrent) ? 'white' : 'var(--text-muted)';
                    $borderColor = ($isCompleted || $isCurrent) ? 'transparent' : 'var(--border)';
                ?>
                <div style="text-align: center; position: relative;">
                    <?php if ($i > 0): ?>
                    <div style="position: absolute; top: 20px; left: -50%; right: 50%; height: 2px; background: <?= $isCompleted ? 'var(--success)' : 'var(--border)' ?>; z-index: 0;"></div>
                    <?php endif; ?>
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: <?= $bgColor ?>; border: 2px solid <?= $borderColor ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; color: <?= $textColor ?>; position: relative; z-index: 1; transition: all 0.3s;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $step['icon'] ?></svg>
                    </div>
                    <div style="font-size: 0.75rem; font-weight: <?= $isCurrent ? '700' : '600' ?>; color: <?= $isCurrent ? 'var(--primary)' : ($isCompleted ? 'var(--text)' : 'var(--text-muted)') ?>;" data-lang-id="<?= $step['label_id'] ?>" data-lang-en="<?= $step['label_en'] ?>"><?= $lang === 'en' ? $step['label_en'] : $step['label_id'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Info Grid: 2 Kolom -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;" class="status-grid">
            
            <!-- Status Dokumen -->
            <div style="background: var(--surface); border-radius: 20px; padding: 1.75rem; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0;" data-lang-id="Status Dokumen" data-lang-en="Document Status">Status Dokumen</h3>
                </div>
                
                <?php if (empty($applicant['documents'])): ?>
                    <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.3; margin-bottom: 0.75rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p style="font-size: 0.875rem;" data-lang-id="Belum ada dokumen yang diupload." data-lang-en="No documents uploaded yet.">Belum ada dokumen yang diupload.</p>
                        <a href="register.php?step=2&kode=<?= urlencode($applicant['kode_pendaftaran']) ?>" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;" data-lang-id="Upload Dokumen" data-lang-en="Upload Documents">Upload Dokumen</a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($applicant['documents'] as $doc): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.875rem; background: var(--bg); border-radius: 12px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span style="font-weight: 600; font-size: 0.875rem; text-transform: capitalize;"><?= sanitize(str_replace('_', ' ', $doc['document_type'])) ?></span>
                            </div>
                            <span class="badge <?= statusBadgeClass($doc['verification_status']) ?>" style="font-size: 0.7rem;"><?= sanitize($doc['verification_status']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Status Pembayaran -->
            <div style="background: var(--surface); border-radius: 20px; padding: 1.75rem; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0;" data-lang-id="Status Pembayaran" data-lang-en="Payment Status">Status Pembayaran</h3>
                </div>
                
                <?php if (empty($applicant['payment'])): ?>
                    <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.3; margin-bottom: 0.75rem;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <p style="font-size: 0.875rem;" data-lang-id="Belum ada transaksi pembayaran." data-lang-en="No payment transaction yet.">Belum ada transaksi pembayaran.</p>
                        <a href="payment.php?kode=<?= urlencode($applicant['kode_pendaftaran']) ?>" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;" data-lang-id="Bayar Sekarang" data-lang-en="Pay Now">Bayar Sekarang</a>
                    </div>
                <?php else: 
                    $pay = $applicant['payment'];
                ?>
                    <div style="padding: 1.25rem; background: var(--bg); border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="Invoice" data-lang-en="Invoice">Invoice</div>
                                <div style="font-family: monospace; font-weight: 700; font-size: 0.9rem;"><?= sanitize($pay['invoice_number'] ?? '-') ?></div>
                            </div>
                            <span class="badge <?= statusBadgeClass($pay['status']) ?>" style="font-size: 0.75rem;"><?= sanitize($pay['status']) ?></span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;" data-lang-id="Jumlah" data-lang-en="Amount">Jumlah</div>
                                <div style="font-weight: 700;">Rp <?= number_format($pay['amount'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;" data-lang-id="Metode" data-lang-en="Method">Metode</div>
                                <div style="font-weight: 600;"><?= sanitize($pay['payment_method'] ?? '-') ?></div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <div style="color: var(--text-muted); font-size: 0.75rem;" data-lang-id="Tanggal" data-lang-en="Date">Tanggal</div>
                                <div style="font-weight: 600;"><?= formatTanggal($pay['payment_date'] ?? $pay['created_at'], $lang) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Catatan Admin (jika ada) -->
        <?php if (!empty($applicant['catatan_admin'])): ?>
        <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                <div style="width: 36px; height: 36px; background: #f59e0b; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div style="flex: 1;">
                    <h4 style="font-weight: 700; margin-bottom: 0.25rem; color: #b45309;" data-lang-id="Catatan dari Tim Admisi" data-lang-en="Notes from Admission Team">Catatan dari Tim Admisi</h4>
                    <p style="color: var(--text); line-height: 1.6; margin: 0; white-space: pre-line;"><?= sanitize($applicant['catatan_admin']) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div style="background: var(--surface); border-radius: 20px; padding: 2rem; border: 1px solid var(--border);">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;" data-lang-id="Tindakan Cepat" data-lang-en="Quick Actions">Tindakan Cepat</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                
                <?php if (in_array($applicant['status_daftar'], ['Lulus', 'Registrasi Ulang', 'Diverifikasi'])): ?>
                <a href="kartu-peserta.php?kode=<?= urlencode($applicant['kode_pendaftaran']) ?>" class="btn btn-primary" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span data-lang-id="Download Kartu Peserta" data-lang-en="Download Participant Card">Download Kartu Peserta</span>
                </a>
                <?php endif; ?>
                
                <?php if (in_array($applicant['status_daftar'], ['Draft', 'Pending'])): ?>
                <a href="register.php?step=2&kode=<?= urlencode($applicant['kode_pendaftaran']) ?>" class="btn btn-outline" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span data-lang-id="Upload Dokumen" data-lang-en="Upload Documents">Upload Dokumen</span>
                </a>
                <?php endif; ?>
                
                <?php if (empty($applicant['payment']) || $applicant['payment']['status'] === 'Belum Bayar'): ?>
                <a href="payment.php?kode=<?= urlencode($applicant['kode_pendaftaran']) ?>" class="btn btn-outline" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span data-lang-id="Bayar Biaya Pendaftaran" data-lang-en="Pay Registration Fee">Bayar Biaya Pendaftaran</span>
                </a>
                <?php endif; ?>
                
                <a href="https://wa.me/<?= sanitize(get_setting($pdo, 'contact_wa', $lang)) ?>?text=<?= urlencode($lang === 'en' ? "Hello, I want to ask about my registration status: " . $applicant['kode_pendaftaran'] : "Halo, saya ingin bertanya tentang status pendaftaran saya: " . $applicant['kode_pendaftaran']) ?>" target="_blank" class="btn btn-outline" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    <span data-lang-id="Hubungi Admin" data-lang-en="Contact Admin">Hubungi Admin</span>
                </a>
                
                <button onclick="window.print()" class="btn btn-outline" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span data-lang-id="Cetak Halaman" data-lang-en="Print Page">Cetak Halaman</span>
                </button>
                
                <a href="cek-status.php" class="btn btn-secondary" style="padding: 1rem; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    <span data-lang-id="Cek Kode Lain" data-lang-en="Check Another Code">Cek Kode Lain</span>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    @media (max-width: 768px) {
        .status-grid { grid-template-columns: 1fr !important; }
    }
    @media print {
        header, .sidebar, footer, .btn, [onclick] { display: none !important; }
        body { background: white !important; }
    }
</style>

<?php if ($is_celebration): ?>
<script>
// Simple Confetti Effect
(function createConfetti() {
    const container = document.getElementById('confetti-container');
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
    const total = 100;
    
    for (let i = 0; i < total; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `
            position: absolute;
            width: ${Math.random() * 10 + 5}px;
            height: ${Math.random() * 10 + 5}px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            left: ${Math.random() * 100}%;
            top: -20px;
            border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
            animation: fall ${Math.random() * 3 + 3}s linear ${Math.random() * 2}s infinite;
            opacity: 0.9;
        `;
        container.appendChild(confetti);
    }
    
    // Stop confetti after 8 seconds
    setTimeout(() => container.style.opacity = '0', 8000);
    setTimeout(() => container.remove(), 9000);
    
    // Add keyframe animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fall {
            to { 
                transform: translateY(100vh) rotate(${Math.random() * 720}deg); 
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
})();

// Show celebration toast
setTimeout(() => {
    if (typeof showToast === 'function') {
        showToast(
            '<?= $lang === 'en' ? '🎉 Congratulations!' : '🎉 Selamat!' ?>',
            '<?= $lang === 'en' ? 'You have been accepted! Please download your participant card.' : 'Anda telah diterima! Silakan download kartu peserta Anda.' ?>',
            'success'
        );
    }
}, 500);
</script>
<?php endif; ?>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>