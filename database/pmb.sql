-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 03, 2026 at 04:13 PM
-- Server version: 5.7.39
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pmb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text,
  `old_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'DELETE', 'gelombang', 2, 'Menghapus gelombang ID: 2', NULL, NULL, '::1', NULL, '2026-11-03 09:10:38'),
(2, 1, 'DELETE', 'gelombang', 1, 'Menghapus gelombang ID: 1', NULL, NULL, '::1', NULL, '2026-11-03 09:15:58'),
(3, 1, 'CREATE', 'gelombang', 3, 'Menambah gelombang: Gelombang Tes', NULL, NULL, '::1', NULL, '2026-11-03 09:17:02'),
(4, 1, 'DELETE', 'gelombang', 3, 'Menghapus gelombang ID: 3', NULL, NULL, '::1', NULL, '2026-11-03 09:23:12'),
(5, 1, 'CREATE', 'gelombang', 4, 'Menambah gelombang: Gelombang Tes', NULL, NULL, '::1', NULL, '2026-11-03 09:26:44'),
(6, 1, 'DELETE', 'gelombang', 4, 'Menghapus gelombang ID: 4', NULL, NULL, '::1', NULL, '2026-11-03 09:26:52'),
(7, 1, 'ADD', 'news', 1, 'Menambah berita: TES', NULL, NULL, '::1', NULL, '2026-11-03 09:31:12'),
(8, 1, 'CREATE', 'prodi', NULL, 'Menambah prodi: Pendidikan Matematika', NULL, NULL, '::1', NULL, '2026-11-03 09:51:18'),
(9, 1, 'UPDATE', 'prodi', 12, 'Mengupdate prodi ID: 12', NULL, NULL, '::1', NULL, '2026-11-03 09:51:53'),
(10, 1, 'UPDATE', 'prodi', 12, 'Mengupdate prodi ID: 12', NULL, NULL, '::1', NULL, '2026-11-03 09:53:56'),
(11, 1, 'UPDATE', 'prodi', 12, 'Mengupdate prodi ID: 12', NULL, NULL, '::1', NULL, '2026-11-03 09:55:25'),
(12, 1, 'UPDATE', 'prodi', 12, 'Mengupdate prodi ID: 12', NULL, NULL, '::1', NULL, '2026-11-03 10:12:15'),
(13, 1, 'UPDATE', 'settings', NULL, 'Memperbarui pengaturan situs', NULL, NULL, '::1', NULL, '2026-11-03 10:23:36'),
(14, NULL, 'LOGIN_SUCCESS', 'users', NULL, 'Login berhasil', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-03 10:28:02'),
(15, NULL, 'LOGIN_SUCCESS', 'users', NULL, 'Login berhasil', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-03 11:28:10'),
(16, 1, 'UPDATE', 'menus', 5, 'Mengupdate menu ID: 5', NULL, NULL, '::1', NULL, '2026-11-03 11:30:02'),
(17, 1, 'UPDATE', 'menus', 3, 'Mengupdate menu ID: 3', NULL, NULL, '::1', NULL, '2026-11-03 11:30:38'),
(18, 1, 'UPDATE', 'prodi', 2, 'Mengupdate prodi ID: 2', NULL, NULL, '::1', NULL, '2026-11-03 15:19:52'),
(19, 1, 'UPDATE', 'menus', 9, 'Mengupdate menu ID: 9', NULL, NULL, '::1', NULL, '2026-11-03 15:20:42'),
(20, 1, 'UPDATE', 'menus', 2, 'Mengupdate menu ID: 2', NULL, NULL, '::1', NULL, '2026-11-03 15:21:10'),
(21, 1, 'UPDATE', 'jalur_pendaftaran', 1, 'Mengupdate jalur ID: 1', NULL, NULL, '::1', NULL, '2026-11-03 15:52:48'),
(22, 1, 'UPDATE', 'prodi', 2, 'Mengupdate prodi ID: 2', NULL, NULL, '::1', NULL, '2026-11-03 15:58:12'),
(23, 1, 'UPDATE', 'prodi', 2, 'Mengupdate prodi ID: 2', NULL, NULL, '::1', NULL, '2026-11-03 15:58:28'),
(24, 1, 'UPDATE', 'prodi', 12, 'Mengupdate prodi ID: 12', NULL, NULL, '::1', NULL, '2026-11-03 16:00:47'),
(25, 1, 'UPDATE', 'settings', NULL, 'Memperbarui pengaturan situs', NULL, NULL, '::1', NULL, '2026-11-03 16:02:09'),
(26, 1, 'UPDATE', 'settings', NULL, 'Memperbarui pengaturan situs', NULL, NULL, '::1', NULL, '2026-11-03 16:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int(11) NOT NULL,
  `kode_pendaftaran` varchar(20) DEFAULT NULL,
  `gelombang_id` int(11) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `agama` varchar(30) DEFAULT NULL,
  `alamat` text,
  `kota` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `asal_sekolah` varchar(150) DEFAULT NULL,
  `jurusan_sekolah` varchar(50) DEFAULT NULL,
  `tahun_lulus` varchar(10) DEFAULT NULL,
  `nilai_rapor` decimal(5,2) DEFAULT NULL,
  `no_ijazah` varchar(50) DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `penghasilan_ortu` varchar(50) DEFAULT NULL,
  `prodi_id` int(11) NOT NULL,
  `prodi_pilihan_2` int(11) DEFAULT NULL,
  `jalur_id` int(11) DEFAULT NULL,
  `jalur_pendaftaran` varchar(50) DEFAULT NULL,
  `status_pembayaran` enum('Belum Bayar','Menunggu Verifikasi','Lunas','Refund') DEFAULT 'Belum Bayar',
  `status_daftar` enum('Draft','Pending','Diverifikasi','Ditolak','Lulus','Tidak Lulus','Registrasi Ulang') DEFAULT 'Pending',
  `catatan_admin` text,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_documents`
