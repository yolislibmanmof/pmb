<?php
$page_title = "Alur Pendaftaran";
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
            <span style="color: var(--primary); font-weight: 600;" data-lang-id="Alur Pendaftaran" data-lang-en="Registration Flow">Alur Pendaftaran</span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;" data-lang-id="Tata Cara Pendaftaran Mahasiswa Baru" data-lang-en="New Student Registration Procedure">Tata Cara Pendaftaran Mahasiswa Baru</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 600px; margin: 0 auto;" data-lang-id="Ikuti panduan langkah demi langkah berikut untuk menyelesaikan proses pendaftaran Anda dengan mudah dan cepat." data-lang-en="Follow this step-by-step guide to complete your registration process easily and quickly.">Ikuti panduan langkah demi langkah berikut untuk menyelesaikan proses pendaftaran Anda dengan mudah dan cepat.</p>
    </div>
</section>

<!-- ==========================================
     2. INTERACTIVE TIMELINE (Alur Pendaftaran)
     ========================================== -->
<section class="section" style="padding-top: 2rem;">
    <div style="max-width: 900px; margin: 0 auto; position: relative;">
        
        <!-- Vertical Line Connector (Hidden on mobile) -->
        <div style="position: absolute; left: 31px; top: 40px; bottom: 40px; width: 2px; background: linear-gradient(to bottom, var(--primary), var(--accent)); display: block; z-index: 0;" class="timeline-line"></div>
        
        <!-- STEP 1 -->
        <div style="display: flex; gap: 2rem; margin-bottom: 3rem; position: relative; z-index: 1;" class="timeline-item">
            <div style="flex-shrink: 0; width: 64px; height: 64px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3); border: 4px solid var(--bg);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            </div>
            <div style="flex-grow: 1; background: var(--surface); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 700;" data-lang-id="1. Buat Akun & Pilih Jalur" data-lang-en="1. Create Account & Choose Pathway">1. Buat Akun & Pilih Jalur</h3>
                    <span style="font-size: 0.75rem; background: rgba(30, 64, 175, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600;">⏱️ 5 Menit</span>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;" data-lang-id="Daftarkan email aktif Anda, lalu pilih gelombang pendaftaran dan jalur masuk (Reguler, Prestasi, atau Transfer)." data-lang-en="Register your active email, then choose the registration wave and entry pathway (Regular, Achievement, or Transfer).">Daftarkan email aktif Anda, lalu pilih gelombang pendaftaran dan jalur masuk (Reguler, Prestasi, atau Transfer).</p>
                <div style="background: rgba(245, 158, 11, 0.1); padding: 0.75rem 1rem; border-radius: 8px; border-left: 3px solid var(--accent); font-size: 0.9rem; color: var(--text);">
                    <strong style="color: var(--accent);" data-lang-id="💡 Tips:" data-lang-en="💡 Tip:">💡 Tips:</strong> <span data-lang-id="Gunakan email pribadi yang sering Anda akses untuk menerima kode verifikasi." data-lang-en="Use a personal email you frequently access to receive verification codes.">Gunakan email pribadi yang sering Anda akses untuk menerima kode verifikasi.</span>
                </div>
            </div>
        </div>

        <!-- STEP 2 -->
        <div style="display: flex; gap: 2rem; margin-bottom: 3rem; position: relative; z-index: 1;" class="timeline-item">
            <div style="flex-shrink: 0; width: 64px; height: 64px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3); border: 4px solid var(--bg);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div style="flex-grow: 1; background: var(--surface); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 700;" data-lang-id="2. Lengkapi Formulir Data Diri" data-lang-en="2. Complete Personal Data Form">2. Lengkapi Formulir Data Diri</h3>
                    <span style="font-size: 0.75rem; background: rgba(30, 64, 175, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600;">⏱️ 15 Menit</span>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;" data-lang-id="Isi biodata lengkap, riwayat pendidikan, data orang tua/wali, dan pilih program studi yang diminati (Pilihan 1 & 2)." data-lang-en="Fill in complete biodata, educational history, parent/guardian data, and choose your preferred study programs (Choice 1 & 2).">Isi biodata lengkap, riwayat pendidikan, data orang tua/wali, dan pilih program studi yang diminati (Pilihan 1 & 2).</p>
                <div style="background: rgba(245, 158, 11, 0.1); padding: 0.75rem 1rem; border-radius: 8px; border-left: 3px solid var(--accent); font-size: 0.9rem; color: var(--text);">
                    <strong style="color: var(--accent);" data-lang-id="💡 Tips:" data-lang-en="💡 Tip:">💡 Tips:</strong> <span data-lang-id="Pastikan NIK dan nama lengkap sesuai dengan KTP/Ijazah untuk menghindari masalah verifikasi." data-lang-en="Ensure your ID number and full name match your ID card/Diploma to avoid verification issues.">Pastikan NIK dan nama lengkap sesuai dengan KTP/Ijazah untuk menghindari masalah verifikasi.</span>
                </div>
            </div>
        </div>

        <!-- STEP 3 -->
        <div style="display: flex; gap: 2rem; margin-bottom: 3rem; position: relative; z-index: 1;" class="timeline-item">
            <div style="flex-shrink: 0; width: 64px; height: 64px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3); border: 4px solid var(--bg);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div style="flex-grow: 1; background: var(--surface); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 700;" data-lang-id="3. Unggah Berkas Persyaratan" data-lang-en="3. Upload Required Documents">3. Unggah Berkas Persyaratan</h3>
                    <span style="font-size: 0.75rem; background: rgba(30, 64, 175, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600;">⏱️ 10 Menit</span>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;" data-lang-id="Scan dan unggah dokumen dalam format PDF/JPG (Maks. 2MB per file). Pastikan hasil scan jelas dan tidak terpotong." data-lang-en="Scan and upload documents in PDF/JPG format (Max. 2MB per file). Ensure scans are clear and not cropped.">Scan dan unggah dokumen dalam format PDF/JPG (Maks. 2MB per file). Pastikan hasil scan jelas dan tidak terpotong.</p>
                <div style="background: rgba(245, 158, 11, 0.1); padding: 0.75rem 1rem; border-radius: 8px; border-left: 3px solid var(--accent); font-size: 0.9rem; color: var(--text);">
                    <strong style="color: var(--accent);" data-lang-id="💡 Tips:" data-lang-en="💡 Tip:">💡 Tips:</strong> <span data-lang-id="Siapkan folder khusus di HP/Laptop berisi semua dokumen yang sudah di-rename agar proses upload lebih cepat." data-lang-en="Prepare a specific folder on your Phone/Laptop containing all renamed documents for faster upload process.">Siapkan folder khusus di HP/Laptop berisi semua dokumen yang sudah di-rename agar proses upload lebih cepat.</span>
                </div>
            </div>
        </div>

        <!-- STEP 4 -->
        <div style="display: flex; gap: 2rem; margin-bottom: 3rem; position: relative; z-index: 1;" class="timeline-item">
            <div style="flex-shrink: 0; width: 64px; height: 64px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3); border: 4px solid var(--bg);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div style="flex-grow: 1; background: var(--surface); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 30px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 700;" data-lang-id="4. Pembayaran Biaya Pendaftaran" data-lang-en="4. Registration Fee Payment">4. Pembayaran Biaya Pendaftaran</h3>
                    <span style="font-size: 0.75rem; background: rgba(30, 64, 175, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600;">⏱️ 5 Menit</span>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;" data-lang-id="Lakukan pembayaran melalui Virtual Account (Bank BCA/Mandiri/BRI) atau E-Wallet (OVO/DANA/GoPay) yang tersedia." data-lang-en="Make payment via Virtual Account (BCA/Mandiri/BRI Bank) or available E-Wallets (OVO/DANA/GoPay).">Lakukan pembayaran melalui Virtual Account (Bank BCA/Mandiri/BRI) atau E-Wallet (OVO/DANA/GoPay) yang tersedia.</p>
                <div style="background: rgba(245, 158, 11, 0.1); padding: 0.75rem 1rem; border-radius: 8px; border-left: 3px solid var(--accent); font-size: 0.9rem; color: var(--text);">
                    <strong style="color: var(--accent);" data-lang-id="💡 Tips:" data-lang-en="💡 Tip:">💡 Tips:</strong> <span data-lang-id="Simpan bukti transfer. Sistem akan memverifikasi pembayaran secara otomatis dalam 1x24 jam." data-lang-en="Save the transfer receipt. The system will automatically verify payment within 24 hours.">Simpan bukti transfer. Sistem akan memverifikasi pembayaran secara otomatis dalam 1x24 jam.</span>
                </div>
            </div>
        </div>

        <!-- STEP 5 -->
        <div style="display: flex; gap: 2rem; position: relative; z-index: 1;" class="timeline-item">
            <div style="flex-shrink: 0; width: 64px; height: 64px; background: linear-gradient(135deg, var(--accent), #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4); border: 4px solid var(--bg);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div style="flex-grow: 1; background: linear-gradient(135deg, var(--surface) 0%, rgba(245, 158, 11, 0.05) 100%); padding: 2rem; border-radius: 16px; border: 1px solid var(--accent); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 30px -5px rgba(245, 158, 11, 0.2)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 700;" data-lang-id="5. Cetak Kartu & Daftar Ulang" data-lang-en="5. Print Card & Re-registration">5. Cetak Kartu & Daftar Ulang</h3>
                    <span style="font-size: 0.75rem; background: var(--accent); color: white; padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600;">🎉 Selesai!</span>
                </div>
                <p style="color: var(--text-muted);" data-lang-id="Setelah lulus seleksi, cetak Kartu Mahasiswa dan lakukan daftar ulang untuk mengamankan kursi Anda di program studi pilihan." data-lang-en="After passing the selection, print your Student Card and complete re-registration to secure your seat in the chosen study program.">Setelah lulus seleksi, cetak Kartu Mahasiswa dan lakukan daftar ulang untuk mengamankan kursi Anda di program studi pilihan.</p>
            </div>
        </div>

    </div>
