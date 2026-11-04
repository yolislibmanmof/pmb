<?php
$page_title = "Virtual Campus Tour";
require_once 'includes/header.php';

// Ambil semua spot aktif
$stmt = $pdo->query("SELECT * FROM virtual_tour_spots WHERE is_active = 1 ORDER BY sort_order ASC");
$spots = $stmt->fetchAll();

// Spot pertama sebagai default
$first_spot = $spots[0] ?? null;

// Generate JSON untuk JavaScript
$spots_json = json_encode(array_map(function($s) use ($lang) {
    return [
        'id' => $s['id'],
        'title' => $lang === 'en' ? $s['title_en'] : $s['title_id'],
        'description' => $lang === 'en' ? ($s['description_en'] ?: $s['description_id']) : $s['description_id'],
        'image' => $s['image_url'],
        'hfov' => (int)$s['hfov'],
        'pitch' => (int)$s['pitch'],
        'yaw' => (int)$s['yaw'],
        'hotspots' => json_decode($s['hotspots_json'] ?? '[]', true) ?: [],
        'facilities' => json_decode($s['facilities_json'] ?? '[]', true) ?: []
    ];
}, $spots));
?>

<!-- Pannellum Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
<script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

<!-- ==========================================
     CINEMATIC HERO INTRO
     ========================================== -->
<section class="vt-hero">
    <div class="vt-hero-bg"></div>
    <div class="vt-hero-content">
        <div class="vt-hero-badge">
            <span class="vt-badge-dot"></span>
            <span data-lang-id="Pengalaman Imersif 360°" data-lang-en="Immersive 360° Experience">Pengalaman Imersif 360°</span>
        </div>
        <h1 class="vt-hero-title" data-lang-id="Jelajahi Kampus Kami" data-lang-en="Explore Our Campus">Jelajahi Kampus Kami</h1>
        <p class="vt-hero-subtitle" data-lang-id="Nikmati tur virtual 360° dan rasakan atmosfer kampus UKJP dari mana saja. Klik, drag, dan zoom untuk eksplorasi penuh." data-lang-en="Enjoy a 360° virtual tour and feel the UKJP campus atmosphere from anywhere. Click, drag, and zoom for full exploration.">Nikmati tur virtual 360° dan rasakan atmosfer kampus UKJP dari mana saja. Klik, drag, dan zoom untuk eksplorasi penuh.</p>
        
        <div class="vt-hero-stats">
            <div class="vt-stat">
                <div class="vt-stat-number"><?= count($spots) ?>+</div>
                <div class="vt-stat-label" data-lang-id="Lokasi Tur" data-lang-en="Tour Spots">Lokasi Tur</div>
            </div>
            <div class="vt-stat-divider"></div>
            <div class="vt-stat">
                <div class="vt-stat-number">360°</div>
                <div class="vt-stat-label" data-lang-id="Immersive View" data-lang-en="Immersive View">Immersive View</div>
            </div>
            <div class="vt-stat-divider"></div>
            <div class="vt-stat">
                <div class="vt-stat-number">HD</div>
                <div class="vt-stat-label" data-lang-id="Kualitas Foto" data-lang-en="Photo Quality">Kualitas Foto</div>
            </div>
        </div>
        
        <div class="vt-hero-cta">
            <button class="vt-btn-primary" onclick="document.getElementById('vt-viewer-section').scrollIntoView({behavior: 'smooth'})">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                <span data-lang-id="Mulai Tur Sekarang" data-lang-en="Start Tour Now">Mulai Tur Sekarang</span>
            </button>
            <button class="vt-btn-secondary" onclick="toggleFullscreen()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                <span data-lang-id="Mode Fullscreen" data-lang-en="Fullscreen Mode">Mode Fullscreen</span>
            </button>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="vt-scroll-indicator">
        <div class="vt-scroll-mouse">
            <div class="vt-scroll-wheel"></div>
        </div>
        <span data-lang-id="Scroll ke bawah" data-lang-en="Scroll down">Scroll ke bawah</span>
    </div>
</section>

<!-- ==========================================
     MAIN VIEWER SECTION
     ========================================== -->
