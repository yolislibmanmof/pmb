<?php
$page_title = "Dashboard";
require_once 'includes/header.php';

// ==========================================
// AMBIL STATISTIK KOMPREHENSIF
// ==========================================
$stmt_total = $pdo->query("SELECT COUNT(*) FROM applicants");
$total_applicants = (int)$stmt_total->fetchColumn();

$stmt_pending = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status_daftar = 'Pending'");
$pending_applicants = (int)$stmt_pending->fetchColumn();

$stmt_verified = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status_daftar = 'Diverifikasi'");
$verified_applicants = (int)$stmt_verified->fetchColumn();

$stmt_rejected = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status_daftar = 'Ditolak'");
$rejected_applicants = (int)$stmt_rejected->fetchColumn();

$stmt_prodi = $pdo->query("SELECT COUNT(*) FROM prodi WHERE is_active = 1");
$total_prodi = (int)$stmt_prodi->fetchColumn();

// Statistik Pendaftar Hari Ini
$stmt_today = $pdo->query("SELECT COUNT(*) FROM applicants WHERE DATE(created_at) = CURDATE()");
$today_applicants = (int)$stmt_today->fetchColumn();

// Statistik Pendaftar Kemarin (untuk hitung trend)
$stmt_yesterday = $pdo->query("SELECT COUNT(*) FROM applicants WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
$yesterday_applicants = (int)$stmt_yesterday->fetchColumn();

// Hitung Trend Persentase
$trend_percentage = 0;
if ($yesterday_applicants > 0) {
    $trend_percentage = round((($today_applicants - $yesterday_applicants) / $yesterday_applicants) * 100);
} elseif ($today_applicants > 0) {
    $trend_percentage = 100;
}
$trend_direction = $trend_percentage >= 0 ? 'up' : 'down';

// Ambil 5 Pendaftar Terbaru
$stmt_recent = $pdo->query("
    SELECT a.id, a.nama_lengkap, a.email, a.no_hp, p.nama_prodi, p.jenjang, a.status_daftar, a.created_at 
    FROM applicants a 
    JOIN prodi p ON a.prodi_id = p.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$recent_applicants = $stmt_recent->fetchAll();

// Statistik per Program Studi (Top 5)
$stmt_prodi_stats = $pdo->query("
    SELECT p.nama_prodi, p.jenjang, COUNT(a.id) as total_pendaftar 
    FROM prodi p 
    LEFT JOIN applicants a ON p.id = a.prodi_id 
    WHERE p.is_active = 1 
    GROUP BY p.id 
    ORDER BY total_pendaftar DESC 
    LIMIT 5
");
$prodi_stats = $stmt_prodi_stats->fetchAll();
$max_prodi_stat = max(array_column($prodi_stats, 'total_pendaftar')) ?: 1;
?>

<!-- ==========================================
     1. PAGE HEADER WITH BREADCRUMB
     ========================================== -->
<div style="margin-bottom: 2rem;">
    <div class="breadcrumb">
        <a href="index.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            Dashboard
        </a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span style="color: var(--primary); font-weight: 600;">Ringkasan Sistem</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.5px;">Selamat Datang, <?= sanitize($_SESSION['username'] ?? 'Admin') ?></h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Berikut ringkasan aktivitas pendaftaran mahasiswa baru hari ini.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="../public/index.php" target="_blank" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Lihat Website
            </a>
            <button onclick="showToast('Info', 'Fitur export akan segera tersedia di update berikutnya.', 'info')" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Laporan
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     2. STATS GRID (With Counter Animation & Trend)
     ========================================== -->
<div class="stats-grid">
    <!-- Card 1: Total Pendaftar -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3 data-counter="<?= $total_applicants ?>"><?= number_format($total_applicants) ?></h3>
            <p>Total Pendaftar</p>
            <span class="stat-trend <?= $trend_direction ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <?php if ($trend_direction === 'up'): ?>
                        <polyline points="18 15 12 9 6 15"/>
                    <?php else: ?>
                        <polyline points="6 9 12 15 18 9"/>
                    <?php endif; ?>
                </svg>
                <?= abs($trend_percentage) ?>% dari kemarin
            </span>
        </div>
    </div>

    <!-- Card 2: Menunggu Verifikasi -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3 data-counter="<?= $pending_applicants ?>"><?= number_format($pending_applicants) ?></h3>
            <p>Menunggu Verifikasi</p>
            <span class="stat-trend" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/></svg>
                Perlu Tindakan
            </span>
        </div>
    </div>

    <!-- Card 3: Terverifikasi -->
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3 data-counter="<?= $verified_applicants ?>"><?= number_format($verified_applicants) ?></h3>
            <p>Terverifikasi</p>
            <span class="stat-trend up">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Disetujui
            </span>
        </div>
    </div>

    <!-- Card 4: Ditolak -->
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3 data-counter="<?= $rejected_applicants ?>"><?= number_format($rejected_applicants) ?></h3>
            <p>Ditolak</p>
            <span class="stat-trend down">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Tidak Lolos
            </span>
        </div>
    </div>
</div>

<!-- ==========================================
     3. QUICK ACTIONS GRID
     ========================================== -->
<div class="data-card" style="margin-bottom: 1.5rem;">
    <div class="data-card-header">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            Aksi Cepat
        </h3>
    </div>
    <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <a href="applicants.php" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--bg); border-radius: var(--radius); border: 1px solid var(--border); text-decoration: none; color: var(--text); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='var(--border)'">
            <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info); flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem;">Kelola Pendaftar</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Verifikasi data</span>
            </div>
        </a>

        <a href="prodi.php" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--bg); border-radius: var(--radius); border: 1px solid var(--border); text-decoration: none; color: var(--text); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='var(--border)'">
            <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--success); flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem;">Kelola Prodi</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Tambah/Edit jurusan</span>
            </div>
        </a>

        <a href="menus.php" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--bg); border-radius: var(--radius); border: 1px solid var(--border); text-decoration: none; color: var(--text); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='var(--border)'">
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning); flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem;">Kelola Menu</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Navigasi website</span>
            </div>
        </a>

        <a href="settings.php" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--bg); border-radius: var(--radius); border: 1px solid var(--border); text-decoration: none; color: var(--text); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='var(--border)'">
            <div style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger); flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/></svg>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem;">Pengaturan</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Logo & Teks Website</span>
            </div>
        </a>
    </div>
