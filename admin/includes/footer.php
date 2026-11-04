        <!-- ==========================================
             FOOTER BAR (Command Footer)
             ========================================== -->
        <footer class="admin-footer">
            <div class="footer-left">
                <div class="footer-info">
                    <span class="footer-copyright">© <?= date('Y') ?> <strong>PMB Ultimate</strong></span>
                    <span class="footer-divider">•</span>
                    <span class="footer-version">v1.0.0</span>
                </div>
                <div class="footer-status">
                    <span class="connection-status">
                        <span class="status-indicator online"></span>
                        <span>Terhubung</span>
                    </span>
                    <span class="footer-divider">•</span>
                    <span class="server-time" id="server-time"><?= date('H:i:s') ?> WITA</span>
                </div>
            </div>
            
            <div class="footer-center">
                <button class="footer-shortcut-btn" onclick="openShortcutsModal()" title="Keyboard Shortcuts (?)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h.01M12 12h.01M16 12h.01M8 16h8"/></svg>
                    <kbd>?</kbd>
                </button>
                <button class="footer-shortcut-btn" onclick="document.querySelector('.global-search')?.click()" title="Search (⌘K)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <kbd>⌘K</kbd>
                </button>
            </div>
            
            <div class="footer-right">
                <div class="footer-performance">
                    <span class="perf-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span id="page-load-time"><?= round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 0) ?>ms</span>
                    </span>
                    <span class="footer-divider">•</span>
                    <span class="perf-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        <span><?= number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) ?>MB</span>
                    </span>
                </div>
            </div>
        </footer>
        
        <!-- Back to Top Button -->
        <button id="back-to-top" class="back-to-top-btn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" aria-label="Back to top">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
        </button>
        
        <!-- Floating Action Button (Quick Actions) -->
        <div class="fab-container">
            <div class="fab-menu" id="fab-menu">
                <a href="applicants.php" class="fab-item" title="Data Pendaftar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </a>
                <a href="prodi.php" class="fab-item" title="Kelola Prodi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </a>
                <a href="exam-schedules.php" class="fab-item" title="Jadwal Ujian">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </a>
                <a href="export.php" class="fab-item" title="Export Data">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
            </div>
            <button class="fab-main" id="fab-main" onclick="toggleFab()" title="Quick Actions">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </div>
    </div>
    
    <!-- ==========================================
         UNIVERSAL SEARCH MODAL
         ========================================== -->
    <div class="modal-overlay" id="search-modal">
        <div class="modal search-modal-content">
            <div class="modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="universal-search-input" class="search-input" placeholder="Cari menu, data pendaftar, prodi..." autofocus style="border: none; background: transparent; flex: 1; font-size: 1rem; outline: none; color: var(--text);">
                    <kbd style="font-size: 0.7rem; padding: 0.125rem 0.375rem; background: var(--bg); border: 1px solid var(--border); border-radius: 4px; color: var(--text-muted);">ESC</kbd>
                </div>
            </div>
            <div class="modal-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                <div id="search-results" style="padding: 0.5rem;">
                    <div class="search-section">
                        <div class="search-section-title">Menu Cepat</div>
                        <a href="index.php" class="search-result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            <div>
                                <div class="result-title">Dashboard</div>
                                <div class="result-desc">Halaman utama admin</div>
                            </div>
                        </a>
                        <a href="applicants.php" class="search-result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <div>
                                <div class="result-title">Data Pendaftar</div>
                                <div class="result-desc">Kelola semua pendaftar</div>
                            </div>
                        </a>
                        <a href="prodi.php" class="search-result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <div>
                                <div class="result-title">Kelola Prodi</div>
                                <div class="result-desc">Program studi & kuota</div>
                            </div>
                        </a>
                        <a href="exam-schedules.php" class="search-result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <div>
                                <div class="result-title">Jadwal Ujian</div>
                                <div class="result-desc">Kelola jadwal & kartu</div>
                            </div>
                        </a>
                        <a href="settings.php" class="search-result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/></svg>
                            <div>
                                <div class="result-title">Pengaturan</div>
                                <div class="result-desc">Konfigurasi sistem</div>
                            </div>
                        </a>
                    </div>
                    <div class="search-empty" style="display: none; padding: 2rem; text-align: center; color: var(--text-muted);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; opacity: 0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <p>Tidak ada hasil ditemukan</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 0.75rem 1.5rem; background: var(--bg); border-top: 1px solid var(--border); font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 1rem;">
                <span><kbd>↑↓</kbd> Navigasi</span>
                <span><kbd>↵</kbd> Pilih</span>
                <span><kbd>ESC</kbd> Tutup</span>
            </div>
        </div>
    </div>
    
    <!-- ==========================================
         KEYBOARD SHORTCUTS MODAL
         ========================================== -->
    <div class="modal-overlay" id="shortcuts-modal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h.01M12 12h.01M16 12h.01M8 16h8"/></svg>
                    Keyboard Shortcuts
                </h3>
                <button type="button" class="modal-close" onclick="closeModal(document.getElementById('shortcuts-modal'))">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="shortcut-group">
                    <div class="shortcut-group-title">Navigasi</div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Cari menu/data</span>
                        <span class="shortcut-keys"><kbd>⌘</kbd> <kbd>K</kbd></span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Tampilkan shortcuts</span>
                        <span class="shortcut-keys"><kbd>?</kbd></span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Tutup modal/popup</span>
                        <span class="shortcut-keys"><kbd>ESC</kbd></span>
                    </div>
                </div>
                <div class="shortcut-group">
                    <div class="shortcut-group-title">Tema & Tampilan</div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Toggle dark mode</span>
                        <span class="shortcut-keys"><kbd>⌘</kbd> <kbd>⇧</kbd> <kbd>D</kbd></span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Scroll ke atas</span>
                        <span class="shortcut-keys"><kbd>⌘</kbd> <kbd>↑</kbd></span>
                    </div>
                </div>
                <div class="shortcut-group">
                    <div class="shortcut-group-title">Aksi Cepat</div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Ke halaman pendaftar</span>
                        <span class="shortcut-keys"><kbd>G</kbd> <kbd>P</kbd></span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Ke halaman prodi</span>
                        <span class="shortcut-keys"><kbd>G</kbd> <kbd>D</kbd></span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-desc">Ke dashboard</span>
                        <span class="shortcut-keys"><kbd>G</kbd> <kbd>H</kbd></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // ==========================================
        // USER DROPDOWN
        // ==========================================
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('open');
        }
        
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
        
        // ==========================================
        // THEME ICON TOGGLE
        // ==========================================
        function updateThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            const lightIcon = document.querySelector('.theme-icon-light');
            const darkIcon = document.querySelector('.theme-icon-dark');
            
            if (lightIcon && darkIcon) {
                if (theme === 'dark') {
                    lightIcon.style.display = 'none';
                    darkIcon.style.display = 'block';
                } else {
                    lightIcon.style.display = 'block';
                    darkIcon.style.display = 'none';
                }
            }
        }
        
        updateThemeIcon();
        
        const themeObserver = new MutationObserver(updateThemeIcon);
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        
        // ==========================================
        // UNIVERSAL SEARCH MODAL (Cmd/Ctrl + K)
        // ==========================================
        function openSearchModal() {
            const modal = document.getElementById('search-modal');
            modal.classList.add('active');
            setTimeout(() => {
                document.getElementById('universal-search-input').focus();
            }, 100);
        }
        
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + K: Open search
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                openSearchModal();
            }
            // ESC: Close all modals
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('user-dropdown');
                if (dropdown) dropdown.classList.remove('open');
                
                document.querySelectorAll('.modal-overlay.active').forEach(m => {
                    m.classList.remove('active');
                });
            }
            // ?: Open shortcuts
            if (e.key === '?' && !e.target.matches('input, textarea, select')) {
                e.preventDefault();
                openModal('shortcuts-modal');
            }
            // Goto shortcuts (G then letter)
            if (e.key === 'g' && !e.target.matches('input, textarea, select')) {
                document.addEventListener('keydown', function gotoHandler(e2) {
                    if (e2.key === 'p' || e2.key === 'P') window.location.href = 'applicants.php';
                    if (e2.key === 'd' || e2.key === 'D') window.location.href = 'prodi.php';
                    if (e2.key === 'h' || e2.key === 'H') window.location.href = 'index.php';
                    document.removeEventListener('keydown', gotoHandler);
                });
                setTimeout(() => document.removeEventListener('keydown', gotoHandler), 1000);
            }
            // Cmd/Ctrl + Shift + D: Toggle dark mode
            if ((e.metaKey || e.ctrlKey) && e.shiftKey && (e.key === 'd' || e.key === 'D')) {
                e.preventDefault();
                document.getElementById('admin-theme-toggle')?.click();
            }
            // Cmd/Ctrl + ArrowUp: Scroll to top
            if ((e.metaKey || e.ctrlKey) && e.key === 'ArrowUp') {
                e.preventDefault();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        });
        
        // Search input filtering
        document.getElementById('universal-search-input')?.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const items = document.querySelectorAll('.search-result-item');
            let hasResults = false;
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query) || query === '') {
                    item.style.display = 'flex';
                    if (query !== '') hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            const emptyEl = document.querySelector('.search-empty');
            if (emptyEl) {
                emptyEl.style.display = (query !== '' && !hasResults) ? 'block' : 'none';
            }
        });
        
        // ==========================================
        // FLOATING ACTION BUTTON (FAB)
        // ==========================================
        function toggleFab() {
            const fabMain = document.getElementById('fab-main');
            const fabMenu = document.getElementById('fab-menu');
            fabMain.classList.toggle('active');
            fabMenu.classList.toggle('active');
        }
        
        document.addEventListener('click', (e) => {
            const fab = document.querySelector('.fab-container');
            if (fab && !fab.contains(e.target)) {
                document.getElementById('fab-main')?.classList.remove('active');
                document.getElementById('fab-menu')?.classList.remove('active');
            }
        });
        
        // ==========================================
        // BACK TO TOP BUTTON
        // ==========================================
        const backToTopBtn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        // ==========================================
        // LIVE SERVER CLOCK
        // ==========================================
        function updateServerTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const timeEl = document.getElementById('server-time');
            if (timeEl) timeEl.textContent = timeStr + ' WITA';
        }
        setInterval(updateServerTime, 1000);
        
        // ==========================================
        // CONNECTION STATUS MONITOR
        // ==========================================
        function updateConnectionStatus() {
            const indicator = document.querySelector('.status-indicator');
            if (!indicator) return;
            
            if (navigator.onLine) {
                indicator.classList.remove('offline');
                indicator.classList.add('online');
                indicator.parentElement.querySelector('span:last-child').textContent = 'Terhubung';
            } else {
                indicator.classList.remove('online');
                indicator.classList.add('offline');
                indicator.parentElement.querySelector('span:last-child').textContent = 'Offline';
            }
        }
        
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        updateConnectionStatus();
    </script>
    
    <style>
        /* ==========================================
           ADMIN FOOTER
           ========================================== */
        .admin-footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: auto;
        }
        
        .footer-left, .footer-right, .footer-center {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .footer-left { flex: 1; }
        .footer-center { flex-shrink: 0; }
        .footer-right { flex: 1; justify-content: flex-end; }
        
        .footer-info, .footer-status, .footer-performance {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .footer-copyright strong { color: var(--text); }
        .footer-divider { opacity: 0.3; }
        
        .connection-status {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-indicator.online {
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            animation: pulse-status 2s ease-in-out infinite;
        }
        
        .status-indicator.offline {
            background: var(--danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .footer-shortcut-btn {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.625rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.75rem;
        }
        
        .footer-shortcut-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .footer-shortcut-btn kbd {
            font-family: inherit;
            font-size: 0.65rem;
            padding: 0.0625rem 0.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 3px;
        }
        
        .perf-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 0.75rem;
        }
        
        /* ==========================================
           BACK TO TOP BUTTON
           ========================================== */
        .back-to-top-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 90;
        }
        
        .back-to-top-btn.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .back-to-top-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
        }
        
        /* ==========================================
           FLOATING ACTION BUTTON (FAB)
           ========================================== */
        .fab-container {
            position: fixed;
            bottom: 2rem;
            right: 5rem;
            z-index: 90;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            gap: 0.75rem;
        }
        
        .fab-main {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .fab-main:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(30, 64, 175, 0.5);
        }
        
        .fab-main.active {
            transform: rotate(45deg);
            background: var(--danger);
        }
        
        .fab-menu {
            display: flex;
            flex-direction: column-reverse;
            gap: 0.625rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .fab-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .fab-item {
            width: 44px;
            height: 44px;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .fab-item:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        .fab-item svg { width: 20px; height: 20px; }
        
        /* ==========================================
           SEARCH MODAL
           ========================================== */
        .search-modal-content {
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
        }
        
        .search-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem 0.5rem;
        }
        
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .search-result-item:hover {
            background: var(--bg);
        }
        
        .search-result-item svg {
            width: 20px;
            height: 20px;
            color: var(--primary);
            flex-shrink: 0;
        }
        
        .result-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text);
        }
        
        .result-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.125rem;
        }
        
        /* ==========================================
           SHORTCUTS MODAL
           ========================================== */
        .shortcut-group {
            margin-bottom: 1.5rem;
        }
        
        .shortcut-group:last-child { margin-bottom: 0; }
        
        .shortcut-group-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }
        
        .shortcut-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .shortcut-item:last-child { border-bottom: none; }
        
        .shortcut-desc {
            font-size: 0.875rem;
            color: var(--text);
        }
        
        .shortcut-keys {
            display: flex;
            gap: 0.25rem;
        }
        
        .shortcut-keys kbd {
            font-family: inherit;
            font-size: 0.7rem;
            padding: 0.125rem 0.5rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        /* ==========================================
           RESPONSIVE FOOTER
           ========================================== */
        @media (max-width: 1024px) {
            .admin-footer {
                flex-wrap: wrap;
                padding: 0.75rem 1rem;
            }
            .footer-center, .footer-performance { display: none; }
            .footer-right { flex: 0; }
        }
        
        @media (max-width: 768px) {
            .fab-container {
                bottom: 1rem;
                right: 1rem;
            }
            .back-to-top-btn {
                bottom: 1rem;
                right: 1rem;
            }
            .fab-container { right: 5rem; }
        }
    </style>
</body>
</html>