<section class="vt-viewer-section" id="vt-viewer-section">
    <div class="vt-container">
        
        <!-- Viewer Area -->
        <div class="vt-viewer-wrap">
            <!-- Loading overlay -->
            <div class="vt-loader" id="vt-loader">
                <div class="vt-loader-ring"></div>
                <p data-lang-id="Memuat Tur Virtual..." data-lang-en="Loading Virtual Tour...">Memuat Tur Virtual...</p>
            </div>
            
            <!-- Pannellum Viewer -->
            <div id="vt-panorama" class="vt-panorama"></div>
            
            <!-- Top Overlay: Current Spot Info -->
            <div class="vt-top-overlay">
                <div class="vt-spot-info">
                    <div class="vt-spot-index">
                        <span id="vt-current-index">1</span>/<span id="vt-total-count"><?= count($spots) ?></span>
                    </div>
                    <div class="vt-spot-text">
                        <h3 id="vt-current-title"><?= $first_spot ? ($lang === 'en' ? $first_spot['title_en'] : $first_spot['title_id']) : '' ?></h3>
                        <p id="vt-current-desc"><?= $first_spot ? ($lang === 'en' ? $first_spot['description_en'] : $first_spot['description_id']) : '' ?></p>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="vt-controls">
                    <button class="vt-ctrl-btn" onclick="prevSpot()" title="Previous">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button class="vt-ctrl-btn" onclick="toggleFullscreen()" title="Fullscreen" id="vt-fs-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                    </button>
                    <button class="vt-ctrl-btn" onclick="nextSpot()" title="Next">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
            
            <!-- Instruction Hint (auto-hide) -->
            <div class="vt-hint" id="vt-hint">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M12 3v18"/></svg>
                <span data-lang-id="Drag untuk melihat sekeliling • Scroll untuk zoom" data-lang-en="Drag to look around • Scroll to zoom">Drag untuk melihat sekeliling • Scroll untuk zoom</span>
            </div>
            
            <!-- Progress bar -->
            <div class="vt-progress">
                <div class="vt-progress-fill" id="vt-progress-fill" style="width: <?= count($spots) > 0 ? (100 / count($spots)) : 0 ?>%"></div>
            </div>
        </div>
        
        <!-- Spot Selector (Thumbnail Carousel) -->
        <div class="vt-selector-wrap">
            <div class="vt-selector-header">
                <h3 data-lang-id="Pilih Lokasi" data-lang-en="Select Location">Pilih Lokasi</h3>
                <div class="vt-selector-count">
                    <span id="vt-selector-current">1</span> <?= $lang === 'en' ? 'of' : 'dari' ?> <?= count($spots) ?>
                </div>
            </div>
            
            <div class="vt-thumbnails" id="vt-thumbnails">
                <?php foreach($spots as $index => $spot): 
                    $title = $lang === 'en' ? $spot['title_en'] : $spot['title_id'];
                    $thumb = $spot['thumbnail_url'] ?: $spot['image_url'];
                ?>
                <div class="vt-thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" onclick="jumpToSpot(<?= $index ?>)">
                    <div class="vt-thumb-img" style="background-image: url('<?= sanitize($thumb) ?>')"></div>
                    <div class="vt-thumb-overlay">
                        <div class="vt-thumb-number"><?= $index + 1 ?></div>
                        <div class="vt-thumb-title"><?= sanitize($title) ?></div>
                        <svg class="vt-thumb-play" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Auto-tour toggle -->
            <div class="vt-auto-tour">
                <label class="vt-switch">
                    <input type="checkbox" id="vt-auto-play" onchange="toggleAutoTour()">
                    <span class="vt-slider"></span>
                </label>
                <span data-lang-id="Auto Tour (5 detik per lokasi)" data-lang-en="Auto Tour (5 sec per spot)">Auto Tour (5 detik per lokasi)</span>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     FEATURES SECTION
     ========================================== -->
