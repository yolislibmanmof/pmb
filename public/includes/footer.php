    </main>
    
    <footer id="kontak" class="footer">
        <div class="footer-grid">
            <!-- Kolom 1: Brand & Visi -->
            <div class="footer-brand">
                <img src="<?= sanitize(get_setting($pdo, 'logo_url', $lang)) ?>" alt="Logo Kampus" style="height: 45px; width: auto; object-fit: contain; margin-bottom: 1.5rem;">
                <p style="line-height: 1.7;" data-lang-id="Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas." data-lang-en="Creating a superior generation, globally competitive, and noble through quality higher education.">Mencetak generasi unggul, berdaya saing global, dan berakhlak mulia melalui pendidikan tinggi berkualitas.</p>
            </div>

            <!-- Kolom 2: Tautan Cepat -->
            <div>
                <h4 data-lang-id="Tautan Cepat" data-lang-en="Quick Links">Tautan Cepat</h4>
                <ul class="footer-links" style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.75rem;">
                        <a href="index.php" style="display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            <span data-lang-id="Beranda" data-lang-en="Home">Beranda</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="prodi.php" style="display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            <span data-lang-id="Program Studi" data-lang-en="Study Programs">Program Studi</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="alur.php" style="display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;" onmouseover="this.style.color='var(--primary)'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateX(0)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            <span data-lang-id="Alur Pendaftaran" data-lang-en="Registration Flow">Alur Pendaftaran</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3: Kontak Resmi -->
            <div>
                <h4 data-lang-id="Hubungi Kami" data-lang-en="Contact Us">Hubungi Kami</h4>
                <ul class="footer-links" style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 1rem;">
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; color: var(--text-muted);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 3px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>
                            <strong style="color: var(--text);" data-lang-id="Kampus 1:" data-lang-en="Campus 1:">Kampus 1:</strong> Jl. Dr. Ratulangi No 172<br>
                            <strong style="color: var(--text);" data-lang-id="Kampus 2:" data-lang-en="Campus 2:">Kampus 2:</strong> Jl. Pemuda Raya
                        </span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>+62 851-7427-0172</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>info@ukjp.ac.id</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p style="margin: 0;">&copy; <?= date('Y') ?> <?= sanitize(get_setting($pdo, 'site_name', $lang)) ?>. <span data-lang-id="Seluruh hak cipta dilindungi undang-undang." data-lang-en="All rights reserved.">Seluruh hak cipta dilindungi undang-undang.</span></p>
        </div>
    </footer>

    <!-- Floating WhatsApp Button (Pure SVG, No Emoji) -->
    <a href="https://wa.me/6285174270172?text=Halo%20Admin%20PMB%20UKJP,%20saya%20ingin%20bertanya." class="floating-wa" target="_blank" title="Hubungi via WhatsApp" style="display: flex; align-items: center; justify-content: center;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" style="color: white;">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <script src="../assets/js/public.js"></script>
</body>
</html>