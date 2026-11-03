<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Cegah Session Fixation
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PMB</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, var(--bg) 0%, rgba(30, 64, 175, 0.05) 100%);">
    
    <div style="background: var(--surface); padding: 2.5rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: var(--shadow-lg); width: 100%; max-width: 420px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 64px; height: 64px; background: rgba(30, 64, 175, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--primary);">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text);">Admin Dashboard</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Masuk untuk mengelola sistem PMB</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c; padding: 0.875rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= sanitize($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" required autocomplete="username" placeholder="Masukkan username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" required autocomplete="current-password" placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; padding: 0.875rem;">
                Masuk ke Sistem
            </button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted);">Default: admin / password</p>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>