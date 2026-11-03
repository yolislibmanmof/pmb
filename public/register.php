<?php
$page_title = "Pendaftaran Mahasiswa Baru";
require_once 'includes/header.php';

// Ambil data master untuk dropdown
$gelombang_list = $pdo->query("SELECT * FROM gelombang WHERE is_open = 1 ORDER BY tanggal_mulai ASC")->fetchAll();
$jalur_list = $pdo->query("SELECT * FROM jalur_pendaftaran WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
$prodi_list = $pdo->query("SELECT id, kode_prodi, jenjang, nama_prodi, biaya_spp FROM prodi WHERE is_active = 1 ORDER BY FIELD(jenjang, 'S2', 'S1', 'Prof', 'D3'), nama_prodi ASC")->fetchAll();

// Pre-select dari URL
$selected_prodi = isset($_GET['prodi']) ? (int)$_GET['prodi'] : 0;
$selected_jalur = isset($_GET['jalur']) ? (int)$_GET['jalur'] : 0;

$success_data = null;
$error_message = '';

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = "Token keamanan tidak valid. Muat ulang halaman.";
    } else {
        // Sanitize semua input
        $data = [
            'nama_lengkap'   => sanitize($_POST['nama_lengkap'] ?? ''),
            'nik'            => sanitize($_POST['nik'] ?? ''),
            'tempat_lahir'   => sanitize($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir'  => sanitize($_POST['tanggal_lahir'] ?? ''),
            'jenis_kelamin'  => sanitize($_POST['jenis_kelamin'] ?? ''),
            'agama'          => sanitize($_POST['agama'] ?? ''),
            'alamat'         => sanitize($_POST['alamat'] ?? ''),
            'kota'           => sanitize($_POST['kota'] ?? ''),
            'provinsi'       => sanitize($_POST['provinsi'] ?? ''),
            'kode_pos'       => sanitize($_POST['kode_pos'] ?? ''),
            'email'          => sanitize($_POST['email'] ?? ''),
            'no_hp'          => sanitize($_POST['no_hp'] ?? ''),
            'no_wa'          => sanitize($_POST['no_wa'] ?? '') ?: sanitize($_POST['no_hp'] ?? ''),
            'asal_sekolah'   => sanitize($_POST['asal_sekolah'] ?? ''),
            'jurusan_sekolah'=> sanitize($_POST['jurusan_sekolah'] ?? ''),
            'tahun_lulus'    => sanitize($_POST['tahun_lulus'] ?? ''),
            'nilai_rapor'    => (float)($_POST['nilai_rapor'] ?? 0),
            'no_ijazah'      => sanitize($_POST['no_ijazah'] ?? ''),
            'nama_ayah'      => sanitize($_POST['nama_ayah'] ?? ''),
            'nama_ibu'       => sanitize($_POST['nama_ibu'] ?? ''),
            'pekerjaan_ayah' => sanitize($_POST['pekerjaan_ayah'] ?? ''),
            'pekerjaan_ibu'  => sanitize($_POST['pekerjaan_ibu'] ?? ''),
            'penghasilan_ortu'=> sanitize($_POST['penghasilan_ortu'] ?? ''),
            'gelombang_id'   => (int)($_POST['gelombang_id'] ?? 0) ?: null,
            'prodi_id'       => (int)($_POST['prodi_id'] ?? 0),
            'prodi_pilihan_2'=> (int)($_POST['prodi_pilihan_2'] ?? 0) ?: null,
            'jalur_id'       => (int)($_POST['jalur_id'] ?? 0) ?: null,
        ];
        
        // Validasi wajib
        $required = ['nama_lengkap', 'email', 'no_hp', 'prodi_id'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) $missing[] = $field;
        }
        
        if (!empty($missing)) {
            $error_message = "Mohon lengkapi semua field wajib (bertanda *).";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid.";
        } else {
            // Ambil nama jalur untuk kolom legacy
            $jalur_nama = 'Reguler';
            if ($data['jalur_id']) {
                $st = $pdo->prepare("SELECT nama_jalur FROM jalur_pendaftaran WHERE id = ?");
                $st->execute([$data['jalur_id']]);
                $jalur_nama = $st->fetchColumn() ?: 'Reguler';
            }
            $data['jalur_pendaftaran'] = $jalur_nama;
            
            try {
                $pdo->beginTransaction();
                
                // Generate kode pendaftaran unik: PMB-2027-00001
                $tahun = date('Y') + 1; // Tahun akademik berikutnya
                $kode = '';
                do {
                    $st = $pdo->query("SELECT COUNT(*) + 1 FROM applicants");
                    $seq = $st->fetchColumn();
                    $kode = 'PMB-' . $tahun . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
                    $check = $pdo->prepare("SELECT id FROM applicants WHERE kode_pendaftaran = ?");
                    $check->execute([$kode]);
                } while ($check->fetch());
                
                $stmt = $pdo->prepare("INSERT INTO applicants (
                    kode_pendaftaran, gelombang_id,
                    nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, agama,
                    alamat, kota, provinsi, kode_pos,
                    email, no_hp, no_wa,
                    asal_sekolah, jurusan_sekolah, tahun_lulus, nilai_rapor, no_ijazah,
                    nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, penghasilan_ortu,
                    prodi_id, prodi_pilihan_2, jalur_id, jalur_pendaftaran,
                    status_pembayaran, status_daftar
                ) VALUES (
                    ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    'Belum Bayar', 'Pending'
                )");
                
                $stmt->execute([
                    $kode, $data['gelombang_id'],
                    $data['nama_lengkap'], $data['nik'], $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], $data['agama'],
                    $data['alamat'], $data['kota'], $data['provinsi'], $data['kode_pos'],
                    $data['email'], $data['no_hp'], $data['no_wa'],
                    $data['asal_sekolah'], $data['jurusan_sekolah'], $data['tahun_lulus'], $data['nilai_rapor'], $data['no_ijazah'],
                    $data['nama_ayah'], $data['nama_ibu'], $data['pekerjaan_ayah'], $data['pekerjaan_ibu'], $data['penghasilan_ortu'],
                    $data['prodi_id'], $data['prodi_pilihan_2'], $data['jalur_id'], $data['jalur_pendaftaran'],
                ]);
                
                $applicant_id = $pdo->lastInsertId();
                
                // Buat invoice pembayaran pendaftaran otomatis
                $gelombang_biaya = 200000;
                if ($data['gelombang_id']) {
                    $st = $pdo->prepare("SELECT biaya_pendaftaran FROM gelombang WHERE id = ?");
                    $st->execute([$data['gelombang_id']]);
                    $gelombang_biaya = (float)$st->fetchColumn();
                }
                $invoice = 'INV-' . date('Ymd') . '-' . str_pad($applicant_id, 6, '0', STR_PAD_LEFT);
                
                $stmt_pay = $pdo->prepare("INSERT INTO payments (applicant_id, invoice_number, payment_type, amount, status) VALUES (?, ?, 'Pendaftaran', ?, 'Pending')");
                $stmt_pay->execute([$applicant_id, $invoice, $gelombang_biaya]);
                
                $pdo->commit();
                
                $success_data = [
                    'kode' => $kode,
                    'invoice' => $invoice,
                    'nama' => $data['nama_lengkap'],
                    'amount' => $gelombang_biaya,
                ];
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>

<?php if ($success_data): ?>
<!-- ==========================================
     SUCCESS STATE
     ========================================== -->
<section style="padding: 8rem 2rem 6rem; max-width: 700px; margin: 0 auto;">
    <div style="background: var(--surface); border-radius: 24px; border: 1px solid var(--border); box-shadow: var(--shadow-lg); overflow: hidden;">
        <div style="background: linear-gradient(135deg, var(--success), #059669); padding: 3rem 2rem; text-align: center; color: white;">
            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;" data-lang-id="Pendaftaran Berhasil!" data-lang-en="Registration Successful!">Pendaftaran Berhasil!</h1>
            <p style="opacity: 0.95;" data-lang-id="Simpan kode pendaftaran Anda di bawah ini" data-lang-en="Save your registration code below">Simpan kode pendaftaran Anda di bawah ini</p>
        </div>
        
        <div style="padding: 2.5rem;">
            <div style="background: var(--bg); border: 2px dashed var(--primary); border-radius: 16px; padding: 1.5rem; text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;" data-lang-id="Kode Pendaftaran" data-lang-en="Registration Code">Kode Pendaftaran</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary); letter-spacing: 2px; font-family: monospace;"><?= sanitize($success_data['kode']) ?></div>
            </div>
            
            <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg); border-radius: 12px;">
                    <span style="color: var(--text-muted);" data-lang-id="Nama Pendaftar" data-lang-en="Applicant Name">Nama Pendaftar</span>
                    <strong><?= sanitize($success_data['nama']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg); border-radius: 12px;">
                    <span style="color: var(--text-muted);" data-lang-id="No. Invoice" data-lang-en="Invoice No.">No. Invoice</span>
                    <strong style="font-family: monospace;"><?= sanitize($success_data['invoice']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg); border-radius: 12px;">
                    <span style="color: var(--text-muted);" data-lang-id="Total Tagihan" data-lang-en="Total Bill">Total Tagihan</span>
                    <strong style="color: var(--primary); font-size: 1.1rem;">Rp <?= number_format($success_data['amount'], 0, ',', '.') ?></strong>
                </div>
            </div>
            
            <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid var(--accent); padding: 1.25rem; border-radius: 8px; margin-bottom: 2rem;">
                <p style="font-size: 0.9rem; color: var(--text); line-height: 1.6;">
                    <strong data-lang-id="Langkah Selanjutnya:" data-lang-en="Next Steps:">Langkah Selanjutnya:</strong>
                    <span data-lang-id=" Lakukan pembayaran sesuai invoice, lalu unggah bukti transfer. Pantau status pendaftaran Anda secara berkala." data-lang-en=" Make payment according to the invoice, then upload the transfer proof. Monitor your registration status regularly."> Lakukan pembayaran sesuai invoice, lalu unggah bukti transfer. Pantau status pendaftaran Anda secara berkala.</span>
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button onclick="window.print()" class="btn btn-secondary" style="flex: 1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span data-lang-id="Cetak Bukti" data-lang-en="Print Proof">Cetak Bukti</span>
                </button>
                <a href="index.php" class="btn btn-primary" style="flex: 1; justify-content: center;">
                    <span data-lang-id="Kembali ke Beranda" data-lang-en="Back to Home">Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<!-- ==========================================
     REGISTRATION WIZARD (5 Steps)
     ========================================== -->