<section class="vt-features">
    <div class="vt-container">
        <div class="vt-section-header">
            <h2 data-lang-id="Tips Menikmati Tur Virtual" data-lang-en="Virtual Tour Tips">Tips Menikmati Tur Virtual</h2>
        </div>
        
        <div class="vt-features-grid">
            <div class="vt-feature-card">
                <div class="vt-feature-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 9l7 7 7-7"/></svg>
                </div>
                <h3 data-lang-id="Klik & Drag" data-lang-en="Click & Drag">Klik & Drag</h3>
                <p data-lang-id="Klik dan drag mouse atau sentuh layar untuk melihat sekeliling 360 derajat" data-lang-en="Click and drag mouse or touch screen to look around 360 degrees">Klik dan drag mouse atau sentuh layar untuk melihat sekeliling 360 derajat</p>
            </div>
            
            <div class="vt-feature-card">
                <div class="vt-feature-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                </div>
                <h3 data-lang-id="Zoom In/Out" data-lang-en="Zoom In/Out">Zoom In/Out</h3>
                <p data-lang-id="Gunakan scroll mouse atau pinch pada layar sentuh untuk memperbesar atau memperkecil tampilan" data-lang-en="Use mouse scroll or pinch on touch screen to zoom in or out">Gunakan scroll mouse atau pinch pada layar sentuh untuk memperbesar atau memperkecil tampilan</p>
            </div>
            
            <div class="vt-feature-card">
                <div class="vt-feature-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                </div>
                <h3 data-lang-id="Hotspot Interaktif" data-lang-en="Interactive Hotspots">Hotspot Interaktif</h3>
                <p data-lang-id="Klik titik-titik putih di layar untuk berpindah lokasi atau melihat informasi detail" data-lang-en="Click white dots on screen to move between locations or see detailed info">Klik titik-titik putih di layar untuk berpindah lokasi atau melihat informasi detail</p>
            </div>
            
            <div class="vt-feature-card">
                <div class="vt-feature-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <h3 data-lang-id="Mode VR" data-lang-en="VR Mode">Mode VR</h3>
                <p data-lang-id="Untuk pengalaman terbaik, gunakan VR headset atau mode layar penuh pada perangkat Anda" data-lang-en="For best experience, use VR headset or fullscreen mode on your device">Untuk pengalaman terbaik, gunakan VR headset atau mode layar penuh pada perangkat Anda</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     CTA SECTION
     ========================================== -->
<section class="vt-cta-section">
    <div class="vt-container">
        <div class="vt-cta-card">
            <div class="vt-cta-content">
                <h2 data-lang-id="Tertarik Bergabung dengan Kami?" data-lang-en="Interested in Joining Us?">Tertarik Bergabung dengan Kami?</h2>
                <p data-lang-id="Jadilah bagian dari kampus inovatif dengan fasilitas modern. Daftarkan diri Anda sekarang!" data-lang-en="Be part of an innovative campus with modern facilities. Register now!">Jadilah bagian dari kampus inovatif dengan fasilitas modern. Daftarkan diri Anda sekarang!</p>
                <div class="vt-cta-buttons">
                    <a href="register.php" class="vt-btn-primary">
                        <span data-lang-id="Daftar Sekarang" data-lang-en="Register Now">Daftar Sekarang</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a href="kontak.php" class="vt-btn-outline">
                        <span data-lang-id="Hubungi Kami" data-lang-en="Contact Us">Hubungi Kami</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ==========================================
   VIRTUAL TOUR - CINEMATIC STYLES
   ========================================== */

:root {
    --vt-primary: #1e3a8a;
    --vt-accent: #3b82f6;
    --vt-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
}

/* HERO SECTION */
.vt-hero {
    position: relative;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 4rem 2rem;
    text-align: center;
    color: white;
}

.vt-hero-bg {
    position: absolute;
    inset: 0;
    background: var(--vt-gradient);
    z-index: -2;
}

.vt-hero-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255,255,255,0.08) 0%, transparent 50%);
    animation: vt-float 20s ease-in-out infinite;
}

.vt-hero-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
    background-size: 40px 40px;
    z-index: -1;
}

@keyframes vt-float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-20px, 20px) rotate(180deg); }
}

.vt-hero-content {
    max-width: 900px;
    z-index: 1;
    animation: vt-fade-up 0.8s ease-out;
}

@keyframes vt-fade-up {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.vt-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 2rem;
    letter-spacing: 0.5px;
}

.vt-badge-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: vt-pulse 2s ease-in-out infinite;
}

@keyframes vt-pulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    50% { opacity: 0.8; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
}

