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

// 1. Ambil 3 Berita Terbaru (Hanya yang Published)
$stmt_news = $pdo->query("SELECT slug, title_id, title_en, thumbnail_url, category, published_at, created_at, content_id, content_en 
    FROM news 
    WHERE is_published = 1 AND (published_at IS NULL OR published_at <= NOW()) 
    ORDER BY published_at DESC, created_at DESC 
    LIMIT 3");
$latest_news = $stmt_news->fetchAll();

// 2. Ambil 4 Testimoni Alumni Terbaik (Hanya yang Aktif)
$stmt_testi = $pdo->query("SELECT name, program, year, photo_url, content_id, content_en, rating 
    FROM testimonials 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, created_at DESC 
    LIMIT 4");
$testimonials = $stmt_testi->fetchAll();

// Helper untuk excerpt berita
function getExcerpt($text, $length = 120) {
    $text = strip_tags($text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return (mb_strlen($text) > $length) ? mb_substr($text, 0, $length) . '...' : $text;
}
?>

<!-- ==========================================
     1. HERO SECTION
     ========================================== -->
<header class="hero">
    <div class="hero-content">
        <span class="hero-badge">
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
        <!-- PERBAIKAN: Menggunakan get_public_image -->
        <img src="<?= get_public_image($hero_image) ?>" alt="Kampus UKJP" loading="eager">
        <div class="hero-image-float-card">
            <div class="float-icon" style="font-size: 2.5rem; line-height: 1;">
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
                <!-- PERBAIKAN: Menggunakan get_public_image -->
                <img src="<?= get_public_image($p['image_url']) ?>" alt="<?= sanitize($p['nama_prodi']) ?>" loading="lazy">
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
     4. LATEST NEWS
     ========================================== -->
<section class="section" style="background: var(--bg); border-radius: 32px; margin: 4rem auto;">
    <div class="section-header">
        <h2 data-lang-id="Berita & Pengumuman Terbaru" data-lang-en="Latest News & Announcements">Berita & Pengumuman Terbaru</h2>
        <p data-lang-id="Ikuti informasi terkini seputar jadwal, seleksi, dan kegiatan kampus." data-lang-en="Stay updated with the latest information about schedules, selection, and campus activities.">Ikuti informasi terkini seputar jadwal, seleksi, dan kegiatan kampus.</p>
    </div>
    
    <div class="prodi-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        <?php if (count($latest_news) > 0): ?>
            <?php foreach ($latest_news as $n): 
                $title = ($lang === 'en' && !empty($n['title_en'])) ? $n['title_en'] : $n['title_id'];
                $content = ($lang === 'en' && !empty($n['content_en'])) ? $n['content_en'] : $n['content_id'];
                $date = $n['published_at'] ?: $n['created_at'];
                $dateFormatted = date('d M Y', strtotime($date));
            ?>
            <article class="prodi-card" style="display: flex; flex-direction: column; overflow: hidden;">
                <a href="detail-berita.php?slug=<?= urlencode($n['slug']) ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%;">
                    <div style="position: relative; height: 200px; overflow: hidden;">
                        <span style="position: absolute; top: 1rem; left: 1rem; z-index: 2; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); color: var(--primary); padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(255,255,255,0.5);"><?= sanitize($n['category'] ?? 'Umum') ?></span>
                        <!-- PERBAIKAN: Menggunakan get_public_image -->
                        <img src="<?= get_public_image($n['thumbnail_url']) ?>" alt="<?= sanitize($title) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E'">
                    </div>
                    <div class="prodi-body" style="flex-grow: 1; display: flex; flex-direction: column;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?= $dateFormatted ?>
                        </div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4;"><?= sanitize($title) ?></h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1rem; flex-grow: 1;"><?= sanitize(getExcerpt($content)) ?></p>
                        <span style="color: var(--primary); font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem; margin-top: auto;">
                            <span data-lang-id="Baca Selengkapnya" data-lang-en="Read More">Baca Selengkapnya</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                <p data-lang-id="Belum ada berita terbaru." data-lang-en="No latest news available yet.">Belum ada berita terbaru.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (count($latest_news) >= 3): ?>
    <div class="section-footer-cta" style="margin-top: 3rem;">
        <a href="berita.php" class="btn btn-outline" style="padding: 0.875rem 2rem;">
            <span data-lang-id="Lihat Semua Berita" data-lang-en="View All News">Lihat Semua Berita</span>
        </a>
    </div>
    <?php endif; ?>
</section>

<!-- ==========================================
     5. TESTIMONIALS
     ========================================== -->
<section class="section">
    <div class="section-header">
        <h2 data-lang-id="Kata Mereka Tentang Kami" data-lang-en="What They Say About Us">Kata Mereka Tentang Kami</h2>
        <p data-lang-id="Dengarkan langsung pengalaman dan kesuksesan para alumni kami." data-lang-en="Listen directly to the experiences and successes of our alumni.">Dengarkan langsung pengalaman dan kesuksesan para alumni kami.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
        <?php if (count($testimonials) > 0): ?>
            <?php foreach ($testimonials as $t): 
                $content = ($lang === 'en' && !empty($t['content_en'])) ? $t['content_en'] : $t['content_id'];
                $stars = str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']);
            ?>
            <div class="prodi-card" style="padding: 2rem; display: flex; flex-direction: column; position: relative;">
                <svg style="position: absolute; top: 1.5rem; right: 1.5rem; width: 32px; height: 32px; color: var(--primary); opacity: 0.15;" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                
                <div style="color: var(--accent); font-size: 1.1rem; margin-bottom: 1rem; letter-spacing: 2px;"><?= $stars ?></div>
                
                <p style="font-size: 0.95rem; color: var(--text); line-height: 1.7; margin-bottom: 1.5rem; flex-grow: 1; font-style: italic;">"<?= sanitize($content) ?>"</p>
                
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: auto; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                    <?php if (!empty($t['photo_url'])): ?>
                        <!-- PERBAIKAN: Menggunakan get_public_image -->
                        <img src="<?= get_public_image($t['photo_url']) ?>" alt="<?= sanitize($t['name']) ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);" onerror="this.style.display='none'">
                    <?php else: ?>
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;">
                            <?= strtoupper(substr($t['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text);"><?= sanitize($t['name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= sanitize($t['program'] ?: 'Alumni') ?> 
                            <?php if (!empty($t['year'])): ?> • <?= sanitize($t['year']) ?> <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                <p data-lang-id="Belum ada testimoni." data-lang-en="No testimonials available yet.">Belum ada testimoni.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ==========================================
     6. WHY CHOOSE US
     ========================================== -->
<section class="section" style="background: var(--surface); border-radius: 32px; border: 1px solid var(--border); margin: 4rem auto;">
    <div class="section-header">
        <h2 data-lang-id="Mengapa Memilih Kami?" data-lang-en="Why Choose Us?">Mengapa Memilih Kami?</h2>
    </div>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(30, 64, 175, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: var(--primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <h3 data-lang-id="Kurikulum Berstandar Industri" data-lang-en="Industry-Standard Curriculum">Kurikulum Berstandar Industri</h3>
            <p data-lang-id="Materi pembelajaran dirancang bersama praktisi untuk memastikan lulusan siap kerja dan kompetitif." data-lang-en="Learning materials designed with practitioners to ensure graduates are job-ready and competitive.">Materi pembelajaran dirancang bersama praktisi untuk memastikan lulusan siap kerja dan kompetitif.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(245, 158, 11, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3 data-lang-id="Jaringan Karir Luas" data-lang-en="Extensive Career Network">Jaringan Karir Luas</h3>
            <p data-lang-id="Kerjasama strategis dengan ratusan perusahaan nasional dan multinasional untuk penyaluran kerja." data-lang-en="Strategic partnerships with hundreds of national and multinational companies for job placement.">Kerjasama strategis dengan ratusan perusahaan nasional dan multinasional untuk penyaluran kerja.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(16, 185, 129, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 32px; height: 32px; color: #10b981;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <h3 data-lang-id="Beasiswa Berprestasi" data-lang-en="Merit Scholarships">Beasiswa Berprestasi</h3>
            <p data-lang-id="Dukungan finansial penuh dan keringanan biaya bagi mahasiswa yang memiliki prestasi akademik." data-lang-en="Full financial support and fee waivers for students with outstanding academic achievements.">Dukungan finansial penuh dan keringanan biaya bagi mahasiswa yang memiliki prestasi akademik.</p>
        </div>
        
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
     7. FINAL CTA BANNER
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