--

CREATE TABLE `applicant_documents` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `verification_status` enum('Pending','Valid','Invalid') DEFAULT 'Pending',
  `verification_note` text,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_key` varchar(50) NOT NULL,
  `subject_id` varchar(255) NOT NULL,
  `subject_en` varchar(255) NOT NULL,
  `body_id` longtext NOT NULL,
  `body_en` longtext,
  `is_active` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_key`, `subject_id`, `subject_en`, `body_id`, `body_en`, `is_active`, `updated_at`) VALUES
(1, 'registration_success', 'Pendaftaran Berhasil - PMB UKJP', 'Registration Successful - UKJP Admission', 'Halo {nama},\n\nPendaftaran Anda telah kami terima.\n\nKode Pendaftaran: {kode}\nProgram Studi: {prodi}\n\nSilakan lakukan pembayaran untuk melanjutkan proses.\n\nTerima kasih,\nPanitia PMB UKJP', 'Hello {nama},\n\nYour registration has been received.\n\nRegistration Code: {kode}\nStudy Program: {prodi}\n\nPlease make payment to continue the process.\n\nThank you,\nUKJP Admission Committee', 1, '2026-11-03 01:49:53'),
(2, 'verification_approved', 'Selamat! Anda Diterima - PMB UKJP', 'Congratulations! You Are Accepted - UKJP Admission', 'Halo {nama},\n\nSelamat! Anda telah DITERIMA di program studi {prodi}.\n\nSilakan lakukan registrasi ulang sebelum {deadline}.\n\nTerima kasih,\nPanitia PMB UKJP', 'Hello {nama},\n\nCongratulations! You have been ACCEPTED in the {prodi} study program.\n\nPlease complete re-registration before {deadline}.\n\nThank you,\nUKJP Admission Committee', 1, '2026-11-03 01:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question_id` text NOT NULL,
  `question_en` text NOT NULL,
  `answer_id` text NOT NULL,
  `answer_en` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question_id`, `question_en`, `answer_id`, `answer_en`, `category`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Apakah bisa mengganti pilihan prodi setelah mendaftar?', 'Can I change my study program choice after registering?', 'Bisa, selama Anda belum melakukan pembayaran. Jika sudah bayar, hubungi admin PMB melalui WhatsApp.', 'Yes, as long as you haven\'t paid. If you have paid, contact PMB admin via WhatsApp.', 'pendaftaran', 1, 1, '2026-11-03 01:49:53'),
(2, 'Berapa lama waktu verifikasi berkas?', 'How long does document verification take?', 'Verifikasi berkas memakan waktu maksimal 2x24 jam pada hari kerja.', 'Document verification takes a maximum of 2x24 hours on working days.', 'dokumen', 2, 1, '2026-11-03 01:49:53'),
(3, 'Apakah lulusan tahun lalu boleh mendaftar?', 'Can graduates from previous years apply?', 'Sangat boleh! Kami menerima lulusan SMA/SMK dari tahun mana pun.', 'Absolutely! We accept graduates from any year.', 'persyaratan', 3, 1, '2026-11-03 01:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `gelombang`
--

CREATE TABLE `gelombang` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tahun_akademik` varchar(20) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `biaya_pendaftaran` decimal(10,2) DEFAULT '200000.00',
  `is_open` tinyint(1) DEFAULT '0',
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `jalur_pendaftaran`
--

CREATE TABLE `jalur_pendaftaran` (
  `id` int(11) NOT NULL,
  `nama_jalur` varchar(100) NOT NULL,
  `kode_jalur` varchar(20) NOT NULL,
  `biaya_tambahan` decimal(10,2) DEFAULT '0.00',
  `deskripsi_id` text,
  `deskripsi_en` text,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jalur_pendaftaran`
--

INSERT INTO `jalur_pendaftaran` (`id`, `nama_jalur`, `kode_jalur`, `biaya_tambahan`, `deskripsi_id`, `deskripsi_en`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Reguler', 'REG', 0.00, 'Jalur pendaftaran standar melalui tes tertulis', 'Standard registration path through written test', 1, 1, '2026-11-03 01:49:53'),
(2, 'Beasiswa Prestasi', 'PRESTASI', -100000.00, 'Jalur khusus untuk siswa berprestasi akademik/non-akademik (potongan biaya)', 'Special path for students with academic/non-academic achievements (fee discount)', 1, 2, '2026-11-03 01:49:53'),
(3, 'RPL Transfer', 'RPL', 50000.00, 'Jalur untuk mahasiswa pindahan atau lanjut dari D3 ke S1', 'Path for transfer students or continuing from D3 to S1', 1, 3, '2026-11-03 01:49:53'),
(4, 'Karyawan', 'KARYAWAN', 0.00, 'Jalur khusus untuk pekerja dengan jadwal kuliah fleksibel', 'Special path for workers with flexible class schedules', 1, 4, '2026-11-03 01:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `label_id` varchar(100) NOT NULL,
  `label_en` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `target` varchar(20) DEFAULT '_self',
  `menu_position` enum('header','footer') DEFAULT 'header',
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `parent_id`, `label_id`, `label_en`, `url`, `icon`, `target`, `menu_position`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 0, 'Beranda', 'Home', 'index.php', 'home', '_self', 'header', 1, 1, '2026-11-03 01:49:53'),
(2, 0, 'Prodi', 'Programs', 'prodi.php', 'book', '_self', 'header', 2, 1, '2026-11-03 01:49:53'),
(3, 0, 'Alur', 'Registration Flow', 'alur.php', 'guide', '_self', 'header', 3, 1, '2026-11-03 01:49:53'),
(4, 0, 'Kontak', 'Contact', 'kontak.php', 'phone', '_self', 'header', 4, 1, '2026-11-03 01:49:53'),
(5, 0, 'Daftar', 'Register Now', 'register.php', 'user', '_self', 'header', 5, 1, '2026-11-03 01:49:53'),
(6, 0, 'Informasi', 'Information', '#', 'help', '_self', 'header', 6, 1, '2026-11-03 11:17:59'),
(7, 6, 'Berita', 'News', 'berita.php', 'news', '_self', 'header', 1, 1, '2026-11-03 11:17:59'),
(8, 6, 'FAQ', 'FAQ', 'faq.php', 'help', '_self', 'header', 2, 1, '2026-11-03 11:17:59'),
(9, 0, 'Status', 'Status', 'cek-status.php', 'search', '_self', 'header', 7, 1, '2026-11-03 11:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title_id` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content_id` longtext NOT NULL,
  `content_en` longtext,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'pengumuman',
  `author_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title_id`, `title_en`, `slug`, `content_id`, `content_en`, `thumbnail_url`, `category`, `author_id`, `views`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'TES', 'TEST', 'tes', 'TES', 'TEST', '../uploads/berita/1793698272_3cafdcd3.jpg', 'PENGUMUMAN', 1, 0, 1, '2026-11-03 16:30:00', '2026-11-03 09:31:12', '2026-11-03 09:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `payment_type` enum('Pendaftaran','SPP','Pangkal','Lainnya') DEFAULT 'Pendaftaran',
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `sender_account` varchar(50) DEFAULT NULL,
  `proof_file` varchar(500) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('Pending','Verified','Rejected','Refunded') DEFAULT 'Pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `prodi`
--

CREATE TABLE `prodi` (
  `id` int(11) NOT NULL,
  `kode_prodi` varchar(20) DEFAULT NULL,
  `jenjang` varchar(50) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `sistem_kuliah` varchar(50) NOT NULL DEFAULT 'Reguler',
  `akreditasi` varchar(20) DEFAULT 'B',
  `gelar_lulusan` varchar(50) DEFAULT NULL,
  `kuota` int(11) DEFAULT '100',
  `biaya_spp` decimal(10,2) DEFAULT '0.00',
  `biaya_pangkal` decimal(10,2) DEFAULT '0.00',
  `jalur_count` int(11) DEFAULT '1',
  `description_id` text,
  `description_en` text,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prodi`
--

INSERT INTO `prodi` (`id`, `kode_prodi`, `jenjang`, `nama_prodi`, `sistem_kuliah`, `akreditasi`, `gelar_lulusan`, `kuota`, `biaya_spp`, `biaya_pangkal`, `jalur_count`, `description_id`, `description_en`, `image_url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 'S1-BD', 'S1', 'Bisnis Digital', 'Reguler', 'A', 'S.Kom.', 60, 4000000.00, 6000000.00, 2, 'Mencetak wirausaha digital yang kompeten, inovatif, dan siap menghadapi era industri 4.0.', 'Creating competent, innovative digital entrepreneurs ready for the industry 4.0 era.', 'uploads/prodi/1793719192_d0703472.png', 1, 2, '2026-11-03 01:49:53', '2026-11-03 15:19:52'),
(3, 'S1-IK', 'S1', 'Ilmu Keperawatan', 'Reguler', 'A', 'S.Kep.', 100, 4500000.00, 7000000.00, 2, 'Menghasilkan perawat profesional, berempati, dan berdaya saing global dengan standar pelayanan prima.', 'Producing professional, empathetic, and globally competitive nurses with premium service standards.', 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80', 1, 3, '2026-11-03 01:49:53', '2026-11-03 01:49:53'),
(4, 'S1-KB', 'S1', 'Kebidanan', 'Reguler', 'B', 'S.Tr.Keb.', 80, 4000000.00, 6000000.00, 2, 'Mencetak bidan profesional yang unggul dalam pelayanan kesehatan ibu dan anak.', 'Creating professional midwives who excel in maternal and child health services.', 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=800&q=80', 1, 4, '2026-11-03 01:49:53', '2026-11-03 01:49:53'),
(5, 'S1-KD', 'S1', 'Kedokteran', 'Reguler', 'A', 'S.Ked.', 50, 15000000.00, 25000000.00, 2, 'Program studi unggulan untuk mencetak dokter umum yang kompeten dan beretika.', 'Leading study program to produce competent and ethical general practitioners.', 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80', 1, 5, '2026-11-03 01:49:53', '2026-11-03 01:49:53'),
(6, 'S1-PGSD', 'S1', 'Pendidikan Guru Sekolah Dasar', 'Reguler', 'B', 'S.Pd.', 120, 3000000.00, 4000000.00, 1, 'Mencetak guru SD yang profesional, kreatif, dan inovatif dalam pembelajaran.', 'Creating professional, creative, and innovative elementary school teachers.', 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80', 1, 6, '2026-11-03 01:49:53', '2026-11-03 01:49:53'),
(12, 'MTK', 'S1', 'Pendidikan Matematika', 'Reguler', 'Unggul', 'S.Pd', 100, 2000000.00, 1500000.00, 1, 'NaN', 'NaN', 'uploads/prodi/1793700735_cff8c7a5.jpg', 1, 0, '2026-11-03 09:51:18', '2026-11-03 10:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `value_id` text,
  `value_en` text,
  `setting_group` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `value_id`, `value_en`, `setting_group`, `is_public`, `updated_at`) VALUES
(1, 'site_name', 'PMB Universitas Kurnia Jaya Persada', 'Admission of Kurnia Jaya Persada University', 'branding', 1, '2026-11-03 01:49:53'),
(2, 'tagline', 'Mencetak Generasi Unggul dan Berdaya Saing', 'Creating Superior and Competitive Generation', 'branding', 1, '2026-11-03 01:49:53'),
(3, 'logo_url', '../uploads/settings/1793721747_69d10081.png', '../uploads/settings/1793721747_69d10081.png', 'branding', 1, '2026-11-03 16:02:27'),
(4, 'favicon_url', '../uploads/settings/1793721747_c92681ef.png', '../uploads/settings/1793721747_c92681ef.png', 'branding', 1, '2026-11-03 16:02:27'),
(5, 'hero_title', 'Wujudkan Masa Depanmu Bersama Kami', 'Shape Your Future With Us', 'hero', 1, '2026-11-03 01:49:53'),
(6, 'hero_subtitle', 'NaN', 'NaN', 'hero', 1, '2026-11-03 10:23:36'),
(7, 'hero_image', '../uploads/settings/1793701416_29e4ae53.png', '../uploads/settings/1793701416_29e4ae53.png', 'hero', 1, '2026-11-03 10:23:36'),
(8, 'contact_phone', '+62 851-7427-0172', '+62 851-7427-0172', 'contact', 1, '2026-11-03 01:49:53'),
(9, 'contact_email', 'info@ukjp.ac.id', 'info@ukjp.ac.id', 'contact', 1, '2026-11-03 01:49:53'),
(10, 'contact_address', 'NaN', 'NaN', 'contact', 1, '2026-11-03 10:23:36'),
(11, 'contact_wa', '6285174270172', '6285174270172', 'contact', 1, '2026-11-03 01:49:53'),
(12, 'meta_description', 'NaN', 'NaN', 'seo', 1, '2026-11-03 10:23:36'),
(13, 'registration_fee', '200000', '200000', 'payment', 1, '2026-11-03 01:49:53'),
(14, 'bank_account_name', 'Universitas Kurnia Jaya Persada', 'Universitas Kurnia Jaya Persada', 'payment', 1, '2026-11-03 01:49:53'),
(15, 'bank_account_number', '1234567890', '1234567890', 'payment', 1, '2026-11-03 01:49:53'),
(16, 'bank_name', 'Bank Mandiri', 'Bank Mandiri', 'payment', 1, '2026-11-03 01:49:53'),
(17, 'maps_embed', 'https://www.google.com/maps/embed?pb=...', 'https://www.google.com/maps/embed?pb=...', 'contact', 1, '2026-11-03 01:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `program` varchar(100) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `content_id` text NOT NULL,
  `content_en` text NOT NULL,
  `rating` int(1) DEFAULT '5',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','superadmin','operator') DEFAULT 'admin',
  `avatar_url` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(3) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `avatar_url`, `last_login`, `login_attempts`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@ukjp.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 'superadmin', NULL, NULL, 0, 1, '2026-11-03 01:49:52', '2026-11-03 01:49:52');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_dashboard_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_dashboard_stats` (
`total_applicants` bigint(21)
,`pending_count` bigint(21)
,`verified_count` bigint(21)
,`rejected_count` bigint(21)
,`active_prodi` bigint(21)
,`verified_payments` bigint(21)
,`total_revenue` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_prodi_statistics`
-- (See below for the actual view)
--
CREATE TABLE `v_prodi_statistics` (
`id` int(11)
,`kode_prodi` varchar(20)
,`jenjang` varchar(50)
,`nama_prodi` varchar(100)
,`kuota` int(11)
,`total_pendaftar` bigint(21)
,`accepted` decimal(23,0)
,`pending` decimal(23,0)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table` (`table_name`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pendaftaran` (`kode_pendaftaran`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status_daftar` (`status_daftar`),
  ADD KEY `idx_status_bayar` (`status_pembayaran`),
  ADD KEY `idx_prodi` (`prodi_id`),
  ADD KEY `idx_gelombang` (`gelombang_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_nama` (`nama_lengkap`),
  ADD KEY `fk_applicant_prodi2` (`prodi_pilihan_2`),
  ADD KEY `fk_applicant_jalur` (`jalur_id`),
  ADD KEY `fk_applicant_verifier` (`verified_by`);

--
-- Indexes for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_applicant` (`applicant_id`),
  ADD KEY `idx_type` (`document_type`),
  ADD KEY `idx_status` (`verification_status`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_key` (`template_key`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `gelombang`
--
ALTER TABLE `gelombang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tahun` (`tahun_akademik`),
  ADD KEY `idx_open` (`is_open`);

--
-- Indexes for table `jalur_pendaftaran`
--
ALTER TABLE `jalur_pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jalur` (`kode_jalur`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_position` (`menu_position`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_order` (`sort_order`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `fk_news_author` (`author_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_applicant` (`applicant_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`payment_date`),
  ADD KEY `fk_payment_verifier` (`verified_by`);

--
-- Indexes for table `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_prodi` (`kode_prodi`),
  ADD KEY `idx_jenjang` (`jenjang`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_nama` (`nama_prodi`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_group` (`setting_group`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gelombang`
--
ALTER TABLE `gelombang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jalur_pendaftaran`
--
ALTER TABLE `jalur_pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure for view `v_dashboard_stats`
--
DROP TABLE IF EXISTS `v_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_dashboard_stats`  AS SELECT (select count(0) from `applicants`) AS `total_applicants`, (select count(0) from `applicants` where (`applicants`.`status_daftar` = 'Pending')) AS `pending_count`, (select count(0) from `applicants` where (`applicants`.`status_daftar` = 'Diverifikasi')) AS `verified_count`, (select count(0) from `applicants` where (`applicants`.`status_daftar` = 'Ditolak')) AS `rejected_count`, (select count(0) from `prodi` where (`prodi`.`is_active` = 1)) AS `active_prodi`, (select count(0) from `payments` where (`payments`.`status` = 'Verified')) AS `verified_payments`, (select coalesce(sum(`payments`.`amount`),0) from `payments` where (`payments`.`status` = 'Verified')) AS `total_revenue` ;

-- --------------------------------------------------------

--
-- Structure for view `v_prodi_statistics`
--
DROP TABLE IF EXISTS `v_prodi_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_prodi_statistics`  AS SELECT `p`.`id` AS `id`, `p`.`kode_prodi` AS `kode_prodi`, `p`.`jenjang` AS `jenjang`, `p`.`nama_prodi` AS `nama_prodi`, `p`.`kuota` AS `kuota`, count(`a`.`id`) AS `total_pendaftar`, sum((case when (`a`.`status_daftar` = 'Diverifikasi') then 1 else 0 end)) AS `accepted`, sum((case when (`a`.`status_daftar` = 'Pending') then 1 else 0 end)) AS `pending` FROM (`prodi` `p` left join `applicants` `a` on((`p`.`id` = `a`.`prodi_id`))) WHERE (`p`.`is_active` = 1) GROUP BY `p`.`id` ORDER BY `total_pendaftar` DESC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `fk_applicant_gelombang` FOREIGN KEY (`gelombang_id`) REFERENCES `gelombang` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_jalur` FOREIGN KEY (`jalur_id`) REFERENCES `jalur_pendaftaran` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_prodi` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_applicant_prodi2` FOREIGN KEY (`prodi_pilihan_2`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD CONSTRAINT `fk_doc_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
