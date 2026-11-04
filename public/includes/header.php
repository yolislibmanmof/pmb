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

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// RECURSIVE MENU RENDERER (dropdown tak terbatas)
// ==========================================
function pmb_render_menu($all_menus, $parent_id, $level, $lang, $current_page) {
    $out = '';
    foreach ($all_menus as $m) {
        if ((int)$m['parent_id'] !== (int)$parent_id) continue;
        $label    = ($lang === 'en' && !empty($m['label_en'])) ? $m['label_en'] : $m['label_id'];
        $active   = ($current_page == $m['url']) ? 'active' : '';
        $target   = $m['target'] ?: '_self';
        $icon     = renderMenuIcon($m['icon'], $m['url'], $m['label_id']);
        $children = pmb_render_menu($all_menus, $m['id'], $level + 1, $lang, $current_page);
        $has_child = trim($children) !== '';

        if ($level === 0) {
            $out .= '<div class="nav-item">';
            if ($has_child) {
                $out .= '<a href="' . sanitize($m['url'] !== '#' ? $m['url'] : '#') . '" class="nav-link ' . $active . '">' . $icon
                      . '<span>' . sanitize($label) . '</span>'
                      . '<svg class="chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></a>';
                $out .= '<div class="dropdown-menu">' . $children . '</div>';
            } else {
                $out .= '<a href="' . sanitize($m['url']) . '" class="nav-link ' . $active . '" target="' . sanitize($target) . '">' . $icon
                      . '<span>' . sanitize($label) . '</span></a>';
            }
            $out .= '</div>';
        } else {
            if ($has_child) {
                $out .= '<div class="nav-item">';
                $out .= '<a href="#" class="dropdown-item ' . $active . '">' . $icon . '<span>' . sanitize($label) . '</span>'
                      . '<svg class="chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></a>';
                $out .= '<div class="dropdown-menu">' . $children . '</div>';
                $out .= '</div>';
            } else {
                $out .= '<a href="' . sanitize($m['url']) . '" class="dropdown-item ' . $active . '" target="' . sanitize($target) . '">' . $icon
                      . '<span>' . sanitize($label) . '</span></a>';
            }
        }
    }
    return $out;
}

// CATATAN: Fungsi renderMenuIcon() dan get_public_image() 
// sudah dipindahkan ke config/functions.php agar tidak bentrok (redeclare).
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' - ' . sanitize($site_name) : sanitize($site_name) ?></title>
    
    <!-- Menggunakan get_public_image() agar path gambar selalu benar -->
    <link rel="icon" type="image/x-icon" href="<?= get_public_image($favicon_url) ?>">
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

        /* ====== ANTI-WRAP: menu top-level & dropdown selalu 1 baris ====== */
.nav-links { gap: 1.25rem; }
.nav-link, .nav-link span { white-space: nowrap; }
.dropdown-item, .dropdown-item span { white-space: nowrap; }
.dropdown-item span { flex: 1; }

/* ====== DROPDOWN melebar sesuai teks terpanjang (rapi) ====== */
.dropdown-menu { min-width: max-content; max-width: min(340px, 90vw); }

/* ====== NESTED DROPDOWN (level 2+) flyout ke kanan ====== */
.dropdown-menu .nav-item { position: relative; }
.dropdown-menu .nav-item .dropdown-menu {
    position: absolute; top: -0.5rem; left: 100%; margin-left: 6px;
    opacity: 0; visibility: hidden; transform: translateX(10px);
}
.dropdown-menu .nav-item:hover > .dropdown-menu {
    opacity: 1; visibility: visible; transform: translateX(0);
}
.chevron-right { width: 14px; height: 14px; margin-left: auto; opacity: 0.6; flex-shrink: 0; }

/* ====== MOBILE: nested jadi accordion statis ====== */
@media (max-width: 900px) {
    .dropdown-menu .nav-item .dropdown-menu {
        position: static; transform: none; margin-left: 1rem; margin-top: 0.25rem;
        opacity: 1; visibility: visible; box-shadow: none; border: none; background: transparent;
    }
}

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo (Menggunakan get_public_image) -->
            <a href="index.php" class="logo">
                <img src="<?= get_public_image($logo_url) ?>" alt="<?= sanitize($site_name) ?>" style="height: 40px; width: auto; object-fit: contain;">
            </a>
            
            <!-- Desktop & Mobile Links -->
            <div class="nav-links" id="nav-links">
<?= pmb_render_menu($all_menus, 0, 0, $lang, $current_page) ?>

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