<?php
$page_title = "Program Studi";
require_once 'includes/header.php';

// Ambil semua prodi aktif dengan statistik pendaftar
$stmt = $pdo->query("SELECT p.*, 
    (SELECT COUNT(*) FROM applicants WHERE prodi_id = p.id) AS total_pendaftar,
    (SELECT COUNT(*) FROM applicants WHERE prodi_id = p.id AND status_daftar IN ('Diverifikasi', 'Lulus', 'Registrasi Ulang')) AS pendaftar_diterima
    FROM prodi p 
    WHERE p.is_active = 1 
    ORDER BY p.sort_order ASC, FIELD(p.jenjang, 'S2', 'S1', 'Prof', 'D3'), p.nama_prodi ASC");
$all_prodi = $stmt->fetchAll();

// Statistik
$total_prodi = count($all_prodi);
$total_pendaftar = array_sum(array_column($all_prodi, 'total_pendaftar'));
$top_prodi = $all_prodi[0]['nama_prodi'] ?? '-';
if (count($all_prodi) > 0) {
    usort($all_prodi, fn($a, $b) => $b['total_pendaftar'] - $a['total_pendaftar']);
    $top_prodi = $all_prodi[0]['nama_prodi'];
    // Re-sort kembali ke urutan default
    usort($all_prodi, function($a, $b) {
        $order = ['S2' => 1, 'S1' => 2, 'Prof' => 3, 'D3' => 4];
        $jenjang_cmp = ($order[$a['jenjang']] ?? 5) <=> ($order[$b['jenjang']] ?? 5);
        if ($jenjang_cmp !== 0) return $jenjang_cmp;
        if ($a['sort_order'] !== $b['sort_order']) return $a['sort_order'] <=> $b['sort_order'];
        return strcmp($a['nama_prodi'], $b['nama_prodi']);
    });
}

// Helper format rupiah
function formatRupiah($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
}
?>

<!-- ==========================================
     1. MINI HERO (Page Header)
     ========================================== -->
<section style="padding: 10rem 2rem 4rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%); text-align: center;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Jelajahi Program Studi Kami" data-lang-en="Explore Our Study Programs">Jelajahi Program Studi Kami</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 2rem;" data-lang-id="Temukan program pendidikan yang sesuai dengan passion dan tujuan karir Anda di masa depan." data-lang-en="Find the educational program that matches your passion and future career goals.">Temukan program pendidikan yang sesuai dengan passion dan tujuan karir Anda di masa depan.</p>
        
        <!-- Quick Stats -->
        <div style="display: inline-flex; gap: 2rem; padding: 1rem 2rem; background: var(--surface); border-radius: 50px; border: 1px solid var(--border); box-shadow: var(--shadow); flex-wrap: wrap; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <span style="font-weight: 700;"><?= $total_prodi ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Prodi" data-lang-en="Programs">Prodi</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <span style="font-weight: 700;"><?= number_format($total_pendaftar) ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Pendaftar" data-lang-en="Applicants">Pendaftar</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span style="font-weight: 700;"><?= sanitize(substr($top_prodi, 0, 20)) ?><?= strlen($top_prodi) > 20 ? '...' : '' ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Terpopuler" data-lang-en="Most Popular">Terpopuler</span>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     2. SEARCH & FILTER BAR
     ========================================== -->
<section class="section" style="padding-top: 2rem; padding-bottom: 1rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Search Box -->
        <div style="position: relative; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            <svg style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-prodi" placeholder="Cari program studi..." style="width: 100%; padding: 1rem 1.25rem 1rem 3rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-size: 1rem; transition: all 0.3s ease; box-shadow: var(--shadow);" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(30, 64, 175, 0.1)'" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow)'" oninput="filterProdi()">
        </div>

        <!-- Filter Pills - Jenjang -->
        <div style="text-align: center; margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; font-weight: 600;" data-lang-id="Jenjang Pendidikan" data-lang-en="Education Level">Jenjang Pendidikan</div>
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;" id="filter-jenjang">
                <button class="filter-pill active" data-filter="all" onclick="setFilter('jenjang', 'all', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--primary); background: var(--primary); color: white; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;" data-lang-id="Semua" data-lang-en="All">Semua</button>
                <button class="filter-pill" data-filter="S2" onclick="setFilter('jenjang', 'S2', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;">S2</button>
                <button class="filter-pill" data-filter="S1" onclick="setFilter('jenjang', 'S1', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;">S1</button>
                <button class="filter-pill" data-filter="Prof" onclick="setFilter('jenjang', 'Prof', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;" data-lang-id="Profesi" data-lang-en="Professional">Profesi</button>
                <button class="filter-pill" data-filter="D3" onclick="setFilter('jenjang', 'D3', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;">D3</button>
            </div>
        </div>

        <!-- Filter Pills - Akreditasi -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; font-weight: 600;" data-lang-id="Akreditasi" data-lang-en="Accreditation">Akreditasi</div>
            <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;" id="filter-akreditasi">
                <button class="filter-pill-sm active" data-filter="all" onclick="setFilter('akreditasi', 'all', this)" style="padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid var(--primary); background: var(--primary); color: white; font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease;" data-lang-id="Semua" data-lang-en="All">Semua</button>
                <button class="filter-pill-sm" data-filter="Unggul" onclick="setFilter('akreditasi', 'Unggul', this)" style="padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease;">Unggul</button>
                <button class="filter-pill-sm" data-filter="A" onclick="setFilter('akreditasi', 'A', this)" style="padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease;">A</button>
                <button class="filter-pill-sm" data-filter="B" onclick="setFilter('akreditasi', 'B', this)" style="padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease;">B</button>
                <button class="filter-pill-sm" data-filter="Baik Sekali" onclick="setFilter('akreditasi', 'Baik Sekali', this)" style="padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease;">Baik Sekali</button>
            </div>
        </div>

        <!-- Results Count -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <span data-lang-id="Menampilkan" data-lang-en="Showing">Menampilkan</span> <strong id="visible-count"><?= count($all_prodi) ?></strong> <span data-lang-id="dari" data-lang-en="of">dari</span> <strong><?= count($all_prodi) ?></strong> <span data-lang-id="program studi" data-lang-en="study programs">program studi</span>
            </div>
            <button id="reset-filter" onclick="resetFilters()" style="display: none; background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 8px; transition: background 0.3s;" onmouseover="this.style.background='rgba(30, 64, 175, 0.05)'" onmouseout="this.style.background='none'" data-lang-id="Reset Filter" data-lang-en="Reset Filter">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Reset Filter
            </button>
        </div>

        <!-- ==========================================
             3. PROGRAMS GRID
             ========================================== -->
        <div class="prodi-grid" id="prodi-grid">
            <?php if (count($all_prodi) > 0): ?>
                <?php foreach($all_prodi as $p): 
                    // Badge akreditasi
                    $akred_class = match($p['akreditasi'] ?? '') {
                        'Unggul', 'A' => 'background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);',
                        'B', 'Baik Sekali' => 'background: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3);',
                        'C', 'Baik' => 'background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);',
                        default => 'background: var(--bg); color: var(--text-muted); border: 1px solid var(--border);'
                    };
                    
                    // Progress kuota
                    $kuota_pct = $p['kuota'] > 0 ? round(($p['total_pendaftar'] / $p['kuota']) * 100) : 0;
                    $kuota_color = $kuota_pct >= 90 ? '#ef4444' : ($kuota_pct >= 70 ? '#f59e0b' : '#10b981');
                    $sisa_kuota = max(0, $p['kuota'] - $p['total_pendaftar']);
                ?>
                <div class="prodi-card" data-jenjang="<?= $p['jenjang'] ?>" data-akreditasi="<?= $p['akreditasi'] ?>" data-nama="<?= strtolower($p['nama_prodi']) ?>">
                    <div class="prodi-card-img">
                        <span class="prodi-badge"><?= sanitize($p['jenjang']) ?></span>
                        <?php if ($p['total_pendaftar'] > 10): ?>
                        <span class="prodi-badge-hot" style="position: absolute; top: 1rem; right: 1rem; background: linear-gradient(135deg, #ef4444, #f59e0b); color: white; padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 0.25rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                            Hot
                        </span>
                        <?php endif; ?>
                        <img src="<?= get_public_image($p['image_url']) ?>" alt="<?= sanitize($p['nama_prodi']) ?>" loading="lazy" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%2364748b%22 font-family=%22sans-serif%22 font-size=%2220%22%3E<?= urlencode($p['nama_prodi']) ?>%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="prodi-body">
                        <!-- Akreditasi & Jenjang -->
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <span style="padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; <?= $akred_class ?>"><?= sanitize($p['akreditasi'] ?: '-') ?></span>
                            <?php if (!empty($p['gelar_lulusan'])): ?>
                            <span style="padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 600; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border);"><?= sanitize($p['gelar_lulusan']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 style="margin-bottom: 0.5rem;"><?= sanitize($p['nama_prodi']) ?></h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;"><?= sanitize($lang === 'en' ? $p['description_en'] : $p['description_id']) ?></p>
                        
                        <!-- Biaya Info -->
                        <div style="background: var(--bg); padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="SPP / Semester" data-lang-en="Tuition / Semester">SPP / Semester</span>
                                <strong style="color: var(--primary); font-size: 0.9rem;"><?= formatRupiah($p['biaya_spp']) ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="Biaya Pangkal" data-lang-en="Entry Fee">Biaya Pangkal</span>
                                <span style="color: var(--text); font-size: 0.85rem;"><?= formatRupiah($p['biaya_pangkal']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Kuota Progress -->
                        <?php if ($p['kuota'] > 0): ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.375rem;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="Sisa Kuota" data-lang-en="Remaining Quota">Sisa Kuota</span>
                                <span style="font-size: 0.75rem; font-weight: 600; color: <?= $kuota_color ?>;"><?= $sisa_kuota ?> / <?= $p['kuota'] ?></span>
                            </div>
                            <div style="width: 100%; height: 6px; background: var(--border); border-radius: 50px; overflow: hidden;">
                                <div style="width: <?= min($kuota_pct, 100) ?>%; height: 100%; background: <?= $kuota_color ?>; border-radius: 50px; transition: width 1s ease-out;"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="prodi-meta">
                            <span class="jalur-count">
                                <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <?= $p['jalur_count'] ?> <?= $lang === 'en' ? 'Pathways' : 'Jalur' ?>
                            </span>
                            <div style="display: flex; gap: 0.5rem;">
                                <button onclick="viewProdiDetail(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                    <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span data-lang-id="Detail" data-lang-en="Detail">Detail</span>
                                </button>
                                <a href="register.php?prodi=<?= $p['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                    <span data-lang-id="Daftar" data-lang-en="Register">Daftar</span>
                                    <svg style="width: 14px; height: 14px; margin-left: 4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State Fallback -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; color: var(--text-muted);">
                    <svg style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Belum Ada Program Studi" data-lang-en="No Programs Available Yet">Belum Ada Program Studi</h3>
                    <p data-lang-id="Silakan hubungi admin untuk informasi lebih lanjut." data-lang-en="Please contact the admin for further information.">Silakan hubungi admin untuk informasi lebih lanjut.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- No Result Message (Hidden by default) -->
        <div id="no-result" style="display: none; text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg style="width: 64px; height: 64px; margin-bottom: 1.5rem; opacity: 0.4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="8" x2="14" y2="14"/><line x1="14" y1="8" x2="8" y2="14"/></svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Tidak Ada Hasil" data-lang-en="No Results Found">Tidak Ada Hasil</h3>
            <p data-lang-id="Tidak ada program studi yang sesuai dengan filter Anda." data-lang-en="No study programs match your filter criteria.">Tidak ada program studi yang sesuai dengan filter Anda.</p>
            <button onclick="resetFilters()" class="btn btn-primary" style="margin-top: 1.5rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                <span data-lang-id="Reset Semua Filter" data-lang-en="Reset All Filters">Reset Semua Filter</span>
            </button>
        </div>
    </div>
</section>

<!-- ==========================================
     4. DETAIL MODAL
     ========================================== -->
<div id="detail-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 9999; overflow-y: auto; padding: 2rem;" onclick="if(event.target === this) closeDetailModal()">
    <div style="max-width: 800px; margin: 2rem auto; background: var(--surface); border-radius: 24px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.3); animation: modalSlideUp 0.4s ease;">
        <!-- Header Image -->
        <div style="position: relative; height: 250px; background: linear-gradient(135deg, var(--primary), var(--primary-light));">
            <img id="detail-image" src="" alt="" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;">
            <button onclick="closeDetailModal()" style="position: absolute; top: 1rem; right: 1rem; width: 40px; height: 40px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem 2rem; background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span id="detail-jenjang" style="background: white; color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">-</span>
                    <span id="detail-akreditasi" style="background: rgba(16, 185, 129, 0.9); color: white; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">-</span>
                </div>
                <h2 id="detail-nama" style="color: white; font-size: 1.75rem; font-weight: 800; margin: 0;">-</h2>
            </div>
        </div>
        
        <!-- Content -->
        <div style="padding: 2rem;">
            <!-- Info Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background: var(--bg); padding: 1rem; border-radius: 12px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="Gelar" data-lang-en="Degree">Gelar</div>
                    <div id="detail-gelar" style="font-weight: 700; font-size: 1.1rem; margin-top: 0.25rem;">-</div>
                </div>
                <div style="background: var(--bg); padding: 1rem; border-radius: 12px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="SPP / Semester" data-lang-en="Tuition / Semester">SPP / Semester</div>
                    <div id="detail-spp" style="font-weight: 700; font-size: 1rem; margin-top: 0.25rem; color: var(--primary);">-</div>
                </div>
                <div style="background: var(--bg); padding: 1rem; border-radius: 12px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="Biaya Pangkal" data-lang-en="Entry Fee">Biaya Pangkal</div>
                    <div id="detail-pangkal" style="font-weight: 700; font-size: 1rem; margin-top: 0.25rem;">-</div>
                </div>
                <div style="background: var(--bg); padding: 1rem; border-radius: 12px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" data-lang-id="Sisa Kuota" data-lang-en="Remaining Quota">Sisa Kuota</div>
                    <div id="detail-kuota" style="font-weight: 700; font-size: 1.1rem; margin-top: 0.25rem;">-</div>
                </div>
            </div>
            
            <!-- Description -->
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;" data-lang-id="Tentang Program Studi" data-lang-en="About This Program">Tentang Program Studi</h3>
                <p id="detail-desc" style="color: var(--text-muted); line-height: 1.7;">-</p>
            </div>
            
            <!-- Stats -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: var(--bg); border-radius: 12px;">
                    <div style="width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><span data-lang-id="Total Pendaftar" data-lang-en="Total Applicants">Total Pendaftar</span></div>
                        <div id="detail-pendaftar" style="font-weight: 700;">-</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: var(--bg); border-radius: 12px;">
                    <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><span data-lang-id="Diterima" data-lang-en="Accepted">Diterima</span></div>
                        <div id="detail-diterima" style="font-weight: 700;">-</div>
                    </div>
                </div>
            </div>
            
            <!-- CTA -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a id="detail-register" href="#" class="btn btn-primary" style="flex: 1; justify-content: center; padding: 1rem; font-size: 1rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    <span data-lang-id="Daftar Sekarang" data-lang-en="Register Now">Daftar Sekarang</span>
                </a>
                <a href="https://wa.me/<?= sanitize(get_setting($pdo, 'contact_wa', $lang)) ?>?text=Halo, saya ingin bertanya tentang program studi" target="_blank" class="btn btn-outline" style="padding: 1rem 1.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 0.5rem;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    <span data-lang-id="Tanya Admin" data-lang-en="Ask Admin">Tanya Admin</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .prodi-badge-hot {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    @media (max-width: 768px) {
        .prodi-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<!-- ==========================================
     5. PAGE-SPECIFIC JAVASCRIPT
     ========================================== -->
<script>
    // Filter state
    const filters = {
        jenjang: 'all',
        akreditasi: 'all',
        search: ''
    };
    
    function setFilter(type, value, btnElement) {
        filters[type] = value;
        
        // Update button styles
        const container = btnElement.parentElement;
        container.querySelectorAll('.filter-pill, .filter-pill-sm').forEach(pill => {
            pill.style.background = 'var(--surface)';
            pill.style.color = 'var(--text)';
            pill.style.borderColor = 'var(--border)';
        });
        btnElement.style.background = 'var(--primary)';
        btnElement.style.color = 'white';
        btnElement.style.borderColor = 'var(--primary)';
        
        filterProdi();
    }
    
    function filterProdi() {
        const cards = document.querySelectorAll('.prodi-card');
        const noResult = document.getElementById('no-result');
        const resetBtn = document.getElementById('reset-filter');
        const searchInput = document.getElementById('search-prodi');
        filters.search = searchInput.value.toLowerCase().trim();
        
        let visibleCount = 0;
        
        cards.forEach((card, index) => {
            const matchJenjang = filters.jenjang === 'all' || card.dataset.jenjang === filters.jenjang;
            const matchAkreditasi = filters.akreditasi === 'all' || card.dataset.akreditasi === filters.akreditasi;
            const matchSearch = filters.search === '' || card.dataset.nama.includes(filters.search);
            const match = matchJenjang && matchAkreditasi && matchSearch;
            
            if (match) {
                card.style.display = 'flex';
                visibleCount++;
                setTimeout(() => {
                    card.style.transitionDelay = `${(visibleCount - 1) * 0.05}s`;
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => { 
                    if(card.style.opacity === '0') card.style.display = 'none'; 
                }, 400);
            }
        });
        
        // Update visible count
        document.getElementById('visible-count').textContent = visibleCount;
        
        // Show/hide no result message
        if (visibleCount === 0) {
            noResult.style.display = 'block';
        } else {
            noResult.style.display = 'none';
        }
        
        // Show/hide reset button
        const hasFilter = filters.jenjang !== 'all' || filters.akreditasi !== 'all' || filters.search !== '';
        resetBtn.style.display = hasFilter ? 'inline-flex' : 'none';
    }
    
    function resetFilters() {
        filters.jenjang = 'all';
        filters.akreditasi = 'all';
        filters.search = '';
        
        document.getElementById('search-prodi').value = '';
        
        // Reset all filter buttons
        document.querySelectorAll('#filter-jenjang .filter-pill, #filter-akreditasi .filter-pill-sm').forEach((pill, i) => {
            if (i === 0 || pill.dataset.filter === 'all') {
                pill.style.background = 'var(--primary)';
                pill.style.color = 'white';
                pill.style.borderColor = 'var(--primary)';
            } else {
                pill.style.background = 'var(--surface)';
                pill.style.color = 'var(--text)';
                pill.style.borderColor = 'var(--border)';
            }
        });
        
        filterProdi();
    }
    
    function viewProdiDetail(data) {
        // PERBAIKAN: Menambahkan '../' jika URL gambar adalah path lokal
        let imgUrl = data.image_url || '';
        if (imgUrl && !imgUrl.startsWith('http')) {
            imgUrl = '../' + imgUrl;
        }
        document.getElementById('detail-image').src = imgUrl;
        
        document.getElementById('detail-jenjang').textContent = data.jenjang;
        document.getElementById('detail-akreditasi').textContent = data.akreditasi || '-';
        document.getElementById('detail-nama').textContent = data.nama_prodi;
        document.getElementById('detail-gelar').textContent = data.gelar_lulusan || '-';
        document.getElementById('detail-spp').textContent = 'Rp ' + parseInt(data.biaya_spp || 0).toLocaleString('id-ID');
        document.getElementById('detail-pangkal').textContent = 'Rp ' + parseInt(data.biaya_pangkal || 0).toLocaleString('id-ID');
        
        const sisaKuota = Math.max(0, (data.kuota || 0) - (data.total_pendaftar || 0));
        document.getElementById('detail-kuota').textContent = sisaKuota + ' / ' + (data.kuota || '-');
        document.getElementById('detail-desc').textContent = data.description_id || data.description_en || 'Belum ada deskripsi.';
        document.getElementById('detail-pendaftar').textContent = (data.total_pendaftar || 0) + ' pendaftar';
        document.getElementById('detail-diterima').textContent = (data.pendaftar_diterima || 0) + ' orang';
        document.getElementById('detail-register').href = 'register.php?prodi=' + data.id;
        
        document.getElementById('detail-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeDetailModal() {
        document.getElementById('detail-modal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Close modal on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDetailModal();
    });
</script>

<?php require_once 'includes/footer.php'; ?>