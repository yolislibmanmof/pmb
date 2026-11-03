<?php
// ======================================================================
// 1. LOAD KONFIGURASI (PATH ABSOLUT)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// ======================================================================
// 2. HELPER FUNCTIONS (Rate Limiting & Audit)
// ======================================================================
function isRateLimited($pdo, $ip, $maxAttempts = 5, $timeWindow = 900) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM admin_logs 
            WHERE action = 'LOGIN_FAILED' 
              AND ip_address = ? 
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $timeWindow]);
        return (int)$stmt->fetchColumn() >= $maxAttempts;
    } catch (Exception $e) {
        return false;
    }
}

function logLoginAttempt($pdo, $username, $status, $ip, $userAgent = '') {
    try {
        $desc = ($status === 'LOGIN_SUCCESS') 
            ? "Login berhasil" 
            : "Login gagal untuk username: {$username}";
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (user_id, action, table_name, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, 'users', ?, ?, ?, NOW())
        ");
        $stmt->execute([null, $status, $desc, $ip, substr($userAgent, 0, 255)]);
    } catch (Exception $e) { /* silent fail */ }
}

// ======================================================================
// 3. AUTO-REDIRECT JIKA SUDAH LOGIN
// ======================================================================
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// ======================================================================
// 4. HANDLE POST LOGIN
// ======================================================================
$error = '';
$rate_limited = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    } elseif (isRateLimited($pdo, $ip)) {
        $rate_limited = true;
        $error = 'Terlalu banyak percobaan login gagal. Tunggu 15 menit sebelum mencoba lagi.';
    } else {
        $username = trim(sanitize($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            // Hanya user aktif yang bisa login
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Cegah Session Fixation
                session_regenerate_id(true);
                
                // Set session data lengkap
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['full_name']     = $user['full_name'] ?: $user['username'];
                $_SESSION['email']         = $user['email'] ?? '';
                $_SESSION['role']          = $user['role'] ?? 'admin';
                $_SESSION['login_time']    = time();
                
                // Update last_login & IP di database
                try {
                    $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?")
                        ->execute([$ip, $user['id']]);
                } catch (Exception $e) { /* silent */ }
                
                // Audit log sukses
                logLoginAttempt($pdo, $username, 'LOGIN_SUCCESS', $ip, $userAgent);
                
                // Remember Me (30 hari)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $cookieOptions = [
                        'expires'  => time() + (86400 * 30),
                        'path'     => '/',
                        'secure'   => false, // Set true jika HTTPS
                        'httponly'  => true,
                        'samesite' => 'Lax'
                    ];
                    setcookie('pmb_remember', $token, $cookieOptions);
                    
                    // Perpanjang lifetime session
                    ini_set('session.gc_maxlifetime', 86400 * 30);
                    $_SESSION['remember_expires'] = time() + (86400 * 30);
                }
                
                header('Location: index.php');
                exit;
            } else {
                // Audit log gagal
                logLoginAttempt($pdo, $username, 'LOGIN_FAILED', $ip, $userAgent);
                $error = 'Username atau password salah, atau akun tidak aktif.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login Admin - PMB UKJP</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
    /* Auto-apply theme dari localStorage atau sistem */
    (function(){
        try {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        } catch(e){}
    })();
    </script>
    <style>
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 1.5rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            z-index: 0;
            animation: float 20s ease-in-out infinite;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #3b82f6, transparent);
            top: -150px; left: -150px;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #8b5cf6, transparent);
            bottom: -100px; right: -100px;
            animation-delay: -10s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-card {
            background: var(--surface);
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
        }
        
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .brand-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: white;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.4);
            position: relative;
        }
        .brand-logo::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            z-index: -1;
            opacity: 0.3;
            filter: blur(10px);
        }
        
        .brand-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
            margin: 0 0 0.5rem;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
        }
        .brand-subtitle strong {
            color: var(--primary);
            font-weight: 700;
        }
        
        /* Error alert */
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            line-height: 1.5;
        }
        .alert-error svg { flex-shrink: 0; margin-top: 2px; }
        .alert-error strong { display: block; margin-bottom: 2px; font-weight: 600; }
        
        /* Form */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        .input-wrapper svg.input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }
        .input-wrapper input:focus ~ svg.input-icon {
            color: var(--primary);
        }
        .input-wrapper input {
            padding-left: 2.75rem;
            height: 48px;
            font-size: 0.9375rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .password-toggle:hover {
            background: var(--bg);
            color: var(--primary);
        }
        .password-toggle svg { width: 18px; height: 18px; }
        
        /* Remember me + Forgot */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            user-select: none;
            color: var(--text-muted);
        }
        .remember-me input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .remember-me:hover { color: var(--text); }
        
        /* Submit button */
        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.3);
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover:not(:disabled)::before { left: 100%; }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.4);
        }
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-login svg { width: 18px; height: 18px; }
        
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .login-footer p {
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin: 0 0 0.75rem;
        }
        .back-to-site {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .back-to-site:hover { background: rgba(30, 64, 175, 0.08); }
        .back-to-site svg { width: 14px; height: 14px; }
        
        /* Theme toggle floating */
        .theme-toggle-floating {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 10;
            width: 44px; height: 44px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            transition: all 0.2s;
            box-shadow: var(--shadow);
        }
        .theme-toggle-floating:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }
        .theme-toggle-floating svg { width: 20px; height: 20px; }
        .theme-icon-light, .theme-icon-dark { display: none; }
        html[data-theme="light"] .theme-icon-dark { display: block; }
        html[data-theme="dark"] .theme-icon-light { display: block; }
        
        /* Security badge */
        .security-badge {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 50px;
            color: #10b981;
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }
        .security-badge svg { width: 14px; height: 14px; }
        
        @media (max-width: 480px) {
            body { padding: 1rem; }
            .login-card { padding: 1.75rem; border-radius: 16px; }
            .brand-title { font-size: 1.5rem; }
            .security-badge { font-size: 0.7rem; padding: 0.375rem 0.75rem; }
        }
    </style>
