<?php
$page_title = "Berita & Pengumuman";
require_once 'includes/header.php';

// ==========================================
// HELPERS
// ==========================================
if (!function_exists('formatDatePublic')) {
    function formatDatePublic($dateStr, $lang = 'id') {
        if (empty($dateStr)) return '-';
        $ts = strtotime($dateStr);
        $id = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $en = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $m = (int)date('n', $ts) - 1;
        return date('j', $ts) . ' ' . ($lang === 'en' ? $en[$m] : $id[$m]) . ' ' . date('Y', $ts);
    }
}
if (!function_exists('makeExcerpt')) {
    function makeExcerpt($text, $len = 150) {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return (mb_strlen($text) > $len) ? mb_substr($text, 0, $len) . '...' : $text;
    }
}

// ==========================================
// FETCH DATA (SINKRON: hanya berita yang PUBLISHED di admin)
// ==========================================
$stmt = $pdo->query("SELECT n.*, u.full_name AS author_name 
    FROM news n 
    LEFT JOIN users u ON n.author_id = u.id 
    WHERE n.is_published = 1 
      AND (n.published_at IS NULL OR n.published_at <= NOW()) 
    ORDER BY n.published_at DESC, n.created_at DESC");
$news_list = $stmt->fetchAll();

$total_news   = count($news_list);
$total_views  = array_sum(array_column($news_list, 'views'));
$categories   = array_values(array_unique(array_filter(array_column($news_list, 'category'))));
$latest_update = $news_list[0]['published_at'] ?? ($news_list[0]['created_at'] ?? null);
?>

<!-- ==========================================
     1. MINI HERO
     ========================================== -->
<section style="padding: 10rem 2rem 4rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%); text-align: center;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none;" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="Berita & Pengumuman" data-lang-en="News & Announcements">Berita & Pengumuman</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Kabar Terbaru Kampus" data-lang-en="Latest Campus Updates">Kabar Terbaru Kampus</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 2rem;" data-lang-id="Informasi resmi, pengumuman seleksi, dan kegiatan terkini seputar penerimaan mahasiswa baru." data-lang-en="Official information, selection announcements, and latest activities around new student admission.">Informasi resmi, pengumuman seleksi, dan kegiatan terkini seputar penerimaan mahasiswa baru.</p>
        
        <!-- Quick Stats -->
        <div style="display: inline-flex; gap: 2rem; padding: 1rem 2rem; background: var(--surface); border-radius: 50px; border: 1px solid var(--border); box-shadow: var(--shadow); flex-wrap: wrap; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                <span style="font-weight: 700;"><?= $total_news ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Artikel" data-lang-en="Articles">Artikel</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span style="font-weight: 700;"><?= number_format($total_views) ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Total Dibaca" data-lang-en="Total Views">Total Dibaca</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span style="font-weight: 700;"><?= formatDatePublic($latest_update, $lang) ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Update Terakhir" data-lang-en="Last Update">Update Terakhir</span>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     2. SEARCH & FILTER
     ========================================== -->
<section class="section" style="padding-top: 2rem; padding-bottom: 1rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="position: relative; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            <svg style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-news" placeholder="Cari berita atau pengumuman..." style="width: 100%; padding: 1rem 1.25rem 1rem 3rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-size: 1rem; transition: all 0.3s ease; box-shadow: var(--shadow);" oninput="filterNews()">
        </div>

        <?php if (count($categories) > 0): ?>
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;" id="filter-kategori">
                <button class="news-pill active" data-filter="all" onclick="setNewsFilter('all', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--primary); background: var(--primary); color: white; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; text-transform: capitalize;" data-lang-id="Semua" data-lang-en="All">Semua</button>
                <?php foreach ($categories as $cat): ?>
                <button class="news-pill" data-filter="<?= sanitize($cat) ?>" onclick="setNewsFilter('<?= sanitize($cat) ?>', this)" style="padding: 0.6rem 1.5rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; text-transform: capitalize;"><?= sanitize($cat) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <span data-lang-id="Menampilkan" data-lang-en="Showing">Menampilkan</span> <strong id="news-visible-count"><?= $total_news ?></strong> <span data-lang-id="dari" data-lang-en="of">dari</span> <strong><?= $total_news ?></strong> <span data-lang-id="artikel" data-lang-en="articles">artikel</span>
            </div>
        </div>

        <!-- ==========================================
             3. NEWS GRID
             ========================================== -->
        <div class="news-grid" id="news-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 2.5rem;">
            <?php if ($total_news > 0): ?>
                <?php foreach ($news_list as $n): 
                    $title   = ($lang === 'en' && !empty($n['title_en'])) ? $n['title_en'] : $n['title_id'];
                    $content = ($lang === 'en' && !empty($n['content_en'])) ? $n['content_en'] : $n['content_id'];
                    $search_blob = strtolower($n['title_id'] . ' ' . ($n['title_en'] ?? '') . ' ' . ($n['category'] ?? ''));
                ?>
                <article class="news-card" data-category="<?= sanitize($n['category'] ?? 'umum') ?>" data-search="<?= sanitize($search_blob) ?>" style="background: var(--surface); border-radius: 24px; overflow: hidden; border: 1px solid var(--border); transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); display: flex; flex-direction: column;">
                    <a href="detail-berita.php?slug=<?= urlencode($n['slug']) ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%;">
                        <div style="position: relative; overflow: hidden; height: 200px;">
                            <span style="position: absolute; top: 1rem; left: 1rem; z-index: 2; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); color: var(--primary); padding: 0.35rem 0.9rem; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(255,255,255,0.5);"><?= sanitize($n['category'] ?? 'Umum') ?></span>
                            <img src="<?= sanitize($n['thumbnail_url']) ?>" alt="<?= sanitize($title) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%2364748b%22 font-family=%22sans-serif%22 font-size=%2220%22%3EBerita%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div style="padding: 1.75rem; flex-grow: 1; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem; flex-wrap: wrap;">
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <?= formatDatePublic($n['published_at'] ?? $n['created_at'], $lang) ?>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <?= number_format($n['views']) ?>
                                </span>
                            </div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4;"><?= $title ?></h3>
                            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.25rem; flex-grow: 1;"><?= sanitize(makeExcerpt($content)) ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border);">
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?= sanitize($n['author_name'] ?? 'Admin') ?>
                                </span>
                                <span style="color: var(--primary); font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <span data-lang-id="Baca Selengkapnya" data-lang-en="Read More">Baca Selengkapnya</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; color: var(--text-muted);">
                    <svg style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Belum Ada Berita" data-lang-en="No News Available Yet">Belum Ada Berita</h3>
                    <p data-lang-id="Pengumuman dan berita terbaru akan tampil di halaman ini setelah dipublikasikan oleh admin." data-lang-en="Latest announcements and news will appear on this page once published by the admin.">Pengumuman dan berita terbaru akan tampil di halaman ini setelah dipublikasikan oleh admin.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- No Result -->
        <div id="news-no-result" style="display: none; text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg style="width: 64px; height: 64px; margin-bottom: 1.5rem; opacity: 0.4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="8" x2="14" y2="14"/><line x1="14" y1="8" x2="8" y2="14"/></svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Tidak Ada Hasil" data-lang-en="No Results Found">Tidak Ada Hasil</h3>
            <p data-lang-id="Coba kata kunci atau kategori lain." data-lang-en="Try another keyword or category.">Coba kata kunci atau kategori lain.</p>
            <button onclick="resetNewsFilter()" class="btn btn-primary" style="margin-top: 1.5rem;" data-lang-id="Reset Filter" data-lang-en="Reset Filter">Reset Filter</button>
        </div>
    </div>
</section>

<style>
    .news-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); border-color: var(--primary-light); }
    .news-card:hover img { transform: scale(1.08); }
    @media (max-width: 768px) {
        .news-grid { grid-template-columns: 1fr !important; }
    }