</section>

<!-- ==========================================
     3. REQUIREMENTS CHECKLIST (Grid)
     ========================================== -->
<section class="section" style="background: var(--surface); border-radius: 32px; border: 1px solid var(--border); margin: 4rem auto;">
    <div class="section-header">
        <h2 data-lang-id="Berkas yang Harus Disiapkan" data-lang-en="Documents to Prepare">Berkas yang Harus Disiapkan</h2>
        <p data-lang-id="Pastikan Anda memiliki softfile (scan) dari dokumen berikut sebelum memulai pendaftaran." data-lang-en="Ensure you have the softfiles (scans) of the following documents before starting registration.">Pastikan Anda memiliki softfile (scan) dari dokumen berikut sebelum memulai pendaftaran.</p>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 1000px; margin: 0 auto;">
        
        <!-- Item 1 -->
        <div style="background: var(--bg); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); display: flex; gap: 1rem; align-items: flex-start;">
            <div style="flex-shrink: 0; width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2"/><path d="M15 12h2"/><path d="M7 16h10"/></svg>
            </div>
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;" data-lang-id="Pas Foto Terbaru" data-lang-en="Recent Passport Photo">Pas Foto Terbaru</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted);" data-lang-id="Latar merah/biru, format JPG (Maks 1MB)" data-lang-en="Red/blue background, JPG format (Max 1MB)">Latar merah/biru, format JPG (Maks 1MB)</p>
            </div>
        </div>

        <!-- Item 2 -->
        <div style="background: var(--bg); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); display: flex; gap: 1rem; align-items: flex-start;">
            <div style="flex-shrink: 0; width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10"/><path d="M7 12h10"/><path d="M7 16h6"/></svg>
            </div>
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;" data-lang-id="Scan Ijazah / SKL" data-lang-en="Diploma / SKL Scan">Scan Ijazah / SKL</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted);" data-lang-id="Format PDF berwarna (Maks 2MB)" data-lang-en="Color PDF format (Max 2MB)">Format PDF berwarna (Maks 2MB)</p>
            </div>
        </div>

        <!-- Item 3 -->
        <div style="background: var(--bg); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); display: flex; gap: 1rem; align-items: flex-start;">
            <div style="flex-shrink: 0; width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;" data-lang-id="Rapor SMA/SMK" data-lang-en="High School Report Card">Rapor SMA/SMK</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted);" data-lang-id="Semester 1-5, Format PDF (Maks 3MB)" data-lang-en="Semester 1-5, PDF Format (Max 3MB)">Semester 1-5, Format PDF (Maks 3MB)</p>
            </div>
        </div>

        <!-- Item 4 -->
        <div style="background: var(--bg); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); display: flex; gap: 1rem; align-items: flex-start;">
            <div style="flex-shrink: 0; width: 40px; height: 40px; background: rgba(30, 64, 175, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;" data-lang-id="Kartu Keluarga (KK)" data-lang-en="Family Card (KK)">Kartu Keluarga (KK)</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted);" data-lang-id="Scan asli, Format PDF (Maks 2MB)" data-lang-en="Original scan, PDF Format (Max 2MB)">Scan asli, Format PDF (Maks 2MB)</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     4. FAQ SECTION (Accordion)
     ========================================== -->