.vt-hero-title {
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 800;
    margin: 0 0 1.5rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    background: linear-gradient(180deg, #fff 0%, rgba(255,255,255,0.8) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.vt-hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.25rem);
    max-width: 700px;
    margin: 0 auto 3rem;
    opacity: 0.9;
    line-height: 1.6;
}

.vt-hero-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.vt-stat {
    text-align: center;
}

.vt-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 0.5rem;
    background: linear-gradient(180deg, #fff 0%, rgba(255,255,255,0.7) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.vt-stat-label {
    font-size: 0.85rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.vt-stat-divider {
    width: 1px;
    background: rgba(255,255,255,0.2);
    margin: 0.5rem 0;
}

.vt-hero-cta {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.vt-btn-primary, .vt-btn-secondary, .vt-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: inherit;
    white-space: nowrap;
}

.vt-btn-primary {
    background: white;
    color: var(--vt-primary);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.vt-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.vt-btn-secondary {
    background: rgba(255,255,255,0.15);
    color: white;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.vt-btn-secondary:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-2px);
}

.vt-btn-outline {
    background: transparent;
    color: var(--vt-primary);
    border: 2px solid var(--vt-primary);
}

.vt-btn-outline:hover {
    background: var(--vt-primary);
    color: white;
}

.vt-scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    opacity: 0.7;
    animation: vt-bounce 2s ease-in-out infinite;
}

@keyframes vt-bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(10px); }
}

.vt-scroll-mouse {
    width: 24px;
    height: 40px;
    border: 2px solid rgba(255,255,255,0.5);
    border-radius: 12px;
    position: relative;
}

.vt-scroll-wheel {
    width: 4px;
    height: 8px;
    background: white;
    border-radius: 2px;
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    animation: vt-scroll-wheel 2s ease-in-out infinite;
}

@keyframes vt-scroll-wheel {
    0%, 100% { transform: translateX(-50%) translateY(0); opacity: 1; }
    50% { transform: translateX(-50%) translateY(10px); opacity: 0.3; }
}

/* VIEWER SECTION */
.vt-viewer-section {
    padding: 4rem 2rem;
    background: var(--surface);
}

.vt-container {
    max-width: 1400px;
    margin: 0 auto;
}

.vt-viewer-wrap {
    position: relative;
    width: 100%;
    height: 70vh;
    min-height: 500px;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    background: #000;
}

.vt-panorama {
    width: 100%;
    height: 100%;
}

/* Loader */
.vt-loader {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    z-index: 10;
    color: white;
    transition: opacity 0.5s ease;
}

.vt-loader.hidden {
    opacity: 0;
    pointer-events: none;
}

.vt-loader-ring {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255,255,255,0.1);
    border-top-color: white;
    border-radius: 50%;
    animation: vt-spin 1s linear infinite;
}

@keyframes vt-spin {
    to { transform: rotate(360deg); }
}

/* Top Overlay */
.vt-top-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    pointer-events: none;
    background: linear-gradient(180deg, rgba(0,0,0,0.6) 0%, transparent 100%);
    z-index: 5;
}

.vt-spot-info {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    color: white;
    max-width: 600px;
    pointer-events: auto;
}

