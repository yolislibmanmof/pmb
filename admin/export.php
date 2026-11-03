<?php
// ======================================================================
// 1. LOAD KONFIGURASI (Gunakan __DIR__ untuk path absolut yang aman)
// ======================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_login();

// ======================================================================
// 2. WHITELIST VALIDATION UNTUK FILTER (Keamanan Tingkat Tinggi)
// ======================================================================
$allowed_status = ['Draft', 'Pending', 'Diverifikasi', 'Ditolak', 'Lulus', 'Tidak Lulus', 'Registrasi Ulang'];
$allowed_payment = ['Belum Bayar', 'Menunggu Verifikasi', 'Lunas', 'Refund'];

$filter_status = $_GET['status'] ?? '';
$filter_prodi = $_GET['prodi'] ?? '';
$filter_gelombang = $_GET['gelombang'] ?? '';
$filter_payment = $_GET['payment'] ?? '';

// Sanitasi dan validasi filter
if (!empty($filter_status) && !in_array($filter_status, $allowed_status, true)) {
    $filter_status = '';
}
if (!empty($filter_payment) && !in_array($filter_payment, $allowed_payment, true)) {
    $filter_payment = '';
}
$filter_prodi = !empty($filter_prodi) ? (int)$filter_prodi : 0;
$filter_gelombang = !empty($filter_gelombang) ? (int)$filter_gelombang : 0;

// ======================================================================
// 3. KUERI DATA LENGKAP (Termasuk kolom penting untuk analisis)
// ======================================================================
$sql = "SELECT 
            a.kode_pendaftaran, 
            a.nama_lengkap, 
            a.nik, 
            a.email, 
            a.no_hp,
            a.no_wa,
            a.tempat_lahir,
            a.tanggal_lahir,
            a.jenis_kelamin,
            a.alamat,
            a.kota,
            a.provinsi,
            a.asal_sekolah,
            a.jurusan_sekolah,
            a.tahun_lulus,
            a.nilai_rapor,
            p.nama_prodi, 
            p.jenjang,
            g.nama as gelombang, 
            g.tahun_akademik,
            j.nama_jalur,
            a.status_pembayaran, 
            a.status_daftar, 
            a.created_at
        FROM applicants a 
        LEFT JOIN prodi p ON a.prodi_id = p.id 
        LEFT JOIN gelombang g ON a.gelombang_id = g.id
        LEFT JOIN jalur_pendaftaran j ON a.jalur_id = j.id
        WHERE 1=1";
$params = [];

if (!empty($filter_status)) { $sql .= " AND a.status_daftar = ?"; $params[] = $filter_status; }
if (!empty($filter_payment)) { $sql .= " AND a.status_pembayaran = ?"; $params[] = $filter_payment; }
if ($filter_prodi > 0) { $sql .= " AND a.prodi_id = ?"; $params[] = $filter_prodi; }
if ($filter_gelombang > 0) { $sql .= " AND a.gelombang_id = ?"; $params[] = $filter_gelombang; }

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ======================================================================
// 4. CEK JIKA DATA KOSONG
// ======================================================================
if (count($applicants) === 0) {
    // Redirect kembali dengan pesan toast jika tidak ada data
    $_SESSION['toast'] = [
        'type' => 'warning',
        'title' => 'Data Kosong',
        'message' => 'Tidak ada data pendaftar yang sesuai dengan filter untuk diekspor.'
    ];
    header('Location: applicants.php');
    exit;
}

// ======================================================================
// 5. FUNGSI HELPER: Sanitasi untuk Mencegah Formula Injection di Excel
// ======================================================================
function sanitize_csv($value) {
    if ($value === null) return '';
    $value = (string)$value;
    // Hapus karakter yang bisa memicu formula di Excel
    if (preg_match('/^[=+\-@\t\r]/', $value)) {
        $value = "'" . $value; // Tambahkan apostrof di depan
    }
    return $value;
}

// ======================================================================
// 6. GENERATE CSV (Dengan BOM UTF-8 untuk Excel Indonesia)
// ======================================================================
$filename = "Data_Pendaftar_PMB_" . date('Ymd_His') . ".csv";

// Header HTTP yang aman dan standar
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM UTF-8 agar karakter Indonesia (é, ñ, dll) terbaca sempurna di Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header CSV (Lebih lengkap)
fputcsv($output, [
    'Kode Pendaftaran',
    'Nama Lengkap',
    'NIK',
    'Email',
    'No. HP',
    'No. WhatsApp',
    'Tempat Lahir',
    'Tanggal Lahir',
    'Jenis Kelamin',
    'Alamat',
    'Kota',
    'Provinsi',
    'Asal Sekolah',
    'Jurusan Sekolah',
    'Tahun Lulus',
    'Nilai Rapor',
    'Program Studi',
    'Jenjang',
    'Gelombang',
    'Tahun Akademik',
    'Jalur Pendaftaran',
    'Status Pembayaran',
    'Status Pendaftaran',
    'Tanggal Daftar'
], ';');

// Data Rows (Dengan sanitasi formula injection)
foreach ($applicants as $row) {
    fputcsv($output, [
        sanitize_csv($row['kode_pendaftaran']),
        sanitize_csv($row['nama_lengkap']),
        sanitize_csv($row['nik']),
        sanitize_csv($row['email']),
        sanitize_csv($row['no_hp']),
        sanitize_csv($row['no_wa']),
        sanitize_csv($row['tempat_lahir']),
        $row['tanggal_lahir'] ? date('d/m/Y', strtotime($row['tanggal_lahir'])) : '-',
        sanitize_csv($row['jenis_kelamin']),
        sanitize_csv($row['alamat']),
        sanitize_csv($row['kota']),
        sanitize_csv($row['provinsi']),
        sanitize_csv($row['asal_sekolah']),
        sanitize_csv($row['jurusan_sekolah']),
        sanitize_csv($row['tahun_lulus']),
        sanitize_csv($row['nilai_rapor']),
        sanitize_csv($row['nama_prodi'] ?: '-'),
        sanitize_csv($row['jenjang'] ?: '-'),
        sanitize_csv($row['gelombang'] ?: '-'),
        sanitize_csv($row['tahun_akademik'] ?: '-'),
        sanitize_csv($row['nama_jalur'] ?: '-'),
        sanitize_csv($row['status_pembayaran']),
        sanitize_csv($row['status_daftar']),
        date('d/m/Y H:i', strtotime($row['created_at']))
    ], ';');
}

// ======================================================================
// 7. LOG AKTIVITAS EXPORT (Audit Trail)
// ======================================================================
try {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $filter_info = [];
    if (!empty($filter_status)) $filter_info[] = "Status: {$filter_status}";
    if (!empty($filter_payment)) $filter_info[] = "Pembayaran: {$filter_payment}";
    if ($filter_prodi > 0) $filter_info[] = "Prodi ID: {$filter_prodi}";
    if ($filter_gelombang > 0) $filter_info[] = "Gelombang ID: {$filter_gelombang}";
    
    $desc = "Mengekspor data pendaftar (Total: " . count($applicants) . ")";
    if (!empty($filter_info)) {
        $desc .= " | Filter: " . implode(', ', $filter_info);
    }
    
    $pdo->prepare("INSERT INTO admin_logs (user_id, action, table_name, description, ip_address) VALUES (?, 'EXPORT', 'applicants', ?, ?)")
        ->execute([$user_id, $desc, $ip]);
} catch (Exception $e) {
    // Abaikan error log agar tidak mengganggu proses unduhan
}

fclose($output);
exit;