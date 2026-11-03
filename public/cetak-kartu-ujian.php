<?php
require_once 'includes/header.php';

// 1. AMBIL DATA BERDASARKAN NOMOR PESERTA
$nomor_peserta = trim($_GET['nomor'] ?? '');
$exam = null;

if (!empty($nomor_peserta)) {
    $stmt = $pdo->prepare("
        SELECT es.*, 
               a.nama_lengkap, a.nik, a.tempat_lahir, a.tanggal_lahir, a.jenis_kelamin, a.email, a.no_hp,
               p.nama_prodi, p.jenjang,
               g.nama as gelombang_nama, g.tahun_akademik
        FROM exam_schedules es
        JOIN applicants a ON es.applicant_id = a.id
        LEFT JOIN prodi p ON a.prodi_id = p.id
        LEFT JOIN gelombang g ON es.gelombang_id = g.id
        WHERE es.nomor_peserta = ?
        LIMIT 1
    ");
    $stmt->execute([$nomor_peserta]);
    $exam = $stmt->fetch();
}

// Jika tidak ditemukan, tampilkan 404 sederhana
if (!$exam) {
    echo '<div style="text-align: center; padding: 5rem 2rem; color: var(--text-muted);">';
    echo '<h2 style="color: var(--danger);">Data Tidak Ditemukan</h2>';
    echo '<p>Nomor peserta tidak valid atau belum memiliki jadwal ujian.</p>';
    echo '<a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>';
    echo '</div>';
    require_once 'includes/footer.php';
    exit;
}

// Helper format tanggal
$tgl_lahir = date('d-m-Y', strtotime($exam['tanggal_lahir']));
$tgl_ujian = date('l, d F Y', strtotime($exam['tanggal_ujian']));
$waktu_ujian = date('H:i', strtotime($exam['waktu_ujian'])) . ' WITA';

// Generate QR Code URL (Berisi data JSON sederhana untuk verifikasi cepat)
$qr_data = urlencode(json_encode(['no' => $exam['nomor_peserta'], 'nama' => $exam['nama_lengkap'], 'prodi' => $exam['nama_prodi']]));
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data={$qr_data}";

// Ambil logo untuk dicetak
$logo_url = get_setting($pdo, 'logo_url', 'id');
$site_name = get_setting($pdo, 'site_name', 'id');
?>

<!-- Tombol Aksi (Akan hilang saat di-print) -->
<div class="no-print" style="max-width: 800px; margin: 2rem auto; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
    <a href="index.php" style="color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
    <button onclick="window.print()" class="btn btn-primary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak Kartu (Ctrl+P)
    </button>
</div>

<!-- KARTU UJIAN (Area yang akan di-print) -->
<div class="print-area">
    <div class="exam-card">
        <!-- Header Kartu -->
        <div class="card-header">
            <div class="header-logo">
                <img src="<?= get_public_image($logo_url) ?>" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="header-text">
                <h2><?= sanitize($site_name) ?></h2>
                <p>KARTU PESERTA UJIAN SELEKSI MASUK<br>Tahun Akademik <?= sanitize($exam['tahun_akademik']) ?></p>
            </div>
            <div class="header-qr">
                <img src="<?= $qr_code_url ?>" alt="QR Code">
            </div>
        </div>

        <!-- Foto & Info Utama -->
        <div class="card-body">
            <div class="photo-section">
                <div class="photo-box">
                    <!-- Jika ada foto di database, tampilkan. Jika tidak, pakai inisial -->
                    <?php if (!empty($exam['avatar_url'])): ?>
                        <img src="<?= get_public_image($exam['avatar_url']) ?>" alt="Foto" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <?php endif; ?>
                    <div class="photo-initials" style="<?= !empty($exam['avatar_url']) ? 'display:none;' : 'display:flex;' ?>">
                        <?= strtoupper(substr($exam['nama_lengkap'], 0, 1)) ?>
                    </div>
                </div>
                <div class="nomor-box">
                    <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Nomor Peserta</span>
                    <strong style="font-size: 1.5rem; color: var(--primary); letter-spacing: 2px;"><?= sanitize($exam['nomor_peserta']) ?></strong>
                </div>
            </div>

            <!-- Detail Peserta -->
            <div class="details-section">
                <div class="detail-row">
                    <span class="label">Nama Lengkap</span>
                    <span class="value"><?= sanitize($exam['nama_lengkap']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">NIK</span>
                    <span class="value"><?= sanitize($exam['nik'] ?: '-') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Program Studi</span>
                    <span class="value"><?= sanitize($exam['jenjang']) ?> - <?= sanitize($exam['nama_prodi']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Jenis Ujian</span>
                    <span class="value" style="font-weight: 700; color: var(--primary);"><?= sanitize($exam['jenis_ujian']) ?></span>
                </div>
            </div>

            <!-- Jadwal Ujian (Highlight) -->
            <div class="schedule-section">
                <div class="schedule-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <span class="sch-label">Tanggal</span>
                        <span class="sch-value"><?= $tgl_ujian ?></span>
                    </div>
                </div>
                <div class="schedule-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div>
                        <span class="sch-label">Waktu</span>
                        <span class="sch-value"><?= $waktu_ujian ?></span>
                    </div>
                </div>
                <div class="schedule-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <div>
                        <span class="sch-label">Ruang / Lokasi</span>
                        <span class="sch-value"><?= sanitize($exam['ruang_ujian']) ?> - <?= sanitize($exam['lokasi_ujian']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Kartu -->
        <div class="card-footer">
            <div class="instructions">
                <strong style="display: block; margin-bottom: 0.5rem;">PENTING:</strong>
                <ul>
                    <li>Kartu ini wajib dibawa saat mengikuti ujian.</li>
                    <li>Peserta hadir 30 menit sebelum ujian dimulai.</li>
                    <li>Gunakan pakaian rapi dan bersepatu.</li>
                </ul>
            </div>
            <div class="signature">
                <p>Palopo, <?= date('d F Y') ?></p>
                <p>Panitia PMB</p>
                <div class="signature-line"></div>
                <p style="font-weight: 700; text-decoration: underline;">( Tim Admisi )</p>
            </div>
        </div>
    </div>
</div>

<!-- CSS KHUSUS UNTUK KARTU & PRINT -->
<style>
    /* Tampilan Layar (Preview) */
    .print-area {
        display: flex;
        justify-content: center;
        padding: 2rem;
        background: #f8fafc;
    }
    .exam-card {
        width: 210mm; /* Lebar A5 Landscape */
        min-height: 148mm; /* Tinggi A5 */
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        font-family: 'Inter', sans-serif;
        color: #1e293b;
    }
    .card-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 4px solid #f59e0b;
    }
    .header-logo img { height: 60px; width: auto; background: white; padding: 5px; border-radius: 8px; }
    .header-text { flex: 1; text-align: center; padding: 0 1rem; }
    .header-text h2 { margin: 0; font-size: 1.25rem; font-weight: 800; letter-spacing: 0.5px; }
    .header-text p { margin: 0.25rem 0 0; font-size: 0.85rem; opacity: 0.9; font-weight: 500; }
    .header-qr img { height: 70px; width: 70px; background: white; padding: 4px; border-radius: 6px; }

    .card-body { padding: 1.5rem 2rem; flex: 1; display: flex; gap: 2rem; }
    .photo-section { display: flex; flex-direction: column; align-items: center; gap: 1rem; width: 140px; flex-shrink: 0; }
    .photo-box { width: 110px; height: 140px; border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }
    .photo-initials { font-size: 3rem; font-weight: 800; color: #1e3a8a; }
    .nomor-box { text-align: center; background: #eff6ff; padding: 0.5rem; border-radius: 6px; width: 100%; border: 1px dashed #3b82f6; }
    
    .details-section { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; justify-content: center; }
    .detail-row { display: flex; border-bottom: 1px dotted #cbd5e1; padding-bottom: 0.25rem; }
    .detail-row .label { width: 130px; font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .detail-row .value { flex: 1; font-size: 0.95rem; font-weight: 600; color: #0f172a; }

    .schedule-section { margin-top: 1.5rem; background: #f8fafc; border-radius: 8px; padding: 1rem; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; gap: 1rem; }
    .schedule-item { flex: 1; display: flex; align-items: center; gap: 0.75rem; }
    .schedule-item svg { color: #3b82f6; flex-shrink: 0; }
    .sch-label { display: block; font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .sch-value { display: block; font-size: 0.9rem; font-weight: 700; color: #0f172a; }

    .card-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: flex-end; }
    .instructions { font-size: 0.75rem; color: #475569; max-width: 60%; }
    .instructions ul { margin: 0.5rem 0 0; padding-left: 1.2rem; }
    .signature { text-align: center; font-size: 0.85rem; color: #0f172a; }
    .signature-line { height: 50px; border-bottom: 1px solid #0f172a; margin: 0.25rem 0; width: 150px; }

    /* CSS KHUSUS PRINT (Menyembunyikan UI, hanya mencetak kartu) */
    @media print {
        @page { size: A5 landscape; margin: 0; }
        body { background: white !important; margin: 0; padding: 0; }
        .no-print, nav.navbar, footer.footer, .floating-wa { display: none !important; }
        .print-area { padding: 0; background: white; }
        .exam-card { 
            box-shadow: none; 
            border: none; 
            border-radius: 0; 
            width: 100%; 
            height: 100vh; 
            min-height: auto;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>