<section style="padding: 8rem 2rem 6rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.03) 0%, transparent 100%);">
    <div style="max-width: 1000px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.75rem;" data-lang-id="Formulir Pendaftaran" data-lang-en="Registration Form">Formulir Pendaftaran</h1>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;" data-lang-id="Lengkapi 5 langkah berikut dengan data yang benar sesuai dokumen resmi Anda." data-lang-en="Complete the following 5 steps with correct data according to your official documents.">Lengkapi 5 langkah berikut dengan data yang benar sesuai dokumen resmi Anda.</p>
        </div>

        <?php if ($error_message): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= sanitize($error_message) ?>
        </div>
        <?php endif; ?>

        <!-- Progress Indicator -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 3rem; position: relative;" id="progress-bar">
            <div style="position: absolute; top: 20px; left: 5%; right: 5%; height: 2px; background: var(--border); z-index: 0;"></div>
            <div id="progress-line" style="position: absolute; top: 20px; left: 5%; width: 0%; height: 2px; background: var(--primary); z-index: 0; transition: width 0.4s ease;"></div>
            
            <?php 
            $steps = [
                ['num' => 1, 'label_id' => 'Data Pribadi', 'label_en' => 'Personal'],
                ['num' => 2, 'label_id' => 'Kontak & Alamat', 'label_en' => 'Contact'],
                ['num' => 3, 'label_id' => 'Pendidikan', 'label_en' => 'Education'],
                ['num' => 4, 'label_id' => 'Orang Tua', 'label_en' => 'Parents'],
                ['num' => 5, 'label_id' => 'Pilihan & Konfirmasi', 'label_en' => 'Confirm'],
            ];
            foreach ($steps as $s): ?>
            <div class="step-indicator" data-step="<?= $s['num'] ?>" style="position: relative; z-index: 1; text-align: center; flex: 1;">
                <div style="width: 40px; height: 40px; background: var(--surface); color: var(--text-muted); border: 2px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 0.5rem; transition: all 0.3s;"><?= $s['num'] ?></div>
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);" data-lang-id="<?= $s['label_id'] ?>" data-lang-en="<?= $s['label_en'] ?>"><?= $s['label_id'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" id="reg-form" style="background: var(--surface); border-radius: 24px; border: 1px solid var(--border); padding: 2.5rem; box-shadow: var(--shadow);">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <!-- ========== STEP 1: DATA PRIBADI ========== -->
            <div class="form-step" data-step="1">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span data-lang-id="Data Pribadi" data-lang-en="Personal Data">Data Pribadi</span>
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-input" required placeholder="Sesuai KTP/Ijazah">
                    </div>
                    <div>
                        <label class="form-label">NIK (No. KTP)</label>
                        <input type="text" name="nik" class="form-input" maxlength="16" placeholder="16 digit NIK">
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin <span style="color: var(--danger);">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota kelahiran">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Agama</label>
                        <select name="agama" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option>Islam</option><option>Kristen</option><option>Katolik</option>
                            <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========== STEP 2: KONTAK & ALAMAT ========== -->
            <div class="form-step" data-step="2" style="display: none;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span data-lang-id="Kontak & Alamat" data-lang-en="Contact & Address">Kontak & Alamat</span>
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label class="form-label">Email Aktif <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email" class="form-input" required placeholder="nama@email.com">
                    </div>
                    <div>
                        <label class="form-label">No. HP / WhatsApp <span style="color: var(--danger);">*</span></label>
                        <input type="tel" name="no_hp" class="form-input" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-textarea" rows="2" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                    </div>
                    <div>
                        <label class="form-label">Kota / Kabupaten</label>
                        <input type="text" name="kota" class="form-input" placeholder="Contoh: Palopo">
                    </div>
                    <div>
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="provinsi" class="form-input" placeholder="Contoh: Sulawesi Selatan">
                    </div>
                    <div>
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="kode_pos" class="form-input" maxlength="5" placeholder="91921">
                    </div>
                </div>
            </div>

            <!-- ========== STEP 3: PENDIDIKAN ========== -->
            <div class="form-step" data-step="3" style="display: none;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <span data-lang-id="Riwayat Pendidikan" data-lang-en="Education History">Riwayat Pendidikan</span>
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Asal Sekolah (SMA/SMK/MA) <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="asal_sekolah" class="form-input" required placeholder="Nama sekolah lengkap">
                    </div>
                    <div>
                        <label class="form-label">Jurusan Sekolah</label>
                        <input type="text" name="jurusan_sekolah" class="form-input" placeholder="IPA / IPS / TKJ">
                    </div>
                    <div>
                        <label class="form-label">Tahun Lulus <span style="color: var(--danger);">*</span></label>
                        <select name="tahun_lulus" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php for ($y = date('Y') + 1; $y >= date('Y') - 10; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Nilai Rapor Rata-rata</label>
                        <input type="number" name="nilai_rapor" class="form-input" step="0.01" min="0" max="100" placeholder="Contoh: 85.50">
                    </div>
                    <div>
                        <label class="form-label">No. Ijazah / SKL</label>
                        <input type="text" name="no_ijazah" class="form-input" placeholder="Nomor ijazah">
                    </div>
                </div>
            </div>

            <!-- ========== STEP 4: ORANG TUA ========== -->
            <div class="form-step" data-step="4" style="display: none;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span data-lang-id="Data Orang Tua / Wali" data-lang-en="Parent / Guardian Data">Data Orang Tua / Wali</span>
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="form-input" placeholder="Nama lengkap ayah">
                    </div>
                    <div>
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="form-input" placeholder="Nama lengkap ibu">
                    </div>
                    <div>
                        <label class="form-label">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" class="form-input" placeholder="Contoh: Wiraswasta">
                    </div>
                    <div>
                        <label class="form-label">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" class="form-input" placeholder="Contoh: Ibu Rumah Tangga">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Penghasilan Orang Tua / Bulan</label>
                        <select name="penghasilan_ortu" class="form-select">
                            <option value="">-- Pilih Range --</option>
                            <option value="< 2 Juta">&lt; Rp 2 Juta</option>
                            <option value="2 - 5 Juta">Rp 2 - 5 Juta</option>
                            <option value="5 - 10 Juta">Rp 5 - 10 Juta</option>
                            <option value="> 10 Juta">&gt; Rp 10 Juta</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========== STEP 5: PILIHAN & KONFIRMASI ========== -->
            <div class="form-step" data-step="5" style="display: none;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span data-lang-id="Pilihan & Konfirmasi" data-lang-en="Choices & Confirmation">Pilihan & Konfirmasi</span>
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Gelombang Pendaftaran <span style="color: var(--danger);">*</span></label>
                        <select name="gelombang_id" class="form-select" required>
                            <option value="">-- Pilih Gelombang --</option>
                            <?php foreach ($gelombang_list as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= sanitize($g['nama']) ?> (<?= date('d M Y', strtotime($g['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($g['tanggal_selesai'])) ?>) - Rp <?= number_format($g['biaya_pendaftaran'], 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Pilihan Prodi 1 <span style="color: var(--danger);">*</span></label>
                        <select name="prodi_id" class="form-select" required>
                            <option value="">-- Pilih Prodi --</option>
                            <?php foreach ($prodi_list as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $selected_prodi == $p['id'] ? 'selected' : '' ?>><?= sanitize($p['jenjang'] . ' - ' . $p['nama_prodi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Pilihan Prodi 2 (Opsional)</label>
                        <select name="prodi_pilihan_2" class="form-select">
                            <option value="">-- Tidak Ada --</option>
                            <?php foreach ($prodi_list as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= sanitize($p['jenjang'] . ' - ' . $p['nama_prodi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Jalur Pendaftaran <span style="color: var(--danger);">*</span></label>
                        <select name="jalur_id" class="form-select" required>
                            <?php foreach ($jalur_list as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $selected_jalur == $j['id'] ? 'selected' : '' ?>><?= sanitize($j['nama_jalur']) ?> <?= $j['biaya_tambahan'] != 0 ? '(' . ($j['biaya_tambahan'] > 0 ? '+' : '-') . ' Rp ' . number_format(abs($j['biaya_tambahan']), 0, ',', '.') . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1; background: rgba(30, 64, 175, 0.05); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(30, 64, 175, 0.1);">
                        <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" required style="margin-top: 4px; width: 18px; height: 18px; accent-color: var(--primary);">
                            <span style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                                <span data-lang-id="Saya menyatakan data yang diisi adalah BENAR dan bersedia mematuhi seluruh " data-lang-en="I declare the data is TRUE and agree to comply with all ">Saya menyatakan data yang diisi adalah BENAR dan bersedia mematuhi seluruh </span>
                                <a href="alur.php" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;" data-lang-id="tata tertib pendaftaran" data-lang-en="registration regulations">tata tertib pendaftaran</a>.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div style="display: flex; justify-content: space-between; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;" onclick="changeStep(-1)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span data-lang-id="Kembali" data-lang-en="Back">Kembali</span>
                </button>
                <button type="button" id="next-btn" class="btn btn-primary" style="margin-left: auto;" onclick="changeStep(1)">
                    <span data-lang-id="Lanjut" data-lang-en="Next">Lanjut</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
                <button type="submit" id="submit-btn" class="btn btn-success" style="display: none; margin-left: auto;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span data-lang-id="Kirim Pendaftaran" data-lang-en="Submit Registration">Kirim Pendaftaran</span>
                </button>
            </div>
        </form>
    </div>
</section>
<?php endif; ?>

<style>
    .form-label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text); }
    @media (max-width: 768px) {
        .form-step > div { grid-template-columns: 1fr !important; }
        .step-indicator span { display: none; }
    }
    @media print {
        .navbar, .footer, .floating-wa { display: none !important; }
    }
</style>

<script>
let currentStep = 1;
const totalSteps = 5;

function changeStep(dir) {
    if (dir === 1) {
        const currentEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const inputs = currentEl.querySelectorAll('input[required], select[required], textarea[required]');
        let valid = true;
        inputs.forEach(inp => {
            if (!inp.value.trim() || (inp.type === 'checkbox' && !inp.checked)) {
                valid = false;
                inp.style.borderColor = 'var(--danger)';
                setTimeout(() => { inp.style.borderColor = 'var(--border)'; }, 2500);
            }
        });
        if (!valid) {
            if (typeof showToast === 'function') showToast('Perhatian', 'Lengkapi semua field wajib terlebih dahulu', 'warning');
            return;
        }
    }
    
    document.querySelector(`.form-step[data-step="${currentStep}"]`).style.display = 'none';
    currentStep += dir;
    document.querySelector(`.form-step[data-step="${currentStep}"]`).style.display = 'block';
    updateWizard();
}

function updateWizard() {
    const pct = ((currentStep - 1) / (totalSteps - 1)) * 90;
    document.getElementById('progress-line').style.width = pct + '%';
    
    document.querySelectorAll('.step-indicator').forEach(ind => {
        const step = parseInt(ind.dataset.step);
        const circle = ind.querySelector('div');
        const text = ind.querySelector('span');
        
        if (step < currentStep) {
            circle.style.background = 'var(--success)';
            circle.style.borderColor = 'var(--success)';
            circle.style.color = 'white';
            circle.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
            if (text) text.style.color = 'var(--success)';
        } else if (step === currentStep) {
            circle.style.background = 'var(--primary)';
            circle.style.borderColor = 'var(--primary)';
            circle.style.color = 'white';
            circle.innerHTML = step;
            if (text) text.style.color = 'var(--primary)';
        } else {
            circle.style.background = 'var(--surface)';
            circle.style.borderColor = 'var(--border)';
            circle.style.color = 'var(--text-muted)';
            circle.innerHTML = step;
            if (text) text.style.color = 'var(--text-muted)';
        }
    });
    
    document.getElementById('prev-btn').style.display = currentStep === 1 ? 'none' : 'inline-flex';
    document.getElementById('next-btn').style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
    document.getElementById('submit-btn').style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
    
    window.scrollTo({ top: 200, behavior: 'smooth' });
}
</script>

<?php require_once 'includes/footer.php'; ?>