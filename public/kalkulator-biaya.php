<?php
$page_title = "Kalkulator Biaya Kuliah";
require_once 'includes/header.php';

// Ambil semua prodi aktif dengan biaya
$stmt_prodi = $pdo->query("
    SELECT id, kode_prodi, nama_prodi, jenjang, sistem_kuliah, 
           biaya_spp, biaya_pangkal, 
           CASE 
               WHEN jenjang = 'S1' THEN 8 
               WHEN jenjang = 'D3' THEN 6 
               WHEN jenjang = 'S2' THEN 4 
               WHEN jenjang = 'Prof' THEN 4 
               ELSE 8 
           END as default_semester
    FROM prodi 
    WHERE is_active = 1 
    ORDER BY FIELD(jenjang, 'S2', 'S1', 'Prof', 'D3'), nama_prodi ASC
");
$prodi_list = $stmt_prodi->fetchAll();

// Ambil jalur pendaftaran dengan biaya tambahan/diskon
$stmt_jalur = $pdo->query("SELECT * FROM jalur_pendaftaran WHERE is_active = 1 ORDER BY sort_order ASC");
$jalur_list = $stmt_jalur->fetchAll();

// Ambil biaya pendaftaran dari settings
$biaya_pendaftaran = (float)get_setting($pdo, 'registration_fee', 'id');
?>

<!-- ==========================================
     HERO SECTION - KALKULATOR
     ========================================== -->
<section style="background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); color: white; padding: 4rem 2rem; text-align: center; margin-bottom: 3rem;">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.2);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="12" y1="10" x2="14" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="12" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="10" y2="18"/><line x1="12" y1="18" x2="16" y2="18"/></svg>
            <span data-lang-id="Transparansi Biaya 100%" data-lang-en="100% Cost Transparency">Transparansi Biaya 100%</span>
        </div>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -0.5px;" data-lang-id="Kalkulator Biaya Kuliah" data-lang-en="Tuition Fee Calculator">Kalkulator Biaya Kuliah</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.6;" data-lang-id="Hitung estimasi total biaya kuliah Anda secara instan. Transparan, akurat, dan tanpa biaya tersembunyi." data-lang-en="Calculate your total tuition cost instantly. Transparent, accurate, and no hidden fees.">Hitung estimasi total biaya kuliah Anda secara instan. Transparan, akurat, dan tanpa biaya tersembunyi.</p>
    </div>
</section>

<!-- ==========================================
     KALKULATOR SECTION
     ========================================== -->
