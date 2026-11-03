<?php
$page_title = "Pertanyaan Umum (FAQ)";
require_once 'includes/header.php';

// ==========================================
// FETCH DATA (SINKRON: hanya FAQ aktif)
// ==========================================
$stmt = $pdo->query("SELECT * FROM faq 
    WHERE is_active = 1 
    ORDER BY category ASC, sort_order ASC, created_at DESC");
$faq_list = $stmt->fetchAll();

// Group by kategori (dinamis dari data)
$grouped = [];
foreach ($faq_list as $f) {
    $cat = $f['category'] ?? 'Umum';
    if (!isset($grouped[$cat])) $grouped[$cat] = [];
    $grouped[$cat][] = $f;
}

$categories = array_keys($grouped);
$total_faq  = count($faq_list);
$total_cat  = count($categories);
?>

<!-- ==========================================
     1. MINI HERO
     ========================================== -->
<section style="padding: 10rem 2rem 4rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%); text-align: center;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none;" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="FAQ" data-lang-en="FAQ">FAQ</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Pusat Bantuan & FAQ" data-lang-en="Help Center & FAQ">Pusat Bantuan & FAQ</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 2rem;" data-lang-id="Temukan jawaban cepat seputar pendaftaran, seleksi, pembayaran, dan informasi penting lainnya." data-lang-en="Find quick answers about registration, selection, payment, and other important information.">Temukan jawaban cepat seputar pendaftaran, seleksi, pembayaran, dan informasi penting lainnya.</p>
        
        <!-- Quick Stats -->
        <div style="display: inline-flex; gap: 2rem; padding: 1rem 2rem; background: var(--surface); border-radius: 50px; border: 1px solid var(--border); box-shadow: var(--shadow); flex-wrap: wrap; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span style="font-weight: 700;"><?= $total_faq ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Pertanyaan" data-lang-en="Questions">Pertanyaan</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <span style="font-weight: 700;"><?= $total_cat ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;" data-lang-id="Kategori" data-lang-en="Categories">Kategori</span>
            </div>
            <div style="width: 1px; height: 24px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span style="font-weight: 700;" data-lang-id="Diperbarui Hari Ini" data-lang-en="Updated Today">Diperbarui Hari Ini</span>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     2. SEARCH & FILTER
     ========================================== -->
<section class="section" style="padding-top: 2rem; padding-bottom: 1rem;">
    <div style="max-width: 900px; margin: 0 auto;">
        <!-- Search Box -->
        <div style="position: relative; margin-bottom: 2rem;">
            <svg style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-faq" placeholder="Cari pertanyaan... (contoh: biaya, beasiswa, dokumen)" style="width: 100%; padding: 1rem 1.25rem 1rem 3rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-size: 1rem; transition: all 0.3s ease; box-shadow: var(--shadow);" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(30, 64, 175, 0.1)'" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow)'" oninput="filterFaq()" data-lang-id-placeholder="Cari pertanyaan..." data-lang-en-placeholder="Search questions... (e.g: tuition, scholarship, documents)">
        </div>

        <!-- Filter Pills Kategori -->
        <?php if ($total_cat > 0): ?>
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; font-weight: 600;" data-lang-id="Pilih Kategori" data-lang-en="Choose Category">Pilih Kategori</div>
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;" id="filter-kategori">
                <button class="faq-pill active" data-filter="all" onclick="setFaqFilter('all', this)" style="padding: 0.6rem 1.25rem; border-radius: 50px; border: 1.5px solid var(--primary); background: var(--primary); color: white; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.4rem;" data-lang-id="Semua" data-lang-en="All">
                    <span>Semua</span>
                    <span style="background: rgba(255,255,255,0.3); padding: 0.1rem 0.5rem; border-radius: 50px; font-size: 0.7rem;"><?= $total_faq ?></span>
                </button>
                <?php foreach ($categories as $cat): ?>
                <button class="faq-pill" data-filter="<?= sanitize($cat) ?>" onclick="setFaqFilter('<?= sanitize(addslashes($cat)) ?>', this)" style="padding: 0.6rem 1.25rem; border-radius: 50px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.4rem; text-transform: capitalize;">
                    <span><?= sanitize($cat) ?></span>
                    <span style="background: var(--bg); padding: 0.1rem 0.5rem; border-radius: 50px; font-size: 0.7rem; border: 1px solid var(--border);"><?= count($grouped[$cat]) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Results Info -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <span data-lang-id="Menampilkan" data-lang-en="Showing">Menampilkan</span> <strong id="faq-visible-count"><?= $total_faq ?></strong> <span data-lang-id="pertanyaan" data-lang-en="questions">pertanyaan</span>
            </div>
            <button id="faq-reset-btn" onclick="resetFaqFilter()" style="display: none; background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 8px; transition: background 0.3s;" onmouseover="this.style.background='rgba(30, 64, 175, 0.05)'" onmouseout="this.style.background='none'" data-lang-id="Reset Filter" data-lang-en="Reset Filter">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Reset Filter
            </button>
        </div>

        <!-- ==========================================
             3. FAQ ACCORDION (Grouped)
             ========================================== -->
        <div id="faq-container" style="display: flex; flex-direction: column; gap: 2.5rem;">
            <?php if ($total_faq > 0): ?>
                <?php foreach ($grouped as $cat => $faqs): ?>
                <div class="faq-group" data-category="<?= sanitize($cat) ?>">
                    <!-- Category Header -->
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border);">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <div>
                            <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; text-transform: capitalize;"><?= sanitize($cat) ?></h2>
                            <span style="font-size: 0.8rem; color: var(--text-muted);" data-lang-id="<?= count($faqs) ?> pertanyaan" data-lang-en="<?= count($faqs) ?> questions"><?= count($faqs) ?> pertanyaan</span>
                        </div>
                    </div>
                    
                    <!-- FAQ Items -->
                    <div class="faq-items" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($faqs as $faq): 
                            $question = ($lang === 'en' && !empty($faq['question_en'])) ? $faq['question_en'] : $faq['question_id'];
                            $answer   = ($lang === 'en' && !empty($faq['answer_en'])) ? $faq['answer_en'] : $faq['answer_id'];
                            $search_blob = strtolower($faq['question_id'] . ' ' . ($faq['question_en'] ?? '') . ' ' . $faq['answer_id'] . ' ' . ($faq['answer_en'] ?? '') . ' ' . $faq['category']);
                        ?>
                        <div class="faq-item" data-id="faq-<?= $faq['id'] ?>" data-search="<?= sanitize($search_blob) ?>" style="background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                            <button class="faq-question" onclick="toggleFaq(this)" style="width: 100%; padding: 1.25rem 1.5rem; background: none; border: none; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 1rem; color: var(--text); font-size: 1rem; font-weight: 600; line-height: 1.5; transition: all 0.2s;" onmouseover="this.style.background='rgba(30, 64, 175, 0.03)'" onmouseout="this.style.background='none'">
                                <span style="flex: 1;"><?= sanitize($question) ?></span>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                                    <button type="button" onclick="event.stopPropagation(); copyFaqLink(<?= $faq['id'] ?>)" style="background: var(--bg); border: 1px solid var(--border); color: var(--text-muted); padding: 0.4rem; border-radius: 8px; cursor: pointer; opacity: 0; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center;" class="faq-copy-btn" title="Salin tautan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    </button>
                                    <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2.5" style="transition: transform 0.3s ease;"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>
                            </button>
                            <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease;">
                                <div style="padding: 0 1.5rem 1.25rem 1.5rem; color: var(--text-muted); line-height: 1.7; font-size: 0.95rem; white-space: pre-line;"><?= sanitize($answer) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div style="text-align: center; padding: 5rem 2rem; color: var(--text-muted);">
                    <svg style="width: 80px; height: 80px; margin-bottom: 1.5rem; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Belum Ada FAQ" data-lang-en="No FAQ Available Yet">Belum Ada FAQ</h3>
                    <p data-lang-id="Daftar pertanyaan umum akan segera tersedia. Silakan hubungi admin jika ada pertanyaan." data-lang-en="FAQ list will be available soon. Please contact admin if you have any questions.">Daftar pertanyaan umum akan segera tersedia. Silakan hubungi admin jika ada pertanyaan.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- No Result -->
        <div id="faq-no-result" style="display: none; text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
            <svg style="width: 64px; height: 64px; margin-bottom: 1.5rem; opacity: 0.4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="8" x2="14" y2="14"/><line x1="14" y1="8" x2="8" y2="14"/></svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);" data-lang-id="Pertanyaan Tidak Ditemukan" data-lang-en="No Questions Found">Pertanyaan Tidak Ditemukan</h3>
            <p data-lang-id="Coba kata kunci lain atau pilih kategori berbeda." data-lang-en="Try another keyword or choose a different category.">Coba kata kunci lain atau pilih kategori berbeda.</p>
        </div>
    </div>
