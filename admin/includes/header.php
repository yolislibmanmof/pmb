<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_login();

$current_page = basename($_SERVER['PHP_SELF']);

// Ambil jumlah notifikasi pending (untuk badge)
try {
    $stmt_notif = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status_daftar = 'Pending'");
    $pending_count = (int)$stmt_notif->fetchColumn();
} catch (Exception $e) {
    $pending_count = 0;
}

// System info
$system_version = 'PMB Ultimate v1.0';
$db_status = 'connected';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
        <script>
    /* Terapkan tema tersimpan SESAWAL mungkin.
       Ini menjaga mode gelap tetap aktif bahkan jika PHP error di tengah halaman. */
    (function(){ try { var t = localStorage.getItem('theme'); if (t) document.documentElement.setAttribute('data-theme', t); } catch(e){} })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' - Admin PMB' : 'Admin PMB' ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Page-specific styles for header enhancements */
        .sidebar-brand-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(16, 185, 129, 0.08);
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            animation: pulse-status 2s ease-in-out infinite;
        }
        
        @keyframes pulse-status {
            0%, 100% { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
            50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.1); }
        }
        
        .menu-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.125rem 0.5rem;
            border-radius: 50px;
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-nav a.active .menu-badge {
            background: rgba(255, 255, 255, 0.25);
        }
        
        .global-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 260px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .global-search:hover {
            border-color: var(--primary);
        }
        
        .global-search svg {
            width: 16px;
            height: 16px;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        
        .global-search span {
            flex: 1;
            color: var(--text-muted);
            font-size: 0.875rem;
            text-align: left;
        }
        
        .global-search kbd {
            font-family: inherit;
            font-size: 0.7rem;
            padding: 0.125rem 0.375rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-muted);
            font-weight: 600;
        }
        
        .icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .icon-btn:hover {
            background: var(--surface-hover);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .icon-btn svg {
            width: 18px;
            height: 18px;
        }
        
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.125rem 0.375rem;
            border-radius: 50px;
            min-width: 18px;
            text-align: center;
            border: 2px solid var(--bg);
            animation: badge-pulse 2s ease-in-out infinite;
        }
        
        @keyframes badge-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.375rem 0.875rem 0.375rem 0.375rem;
            background: var(--bg);
            border-radius: 50px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-trigger:hover {
            border-color: var(--primary);
            background: var(--surface-hover);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text);
            line-height: 1.2;
        }
        
        .user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
            line-height: 1.2;
        }
        
        .user-trigger svg.chevron {
            width: 14px;
            height: 14px;
            color: var(--text-muted);
            transition: transform 0.3s ease;
        }
        
        .user-dropdown.open .user-trigger svg.chevron {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 220px;
            padding: 0.5rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.25s ease;
            z-index: 100;
        }
        
        .user-dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 0.75rem 0.75rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 0.5rem;
        }
        
        .dropdown-header-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text);
        }
        
        .dropdown-header-email {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.125rem;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        
        .dropdown-item:hover {
            background: var(--bg);
            color: var(--primary);
        }
        
        .dropdown-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 0.5rem 0;
        }
        
        .dropdown-item.danger {
            color: var(--danger);
        }
        
        .dropdown-item.danger:hover {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
        }
        
        @media (max-width: 1024px) {
            .global-search {
                display: none;
            }
            .user-info {
                display: none;
            }
            .user-trigger {
                padding: 0.375rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay"></div>

        <!-- ==========================================
             SIDEBAR (Command Bridge)
             ========================================== -->
        <aside class="sidebar">
            <!-- Brand Header -->
            <div class="sidebar-header">
                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <span class="sidebar-brand-gradient" style="font-size: 1.1rem;">PMB Admin</span>
                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 500; letter-spacing: 0.5px;">COMMAND CENTER</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="sidebar-section-title">Main Menu</div>
                
                <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/>
                        <rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="applicants.php" class="<?= $current_page == 'applicants.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Data Pendaftar</span>
                    <?php if ($pending_count > 0): ?>
                        <span class="menu-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>

                <div class="sidebar-section-title" style="margin-top: 1.5rem;">Akademik</div>

                <a href="prodi.php" class="<?= $current_page == 'prodi.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    <span>Kelola Prodi</span>
                </a>
                
                <a href="gelombang.php" class="<?= $current_page == 'gelombang.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Kelola Gelombang</span>
                </a>

                <a href="jalur.php" class="<?= $current_page == 'jalur.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    <span>Kelola Jalur</span>
                </a>

                <div class="sidebar-section-title" style="margin-top: 1.5rem;">Konten Publik</div>

                <a href="berita.php" class="<?= $current_page == 'berita.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                        <path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>
                    </svg>
                    <span>Kelola Berita</span>
                </a>

                <a href="faq.php" class="<?= $current_page == 'faq.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>Kelola FAQ</span>
                </a>

                <a href="testimoni.php" class="<?= $current_page == 'testimoni.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Kelola Testimoni</span>
                </a>

                <a href="menus.php" class="<?= $current_page == 'menus.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    <span>Kelola Menu</span>
                </a>

                <div class="sidebar-section-title" style="margin-top: 1.5rem;">Sistem</div>
                
                <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Manajemen Pengguna</span>
                </a>

                <a href="email.php" class="<?= $current_page == 'email.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>Template Email</span>
                </a>

                <a href="logs.php" class="<?= $current_page == 'logs.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>Audit Log</span>
                </a>
                
                <a href="settings.php" class="<?= $current_page == 'settings.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>
                
                <a href="../public/index.php" target="_blank">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    <span>Lihat Website</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="sidebar-status">
                    <div class="status-dot"></div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--success);">System Online</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= $system_version ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ==========================================
             MAIN CONTENT
             ========================================== -->
        <main class="main-content">
            <div class="topbar">
                <!-- Topbar Left -->
                <div class="topbar-left">
                    <button class="mobile-toggle" aria-label="Toggle Menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div>
                        <h1><?= isset($page_title) ? sanitize($page_title) : 'Dashboard' ?></h1>
                        <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                            <div class="breadcrumb" style="margin-top: 0.25rem;">
                                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                                    <?php if ($i > 0): ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                                    <?php endif; ?>
                                    <?php if (isset($crumb['url'])): ?>
                                        <a href="<?= sanitize($crumb['url']) ?>"><?= sanitize($crumb['label']) ?></a>
                                    <?php else: ?>
                                        <span style="color: var(--primary); font-weight: 600;"><?= sanitize($crumb['label']) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Topbar Actions -->
                <div class="topbar-actions">
                    <!-- Page Actions Slot (inject dari halaman child) -->
                    <?php if (!empty($page_actions)): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <?= $page_actions ?>
                        </div>
                    <?php endif; ?>

                    <!-- Global Search -->
                    <button class="global-search" onclick="if(typeof openSearchModal === 'function') openSearchModal(); else showToast('Info', 'Fitur pencarian universal segera hadir', 'info');">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <span>Cari menu, data...</span>
                        <kbd>⌘K</kbd>
                    </button>

                    <!-- Notification Bell -->
                    <a href="applicants.php<?= $pending_count > 0 ? '?status=Pending' : '' ?>" class="icon-btn" title="Notifikasi Pendaftar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                        </svg>
                        <?php if ($pending_count > 0): ?>
                            <span class="notification-badge"><?= $pending_count > 99 ? '99+' : $pending_count ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Theme Toggle -->
                    <button id="admin-theme-toggle" class="icon-btn" title="Toggle Dark Mode">
                        <svg class="theme-icon-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        <svg class="theme-icon-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div class="user-dropdown" id="user-dropdown">
                        <button class="user-trigger" onclick="toggleUserDropdown(event)">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?= sanitize($_SESSION['username'] ?? 'Admin') ?></div>
                                <div class="user-role">Administrator</div>
                            </div>
                            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>

                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <div class="dropdown-header-name"><?= sanitize($_SESSION['username'] ?? 'Admin') ?></div>
                                   <div class="dropdown-header-email" style="text-transform: capitalize;"><?= sanitize($_SESSION['role'] ?? 'Administrator') ?></div>
                            </div>
                            
                            <a href="index.php" class="dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Profil Saya
                            </a>
                            
                            <button type="button" class="dropdown-item" onclick="showToast('Info', 'Fitur ganti password akan segera tersedia', 'info')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Ganti Password
                            </button>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="logout.php" class="dropdown-item danger" onclick="return confirm('Yakin ingin keluar dari sistem?');">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>