<div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem 4rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;" class="calculator-grid">
        
        <!-- KIRI: INPUT FORM -->
        <div class="calculator-input">
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 2rem; position: sticky; top: 100px;">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <span data-lang-id="Isi Data Kuliah" data-lang-en="Fill Study Details">Isi Data Kuliah</span>
                </h2>
                
                <!-- Step 1: Pilih Program Studi -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;" data-lang-id="1. Pilih Program Studi" data-lang-en="1. Select Study Program">1. Pilih Program Studi</label>
                    <select id="kalk-prodi" class="form-select" style="font-size: 1rem; padding: 0.875rem;">
                        <option value="" data-lang-id="-- Pilih Program Studi --" data-lang-en="-- Select Program --">-- Pilih Program Studi --</option>
                        <?php foreach($prodi_list as $p): ?>
                            <option value="<?= $p['id'] ?>" 
                                data-spp="<?= $p['biaya_spp'] ?>" 
                                data-pangkal="<?= $p['biaya_pangkal'] ?>"
                                data-semester="<?= $p['default_semester'] ?>"
                                data-jenjang="<?= $p['jenjang'] ?>">
                                <?= sanitize($p['jenjang'] . ' - ' . $p['nama_prodi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="prodi-info" style="margin-top: 0.75rem; padding: 0.875rem; background: var(--bg); border-radius: 10px; display: none; font-size: 0.875rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="color: var(--text-muted);" data-lang-id="SPP per Semester" data-lang-en="Tuition per Semester">SPP per Semester:</span>
                            <strong id="info-spp">Rp 0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="color: var(--text-muted);" data-lang-id="Biaya Pangkal" data-lang-en="Initial Fee">Biaya Pangkal:</span>
                            <strong id="info-pangkal">Rp 0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);" data-lang-id="Estimasi Lama Studi" data-lang-en="Study Duration">Estimasi Lama Studi:</span>
                            <strong id="info-semester">0 semester</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Pilih Jalur Pendaftaran -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;" data-lang-id="2. Pilih Jalur Pendaftaran" data-lang-en="2. Select Registration Path">2. Pilih Jalur Pendaftaran</label>
                    <select id="kalk-jalur" class="form-select" style="font-size: 1rem; padding: 0.875rem;">
                        <?php foreach($jalur_list as $j): 
                            $biaya_tambahan = (float)$j['biaya_tambahan'];
                            $label_diskon = '';
                            if ($biaya_tambahan < 0) {
                                $label_diskon = ' (Diskon ' . number_format(abs($biaya_tambahan), 0, ',', '.') . ')';
                            } elseif ($biaya_tambahan > 0) {
                                $label_diskon = ' (+ Rp ' . number_format($biaya_tambahan, 0, ',', '.') . ')';
                            }
                        ?>
                            <option value="<?= $j['id'] ?>" data-biaya="<?= $biaya_tambahan ?>">
                                <?= sanitize($j['nama_jalur']) ?><?= $label_diskon ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: rgba(16, 185, 129, 0.08); border-radius: 6px; border-left: 3px solid var(--success);">
                        <strong data-lang-id="💡 Tips:" data-lang-en="💡 Tip:">💡 Tips:</strong>
                        <span data-lang-id=" Jalur Beasiswa Prestasi bisa menghemat hingga Rp 100.000!" data-lang-en=" Merit Scholarship path can save you up to Rp 100,000!"> Jalur Beasiswa Prestasi bisa menghemat hingga Rp 100.000!</span>
                    </div>
                </div>
                
                <!-- Step 3: Lama Studi (Custom) -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;" data-lang-id="3. Lama Studi (Semester)" data-lang-en="3. Study Duration (Semesters)">3. Lama Studi (Semester)</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <button type="button" onclick="adjustSemester(-1)" style="width: 44px; height: 44px; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 1.25rem; font-weight: 700; color: var(--text);">−</button>
                        <input type="number" id="kalk-semester" min="1" max="14" value="8" class="form-input" style="text-align: center; font-size: 1.1rem; font-weight: 700; flex: 1;">
                        <button type="button" onclick="adjustSemester(1)" style="width: 44px; height: 44px; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 1.25rem; font-weight: 700; color: var(--text);">+</button>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;" data-lang-id="Rata-rata mahasiswa lulus dalam 8 semester (4 tahun)" data-lang-en="Average students graduate in 8 semesters (4 years)">Rata-rata mahasiswa lulus dalam 8 semester (4 tahun)</div>
                </div>
                
                <!-- Step 4: Biaya Hidup (Optional) -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="kalk-hidup-check" onchange="toggleLivingCost()">
                        <span data-lang-id="4. Sertakan Biaya Hidup Bulanan (Opsional)" data-lang-en="4. Include Monthly Living Cost (Optional)">4. Sertakan Biaya Hidup Bulanan (Opsional)</span>
                    </label>
                    <div id="living-cost-wrap" style="display: none; margin-top: 0.75rem;">
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 600;">Rp</span>
                            <input type="number" id="kalk-hidup" value="1500000" min="0" step="100000" class="form-input" style="padding-left: 3rem; font-size: 1rem;" oninput="calculateAll()">
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;" data-lang-id="Estimasi biaya kos, makan, transportasi per bulan" data-lang-en="Estimate for boarding, food, transport per month">Estimasi biaya kos, makan, transportasi per bulan</div>
                    </div>
                </div>
                
                <!-- Reset Button -->
                <button type="button" onclick="resetCalculator()" style="width: 100%; padding: 0.75rem; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; color: var(--text-muted); font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.background='var(--danger)'; this.style.color='white'; this.style.borderColor='var(--danger)'" onmouseout="this.style.background='var(--bg)'; this.style.color='var(--text-muted)'; this.style.borderColor='var(--border)'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span data-lang-id="Reset Kalkulator" data-lang-en="Reset Calculator">Reset Kalkulator</span>
                </button>
            </div>
        </div>
        
        <!-- KANAN: HASIL PERHITUNGAN -->
        <div class="calculator-result">
            <!-- Empty State -->
            <div id="result-empty" style="background: var(--surface); border: 2px dashed var(--border); border-radius: 20px; padding: 4rem 2rem; text-align: center;">
                <div style="width: 100px; height: 100px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/></svg>
                </div>
                <h3 style="margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Pilih Program Studi Terlebih Dahulu" data-lang-en="Select Study Program First">Pilih Program Studi Terlebih Dahulu</h3>
                <p style="color: var(--text-muted); max-width: 300px; margin: 0 auto;" data-lang-id="Hasil perhitungan akan muncul otomatis di sini setelah Anda memilih prodi." data-lang-en="Calculation result will appear here automatically after you select a program.">Hasil perhitungan akan muncul otomatis di sini setelah Anda memilih prodi.</p>
            </div>
            
            <!-- Result Card (Hidden by default) -->
            <div id="result-card" style="display: none;">
                <!-- Grand Total Card -->
                <div style="background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); color: white; border-radius: 20px; padding: 2rem; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.375rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
                            <span data-lang-id="Total Biaya Kuliah" data-lang-en="Total Tuition Cost">Total Biaya Kuliah</span>
                        </div>
                        <div id="total-display" style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">Rp 0</div>
                        <div style="font-size: 0.9rem; opacity: 0.85;" id="total-desc" data-lang-id="Pilih program studi untuk melihat estimasi" data-lang-en="Select a program to see estimate">Pilih program studi untuk melihat estimasi</div>
                    </div>
                </div>
                
                <!-- Detail Breakdown -->
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 2rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span data-lang-id="Rincian Biaya" data-lang-en="Cost Breakdown">Rincian Biaya</span>
                    </h3>
                    
                    <div id="breakdown-list" style="display: flex; flex-direction: column; gap: 0.875rem;">
                        <!-- Filled by JS -->
                    </div>
                    
                    <div style="border-top: 2px dashed var(--border); margin-top: 1.5rem; padding-top: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 600; color: var(--text-muted);" data-lang-id="Total SPP" data-lang-en="Total Tuition">Total SPP</span>
                            <strong id="breakdown-total-spp" style="font-size: 1rem;">Rp 0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 600; color: var(--text-muted);" data-lang-id="Total Keseluruhan" data-lang-en="Grand Total">Total Keseluruhan</span>
                            <strong id="breakdown-grand" style="font-size: 1.1rem; color: var(--primary);">Rp 0</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Cicilan Simulation -->
                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0.03) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--success); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 700;" data-lang-id="Simulasi Cicilan Bulanan" data-lang-en="Monthly Installment Simulation">Simulasi Cicilan Bulanan</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);" data-lang-id="Jika dibagi rata per bulan selama masa studi" data-lang-en="If divided evenly per month during study period">Jika dibagi rata per bulan selama masa studi</p>
                        </div>
                    </div>
                    <div style="background: white; border-radius: 12px; padding: 1rem; text-align: center; border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="Per Bulan" data-lang-en="Per Month">Per Bulan</div>
                        <div id="monthly-installment" style="font-size: 1.75rem; font-weight: 800; color: var(--success);">Rp 0</div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <button onclick="printPDF()" style="padding: 0.875rem; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: var(--text); font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='var(--surface)'; this.style.color='var(--text)'; this.style.borderColor='var(--border)'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span data-lang-id="Export PDF" data-lang-en="Export PDF">Export PDF</span>
                    </button>
                    <a href="register.php" id="btn-daftar" style="padding: 0.875rem; background: var(--primary); border: 1px solid var(--primary); border-radius: 12px; color: white; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 64, 175, 0.35)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(30, 64, 175, 0.25)'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <span data-lang-id="Daftar Sekarang" data-lang-en="Register Now">Daftar Sekarang</span>
                    </a>
                </div>
                
                <!-- Disclaimer -->
                <div style="margin-top: 1.5rem; padding: 1rem; background: var(--bg); border-radius: 10px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
                    <strong data-lang-id="ℹ️ Catatan:" data-lang-en="ℹ️ Note:">ℹ️ Catatan:</strong>
                    <span data-lang-id=" Perhitungan ini hanya estimasi. Biaya aktual dapat berubah sesuai kebijakan kampus. Hubungi admin untuk informasi terbaru." data-lang-en=" This calculation is only an estimate. Actual costs may vary according to campus policy. Contact admin for latest information."> Perhitungan ini hanya estimasi. Biaya aktual dapat berubah sesuai kebijakan kampus. Hubungi admin untuk informasi terbaru.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Template (Hidden) -->
