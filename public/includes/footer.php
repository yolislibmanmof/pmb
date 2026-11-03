<?php
// ======================================================================
// 1. AMBIL DATA DINAMIS DARI DATABASE (Sinkron dengan Admin Settings)
// ======================================================================
$site_name = get_setting($pdo, 'site_name', $lang);
$tagline = get_setting($pdo, 'tagline', $lang);
$contact_address = get_setting($pdo, 'contact_address', $lang);
$contact_phone = get_setting($pdo, 'contact_phone', $lang);
$contact_email = get_setting($pdo, 'contact_email', $lang);
$contact_wa = get_setting($pdo, 'contact_wa', $lang);
$logo_url = get_setting($pdo, 'logo_url', $lang);

// Ambil menu footer dari database (jika admin mengaturnya di menu_position = 'footer')
$stmt_footer = $pdo->query("SELECT label_id, label_en, url, target FROM menus WHERE menu_position = 'footer' AND is_active = 1 ORDER BY sort_order ASC");
$footer_menus = $stmt_footer->fetchAll();

// Bersihkan nomor WA untuk link (hapus spasi, +, dll)
$clean_wa = preg_replace('/[^0-9]/', '', $contact_wa ?: '6285174270172');
$wa_message = urlencode($lang === 'en' 
    ? "Hello Admin, I would like to ask about the new student admission." 
    : "Halo Admin PMB, saya ingin bertanya tentang pendaftaran mahasiswa baru.");
?>

    </main> <!-- Penutup tag <main> dari header.php -->
    
    <!-- ==========================================
         FOOTER SECTION
         ========================================== -->
    <footer class="footer" style="background: var(--surface); border-top: 1px solid var(--border); margin-top: 4rem;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 4rem 2rem 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem;">
                
                <!-- Kolom 1: Brand & Visi -->
                <div>
                    <img src="<?= get_public_image($logo_url) ?>" alt="<?= sanitize($site_name) ?>" style="height: 45px; width: auto; object-fit: contain; margin-bottom: 1.5rem;">
                    <p style="line-height: 1.7; color: var(--text-muted); font-size: 0.95rem;" data-lang-id="<?= sanitize($tagline ?: 'Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas.') ?>" data-lang-en="<?= sanitize($tagline ?: 'Creating a superior generation, globally competitive, and noble through quality higher education.') ?>"><?= sanitize($tagline ?: 'Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas.') ?></p>
                </div>

                <!-- Kolom 2: Tautan Cepat (Dinamis dari Database) -->
                <div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);" data-lang-id="Tautan Cepat" data-lang-en="Quick Links">Tautan Cepat</h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php if (count($footer_menus) > 0): ?>
                            <?php foreach ($footer_menus as $menu): 
                                $menu_label = ($lang === 'en' && !empty($menu['label_en'])) ? $menu['label_en'] : $menu['label_id'];
                            ?>
                            <li>
                                <a href="<?= sanitize($menu['url']) ?>" target="<?= sanitize($menu['target'] ?: '_self') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); text-decoration: none; font-size: 0.95rem; transition: all 0.3s ease;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    <span><?= sanitize($menu_label) ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback jika admin belum mengatur menu footer -->
                            <li><a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem;" data-lang-id="Beranda" data-lang-en="Home">Beranda</a></li>
                            <li><a href="prodi.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem;" data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</a></li>
                            <li><a href="alur.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem;" data-lang-id="Alur Pendaftaran" data-lang-en="Registration Flow">Alur Pendaftaran</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak Resmi (Dinamis dari Database) -->
                <div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);" data-lang-id="Hubungi Kami" data-lang-en="Contact Us">Hubungi Kami</h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 1rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 3px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span style="white-space: pre-line;"><?= sanitize($contact_address ?: 'Jl. Dr. Ratulangi No 172, Palopo') ?></span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span><?= sanitize($contact_phone ?: '+62 851-7427-0172') ?></span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <a href="mailto:<?= sanitize($contact_email ?: 'info@ukjp.ac.id') ?>" style="color: inherit; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?= sanitize($contact_email ?: 'info@ukjp.ac.id') ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div style="border-top: 1px solid var(--border); margin-top: 3rem; padding-top: 2rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; text-align: center;">
                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">
                    &copy; <?= date('Y') ?> <strong><?= sanitize($site_name) ?></strong>. <span data-lang-id="Seluruh hak cipta dilindungi undang-undang." data-lang-en="All rights reserved.">Seluruh hak cipta dilindungi undang-undang.</span>
                </p>
                
                <!-- Back to Top Button -->
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" style="background: var(--primary); color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(30, 64, 175, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    <span data-lang-id="Kembali ke Atas" data-lang-en="Back to Top">Kembali ke Atas</span>
                </button>
            </div>
        </div>
    </footer>

    <!-- ==========================================
         FLOATING WHATSAPP BUTTON (Dinamis)
         ========================================== -->
    <a href="https://wa.me/<?= $clean_wa ?>?text=<?= $wa_message ?>" class="floating-wa" target="_blank" title="Hubungi via WhatsApp" style="position: fixed; bottom: 2rem; right: 2rem; width: 56px; height: 56px; background: #25D366; border-radius: 50%; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); display: flex; align-items: center; justify-content: center; z-index: 999; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1) translateY(-5px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" style="color: white;">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <script src="../assets/js/public.js"></script>
</body>
</html>