.vt-spot-index {
    background: var(--vt-accent);
    padding: 0.5rem 0.875rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.vt-spot-text h3 {
    margin: 0 0 0.25rem;
    font-size: 1.25rem;
    font-weight: 700;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.vt-spot-text p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.9;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.vt-controls {
    display: flex;
    gap: 0.5rem;
    pointer-events: auto;
    flex-shrink: 0;
}

.vt-ctrl-btn {
    width: 44px;
    height: 44px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.vt-ctrl-btn:hover {
    background: var(--vt-accent);
    border-color: var(--vt-accent);
    transform: scale(1.05);
}

/* Hint */
.vt-hint {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 5;
    opacity: 1;
    transition: opacity 0.5s ease;
    pointer-events: none;
}

.vt-hint.hidden {
    opacity: 0;
}

/* Progress bar */
.vt-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(255,255,255,0.1);
    z-index: 5;
}

.vt-progress-fill {
    height: 100%;
    background: var(--vt-accent);
    transition: width 0.5s ease;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

/* Selector Section */
.vt-selector-wrap {
    margin-top: 2rem;
    padding: 2rem;
    background: var(--bg);
    border-radius: 20px;
    border: 1px solid var(--border);
}

.vt-selector-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.vt-selector-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.vt-selector-count {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 600;
}

.vt-thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.vt-thumb {
    position: relative;
    aspect-ratio: 16/10;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 2px solid transparent;
}

.vt-thumb:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.vt-thumb.active {
    border-color: var(--vt-accent);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.vt-thumb-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.5s ease;
}

.vt-thumb:hover .vt-thumb-img {
    transform: scale(1.1);
}

.vt-thumb-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 30%, rgba(0,0,0,0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1rem;
    color: white;
}

.vt-thumb-number {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    width: 28px;
    height: 28px;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
}

.vt-thumb-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.vt-thumb-play {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    color: white;
    opacity: 0;
    transition: all 0.3s ease;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
}

.vt-thumb:hover .vt-thumb-play {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

/* Auto-tour toggle */
.vt-auto-tour {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--surface);
    border-radius: 12px;
    border: 1px solid var(--border);
    font-size: 0.9rem;
    color: var(--text);
}

.vt-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.vt-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.vt-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: var(--border);
    border-radius: 50px;
    transition: 0.3s;
}

.vt-slider::before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.vt-switch input:checked + .vt-slider {
    background: var(--vt-accent);
}

.vt-switch input:checked + .vt-slider::before {
    transform: translateX(22px);
}

/* Features Section */
.vt-features {
    padding: 4rem 2rem;
    background: var(--bg);
}

.vt-section-header {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 3rem;
}

.vt-section-header h2 {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 800;
    margin: 0;
    color: var(--text);
    letter-spacing: -0.02em;
}

.vt-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.vt-feature-card {
    padding: 2rem;
    background: var(--surface);
    border-radius: 16px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.vt-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    border-color: var(--vt-accent);
}

.vt-feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.vt-feature-card h3 {
    margin: 0 0 0.75rem;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text);
}

.vt-feature-card p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
    line-height: 1.6;
}

/* CTA Section */
.vt-cta-section {
    padding: 4rem 2rem;
}

.vt-cta-card {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--vt-gradient);
    border-radius: 24px;
    padding: 4rem 3rem;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(30, 58, 138, 0.3);
}

.vt-cta-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 0);
    background-size: 30px 30px;
}

.vt-cta-content {
    position: relative;
    z-index: 1;
    max-width: 600px;
    margin: 0 auto;
}

.vt-cta-content h2 {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 800;
    margin: 0 0 1rem;
    letter-spacing: -0.02em;
}

.vt-cta-content p {
    margin: 0 0 2rem;
    opacity: 0.9;
    font-size: 1.05rem;
    line-height: 1.6;
}

.vt-cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.vt-cta-card .vt-btn-outline {
    color: white;
    border-color: rgba(255,255,255,0.3);
}

.vt-cta-card .vt-btn-outline:hover {
    background: white;
    color: var(--vt-primary);
    border-color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .vt-hero {
        min-height: 70vh;
        padding: 3rem 1.5rem;
    }
    
    .vt-hero-stats {
        gap: 1rem;
    }
    
    .vt-stat-number {
        font-size: 2rem;
    }
    
    .vt-stat-divider {
        display: none;
    }
    
    .vt-viewer-wrap {
        height: 50vh;
        min-height: 350px;
        border-radius: 16px;
    }
    
    .vt-top-overlay {
        padding: 1rem;
        flex-direction: column;
    }
    
    .vt-controls {
        align-self: flex-end;
    }
    
    .vt-hint {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
    }
    
    .vt-selector-wrap {
        padding: 1.25rem;
    }
    
    .vt-thumbnails {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }
    
    .vt-cta-card {
        padding: 2.5rem 1.5rem;
    }
}
</style>

<script>
// ==========================================
// VIRTUAL TOUR ENGINE
// ==========================================
const spots = <?= $spots_json ?>;
let viewer = null;
let currentIndex = 0;
let autoTourInterval = null;