<div id="print-area" style="display: none;">
    <div style="padding: 2rem; font-family: 'Inter', sans-serif;">
        <div style="text-align: center; border-bottom: 3px solid #1e3a8a; padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <h1 style="color: #1e3a8a; margin: 0 0 0.5rem; font-size: 1.5rem;">ESTIMASI BIAYA KULIAH</h1>
            <p style="margin: 0; color: #64748b; font-size: 0.9rem;" id="print-site-name"><?= sanitize($site_name) ?></p>
        </div>
        
        <div style="margin-bottom: 2rem;">
            <h3 style="margin: 0 0 1rem; color: #1e3a8a; font-size: 1.1rem;">Detail Pilihan</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="padding: 0.5rem 0; color: #64748b; width: 40%;">Program Studi</td><td style="padding: 0.5rem 0; font-weight: 600;" id="print-prodi">-</td></tr>
                <tr><td style="padding: 0.5rem 0; color: #64748b;">Jalur Pendaftaran</td><td style="padding: 0.5rem 0; font-weight: 600;" id="print-jalur">-</td></tr>
                <tr><td style="padding: 0.5rem 0; color: #64748b;">Lama Studi</td><td style="padding: 0.5rem 0; font-weight: 600;" id="print-lama">-</td></tr>
                <tr><td style="padding: 0.5rem 0; color: #64748b;">Tanggal Cetak</td><td style="padding: 0.5rem 0; font-weight: 600;" id="print-tanggal">-</td></tr>
            </table>
        </div>
        
        <div style="margin-bottom: 2rem;">
            <h3 style="margin: 0 0 1rem; color: #1e3a8a; font-size: 1.1rem;">Rincian Biaya</h3>
            <table style="width: 100%; border-collapse: collapse;" id="print-breakdown"></table>
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #1e3a8a; display: flex; justify-content: space-between;">
                <strong style="font-size: 1.1rem; color: #1e3a8a;">TOTAL</strong>
                <strong style="font-size: 1.25rem; color: #1e3a8a;" id="print-total">Rp 0</strong>
            </div>
        </div>
        
        <div style="text-align: center; padding: 1rem; background: #f1f5f9; border-radius: 8px; font-size: 0.8rem; color: #64748b;">
            <p style="margin: 0;">Dokumen ini adalah estimasi resmi dari sistem PMB.</p>
            <p style="margin: 0.25rem 0 0;">Untuk informasi lebih lanjut: <?= sanitize($contact_phone ?: '+62 851-7427-0172') ?></p>
        </div>
    </div>
