-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table order_projectflow.dokumentasi
CREATE TABLE IF NOT EXISTS `dokumentasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) DEFAULT NULL,
  `status` int DEFAULT '1',
  `row_status` int DEFAULT '1',
  `project_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`project_id`) USING BTREE,
  CONSTRAINT `project_id` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.dokumentasi: ~8 rows (approximately)
INSERT INTO `dokumentasi` (`id`, `foto`, `status`, `row_status`, `project_id`) VALUES
	(1, 'dokumen/pixel-art-flowers-reflection-trees-Moon-computer-2271556-wallhere.com.jpg', 1, 1, 1),
	(2, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(3, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(4, 'dokumen/fujiphilm-VgU5zIEy57A-unsplash.jpg', 1, 1, 1),
	(5, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(6, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(7, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(8, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1),
	(9, 'dokumen/Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, 1);

-- Dumping structure for table order_projectflow.pekerjaan
CREATE TABLE IF NOT EXISTS `pekerjaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_pekerjaan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah_total` int NOT NULL,
  `sudah_dikerjakan` int DEFAULT '0',
  `status` enum('Belum Dikerjakan','Sedang Dikerjakan','Selesai') COLLATE utf8mb4_general_ci NOT NULL,
  `project_id` int DEFAULT NULL,
  `row_status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `pekerjaan_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.pekerjaan: ~4 rows (approximately)
INSERT INTO `pekerjaan` (`id`, `nama_pekerjaan`, `jumlah_total`, `sudah_dikerjakan`, `status`, `project_id`, `row_status`, `created_at`, `updated_at`) VALUES
	(1, 'qwerty 1', 123, 123, 'Selesai', 1, 1, '2024-09-07 09:22:25', '2024-09-07 11:33:02'),
	(2, 'qwerty 2', 2, 2, 'Selesai', 1, 1, '2024-09-07 09:22:25', '2024-09-07 11:33:32'),
	(3, 'qwerty 1', 123, 123, 'Selesai', 1, 2, '2024-09-07 09:22:25', '2024-09-07 11:37:14'),
	(4, 'qwerty 2', 2, 2, 'Selesai', 1, 2, '2024-09-07 09:22:25', '2024-09-07 11:37:14');

-- Dumping structure for table order_projectflow.pengeluaran
CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int DEFAULT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.pengeluaran: ~3 rows (approximately)
INSERT INTO `pengeluaran` (`id`, `project_id`, `nama_barang`, `qty`, `harga`, `updated_at`) VALUES
	(1, 2, 'qwerty', 12, 1000.00, '2024-09-17 19:49:43'),
	(2, 2, 'qwerty', 1, 100.00, '2024-09-17 19:49:43'),
	(3, 2, 'qwerty', 1, 1.00, '2024-09-17 19:49:43');

-- Dumping structure for table order_projectflow.persiapan
CREATE TABLE IF NOT EXISTS `persiapan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Belum Siap','Siap','Dalam Proses') COLLATE utf8mb4_general_ci NOT NULL,
  `project_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `row_status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `persiapan_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.persiapan: ~2 rows (approximately)
INSERT INTO `persiapan` (`id`, `nama_barang`, `status`, `project_id`, `created_at`, `updated_at`, `row_status`) VALUES
	(5, 'qwerty a', 'Siap', 1, '2024-09-07 09:40:20', '2024-09-07 10:57:41', 1),
	(7, 'qwerty', 'Belum Siap', 2, '2024-09-07 09:42:35', '2024-09-07 09:42:35', 2);

-- Dumping structure for table order_projectflow.project
CREATE TABLE IF NOT EXISTS `project` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_project` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `catatan_persiapan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `catatan_pekerjaan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `catatan_finish` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_project` (`user_id`),
  CONSTRAINT `fk_user_project` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.project: ~0 rows (approximately)
INSERT INTO `project` (`id`, `nama_project`, `deskripsi`, `code`, `user_id`, `catatan_persiapan`, `catatan_pekerjaan`, `catatan_finish`, `created_at`, `updated_at`) VALUES
	(1, 'qwerty', 'qwerty qwerty', 'Selesai', 2, 'oke', 'qwerty', 'qwerty', '2024-09-07 09:22:25', '2024-09-07 11:39:56'),
	(2, 'qwerty', 'qwerty', 'Selesai', 2, '', '', '', '2024-09-07 09:42:35', '2024-09-17 19:19:33');

-- Dumping structure for table order_projectflow.riwayat
CREATE TABLE IF NOT EXISTS `riwayat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `edited` text NOT NULL,
  `status` tinyint DEFAULT '1',
  `row_status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `riwayat_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.riwayat: ~32 rows (approximately)
INSERT INTO `riwayat` (`id`, `project_id`, `edited`, `status`, `row_status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Mengubah status persiapan ID 5 menjadi Siap', 1, 1, '2024-09-07 10:57:41', '2024-09-07 10:57:41'),
	(2, 1, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-07 10:57:41', '2024-09-07 10:57:41'),
	(3, 1, 'Mengubah status persiapan ID 5 menjadi Siap', 1, 1, '2024-09-07 11:09:56', '2024-09-07 11:09:56'),
	(4, 1, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-07 11:09:56', '2024-09-07 11:09:56'),
	(5, 1, 'Mengubah status persiapan ID 5 menjadi Siap', 1, 1, '2024-09-07 11:10:09', '2024-09-07 11:10:09'),
	(6, 1, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-07 11:10:09', '2024-09-07 11:10:09'),
	(7, 1, 'Mengubah status persiapan ID 5 menjadi Siap', 1, 1, '2024-09-07 11:10:35', '2024-09-07 11:10:35'),
	(8, 1, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-07 11:10:35', '2024-09-07 11:10:35'),
	(9, 1, 'Mengubah status persiapan ID 5 menjadi Siap', 1, 1, '2024-09-07 11:10:47', '2024-09-07 11:10:47'),
	(10, 1, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-07 11:10:47', '2024-09-07 11:10:47'),
	(11, 1, 'Menambahkan foto persiapan: pixel-art-flowers-reflection-trees-Moon-computer-2271556-wallhere.com.jpg', 1, 1, '2024-09-07 11:10:47', '2024-09-07 11:10:47'),
	(12, 1, 'Menambahkan foto persiapan: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:10:47', '2024-09-07 11:10:47'),
	(13, 1, 'Mengubah pekerjaan ID 1 menjadi status Selesai, sudah dikerjakan: 123', 1, 1, '2024-09-07 11:33:02', '2024-09-07 11:33:02'),
	(14, 1, 'Mengubah pekerjaan ID 2 menjadi status Belum Dikerjakan, sudah dikerjakan: 0', 1, 1, '2024-09-07 11:33:02', '2024-09-07 11:33:02'),
	(15, 1, 'Menambahkan foto pekerjaan: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:33:02', '2024-09-07 11:33:02'),
	(16, 1, 'Menambahkan foto pekerjaan: fujiphilm-VgU5zIEy57A-unsplash.jpg', 1, 1, '2024-09-07 11:33:02', '2024-09-07 11:33:02'),
	(17, 1, 'Mengubah pekerjaan ID 1 menjadi status Selesai, sudah dikerjakan: 123', 1, 1, '2024-09-07 11:33:32', '2024-09-07 11:33:32'),
	(18, 1, 'Mengubah pekerjaan ID 2 menjadi status Selesai, sudah dikerjakan: 2', 1, 1, '2024-09-07 11:33:32', '2024-09-07 11:33:32'),
	(19, 1, 'Menambahkan foto pekerjaan: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:33:32', '2024-09-07 11:33:32'),
	(20, 1, 'Menambahkan foto pekerjaan: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:33:32', '2024-09-07 11:33:32'),
	(21, 1, 'Mengubah pekerjaan ID 3 menjadi status Selesai, sudah dikerjakan: 123', 1, 1, '2024-09-07 11:37:14', '2024-09-07 11:37:14'),
	(22, 1, 'Mengubah pekerjaan ID 4 menjadi status Selesai, sudah dikerjakan: 2', 1, 1, '2024-09-07 11:37:14', '2024-09-07 11:37:14'),
	(23, 1, 'Menambahkan foto pekerjaan finishing: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:37:14', '2024-09-07 11:37:14'),
	(24, 1, 'Menambahkan foto pekerjaan finishing: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:37:14', '2024-09-07 11:37:14'),
	(25, 1, 'Menambahkan foto persiapan: Aesthetic Laptop Background Pink Wallpaper Cute Desktop.jpg', 1, 1, '2024-09-07 11:39:56', '2024-09-07 11:39:56'),
	(26, 2, 'Mengubah catatan persiapan proyek', 1, 1, '2024-09-17 18:56:30', '2024-09-17 18:56:30'),
	(27, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:19:33', '2024-09-17 19:19:33'),
	(28, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:20:39', '2024-09-17 19:20:39'),
	(29, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:20:44', '2024-09-17 19:20:44'),
	(30, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:21:07', '2024-09-17 19:21:07'),
	(31, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:21:30', '2024-09-17 19:21:30'),
	(32, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:23:04', '2024-09-17 19:23:04'),
	(33, 2, 'Mengubah status persiapan ID 7 menjadi Belum Siap', 1, 1, '2024-09-17 19:44:29', '2024-09-17 19:44:29');

-- Dumping structure for table order_projectflow.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.user: ~2 rows (approximately)
INSERT INTO `user` (`id`, `name`, `email`, `role`, `password`, `created_at`) VALUES
	(1, 'user', 'admin@admin.id', 'admin', '$2y$10$SlYVr40yPFWCKWMOVU9dqeJHrREAvX4H0W5IwLb0XaRKZGvgcV6Ue', '2024-09-07 09:20:48'),
	(2, 'Risa nussy ', 'risa@gmail.com', 'user', '$2y$10$B.wxO5qeCroJ.y7a/PCNcurie69ZjNXEXM.xH8oNoDn.BQ5WD3/bG', '2024-09-07 09:21:02');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
