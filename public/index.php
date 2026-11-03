<?php
$page_title = "Beranda";
require_once 'includes/header.php';

$hero_title = get_setting($pdo, 'hero_title', $lang);
$hero_subtitle = get_setting($pdo, 'hero_subtitle', $lang);
$hero_image = get_setting($pdo, 'hero_image', $lang);

// Ambil 6 Prodi Unggulan untuk ditampilkan
$stmt_featured = $pdo->query("SELECT * FROM prodi WHERE is_active = 1 ORDER BY FIELD(jenjang, 'S2', 'S1', 'Prof', 'D3'), nama_prodi ASC LIMIT 6");
$featured_prodi = $stmt_featured->fetchAll();

// Ambil SEMUA prodi untuk dropdown filter yang komprehensif
$stmt_all = $pdo->query("SELECT * FROM prodi WHERE is_active = 1 ORDER BY FIELD(jenjang, 'S2', 'S1', 'Prof', 'D3'), nama_prodi ASC");
$all_prodi = $stmt_all->fetchAll();
?>

<!-- ==========================================
     1. HERO SECTION (High Impact & Professional)
     ========================================== -->
<header class="hero">
    <div class="hero-content">
        <span class="hero-badge">
            <!-- SVG Graduation Cap -->
            <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 6px; color: var(--primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
            <span data-lang-id="PMB Gelombang 1 Telah Dibuka" data-lang-en="1st Wave Admission is Now Open">PMB Gelombang 1 Telah Dibuka</span>
        </span>
        
        <h1><?= sanitize($hero_title) ?></h1>
        <p><?= sanitize($hero_subtitle) ?></p>
        
        <div class="hero-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="prodi.php" class="btn btn-primary">
                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <span data-lang-id="Jelajahi Program Studi" data-lang-en="Explore Programs">Jelajahi Program Studi</span>
            </a>
            <a href="alur.php" class="btn btn-outline">
                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span data-lang-id="Panduan Pendaftaran" data-lang-en="Registration Guide">Panduan Pendaftaran</span>
            </a>
        </div>
        
        <!-- Mini Stats untuk Kredibilitas -->
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number">15+</span>
                <span class="stat-label" data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">10K+</span>
                <span class="stat-label" data-lang-id="Alumni Sukses" data-lang-en="Successful Alumni">Alumni Sukses</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">A</span>
                <span class="stat-label" data-lang-id="Akreditasi Unggul" data-lang-en="Excellent Accreditation">Akreditasi Unggul</span>
            </div>
        </div>
    </div>
    
    <div class="hero-image">
        <img src="<?= sanitize($hero_image) ?>" alt="Kampus UKJP" loading="eager">
        <div class="hero-image-float-card">
            <div class="float-icon" style="font-size: 2.5rem; line-height: 1;">
                <!-- SVG Trophy -->
                <svg style="width: 40px; height: 40px; color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem; color: var(--text);" data-lang-id="Kampus Terbaik" data-lang-en="Best Campus">Kampus Terbaik</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);" data-lang-id="Versi Wilayah 2026" data-lang-en="Regional Version 2026">Versi Wilayah 2026</span>
            </div>
        </div>
    </div>
</header>

<!-- ==========================================
     2. ADVANCED SEARCH WIDGET
     ========================================== -->