</head>
<body>
    
    <!-- Theme Toggle Floating -->
    <button class="theme-toggle-floating" onclick="toggleTheme()" title="Toggle Dark Mode" aria-label="Toggle theme">
        <svg class="theme-icon-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
        <svg class="theme-icon-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
    </button>
    
    <!-- Security Badge -->
    <div class="security-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
        </svg>
        <span>SSL Secured • CSRF Protected</span>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-logo">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <h1 class="brand-title">PMB Admin</h1>
                <p class="brand-subtitle">Portal <strong>Command Center</strong> Penerimaan Mahasiswa Baru</p>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <strong><?= $rate_limited ? 'Akun Terkunci Sementara' : 'Login Gagal' ?></strong>
                        <?= sanitize($error) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" id="login-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username"
                            name="username" 
                            class="form-input" 
                            required 
                            autocomplete="username" 
                            placeholder="Masukkan username Anda"
                            value="<?= sanitize($_POST['username'] ?? '') ?>"
                            autofocus
                        >
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="form-input" 
                            required 
                            autocomplete="current-password" 
                            placeholder="Masukkan password"
                        >
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Show password">
                            <svg id="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                            <svg id="eye-on" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" <?= (isset($_POST['remember']) ? 'checked' : '') ?>>
                        <span>Ingat saya selama 30 hari</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-login" id="btn-login">
                    <span id="btn-text">Masuk ke Dashboard</span>
                    <svg id="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <div class="divider">Sistem Terlindungi</div>

            <!-- Footer -->
            <div class="login-footer">
                <p>Butuh akses? Hubungi <strong>Super Administrator</strong></p>
                <a href="../public/index.php" class="back-to-site">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali ke Website Publik
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eyeOff = document.getElementById('eye-off');
            const eyeOn = document.getElementById('eye-on');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeOff.style.display = 'none';
                eyeOn.style.display = 'block';
            } else {
                pwd.type = 'password';
                eyeOff.style.display = 'block';
                eyeOn.style.display = 'none';
            }
        }
        
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch(e){}
        }
        
        // Form submit loading state
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const btn = document.getElementById('btn-login');
            const btnText = document.getElementById('btn-text');
            const btnIcon = document.getElementById('btn-icon');
            
            // Validasi sederhana
            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value.trim();
            
            if (!u || !p) {
                e.preventDefault();
                alert('Username dan password wajib diisi!');
                return false;
            }
            
            btn.disabled = true;
            btnText.textContent = 'Memverifikasi...';
            btnIcon.outerHTML = '<div class="spinner"></div>';
        });
        
        // Auto-hide alert error setelah 8 detik (kecuali rate limited)
        <?php if ($error && !$rate_limited): ?>
        setTimeout(() => {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.style.transition = 'opacity 0.5s, transform 0.5s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 500);
            }
        }, 8000);
        <?php endif; ?>
        
        // Focus password jika username sudah diisi (dari POST)
        <?php if (!empty($_POST['username'])): ?>
        document.getElementById('password').focus();
        <?php endif; ?>
    </script>
</body>
</html>