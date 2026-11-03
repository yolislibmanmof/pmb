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
?>