</div>

<!-- ==========================================
     4. TWO COLUMN LAYOUT (Recent + Prodi Stats)
     ========================================== -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;" class="dashboard-two-col">

    <!-- Kolom Kiri: Pendaftar Terbaru -->
    <div class="data-card">
        <div class="data-card-header">
            <div>
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Pendaftar Terbaru
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Menampilkan 5 pendaftar terakhir</p>
            </div>
            <div class="table-controls">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" placeholder="Cari nama..." data-table-search="recent-table">
                </div>
                <a href="applicants.php" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="admin-table" id="recent-table">
                <thead>
                    <tr>
                        <th>Pendaftar</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_applicants) > 0): ?>
                        <?php foreach($recent_applicants as $app): 
                            $badge_class = match($app['status_daftar']) {
                                'Diverifikasi' => 'badge-success',
                                'Ditolak' => 'badge-danger',
                                default => 'badge-warning'
                            };
                            $initial = strtoupper(substr($app['nama_lengkap'], 0, 1));
                            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
                            $avatar_color = $colors[crc32($app['nama_lengkap']) % count($colors)];
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 36px; height: 36px; background: <?= $avatar_color ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                        <?= $initial ?>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= sanitize($app['nama_lengkap']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= sanitize($app['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div style="font-weight: 500;"><?= sanitize($app['nama_prodi']) ?></div>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($app['jenjang']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge <?= $badge_class ?>"><?= sanitize($app['status_daftar']) ?></span></td>
                            <td style="color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;"><?= date('d M Y, H:i', strtotime($app['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="table-empty-state">
                            <td colspan="4">
                                <div style="text-align: center; padding: 3rem 2rem;">
                                    <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--text-muted);">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                    </div>
                                    <p style="font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Pendaftar</p>
                                    <p style="font-size: 0.85rem; color: var(--text-muted);">Data pendaftar akan muncul di sini setelah ada yang mendaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: Statistik per Prodi -->
    <div class="data-card">
        <div class="data-card-header">
            <div>
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    Prodi Terpopuler
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Top 5 berdasarkan pendaftar</p>
            </div>
        </div>
        <div style="padding: 1.5rem;">
            <?php if (count($prodi_stats) > 0): ?>
                <?php foreach($prodi_stats as $index => $stat): 
                    $percentage = ($stat['total_pendaftar'] / $max_prodi_stat) * 100;
                    $bar_colors = ['var(--primary)', 'var(--success)', 'var(--warning)', '#8b5cf6', '#ec4899'];
                    $bar_color = $bar_colors[$index % count($bar_colors)];
                ?>
                <div style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                            <span style="font-size: 0.75rem; background: var(--bg); padding: 0.125rem 0.5rem; border-radius: 50px; font-weight: 600; color: var(--text-muted); flex-shrink: 0;"><?= sanitize($stat['jenjang']) ?></span>
                            <strong style="font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= sanitize($stat['nama_prodi']) ?></strong>
                        </div>
                        <span style="font-weight: 700; color: var(--text); flex-shrink: 0; margin-left: 0.5rem;"><?= $stat['total_pendaftar'] ?></span>
                    </div>
                    <div style="height: 6px; background: var(--bg); border-radius: 50px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $percentage ?>%; background: <?= $bar_color ?>; border-radius: 50px; transition: width 1s ease-out;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <p style="font-size: 0.9rem;">Belum ada data statistik</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==========================================
     5. SYSTEM INFO CARD
     ========================================== -->
<div class="data-card" style="margin-top: 1.5rem;">
    <div class="data-card-header">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            Informasi Sistem
        </h3>
    </div>
    <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);"></div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Status Sistem</div>
                <div style="font-weight: 600;">Beroperasi Normal</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Versi Sistem</div>
                <div style="font-weight: 600;">PMB Ultimate v1.0</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Waktu Server</div>
                <div style="font-weight: 600;"><?= date('d M Y, H:i') ?> WITA</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Database</div>
                <div style="font-weight: 600;">pmb (MySQL)</div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 1024px) {
        .dashboard-two-col {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>