</section>

<!-- ==========================================
     4. CTA: BELUM KETEMU JAWABAN?
     ========================================== -->
<section style="padding: 2rem 2rem 6rem;">
    <div style="max-width: 800px; margin: 0 auto; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 24px; padding: 3rem 2rem; text-align: center; color: white; position: relative; overflow: hidden;">
        <!-- Decorative circles -->
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -80px; left: -80px; width: 250px; height: 250px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 1;">
            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.75rem;" data-lang-id="Belum Menemukan Jawaban?" data-lang-en="Still Can't Find the Answer?">Belum Menemukan Jawaban?</h2>
            <p style="font-size: 1.05rem; opacity: 0.95; margin-bottom: 1.75rem; max-width: 500px; margin-left: auto; margin-right: auto;" data-lang-id="Tim admisi kami siap membantu Anda 24/7. Hubungi kami via WhatsApp untuk respon cepat." data-lang-en="Our admission team is ready to help you 24/7. Contact us via WhatsApp for quick response.">Tim admisi kami siap membantu Anda 24/7. Hubungi kami via WhatsApp untuk respon cepat.</p>
            <div style="display: inline-flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                <a href="https://wa.me/<?= sanitize(get_setting($pdo, 'contact_wa', $lang)) ?>?text=<?= urlencode($lang === 'en' ? 'Hello, I have a question about the admission' : 'Halo, saya ingin bertanya tentang pendaftaran') ?>" target="_blank" class="btn" style="background: white; color: var(--primary); padding: 0.875rem 1.75rem; font-weight: 700; border-radius: 50px; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.25)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    <span data-lang-id="Chat WhatsApp" data-lang-en="Chat WhatsApp">Chat WhatsApp</span>
                </a>
                <a href="mailto:<?= sanitize(get_setting($pdo, 'contact_email', $lang)) ?>" class="btn" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.875rem 1.75rem; font-weight: 700; border-radius: 50px; border: 1.5px solid rgba(255,255,255,0.3); display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span data-lang-id="Kirim Email" data-lang-en="Send Email">Kirim Email</span>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .faq-item:hover { 
        border-color: var(--primary-light); 
        box-shadow: 0 4px 15px rgba(30, 64, 175, 0.08); 
    }
    .faq-item:hover .faq-copy-btn { opacity: 1 !important; }
    
    .faq-item.open {
        border-color: var(--primary);
        background: linear-gradient(135deg, var(--surface) 0%, rgba(30, 64, 175, 0.02) 100%);
    }
    .faq-item.open .faq-question {
        color: var(--primary);
    }
    .faq-item.open .faq-chevron {
        transform: rotate(180deg);
        color: var(--primary);
    }
    .faq-item.open .faq-copy-btn {
        opacity: 1;
    }
    
    .faq-question:focus {
        outline: 2px solid var(--primary);
        outline-offset: -2px;
        border-radius: 16px;
    }
    
    /* Animasi highlight saat auto-expand dari URL */
    @keyframes highlightFaq {
        0% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.5); }
        70% { box-shadow: 0 0 0 15px rgba(30, 64, 175, 0); }
        100% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0); }
    }
    .faq-item.highlighted {
        animation: highlightFaq 2s ease-out;
    }