<div class="search-widget">
    <div class="form-group">
        <label data-lang-id="Pilih Jenjang" data-lang-en="Select Level">Pilih Jenjang</label>
        <select class="form-select" id="filter-jenjang">
            <option value="" data-lang-id="-- Semua Jenjang --" data-lang-en="-- All Levels --">-- Semua Jenjang --</option>
            <option value="S2">S2 - Strata 2</option>
            <option value="S1">S1 - Strata 1</option>
            <option value="Prof">Prof - Profesi</option>
            <option value="D3">D3 - Diploma 3</option>
        </select>
    </div>
    <div class="form-group">
        <label data-lang-id="Program Studi" data-lang-en="Study Program">Program Studi</label>
        <select class="form-select" id="filter-prodi">
            <option value="" data-lang-id="-- Semua Program Studi --" data-lang-en="-- All Programs --">-- Semua Program Studi --</option>
            <?php foreach($all_prodi as $p): ?>
                <option value="<?= $p['id'] ?>" data-jenjang="<?= $p['jenjang'] ?>" data-sistem="<?= $p['sistem_kuliah'] ?>">
                    <?= sanitize($p['jenjang'] . ' - ' . $p['nama_prodi']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label data-lang-id="Sistem Kuliah" data-lang-en="Study System">Sistem Kuliah</label>
        <select class="form-select" id="filter-sistem">
            <option value="" data-lang-id="-- Semua Sistem --" data-lang-en="-- All Systems --">-- Semua Sistem --</option>
            <option value="Reguler">Reguler</option>
            <option value="RPL Transfer">RPL Transfer</option>
            <option value="Progsus">Progsus</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="filterProdi()" style="height: 54px;">
        <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <span data-lang-id="Cari Jalur" data-lang-en="Search">Cari Jalur</span>
    </button>
</div>

<!-- ==========================================
     3. FEATURED PROGRAMS
     ========================================== -->
<section id="prodi" class="section">
    <div class="section-header">
        <h2 data-lang-id="Program Studi Unggulan" data-lang-en="Featured Study Programs">Program Studi Unggulan</h2>
        <p data-lang-id="Temukan jalur pendaftaran sesuai dengan minat dan bakatmu untuk masa depan yang gemilang." data-lang-en="Find the registration pathway according to your interests and talents for a brilliant future.">Temukan jalur pendaftaran sesuai dengan minat dan bakatmu untuk masa depan yang gemilang.</p>
    </div>
    
    <div class="prodi-grid" id="prodi-grid">
        <?php foreach($featured_prodi as $p): ?>
        <div class="prodi-card" data-jenjang="<?= $p['jenjang'] ?>" data-sistem="<?= $p['sistem_kuliah'] ?>">
            <div class="prodi-card-img">
                <span class="prodi-badge"><?= sanitize($p['jenjang']) ?></span>
                <img src="<?= sanitize($p['image_url']) ?>" alt="<?= sanitize($p['nama_prodi']) ?>" loading="lazy">
            </div>
            <div class="prodi-body">
                <h3><?= sanitize($p['nama_prodi']) ?></h3>
                <p><?= sanitize($lang === 'en' ? $p['description_en'] : $p['description_id']) ?></p>
                <div class="prodi-meta">
                    <span class="jalur-count">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <?= $p['jalur_count'] ?> <?= $lang === 'en' ? 'Pathways' : 'Jalur' ?>
                    </span>
                    <a href="register.php?prodi=<?= $p['id'] ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                        <?= $lang === 'en' ? 'Detail' : 'Detail' ?> 
                        <svg style="width: 14px; height: 14px; margin-left: 4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="section-footer-cta">
        <a href="prodi.php" class="btn btn-primary" style="padding: 1rem 2.5rem;">
            <span data-lang-id="Lihat Semua Program Studi" data-lang-en="View All Study Programs">Lihat Semua Program Studi</span>
            <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</section>

<!-- ==========================================
     4. WHY CHOOSE US (Keunggulan Kampus - Pure SVG)
     ========================================== -->
<section class="section" style="background: var(--surface); border-radius: 32px; border: 1px solid var(--border); margin: 4rem auto;">
    <div class="section-header">
        <h2 data-lang-id="Mengapa Memilih Kami?" data-lang-en="Why Choose Us?">Mengapa Memilih Kami?</h2>
    </div>
    <div class="feature-grid">
        <!-- Feature 1: Kurikulum -->
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(30, 64, 175, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: var(--primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <h3 data-lang-id="Kurikulum Berstandar Industri" data-lang-en="Industry-Standard Curriculum">Kurikulum Berstandar Industri</h3>
            <p data-lang-id="Materi pembelajaran dirancang bersama praktisi untuk memastikan lulusan siap kerja dan kompetitif." data-lang-en="Learning materials designed with practitioners to ensure graduates are job-ready and competitive.">Materi pembelajaran dirancang bersama praktisi untuk memastikan lulusan siap kerja dan kompetitif.</p>
        </div>
        
        <!-- Feature 2: Karir -->
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(245, 158, 11, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3 data-lang-id="Jaringan Karir Luas" data-lang-en="Extensive Career Network">Jaringan Karir Luas</h3>
            <p data-lang-id="Kerjasama strategis dengan ratusan perusahaan nasional dan multinasional untuk penyaluran kerja." data-lang-en="Strategic partnerships with hundreds of national and multinational companies for job placement.">Kerjasama strategis dengan ratusan perusahaan nasional dan multinasional untuk penyaluran kerja.</p>
        </div>
        
        <!-- Feature 3: Beasiswa -->
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(16, 185, 129, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: #10b981;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <h3 data-lang-id="Beasiswa Berprestasi" data-lang-en="Merit Scholarships">Beasiswa Berprestasi</h3>
            <p data-lang-id="Dukungan finansial penuh dan keringanan biaya bagi mahasiswa yang memiliki prestasi akademik." data-lang-en="Full financial support and fee waivers for students with outstanding academic achievements.">Dukungan finansial penuh dan keringanan biaya bagi mahasiswa yang memiliki prestasi akademik.</p>
        </div>
        
        <!-- Feature 4: Fasilitas -->
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(139, 92, 246, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: #8b5cf6;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
            </div>
            <h3 data-lang-id="Fasilitas Modern" data-lang-en="Modern Facilities">Fasilitas Modern</h3>
            <p data-lang-id="Laboratorium lengkap, perpustakaan digital, dan ruang kelas ber-AC yang mendukung kenyamanan." data-lang-en="Complete laboratories, digital libraries, and air-conditioned classrooms supporting learning comfort.">Laboratorium lengkap, perpustakaan digital, dan ruang kelas ber-AC yang mendukung kenyamanan.</p>
        </div>
    </div>
</section>

<!-- ==========================================
     5. FINAL CTA BANNER
     ========================================== -->
<section class="cta-banner">
    <div class="cta-content">
        <svg style="width: 48px; height: 48px; color: rgba(255,255,255,0.9); margin-bottom: 1.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <h2 data-lang-id="Siap Memulai Perjalanan Suksesmu?" data-lang-en="Ready to Start Your Successful Journey?">Siap Memulai Perjalanan Suksesmu?</h2>
        <p data-lang-id="Kuota terbatas. Daftarkan dirimu sekarang dan raih masa depan cemerlang bersama kami." data-lang-en="Limited quota. Register now and achieve a brilliant future with us.">Kuota terbatas. Daftarkan dirimu sekarang dan raih masa depan cemerlang bersama kami.</p>
        <a href="register.php" class="btn btn-primary" style="background: white; color: var(--primary); font-size: 1.1rem; padding: 1rem 2.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <span data-lang-id="Daftar Sekarang" data-lang-en="Register Now">Daftar Sekarang</span>
            <svg style="width: 20px; height: 20px; margin-left: 8px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<!-- ==========================================
     6. PAGE-SPECIFIC JAVASCRIPT
     ========================================== -->
<script>
    function filterProdi() {
        const jenjang = document.getElementById('filter-jenjang').value;
        const prodiId = document.getElementById('filter-prodi').value;
        const sistem = document.getElementById('filter-sistem').value;
        const cards = document.querySelectorAll('.prodi-card');
        
        let visibleCount = 0;

        cards.forEach((card, index) => {
            const matchJenjang = !jenjang || card.dataset.jenjang === jenjang;
            const matchProdi = !prodiId || card.querySelector('a').href.includes(`prodi=${prodiId}`);
            const matchSistem = !sistem || card.dataset.sistem === sistem;
            
            if (matchJenjang && matchProdi && matchSistem) {
                card.style.display = 'flex';
                visibleCount++;
                // Re-trigger staggered animation
                setTimeout(() => {
                    card.style.transitionDelay = `${(visibleCount - 1) * 0.08}s`;
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

        // Show "No results" message with SVG icon if visibleCount === 0
        const grid = document.getElementById('prodi-grid');
        let noResultMsg = grid.querySelector('.no-result');
        if (visibleCount === 0) {
            if (!noResultMsg) {
                noResultMsg = document.createElement('div');
                noResultMsg.className = 'no-result';
                noResultMsg.style.gridColumn = '1 / -1';
                noResultMsg.style.textAlign = 'center';
                noResultMsg.style.padding = '3rem';
                noResultMsg.style.color = 'var(--text-muted)';
                // Pure SVG, NO EMOJI
                noResultMsg.innerHTML = `
                    <svg style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="8" y1="8" x2="14" y2="14"/><line x1="14" y1="8" x2="8" y2="14"/>
                    </svg>
                    <p style="font-size: 1.1rem; font-weight: 600;" data-lang-id="Tidak ada program studi yang cocok dengan filter." data-lang-en="No study programs match your filter.">Tidak ada program studi yang cocok dengan filter.</p>
                `;
                grid.appendChild(noResultMsg);
            }
        } else {
            if (noResultMsg) noResultMsg.remove();
        }
    }
</script>