</style>

<script>
    const newsFilters = { kategori: 'all', search: '' };

    function setNewsFilter(value, btn) {
        newsFilters.kategori = value;
        document.querySelectorAll('.news-pill').forEach(p => {
            p.style.background = 'var(--surface)';
            p.style.color = 'var(--text)';
            p.style.borderColor = 'var(--border)';
        });
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
        btn.style.borderColor = 'var(--primary)';
        filterNews();
    }

    function filterNews() {
        const cards = document.querySelectorAll('.news-card');
        const searchInput = document.getElementById('search-news');
        newsFilters.search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visible = 0;

        cards.forEach(card => {
            const matchCat = newsFilters.kategori === 'all' || card.dataset.category === newsFilters.kategori;
            const matchSearch = newsFilters.search === '' || card.dataset.search.includes(newsFilters.search);
            if (matchCat && matchSearch) {
                card.style.display = 'flex';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('news-visible-count').textContent = visible;
        document.getElementById('news-no-result').style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';
    }

    function resetNewsFilter() {
        newsFilters.kategori = 'all';
        const searchInput = document.getElementById('search-news');
        if (searchInput) searchInput.value = '';
        const firstPill = document.querySelector('.news-pill[data-filter="all"]');
        if (firstPill) setNewsFilter('all', firstPill); else filterNews();
    }
</script>

<?php require_once 'includes/footer.php'; ?>