</style>

<script>
    const faqFilters = { kategori: 'all', search: '' };

    function setFaqFilter(value, btn) {
        faqFilters.kategori = value;
        document.querySelectorAll('.faq-pill').forEach(p => {
            p.style.background = 'var(--surface)';
            p.style.color = 'var(--text)';
            p.style.borderColor = 'var(--border)';
        });
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
        btn.style.borderColor = 'var(--primary)';
        filterFaq();
    }

    function filterFaq() {
        const searchInput = document.getElementById('search-faq');
        faqFilters.search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const groups = document.querySelectorAll('.faq-group');
        const resetBtn = document.getElementById('faq-reset-btn');
        const noResult = document.getElementById('faq-no-result');
        const visibleCount = document.getElementById('faq-visible-count');
        
        let totalVisible = 0;

        groups.forEach(group => {
            const matchCat = faqFilters.kategori === 'all' || group.dataset.category === faqFilters.kategori;
            const items = group.querySelectorAll('.faq-item');
            let groupVisible = 0;
            
            items.forEach(item => {
                const matchSearch = faqFilters.search === '' || item.dataset.search.includes(faqFilters.search);
                if (matchCat && matchSearch) {
                    item.style.display = '';
                    groupVisible++;
                    totalVisible++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            group.style.display = groupVisible > 0 ? '' : 'none';
        });

        visibleCount.textContent = totalVisible;
        noResult.style.display = (totalVisible === 0 && document.querySelectorAll('.faq-item').length > 0) ? 'block' : 'none';
        const hasFilter = faqFilters.kategori !== 'all' || faqFilters.search !== '';
        resetBtn.style.display = hasFilter ? 'inline-flex' : 'none';
    }

    function resetFaqFilter() {
        faqFilters.kategori = 'all';
        const searchInput = document.getElementById('search-faq');
        if (searchInput) searchInput.value = '';
        const firstPill = document.querySelector('.faq-pill[data-filter="all"]');
        if (firstPill) setFaqFilter('all', firstPill); else filterFaq();
    }

    function toggleFaq(btn) {
        const item = btn.closest('.faq-item');
        const answer = item.querySelector('.faq-answer');
        const isOpen = item.classList.contains('open');
        
        // Close all others (accordion style - optional, comment out jika ingin multi-open)
        // document.querySelectorAll('.faq-item.open').forEach(el => {
        //     if (el !== item) {
        //         el.classList.remove('open');
        //         el.querySelector('.faq-answer').style.maxHeight = '0';
        //     }
        // });
        
        if (isOpen) {
            item.classList.remove('open');
            answer.style.maxHeight = '0';
        } else {
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }
    }

    function copyFaqLink(id) {
        const item = document.querySelector(`.faq-item[data-id="faq-${id}"]`);
        if (item) {
            item.classList.add('highlighted');
            setTimeout(() => item.classList.remove('highlighted'), 2000);
            
            const url = window.location.origin + window.location.pathname + '#faq-' + id;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof showToast === 'function') {
                        showToast('<?= $lang === 'en' ? 'Link Copied!' : 'Tautan Disalin!' ?>', '<?= $lang === 'en' ? 'Share this link to open this specific question' : 'Bagikan tautan ini untuk membuka pertanyaan spesifik ini' ?>', 'success');
                    } else {
                        alert('<?= $lang === 'en' ? 'Link copied!' : 'Tautan disalin!' ?>');
                    }
                });
            } else {
                prompt('<?= $lang === 'en' ? 'Copy link:' : 'Salin tautan:' ?>', url);
            }
        }
    }

    // Auto-expand FAQ dari URL hash (deep-link)
    document.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#faq-')) {
            const item = document.querySelector(`.faq-item[data-id="${hash.substring(1)}"]`);
            if (item) {
                setTimeout(() => {
                    const btn = item.querySelector('.faq-question');
                    if (btn) toggleFaq(btn);
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    item.classList.add('highlighted');
                    setTimeout(() => item.classList.remove('highlighted'), 2000);
                }, 500);
            }
        }
    });

    // Keyboard accessibility (Enter/Space toggle)
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleFaq(btn);
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>