<section class="section">
    <div class="section-header">
        <h2 data-lang-id="Pertanyaan yang Sering Diajukan" data-lang-en="Frequently Asked Questions">Pertanyaan yang Sering Diajukan</h2>
    </div>
    <div style="max-width: 800px; margin: 0 auto;" id="faq-container">
        
        <!-- FAQ Item 1 -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; overflow: hidden; transition: all 0.3s ease;">
            <button onclick="toggleFaq(this)" style="width: 100%; padding: 1.5rem; background: none; border: none; text-align: left; display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: var(--text); font-weight: 600; font-size: 1.05rem;">
                <span data-lang-id="Apakah bisa mengganti pilihan prodi setelah mendaftar?" data-lang-en="Can I change my study program choice after registering?">Apakah bisa mengganti pilihan prodi setelah mendaftar?</span>
                <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition: transform 0.3s ease; flex-shrink: 0; margin-left: 1rem;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                <div style="padding: 0 1.5rem 1.5rem;" data-lang-id="Bisa, selama Anda belum melakukan pembayaran biaya pendaftaran. Jika sudah bayar, silakan hubungi admin PMB melalui WhatsApp untuk permintaan perubahan." data-lang-en="Yes, as long as you haven't paid the registration fee. If you have paid, please contact the PMB admin via WhatsApp to request changes.">Bisa, selama Anda belum melakukan pembayaran biaya pendaftaran. Jika sudah bayar, silakan hubungi admin PMB melalui WhatsApp untuk permintaan perubahan.</div>
            </div>
        </div>

        <!-- FAQ Item 2 -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; overflow: hidden; transition: all 0.3s ease;">
            <button onclick="toggleFaq(this)" style="width: 100%; padding: 1.5rem; background: none; border: none; text-align: left; display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: var(--text); font-weight: 600; font-size: 1.05rem;">
                <span data-lang-id="Berapa lama waktu verifikasi berkas?" data-lang-en="How long does document verification take?">Berapa lama waktu verifikasi berkas?</span>
                <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition: transform 0.3s ease; flex-shrink: 0; margin-left: 1rem;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                <div style="padding: 0 1.5rem 1.5rem;" data-lang-id="Verifikasi berkas memakan waktu maksimal 2x24 jam pada hari kerja. Notifikasi akan dikirimkan ke email dan dashboard Anda." data-lang-en="Document verification takes a maximum of 2x24 hours on working days. Notifications will be sent to your email and dashboard.">Verifikasi berkas memakan waktu maksimal 2x24 jam pada hari kerja. Notifikasi akan dikirimkan ke email dan dashboard Anda.</div>
            </div>
        </div>

        <!-- FAQ Item 3 -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; overflow: hidden; transition: all 0.3s ease;">
            <button onclick="toggleFaq(this)" style="width: 100%; padding: 1.5rem; background: none; border: none; text-align: left; display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: var(--text); font-weight: 600; font-size: 1.05rem;">
                <span data-lang-id="Apakah lulusan tahun lalu boleh mendaftar?" data-lang-en="Can graduates from previous years apply?">Apakah lulusan tahun lalu boleh mendaftar?</span>
                <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition: transform 0.3s ease; flex-shrink: 0; margin-left: 1rem;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                <div style="padding: 0 1.5rem 1.5rem;" data-lang-id="Sangat boleh! Kami menerima lulusan SMA/SMK/Sederajat dari tahun mana pun selama memiliki ijazah atau SKL yang sah." data-lang-en="Absolutely! We accept High School/Vocational graduates from any year as long as they have a valid diploma or temporary certificate.">Sangat boleh! Kami menerima lulusan SMA/SMK/Sederajat dari tahun mana pun selama memiliki ijazah atau SKL yang sah.</div>
            </div>
        </div>

    </div>
