<?php
$page_title = "Kontak Kami";
require_once 'includes/header.php';
?>

<!-- ==========================================
     1. MINI HERO (Page Header)
     ========================================== -->
<section style="padding: 10rem 2rem 4rem; background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, transparent 100%); text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'" data-lang-id="Beranda" data-lang-en="Home">Beranda</a>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="Kontak Kami" data-lang-en="Contact Us">Kontak Kami</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Kami Siap Membantu Anda" data-lang-en="We Are Ready to Help You">Kami Siap Membantu Anda</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 600px; margin: 0 auto;" data-lang-id="Punya pertanyaan seputar pendaftaran, beasiswa, atau program studi? Tim kami siap memberikan jawaban terbaik." data-lang-en="Have questions about registration, scholarships, or study programs? Our team is ready to provide the best answers.">Punya pertanyaan seputar pendaftaran, beasiswa, atau program studi? Tim kami siap memberikan jawaban terbaik.</p>
    </div>
</section>

<!-- ==========================================
     2. CONTACT INFO GRID (3 Cards)
     ========================================== -->
<section class="section" style="padding-top: 2rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; max-width: 1100px; margin: 0 auto;">
        
        <!-- Card 1: Alamat -->
        <div style="background: var(--surface); padding: 2.5rem 2rem; border-radius: 20px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 15px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <div style="width: 56px; height: 56px; background: rgba(30, 64, 175, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--primary); margin-bottom: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;" data-lang-id="Alamat Kampus" data-lang-en="Campus Address">Alamat Kampus</h3>
            <div style="color: var(--text-muted); line-height: 1.8;">
                <p style="margin-bottom: 0.75rem;"><strong style="color: var(--text);" data-lang-id="Kampus 1 (Pusat):" data-lang-en="Campus 1 (Main):">Kampus 1 (Pusat):</strong><br>Jl. Dr. Ratulangi No 172, Palopo</p>
                <p><strong style="color: var(--text);" data-lang-id="Kampus 2:" data-lang-en="Campus 2:">Kampus 2:</strong><br>Jl. Pemuda Raya, Palopo</p>
            </div>
            <a href="https://maps.google.com" target="_blank" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem; color: var(--primary); font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: gap 0.3s;" onmouseover="this.style.gap='0.75rem'" onmouseout="this.style.gap='0.5rem'">
                <span data-lang-id="Buka di Google Maps" data-lang-en="Open in Google Maps">Buka di Google Maps</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>

        <!-- Card 2: Kontak Langsung -->
        <div style="background: var(--surface); padding: 2.5rem 2rem; border-radius: 20px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 15px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <div style="width: 56px; height: 56px; background: rgba(16, 185, 129, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #10b981; margin-bottom: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;" data-lang-id="Kontak Langsung" data-lang-en="Direct Contact">Kontak Langsung</h3>
            <div style="color: var(--text-muted); line-height: 1.8;">
                <p style="margin-bottom: 0.75rem;"><strong style="color: var(--text);">WhatsApp / Telepon:</strong><br>+62 851-7427-0172</p>
                <p><strong style="color: var(--text);">Email:</strong><br>info@ukjp.ac.id</p>
            </div>
            <a href="https://wa.me/6285174270172?text=Halo%20Admin%20PMB%20UKJP,%20saya%20ingin%20bertanya%20tentang%20pendaftaran." target="_blank" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%; justify-content: center; background: #25D366; border: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span data-lang-id="Chat WhatsApp Sekarang" data-lang-en="Chat on WhatsApp Now">Chat WhatsApp Sekarang</span>
            </a>
        </div>

        <!-- Card 3: Jam Operasional -->
        <div style="background: var(--surface); padding: 2.5rem 2rem; border-radius: 20px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 15px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <div style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--accent); margin-bottom: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;" data-lang-id="Jam Operasional" data-lang-en="Operating Hours">Jam Operasional</h3>
            <div style="color: var(--text-muted); line-height: 1.8;">
                <p style="margin-bottom: 0.75rem;"><strong style="color: var(--text);" data-lang-id="Senin - Jumat:" data-lang-en="Monday - Friday:">Senin - Jumat:</strong><br>08.00 - 16.00 WITA</p>
                <p style="margin-bottom: 0.75rem;"><strong style="color: var(--text);" data-lang-id="Sabtu:" data-lang-en="Saturday:">Sabtu:</strong><br>09.00 - 14.00 WITA</p>
                <p><strong style="color: var(--text);" data-lang-id="Minggu & Hari Libur:" data-lang-en="Sunday & Holidays:">Minggu & Hari Libur:</strong><br><span style="color: #ef4444; font-weight: 600;" data-lang-id="Tutup" data-lang-en="Closed">Tutup</span></p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     3. MAP & QUICK MESSAGE SECTION
     ========================================== -->
<section class="section" style="padding-top: 2rem;">
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 3rem; max-width: 1100px; margin: 0 auto; align-items: stretch;">
        
        <!-- Peta Lokasi (Styled Placeholder / Iframe ready) -->
        <div style="background: var(--surface); border-radius: 24px; border: 1px solid var(--border); overflow: hidden; position: relative; min-height: 400px;">
            <!-- Ganti src iframe di bawah ini dengan link Google Maps Embed asli kampus -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.123456789!2d120.123456!3d-3.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMDcnMjQuNCJTIDEyMMKwMDcnMjQuNCJF!5e0!3m2!1sen!2sid!4v1234567890" width="100%" height="100%" style="border:0; position: absolute; top: 0; left: 0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            <!-- Overlay badge -->
            <div style="position: absolute; bottom: 20px; left: 20px; background: var(--surface); padding: 1rem 1.5rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem; z-index: 10;">
                <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></div>
                <span style="font-weight: 600; font-size: 0.9rem;" data-lang-id="Lokasi Kampus Aktif" data-lang-en="Active Campus Location">Lokasi Kampus Aktif</span>
            </div>
        </div>

        <!-- Quick Message Form (WhatsApp Redirect) -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 24px; padding: 2.5rem; color: white; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 20px 40px -10px rgba(30, 64, 175, 0.4);">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;" data-lang-id="Butuh Bantuan Cepat?" data-lang-en="Need Quick Help?">Butuh Bantuan Cepat?</h3>
            <p style="opacity: 0.9; margin-bottom: 2rem; line-height: 1.6;" data-lang-id="Kirimkan pesan Anda, dan tim admin kami akan membalas secepat mungkin melalui WhatsApp." data-lang-en="Send us a message, and our admin team will reply as soon as possible via WhatsApp.">Kirimkan pesan Anda, dan tim admin kami akan membalas secepat mungkin melalui WhatsApp.</p>
            
            <form onsubmit="event.preventDefault(); window.open('https://wa.me/6285174270172?text=' + encodeURIComponent('Halo Admin, ' + document.getElementById('msg-name').value + ' ingin bertanya tentang: ' + document.getElementById('msg-topic').value), '_blank');" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="text" id="msg-name" placeholder="Nama Lengkap Anda" required style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white; font-size: 0.95rem; outline: none; transition: all 0.3s;" onfocus="this.style.background='rgba(255,255,255,0.2)'; this.style.borderColor='white'" onblur="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.2)'" data-lang-id="Nama Lengkap Anda" data-lang-en="Your Full Name">
                
                <select id="msg-topic" required style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white; font-size: 0.95rem; outline: none; cursor: pointer; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23ffffff' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 1rem center;" onfocus="this.style.background='rgba(255,255,255,0.2)'">
                    <option value="Informasi Prodi" style="color: black;" data-lang-id="Informasi Program Studi" data-lang-en="Study Program Info">Informasi Program Studi</option>
                    <option value="Biaya & Beasiswa" style="color: black;" data-lang-id="Biaya Kuliah & Beasiswa" data-lang-en="Tuition & Scholarships">Biaya Kuliah & Beasiswa</option>
                    <option value="Teknis Pendaftaran" style="color: black;" data-lang-id="Kendala Teknis Pendaftaran" data-lang-en="Registration Technical Issues">Kendala Teknis Pendaftaran</option>
                    <option value="Lainnya" style="color: black;" data-lang-id="Lainnya" data-lang-en="Others">Lainnya</option>
                </select>

                <button type="submit" class="btn" style="background: white; color: var(--primary); font-weight: 700; padding: 1rem; border-radius: 12px; border: none; cursor: pointer; margin-top: 0.5rem; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span data-lang-id="Kirim Pesan via WhatsApp" data-lang-en="Send Message via WhatsApp">Kirim Pesan via WhatsApp</span>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Mobile Responsive Adjustments for this page only -->
<style>
    @media (max-width: 900px) {
        .section > div[style*="grid-template-columns: 1.5fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 768px) {
        h1[style*="font-size: 3rem"] { font-size: 2.25rem !important; }
    }
</style>

<?php require_once 'includes/footer.php'; ?>