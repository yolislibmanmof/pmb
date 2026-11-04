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

// Ambil menu footer dari database
$stmt_footer = $pdo->query("SELECT label_id, label_en, url, target FROM menus WHERE menu_position = 'footer' AND is_active = 1 ORDER BY sort_order ASC");
$footer_menus = $stmt_footer->fetchAll();

// Ambil statistik untuk social proof
try {
    $total_prodi = (int)$pdo->query("SELECT COUNT(*) FROM prodi WHERE is_active = 1")->fetchColumn();
    $total_alumni = (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_active = 1")->fetchColumn();
    $total_pendaftar = (int)$pdo->query("SELECT COUNT(*) FROM applicants WHERE status_daftar IN ('Diverifikasi', 'Lulus', 'Registrasi Ulang')")->fetchColumn();
} catch (Exception $e) {
    $total_prodi = 12;
    $total_alumni = 5000;
    $total_pendaftar = 2500;
}

// Bersihkan nomor WA untuk link
$clean_wa = preg_replace('/[^0-9]/', '', $contact_wa ?: '6285174270172');
$wa_message = urlencode($lang === 'en' 
    ? "Hello Admin, I would like to ask about the new student admission." 
    : "Halo Admin PMB, saya ingin bertanya tentang pendaftaran mahasiswa baru.");

// Social media links (hardcoded, bisa dipindah ke settings jika perlu)
$social_links = [
    'facebook' => ['url' => 'https://facebook.com', 'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>'],
    'instagram' => ['url' => 'https://instagram.com', 'icon' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>'],
    'youtube' => ['url' => 'https://youtube.com', 'icon' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>'],
    'tiktok' => ['url' => 'https://tiktok.com', 'icon' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>']
];
?>

    </main> <!-- Penutup tag <main> dari header.php -->
    
    <!-- ==========================================
         FOOTER SECTION (Enterprise Grade)
         ========================================== -->
    <footer class="footer" style="background: var(--surface); border-top: 1px solid var(--border); margin-top: 4rem; position: relative; overflow: hidden;">
        
        <!-- Decorative Background Pattern -->
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(circle at 1px 1px, var(--border) 1px, transparent 0); background-size: 24px 24px; opacity: 0.3; pointer-events: none;"></div>
        
        <!-- Pre-Footer: Newsletter & Stats -->
        <div style="position: relative; background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); padding: 3rem 2rem; color: white;">
            <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;" class="newsletter-grid">
                <div>
                    <h3 style="font-size: 1.5rem; font-weight: 800; margin: 0 0 0.5rem; letter-spacing: -0.5px;" data-lang-id="Dapatkan Info Terbaru" data-lang-en="Get Latest Updates">Dapatkan Info Terbaru</h3>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;" data-lang-id="Berlangganan newsletter kami untuk info pendaftaran, beasiswa, dan pengumuman penting." data-lang-en="Subscribe to our newsletter for admission info, scholarships, and important announcements.">Berlangganan newsletter kami untuk info pendaftaran, beasiswa, dan pengumuman penting.</p>
                </div>
                <div>
                    <form id="newsletter-form" onsubmit="handleNewsletter(event)" style="display: flex; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.5rem; border-radius: 12px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                        <input type="email" id="newsletter-email" required placeholder="<?= $lang === 'en' ? 'Enter your email...' : 'Masukkan email Anda...' ?>" style="flex: 1; background: transparent; border: none; padding: 0.75rem 1rem; color: white; font-size: 0.95rem; outline: none;">
                        <button type="submit" style="background: white; color: var(--primary); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s; white-space: nowrap;" onmouseover="this.style.transform='translateX(2px)'" onmouseout="this.style.transform='translateX(0)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            <span data-lang-id="Berlangganan" data-lang-en="Subscribe">Berlangganan</span>
                        </button>
                    </form>
                    <p style="font-size: 0.75rem; opacity: 0.7; margin: 0.5rem 0 0;" data-lang-id="🔒 Privasi Anda aman. Kami tidak akan spam." data-lang-en="🔒 Your privacy is safe. We won't spam you.">🔒 Privasi Anda aman. Kami tidak akan spam.</p>
                </div>
            </div>
        </div>
        
        <!-- Main Footer Content -->
        <div style="position: relative; max-width: 1200px; margin: 0 auto; padding: 4rem 2rem 2rem;">
            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 3rem;" class="footer-grid">
                
                <!-- Kolom 1: Brand, Visi & Social Media -->
                <div>
                    <img src="<?= get_public_image($logo_url) ?>" alt="<?= sanitize($site_name) ?>" style="height: 45px; width: auto; object-fit: contain; margin-bottom: 1.5rem;">
                    <p style="line-height: 1.7; color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;" data-lang-id="<?= sanitize($tagline ?: 'Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas.') ?>" data-lang-en="<?= sanitize($tagline ?: 'Creating a superior generation, globally competitive, and noble through quality higher education.') ?>"><?= sanitize($tagline ?: 'Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas.') ?></p>
                    
                    <!-- Social Media Links -->
                    <div style="margin-top: 1.5rem;">
                        <p style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;" data-lang-id="Ikuti Kami" data-lang-en="Follow Us">Ikuti Kami</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <?php foreach ($social_links as $name => $social): ?>
                            <a href="<?= $social['url'] ?>" target="_blank" rel="noopener" class="social-icon" style="width: 40px; height: 40px; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); text-decoration: none; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.background='var(--bg)'; this.style.color='var(--text-muted)'; this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'" title="<?= ucfirst($name) ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <?= $social['icon'] ?>
                                </svg>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Tautan Cepat (Dinamis dari Database) -->
                <div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text); position: relative; padding-bottom: 0.75rem;" data-lang-id="Tautan Cepat" data-lang-en="Quick Links">
                        Tautan Cepat
                        <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 3px; background: var(--primary); border-radius: 2px;"></span>
                    </h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php if (count($footer_menus) > 0): ?>
                            <?php foreach ($footer_menus as $menu): 
                                $menu_label = ($lang === 'en' && !empty($menu['label_en'])) ? $menu['label_en'] : $menu['label_id'];
                            ?>
                            <li>
                                <a href="<?= sanitize($menu['url']) ?>" target="<?= sanitize($menu['target'] ?: '_self') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); text-decoration: none; font-size: 0.95rem; transition: all 0.3s ease;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    <span><?= sanitize($menu_label) ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                                <span data-lang-id="Beranda" data-lang-en="Home">Beranda</span>
                            </a></li>
                            <li><a href="prodi.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                                <span data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</span>
                            </a></li>
                            <li><a href="alur.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                                <span data-lang-id="Alur Pendaftaran" data-lang-en="Registration Flow">Alur Pendaftaran</span>
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak Resmi (Dinamis dari Database) -->
                <div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text); position: relative; padding-bottom: 0.75rem;" data-lang-id="Hubungi Kami" data-lang-en="Contact Us">
                        Hubungi Kami
                        <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 3px; background: var(--primary); border-radius: 2px;"></span>
                    </h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 1rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <div style="width: 36px; height: 36px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <span style="white-space: pre-line; line-height: 1.5;"><?= sanitize($contact_address ?: 'Jl. Dr. Ratulangi No 172, Palopo') ?></span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <div style="width: 36px; height: 36px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone ?: '+6285174270172') ?>" style="color: inherit; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?= sanitize($contact_phone ?: '+62 851-7427-0172') ?></a>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.95rem;">
                            <div style="width: 36px; height: 36px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <a href="mailto:<?= sanitize($contact_email ?: 'info@ukjp.ac.id') ?>" style="color: inherit; text-decoration: none; transition: color 0.3s; word-break: break-all;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?= sanitize($contact_email ?: 'info@ukjp.ac.id') ?></a>
                        </li>
                    </ul>
                </div>
                
                <!-- Kolom 4: Statistik & Trust Badges (BARU) -->
                <div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text); position: relative; padding-bottom: 0.75rem;" data-lang-id="Kenapa Kami?" data-lang-en="Why Choose Us?">
                        Kenapa Kami?
                        <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 3px; background: var(--primary); border-radius: 2px;"></span>
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--bg); border-radius: 10px; border: 1px solid var(--border);">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 1.25rem; font-weight: 800; color: var(--text);" data-counter="<?= $total_prodi ?>"><?= $total_prodi ?>+</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--bg); border-radius: 10px; border: 1px solid var(--border);">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 1.25rem; font-weight: 800; color: var(--text);" data-counter="<?= $total_alumni ?>"><?= number_format($total_alumni) ?>+</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="Alumni Sukses" data-lang-en="Successful Alumni">Alumni Sukses</div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--bg); border-radius: 10px; border: 1px solid var(--border);">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 1.25rem; font-weight: 800; color: var(--text);" data-counter="<?= $total_pendaftar ?>"><?= number_format($total_pendaftar) ?>+</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);" data-lang-id="Pendaftar Tahun Ini" data-lang-en="Applicants This Year">Pendaftar Tahun Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Middle: Legal & Additional Links -->
            <div style="margin-top: 3rem; padding: 1.5rem 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem; font-size: 0.875rem;">
                <a href="#" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span data-lang-id="Kebijakan Privasi" data-lang-en="Privacy Policy">Kebijakan Privasi</span>
                </a>
                <a href="#" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span data-lang-id="Syarat & Ketentuan" data-lang-en="Terms & Conditions">Syarat & Ketentuan</span>
                </a>
                <a href="faq.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>FAQ</span>
                </a>
                <a href="kontak.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <span data-lang-id="Bantuan" data-lang-en="Help">Bantuan</span>
                </a>
                <a href="../admin/login.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; transition: color 0.3s; opacity: 0.6;" onmouseover="this.style.color='var(--primary)'; this.style.opacity='1'" onmouseout="this.style.color='var(--text-muted)'; this.style.opacity='0.6'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    <span data-lang-id="Admin" data-lang-en="Admin">Admin</span>
                </a>
            </div>

            <!-- Footer Bottom -->
            <div style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <p style="margin: 0; color: var(--text-muted); font-size: 0.875rem;">
                        &copy; <?= date('Y') ?> <strong style="color: var(--text);"><?= sanitize($site_name) ?></strong>. 
                        <span data-lang-id="Seluruh hak cipta dilindungi." data-lang-en="All rights reserved.">Seluruh hak cipta dilindungi.</span>
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; background: var(--bg); border: 1px solid var(--border); border-radius: 50px; font-size: 0.75rem; font-weight: 600;">
                        <span style="width: 6px; height: 6px; background: var(--success); border-radius: 50%; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);"></span>
                        <span style="color: var(--text-muted);" data-lang-id="Online" data-lang-en="Online">Online</span>
                    </div>
                </div>
                
                <!-- Back to Top Button -->
                <button id="footer-back-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" style="background: var(--surface); color: var(--text); border: 1px solid var(--border); padding: 0.6rem 1.25rem; border-radius: 50px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05);" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(30, 64, 175, 0.3)'" onmouseout="this.style.background='var(--surface)'; this.style.color='var(--text)'; this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    <span data-lang-id="Kembali ke Atas" data-lang-en="Back to Top">Kembali ke Atas</span>
                </button>
            </div>
        </div>
    </footer>

    <!-- ==========================================
         FLOATING WHATSAPP BUTTON (DIPINDAH KE KIRI)
         ========================================== -->
    <a href="https://wa.me/<?= $clean_wa ?>?text=<?= $wa_message ?>" class="floating-wa" target="_blank" rel="noopener" title="<?= $lang === 'en' ? 'Chat with us on WhatsApp' : 'Chat dengan kami via WhatsApp' ?>">
        <!-- Pulse Ring Animation -->
        <span class="wa-pulse"></span>
        <span class="wa-pulse delay"></span>
        
        <!-- WhatsApp Icon -->
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" style="color: white; position: relative; z-index: 2;">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        
        <!-- Tooltip Label -->
        <span class="wa-tooltip" data-lang-id="Chat WhatsApp" data-lang-en="WhatsApp Chat">Chat WhatsApp</span>
    </a>
    
    <!-- Floating Back to Top (KANAN BAWAH) -->
    <button id="floating-back-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="<?= $lang === 'en' ? 'Back to Top' : 'Kembali ke Atas' ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        <!-- Scroll Progress Ring -->
        <svg class="scroll-progress" viewBox="0 0 100 100" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: rotate(-90deg);">
            <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
            <circle class="progress-circle" cx="50" cy="50" r="46" fill="none" stroke="white" stroke-width="3" stroke-dasharray="289" stroke-dashoffset="289" stroke-linecap="round"/>
        </svg>
    </button>

    <script src="../assets/js/public.js"></script>
    
    <style>
        /* ==========================================
           NEWSLETTER GRID RESPONSIVE
           ========================================== */
        @media (max-width: 768px) {
            .newsletter-grid { grid-template-columns: 1fr !important; }
            .footer-grid { grid-template-columns: 1fr !important; gap: 2rem !important; }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .footer-grid { grid-template-columns: 1fr 1fr !important; }
        }
        
        /* ==========================================
           FLOATING WHATSAPP (KIRI BAWAH)
           ========================================== */
        .floating-wa {
            position: fixed;
            bottom: 2rem;
            left: 2rem; /* DIPINDAH KE KIRI */
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: wa-bounce 2s ease-in-out infinite;
        }
        
        .floating-wa:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.6);
            animation-play-state: paused;
        }
        
        .floating-wa:hover .wa-tooltip {
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }
        
        @keyframes wa-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        /* Pulse Ring Animation */
        .wa-pulse {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #25D366;
            opacity: 0.7;
            animation: wa-pulse-anim 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            z-index: 1;
        }
        
        .wa-pulse.delay {
            animation-delay: 1s;
        }
        
        @keyframes wa-pulse-anim {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }
            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }
        
        /* WhatsApp Tooltip */
        .wa-tooltip {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(-10px);
            background: var(--surface);
            color: var(--text);
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }
        
        .wa-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: var(--surface);
        }
        
        /* ==========================================
           FLOATING BACK TO TOP (KANAN BAWAH)
           ========================================== */
        #floating-back-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        
        #floating-back-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        #floating-back-top:hover {
            background: #1e40af;
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
        }
        
        /* ==========================================
           RESPONSIVE FLOATING BUTTONS
           ========================================== */
        @media (max-width: 768px) {
            .floating-wa {
                bottom: 1.5rem;
                left: 1.5rem;
                width: 54px;
                height: 54px;
            }
            .wa-tooltip { display: none; }
            #floating-back-top {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 44px;
                height: 44px;
            }
        }
    </style>
    
    <script>
        // ==========================================
        // NEWSLETTER FORM HANDLER
        // ==========================================
        function handleNewsletter(e) {
            e.preventDefault();
            const email = document.getElementById('newsletter-email').value;
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> <span>' + (<?= $lang === 'en' ? "'Processing...'" : "'Memproses...'" ?>) + '</span>';
            
            // Simulate submission
            setTimeout(() => {
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span>' + (<?= $lang === 'en' ? "'Subscribed!'" : "'Berhasil!'" ?>) + '</span>';
                btn.style.background = '#10b981';
                btn.style.color = 'white';
                document.getElementById('newsletter-email').value = '';
                
                if (typeof showToast === 'function') {
                    showToast(
                        <?= $lang === 'en' ? "'Success'" : "'Berhasil'" ?>,
                        <?= $lang === 'en' ? "'You have subscribed to our newsletter'" : "'Anda telah berlangganan newsletter kami'" ?>,
                        'success'
                    );
                }
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.background = 'white';
                    btn.style.color = 'var(--primary)';
                }, 3000);
            }, 1200);
        }
        
        // ==========================================
        // FLOATING BACK TO TOP WITH PROGRESS RING
        // ==========================================
        const floatingBackTop = document.getElementById('floating-back-top');
        const progressCircle = document.querySelector('.progress-circle');
        const circumference = 2 * Math.PI * 46; // 289
        
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = scrolled / maxScroll;
            
            // Show/hide button
            if (scrolled > 300) {
                floatingBackTop.classList.add('show');
            } else {
                floatingBackTop.classList.remove('show');
            }
            
            // Update progress ring
            if (progressCircle) {
                const offset = circumference - (scrollPercent * circumference);
                progressCircle.style.strokeDashoffset = offset;
            }
        });
        
        // ==========================================
        // COUNTER ANIMATION ON SCROLL
        // ==========================================
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.getAttribute('data-counter'));
                    animateCounter(entry.target, target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('[data-counter]').forEach(el => observer.observe(el));
        
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString() + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString() + '+';
                }
            }, 30);
        }
        
        // Spin animation for loading
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    </script>
</body>
</html>