</div>

<style>
    /* Responsive Calculator */
    @media (max-width: 1024px) {
        .calculator-grid { grid-template-columns: 1fr !important; }
        .calculator-input > div { position: static !important; }
    }
    
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { 
            display: block !important; 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%;
        }
        @page { margin: 1cm; size: A4; }
    }
</style>

<script>
const formatRupiah = (n) => 'Rp ' + (parseInt(n) || 0).toLocaleString('id-ID');

// Update prodi info when selected
document.getElementById('kalk-prodi').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const info = document.getElementById('prodi-info');
    
    if (selected.value) {
        const spp = selected.dataset.spp;
        const pangkal = selected.dataset.pangkal;
        const semester = selected.dataset.semester;
        
        document.getElementById('info-spp').textContent = formatRupiah(spp);
        document.getElementById('info-pangkal').textContent = formatRupiah(pangkal);
        document.getElementById('info-semester').textContent = semester + ' semester';
        
        document.getElementById('kalk-semester').value = semester;
        document.getElementById('btn-daftar').href = 'register.php?prodi=' + selected.value;
        
        info.style.display = 'block';
        document.getElementById('result-empty').style.display = 'none';
        document.getElementById('result-card').style.display = 'block';
    } else {
        info.style.display = 'none';
        document.getElementById('result-empty').style.display = 'block';
        document.getElementById('result-card').style.display = 'none';
    }
    
    calculateAll();
});

// Event listeners
document.getElementById('kalk-jalur').addEventListener('change', calculateAll);
document.getElementById('kalk-semester').addEventListener('input', calculateAll);

function adjustSemester(delta) {
    const input = document.getElementById('kalk-semester');
    const newVal = parseInt(input.value || 0) + delta;
    if (newVal >= 1 && newVal <= 14) {
        input.value = newVal;
        calculateAll();
    }
}

function toggleLivingCost() {
    const checked = document.getElementById('kalk-hidup-check').checked;
    document.getElementById('living-cost-wrap').style.display = checked ? 'block' : 'none';
    calculateAll();
}