// Initialize Pannellum viewer
document.addEventListener('DOMContentLoaded', () => {
    if (spots.length === 0) {
        document.getElementById('vt-loader').classList.add('hidden');
        return;
    }
    
    const firstSpot = spots[0];
    
    viewer = pannellum.viewer('vt-panorama', {
        type: 'equirectangular',
        panorama: firstSpot.image,
        autoLoad: true,
        showControls: false,
        hfov: firstSpot.hfov,
        pitch: firstSpot.pitch,
        yaw: firstSpot.yaw,
        minHfov: 50,
        maxHfov: 120,
        compass: false,
        showFullscreenCtrl: false,
        showZoomCtrl: false,
        keyboardZoom: true,
        mouseZoom: true,
        draggable: true,
        hotSpots: buildHotspots(firstSpot.hotspots),
        sceneId: 'current',
        
        // Loading callbacks
        onLoad: () => {
            document.getElementById('vt-loader').classList.add('hidden');
        }
    });
    
    // Auto-hide hint after 5s
    setTimeout(() => {
        const hint = document.getElementById('vt-hint');
        if (hint) hint.classList.add('hidden');
    }, 5000);
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') nextSpot();
        if (e.key === 'ArrowLeft') prevSpot();
        if (e.key === 'f' || e.key === 'F') toggleFullscreen();
        if (e.key === 'Escape') exitFullscreen();
    });
});

// Build hotspots for current scene
function buildHotspots(hotspots) {
    if (!Array.isArray(hotspots)) return [];
    return hotspots.map((h, i) => ({
        pitch: h.pitch || 0,
        yaw: h.yaw || 0,
        type: h.type || 'info',
        text: h.text || '',
        clickHandlerFunc: () => {
            if (h.targetIndex !== undefined) jumpToSpot(h.targetIndex);
        },
        cssClass: 'vt-hotspot'
    }));
}

// Navigate to spot by index
function jumpToSpot(index) {
    if (index < 0 || index >= spots.length || !viewer) return;
    
    currentIndex = index;
    const spot = spots[index];
    
    // Show loader
    document.getElementById('vt-loader').classList.remove('hidden');
    
    // Load new panorama
    viewer.loadScene({
        type: 'equirectangular',
        panorama: spot.image,
        hfov: spot.hfov,
        pitch: spot.pitch,
        yaw: spot.yaw,
        hotSpots: buildHotspots(spot.hotspots)
    }, 'current');
    
    viewer.on('load', () => {
        document.getElementById('vt-loader').classList.add('hidden');
    });
    
    // Update UI
    updateUI();
}

function updateUI() {
    const spot = spots[currentIndex];
    
    // Update text
    document.getElementById('vt-current-title').textContent = spot.title;
    document.getElementById('vt-current-desc').textContent = spot.description;
    document.getElementById('vt-current-index').textContent = currentIndex + 1;
    document.getElementById('vt-selector-current').textContent = currentIndex + 1;
    
    // Update progress bar
    const progress = ((currentIndex + 1) / spots.length) * 100;
    document.getElementById('vt-progress-fill').style.width = progress + '%';
    
    // Update thumbnails
    document.querySelectorAll('.vt-thumb').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentIndex);
    });
    
    // Scroll thumbnail into view
    const activeThumb = document.querySelector('.vt-thumb.active');
    if (activeThumb) {
        activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
}

function nextSpot() {
    const next = (currentIndex + 1) % spots.length;
    jumpToSpot(next);
}

function prevSpot() {
    const prev = (currentIndex - 1 + spots.length) % spots.length;
    jumpToSpot(prev);
}

// Auto tour
function toggleAutoTour() {
    const enabled = document.getElementById('vt-auto-play').checked;
    
    if (enabled) {
        autoTourInterval = setInterval(() => {
            nextSpot();
        }, 5000);
    } else {
        clearInterval(autoTourInterval);
        autoTourInterval = null;
    }
}

// Fullscreen
function toggleFullscreen() {
    const viewerWrap = document.querySelector('.vt-viewer-wrap');
    
    if (!document.fullscreenElement) {
        if (viewerWrap.requestFullscreen) {
            viewerWrap.requestFullscreen();
        } else if (viewerWrap.webkitRequestFullscreen) {
            viewerWrap.webkitRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
}

function exitFullscreen() {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    }
}

// Fullscreen change handler (resize viewer)
document.addEventListener('fullscreenchange', () => {
    if (viewer) {
        setTimeout(() => {
            // Trigger Pannellum resize
            viewer.resize();
        }, 100);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>