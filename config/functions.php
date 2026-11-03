<?php
session_start();

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function require_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function get_setting($pdo, $key, $lang = 'id') {
    $col = $lang === 'en' ? 'value_en' : 'value_id';
    $stmt = $pdo->prepare("SELECT $col FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $res = $stmt->fetch();
    return $res ? $res[$col] : $key;
}

// ==========================================
// FUNGSI BARU: Handle Public Image URL
// ==========================================
function get_public_image($url) {
    if (empty($url)) {
        return '';
    }
    
    // 1. Jika URL eksternal (sudah ada http:// atau https://), kembalikan apa adanya
    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }
    
    // 2. Jika URL lokal dari folder uploads (misal: uploads/prodi/xxx.jpg atau ../uploads/xxx.jpg)
    if (strpos($url, 'uploads/') === 0) {
        return '../' . $url;
    }
    
    // 3. Jika sudah ada ../ di depan, kembalikan apa adanya
    if (strpos($url, '../uploads/') === 0) {
        return $url;
    }
    
    // 4. Fallback: kembalikan apa adanya
    return $url;
}

// ==========================================
// FUNGSI BARU: Render Menu Icon (untuk admin & public)
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
        } elseif (str_contains($url, 'cek-status') || str_contains($label, 'cek status') || str_contains($label, 'check status')) {
            $icon_name = 'search';
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