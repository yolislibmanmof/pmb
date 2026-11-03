<?php
$page_title = "Detail Berita";
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

// ==========================================
// FETCH BERITA BERDASARKAN SLUG (SINKRON DENGAN ADMIN)
// ==========================================
$slug = trim($_GET['slug'] ?? '');
$news = null;

if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT n.*, u.full_name AS author_name 
        FROM news n LEFT JOIN users u ON n.author_id = u.id 
        WHERE n.slug = ? AND n.is_published = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $news = $stmt->fetch();
    
    // Increment views (sekali per sesi per artikel)
    if ($news && !isset($_SESSION['viewed_news'][$news['id']])) {
        $pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$news['id']]);
        $_SESSION['viewed_news'][$news['id']] = true;
        $news['views'] = $news['views'] + 1;
    }
}

// Berita terkait (kategori sama, max 3)
$related = [];
if ($news) {
    $stmt = $pdo->prepare("SELECT slug, title_id, title_en, thumbnail_url, category, published_at, created_at 
        FROM news 
        WHERE is_published = 1 AND category = ? AND id != ? 
        ORDER BY published_at DESC LIMIT 3");
    $stmt->execute([$news['category'], $news['id']]);
    $related = $stmt->fetchAll();
}

$title   = $news ? (($lang === 'en' && !empty($news['title_en'])) ? $news['title_en'] : $news['title_id']) : '';
$content = $news ? (($lang === 'en' && !empty($news['content_en'])) ? $news['content_en'] : $news['content_id']) : '';
$share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
?>

<?php if (!$news): ?>
    <!-- ==========================================
         STATE 404
         ========================================== -->
    <section style="padding: 12rem 2rem 6rem; text-align: center;">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="font-size: 5rem; font-weight: 900; color: var(--border); line-height: 1;">404</div>
            <h1 style="font-size: 2rem; font-weight: 800; margin: 1rem 0;" data-lang-id="Berita Tidak Ditemukan" data-lang-en="News Not Found">Berita Tidak Ditemukan</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;" data-lang-id="Artikel yang Anda cari mungkin telah dihapus atau belum dipublikasikan oleh admin." data-lang-en="The article you are looking for may have been removed or not yet published by the admin.">Artikel yang Anda cari mungkin telah dihapus atau belum dipublikasikan oleh admin.</p>
            <a href="berita.php" class="btn btn-primary" data-lang-id="Kembali ke Daftar Berita" data-lang-en="Back to News List">Kembali ke Daftar Berita</a>
        </div>
    </section>
<?php else: ?>
    <!-- ==========================================
         1. ARTICLE HEADER
         ========================================== -->
    <section style="padding: 9rem 2rem 3rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%);">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                <a href="index.php" style="color: var(--text-muted); text-decoration: none;" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="berita.php" style="color: var(--text-muted); text-decoration: none;" data-lang-id="Berita" data-lang-en="News">Berita</a>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span style="color: var(--primary); font-weight: 600;"><?= sanitize($title) ?></span>
            </div>
            
            <span style="display: inline-block; background: rgba(30, 64, 175, 0.1); color: var(--primary); padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;"><?= sanitize($news['category'] ?? 'Umum') ?></span>
            
            <h1 style="font-size: 2.5rem; font-weight: 800; line-height: 1.25; margin-bottom: 1.5rem;"><?= $title ?></h1>
            
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; color: var(--text-muted); font-size: 0.875rem;">
                <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;"><?= strtoupper(substr($news['author_name'] ?? 'A', 0, 1)) ?></span>
                    <?= sanitize($news['author_name'] ?? 'Admin') ?>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?= formatDatePublic($news['published_at'] ?? $news['created_at'], $lang) ?>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <?= number_format($news['views']) ?> <span data-lang-id="dibaca" data-lang-en="views">dibaca</span>
                </span>
            </div>
        </div>
    </section>

    <!-- ==========================================
         2. FEATURED IMAGE
         ========================================== -->
    <section style="padding: 0 2rem 3rem;">
        <div style="max-width: 900px; margin: 0 auto;">
            <img src="<?= sanitize($news['thumbnail_url']) ?>" alt="<?= sanitize($title) ?>" style="width: 100%; max-height: 450px; object-fit: cover; border-radius: 24px; box-shadow: var(--shadow-lg); border: 1px solid var(--border);" onerror="this.style.display='none'">
        </div>
    </section>

    <!-- ==========================================
         3. ARTICLE BODY + SHARE
         ========================================== -->
    <section style="padding: 0 2rem 4rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="font-size: 1.1rem; line-height: 1.9; color: var(--text); white-space: pre-line;"><?= $content ?></div>
            
            <!-- Share Bar -->
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border); flex-wrap: wrap;">
                <span style="font-weight: 700; font-size: 0.9rem;" data-lang-id="Bagikan:" data-lang-en="Share:">Bagikan:</span>
                <a href="https://wa.me/?text=<?= urlencode($title . ' - ' . $share_url) ?>" target="_blank" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    WhatsApp
                </a>
                <button onclick="copyLink()" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    <span data-lang-id="Salin Tautan" data-lang-en="Copy Link">Salin Tautan</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ==========================================
         4. RELATED NEWS
         ========================================== -->
    <?php if (count($related) > 0): ?>
    <section style="padding: 0 2rem 6rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 2rem; text-align: center;" data-lang-id="Berita Terkait" data-lang-en="Related News">Berita Terkait</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <?php foreach ($related as $r): 
                    $r_title = ($lang === 'en' && !empty($r['title_en'])) ? $r['title_en'] : $r['title_id'];
                ?>
                <a href="detail-berita.php?slug=<?= urlencode($r['slug']) ?>" style="text-decoration: none; color: inherit; background: var(--surface); border-radius: 20px; overflow: hidden; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                    <div style="height: 160px; overflow: hidden;">
                        <img src="<?= sanitize($r['thumbnail_url']) ?>" alt="<?= sanitize($r_title) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E'">
                    </div>
                    <div style="padding: 1.25rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;"><?= formatDatePublic($r['published_at'] ?? $r['created_at'], $lang) ?></div>
                        <h3 style="font-size: 1rem; font-weight: 700; line-height: 1.4;"><?= sanitize($r_title) ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ==========================================
         5. CTA BACK
         ========================================== -->
    <section style="padding: 0 2rem 6rem; text-align: center;">
        <a href="berita.php" class="btn btn-outline">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span data-lang-id="Kembali ke Semua Berita" data-lang-en="Back to All News">Kembali ke Semua Berita</span>
        </a>
    </section>

    <script>
        function copyLink() {
            const url = <?= json_encode($share_url) ?>;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('<?= $lang === 'en' ? 'Link copied!' : 'Tautan berhasil disalin!' ?>');
                });
            } else {
                prompt('<?= $lang === 'en' ? 'Copy link:' : 'Salin tautan:' ?>', url);
            }
        }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>