</section>

<!-- ==========================================
     5. FINAL CTA
     ========================================== -->
<section style="background: var(--surface); border: 1px solid var(--border); border-radius: 32px; padding: 4rem 2rem; text-align: center; max-width: 900px; margin: 4rem auto;">
    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: white; box-shadow: 0 10px 30px rgba(30, 64, 175, 0.3);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </div>
    <h2 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem;" data-lang-id="Sudah Siap Bergabung?" data-lang-en="Ready to Join?">Sudah Siap Bergabung?</h2>
    <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto 2.5rem;" data-lang-id="Jangan lewatkan kesempatan emas ini. Klik tombol di bawah untuk mulai mengisi formulir pendaftaran." data-lang-en="Don't miss this golden opportunity. Click the button below to start filling out the registration form.">Jangan lewatkan kesempatan emas ini. Klik tombol di bawah untuk mulai mengisi formulir pendaftaran.</p>
    <a href="register.php" class="btn btn-primary" style="padding: 1.1rem 3rem; font-size: 1.1rem;" data-lang-id="Mulai Pendaftaran Sekarang" data-lang-en="Start Registration Now">Mulai Pendaftaran Sekarang</a>
</section>

<!-- Hide timeline line on mobile -->
<style>
    @media (max-width: 768px) {
        .timeline-line { display: none !important; }
        .timeline-item { flex-direction: column; gap: 1rem; }
        .timeline-item > div:first-child { align-self: flex-start; }
    }
</style>

<script>
    // FAQ Accordion Logic
    function toggleFaq(button) {
        const item = button.parentElement;
        const answer = item.querySelector('.faq-answer');
        const icon = button.querySelector('.faq-icon');
        
        // Close all others
        document.querySelectorAll('#faq-container > div').forEach(otherItem => {
            if (otherItem !== item) {
                otherItem.querySelector('.faq-answer').style.maxHeight = null;
                otherItem.querySelector('.faq-icon').style.transform = 'rotate(0deg)';
            }
        });

        // Toggle current
        if (answer.style.maxHeight) {
            answer.style.maxHeight = null;
            icon.style.transform = 'rotate(0deg)';
        } else {
            answer.style.maxHeight = answer.scrollHeight + "px";
            icon.style.transform = 'rotate(180deg)';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>