function calculateAll() {
    const prodiSelect = document.getElementById('kalk-prodi');
    const selected = prodiSelect.options[prodiSelect.selectedIndex];
    
    if (!selected.value) return;
    
    const spp = parseFloat(selected.dataset.spp) || 0;
    const pangkal = parseFloat(selected.dataset.pangkal) || 0;
    const semester = parseInt(document.getElementById('kalk-semester').value) || 0;
    
    // Jalur biaya tambahan
    const jalurSelect = document.getElementById('kalk-jalur');
    const jalurSelected = jalurSelect.options[jalurSelect.selectedIndex];
    const jalurBiaya = parseFloat(jalurSelected.dataset.biaya) || 0;
    
    // Biaya hidup
    const hidup = document.getElementById('kalk-hidup-check').checked 
        ? (parseFloat(document.getElementById('kalk-hidup').value) || 0)
        : 0;
    
    // Biaya pendaftaran dari settings
    const pendaftaran = <?= $biaya_pendaftaran ?>;
    
    // Perhitungan
    const totalSPP = spp * semester;
    const totalHidup = hidup * semester * 6; // 6 bulan per semester
    const totalBiaya = pendaftaran + pangkal + jalurBiaya + totalSPP + totalHidup;
    
    // Update UI
    document.getElementById('total-display').textContent = formatRupiah(totalBiaya);
    document.getElementById('total-desc').textContent = 
        `${semester} semester (${semester / 2} tahun) • ${selected.text}`;
    
    // Breakdown
    const breakdownList = document.getElementById('breakdown-list');
    breakdownList.innerHTML = '';
    
    const items = [
        { label: 'Biaya Pendaftaran', value: pendaftaran, icon: '📝' },
        { label: 'Biaya Pangkal', value: pangkal, icon: '🏛️' },
        { label: `SPP (${semester} semester × ${formatRupiah(spp)})`, value: totalSPP, icon: '📚' },
        { label: 'Penyesuaian Jalur (' + jalurSelected.text.split('(')[0].trim() + ')', value: jalurBiaya, icon: '🎯' }
    ];
    
    if (hidup > 0) {
        items.push({ label: `Biaya Hidup (${semester * 6} bulan × ${formatRupiah(hidup)})`, value: totalHidup, icon: '🏠' });
    }
    
    items.forEach(item => {
        if (item.value !== 0) {
            const color = item.value < 0 ? 'var(--success)' : 'var(--text)';
            const div = document.createElement('div');
            div.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--bg); border-radius: 10px;';
            div.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.25rem;">${item.icon}</span>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">${item.label}</span>
                </div>
                <strong style="color: ${color}; font-size: 0.95rem;">${formatRupiah(Math.abs(item.value))}${item.value < 0 ? ' (Diskon)' : ''}</strong>
            `;
            breakdownList.appendChild(div);
        }
    });
    
    document.getElementById('breakdown-total-spp').textContent = formatRupiah(totalSPP);
    document.getElementById('breakdown-grand').textContent = formatRupiah(totalBiaya);
    
    // Cicilan bulanan
    const monthly = semester > 0 ? totalBiaya / (semester * 6) : 0;
    document.getElementById('monthly-installment').textContent = formatRupiah(monthly);
}

function resetCalculator() {
    document.getElementById('kalk-prodi').value = '';
    document.getElementById('kalk-jalur').selectedIndex = 0;
    document.getElementById('kalk-semester').value = 8;
    document.getElementById('kalk-hidup-check').checked = false;
    document.getElementById('kalk-hidup').value = 1500000;
    document.getElementById('prodi-info').style.display = 'none';
    document.getElementById('living-cost-wrap').style.display = 'none';
    document.getElementById('result-empty').style.display = 'block';
    document.getElementById('result-card').style.display = 'none';
}

function printPDF() {
    const prodiSelect = document.getElementById('kalk-prodi');
    const selected = prodiSelect.options[prodiSelect.selectedIndex];
    const jalurSelect = document.getElementById('kalk-jalur');
    const jalurSelected = jalurSelect.options[jalurSelect.selectedIndex];
    const semester = document.getElementById('kalk-semester').value;
    
    // Fill print area
    document.getElementById('print-prodi').textContent = selected.text;
    document.getElementById('print-jalur').textContent = jalurSelected.text;
    document.getElementById('print-lama').textContent = semester + ' semester (' + (semester / 2) + ' tahun)';
    document.getElementById('print-tanggal').textContent = new Date().toLocaleDateString('id-ID', { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
    });
    document.getElementById('print-total').textContent = document.getElementById('total-display').textContent;
    
    // Fill breakdown table
    const breakdownHTML = Array.from(document.getElementById('breakdown-list').children)
        .map(row => {
            const cells = row.children;
            return `<tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 0.75rem 0;">${cells[0].textContent.trim()}</td>
                <td style="padding: 0.75rem 0; text-align: right; font-weight: 600;">${cells[1].textContent.trim()}</td>
            </tr>`;
        }).join('');
    document.getElementById('print-breakdown').innerHTML = breakdownHTML;
    
    window.print();
}

// Auto-calculate if prodi passed via URL
const urlParams = new URLSearchParams(window.location.search);
const prodiId = urlParams.get('prodi');
if (prodiId) {
    const select = document.getElementById('kalk-prodi');
    select.value = prodiId;
    select.dispatchEvent(new Event('change'));
}
</script>

<?php require_once 'includes/footer.php'; ?>