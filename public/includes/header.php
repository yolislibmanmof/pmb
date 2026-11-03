<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

$lang = $_GET['lang'] ?? 'id';
$site_name = get_setting($pdo, 'site_name', $lang);
$logo_url = get_setting($pdo, 'logo_url', $lang);
$favicon_url = get_setting($pdo, 'favicon_url', $lang);

// ==========================================
// 1. FETCH & BUILD HIERARCHICAL MENU TREE
// ==========================================
$stmt = $pdo->query("SELECT * FROM menus WHERE is_active = 1 AND menu_position = 'header' ORDER BY sort_order ASC");
$all_menus = $stmt->fetchAll();

$menu_tree = [];
// 1a. Masukkan menu utama (parent_id = 0)
foreach ($all_menus as $m) {
    if ($m['parent_id'] == 0) {
        $menu_tree[$m['id']] = $m;
        $menu_tree[$m['id']]['children'] = [];
    }
}
// 1b. Masukkan sub-menu ke dalam parent-nya
foreach ($all_menus as $m) {
    if ($m['parent_id'] != 0 && isset($menu_tree[$m['parent_id']])) {
        $menu_tree[$m['parent_id']]['children'][] = $m;
    }
}

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// 2. DYNAMIC ICON MAPPER (Auto-detect jika NULL)
// ==========================================
function renderMenuIcon($icon_name, $url = '', $label = '') {
    // Jika icon kosong/null, coba tebak dari URL atau label
    if (empty($icon_name)) {
        $url = strtolower($url);
        $label = strtolower($label);
        
        if (str_contains($url, 'index') || str_contains($label, 'beranda') || str_contains($label, 'home')) {
            $icon_name = 'home';
        } elseif (str_contains($url, 'prodi') || str_contains($label, 'program')) {
            $icon_name = 'book';
        } elseif (str_contains($url, 'alur') || str_contains($label, 'guide') || str_contains($label, 'flow')) {
            $icon_name = 'guide';
        } elseif (str_contains($url, 'kontak') || str_contains($label, 'contact')) {
            $icon_name = 'phone';
        } elseif (str_contains($url, 'daftar') || str_contains($label, 'register')) {
            $icon_name = 'user';
        } else {
            $icon_name = 'default';
        }
    }
    
    $icons = [
        'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'guide' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/>',
        'news' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>',
        'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'user' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'default' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>'
    ];
    
    $icon_key = strtolower(trim($icon_name));
    $path = $icons[$icon_key] ?? $icons['default'];
    
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
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
    
    <!-- Inline CSS untuk Dropdown Premium -->
    <style>
        .nav-item { position: relative; }
        .nav-link { 
            display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0; 
            color: var(--text); font-weight: 500; font-size: 0.95rem; text-decoration: none; 
            transition: color 0.3s ease; position: relative;
        }
        .nav-link::after { 
            content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0; 
            background: var(--primary); transition: width 0.3s var(--bounce); border-radius: 2px;
        }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link.active { color: var(--primary); font-weight: 700; }
        
        /* Dropdown Styles */
        .dropdown-menu {
            position: absolute; top: 100%; left: 0; min-width: 220px;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; box-shadow: var(--shadow-lg);
            padding: 0.5rem; opacity: 0; visibility: hidden; transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); z-index: 100;
        }
        .nav-item:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        
        .dropdown-item {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
            color: var(--text); text-decoration: none; border-radius: 8px; font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .dropdown-item:hover { background: var(--bg); color: var(--primary); transform: translateX(4px); }
        .dropdown-item svg { width: 16px; height: 16px; opacity: 0.7; }
        
        .chevron-down { width: 14px; height: 14px; transition: transform 0.3s ease; }
        .nav-item:hover .chevron-down { transform: rotate(180deg); }
        
        /* Mobile Menu Adjustments */
        @media (max-width: 900px) {
            .dropdown-menu { 
                position: static; opacity: 1; visibility: visible; transform: none; 
                box-shadow: none; border: none; background: rgba(0,0,0,0.03); margin-top: 0.5rem;
            }
            .nav-link::after { display: none; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="<?= sanitize($logo_url) ?>" alt="<?= sanitize($site_name) ?>" style="height: 40px; width: auto; object-fit: contain;">
            </a>
            
            <!-- Desktop & Mobile Links -->
            <div class="nav-links" id="nav-links">
                <?php foreach($menu_tree as $menu): 
                    $label = ($lang === 'en' && !empty($menu['label_en'])) ? $menu['label_en'] : $menu['label_id'];
                    $is_active = ($current_page == $menu['url']) ? 'active' : '';
                    $has_children = !empty($menu['children']);
                ?>
                    <div class="nav-item">
                        <?php if ($has_children): ?>
                            <!-- Parent Menu with Dropdown -->
                            <a href="<?= sanitize($menu['url']) ?: '#' ?>" class="nav-link <?= $is_active ?>" style="cursor: default;">
                                <?= renderMenuIcon($menu['icon'], $menu['url'], $menu['label_id']) ?>
                                <span><?= sanitize($label) ?></span>
                                <svg class="chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </a>
                            <div class="dropdown-menu">
                                <?php foreach($menu['children'] as $child): 
                                    $child_label = ($lang === 'en' && !empty($child['label_en'])) ? $child['label_en'] : $child['label_id'];
                                    $child_active = ($current_page == $child['url']) ? 'active' : '';
                                ?>
                                    <a href="<?= sanitize($child['url']) ?>" class="dropdown-item <?= $child_active ?>" target="<?= sanitize($child['target'] ?: '_self') ?>">
                                        <?= renderMenuIcon($child['icon'], $child['url'], $child['label_id']) ?>
                                        <span><?= sanitize($child_label) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <!-- Regular Menu Item -->
                            <a href="<?= sanitize($menu['url']) ?>" class="nav-link <?= $is_active ?>" target="<?= sanitize($menu['target'] ?: '_self') ?>">
                                <?= renderMenuIcon($menu['icon'], $menu['url'], $menu['label_id']) ?>
                                <span><?= sanitize($label) ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Right Side Actions -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-left: 1rem; padding-left: 1rem; border-left: 1px solid var(--border);">
                    <!-- Language Toggle -->
                    <button class="btn btn-outline" id="lang-toggle" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <?= strtoupper($lang === 'id' ? 'EN' : 'ID') ?>
                    </button>
                    
                    <!-- Theme Toggle -->
                    <button class="btn btn-outline" id="theme-toggle" style="padding: 0.4rem; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;" title="Toggle Dark Mode">
                        <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                        <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>

                    <!-- CTA Button (Daftar Sekarang) -->
                    <a href="register.php" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span data-lang-id="Daftar" data-lang-en="Register">Daftar</span>
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" aria-label="Toggle Menu" onclick="document.getElementById('nav-links').classList.toggle('active')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </nav>
    <main>