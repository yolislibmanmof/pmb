<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

$lang = $_GET['lang'] ?? 'id';
$site_name = get_setting($pdo, 'site_name', $lang);
$logo_url = get_setting($pdo, 'logo_url', $lang);
$favicon_url = get_setting($pdo, 'favicon_url', $lang);

// Ambil menu aktif dari database
$stmt = $pdo->prepare("SELECT * FROM menus WHERE is_active = 1 ORDER BY sort_order ASC");
$stmt->execute();
$menus = $stmt->fetchAll();

// Tentukan halaman aktif untuk styling navbar
$current_page = basename($_SERVER['PHP_SELF']);

// Fungsi helper untuk mendapatkan ikon SVG berdasarkan URL/Label (Pure SVG, No Emoji)
function getMenuIcon($url, $label) {
    $url = strtolower($url);
    $label = strtolower($label);
    
    // Home / Beranda
    if (str_contains($url, 'index') || str_contains($label, 'beranda') || str_contains($label, 'home')) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
    }
    // Program Studi
    if (str_contains($url, 'prodi') || str_contains($label, 'program studi') || str_contains($label, 'programs')) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>';
    }
    // Alur / Panduan
    if (str_contains($url, 'alur') || str_contains($label, 'alur') || str_contains($label, 'guide') || str_contains($label, 'flow')) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>';
    }
    // Kontak
    if (str_contains($url, 'kontak') || str_contains($label, 'kontak') || str_contains($label, 'contact')) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
    }
    // Daftar / Register
    if (str_contains($url, 'register') || str_contains($label, 'daftar') || str_contains($label, 'register')) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>';
    }
    
    // Default Fallback Icon
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' - ' . sanitize($site_name) : sanitize($site_name) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= sanitize($favicon_url) ?>">
    <link rel="stylesheet" href="../assets/css/public.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="<?= sanitize($logo_url) ?>" alt="Logo" style="height: 40px; width: auto; object-fit: contain;">
            </a>
            
            <div class="nav-links">
                <?php foreach($menus as $menu): 
                    $is_active = ($current_page == $menu['url']) ? 'active' : '';
                    $label = ($lang === 'en') ? $menu['label_en'] : $menu['label_id'];
                    $icon_svg = getMenuIcon($menu['url'], $menu['label_id']);
                ?>
                    <a href="<?= sanitize($menu['url']) ?>" 
                       target="<?= sanitize($menu['target']) ?>" 
                       class="<?= $is_active ?>"
                       style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <?= $icon_svg ?>
                        <span><?= sanitize($label) ?></span>
                    </a>
                <?php endforeach; ?>
                
                <!-- Language Toggle -->
                <button class="btn btn-outline" id="lang-toggle" style="padding: 0.5rem 1rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <?= strtoupper($lang === 'id' ? 'EN' : 'ID') ?>
                </button>
                
                <!-- Theme Toggle -->
                <button class="btn btn-outline" id="theme-toggle" style="padding: 0.5rem; border-radius: 50%; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;" title="Toggle Dark Mode">
                    <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
            </div>

            <!-- Mobile Menu Toggle (Visible only on mobile via CSS) -->
            <button class="mobile-toggle" aria-label="Toggle Menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </nav>
    <main>