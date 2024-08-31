/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table order_projectflow.pekerjaan
CREATE TABLE IF NOT EXISTS `pekerjaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_pekerjaan` varchar(255) NOT NULL,
  `jumlah_total` int NOT NULL,
  `sudah_dikerjakan` int DEFAULT '0',
  `status` enum('Belum Dikerjakan','Sedang Dikerjakan','Selesai') NOT NULL,
  `project_id` int DEFAULT NULL,
  `row_status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `pekerjaan_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.pekerjaan: ~2 rows (approximately)
INSERT INTO `pekerjaan` (`id`, `nama_pekerjaan`, `jumlah_total`, `sudah_dikerjakan`, `status`, `project_id`, `row_status`, `created_at`, `updated_at`) VALUES
    (4, 'qwerty 1', 123, 123, 'Selesai', 3, 1, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (5, 'qwerty 2', 123, 123, 'Selesai', 3, 1, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (6, 'qwerty 3', 123, 123, 'Selesai', 3, 1, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (7, 'qwerty 1', 2, 2, 'Selesai', 4, 1, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (8, 'qwerty 2 bkn', 2, 2, 'Selesai', 4, 1, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (9, 'qwerty 1', 2, 2, 'Selesai', 4, 2, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (10, 'qwerty 2', 2, 2, 'Selesai', 4, 2, '2024-08-25 17:36:59', '2024-08-25 17:36:59'),
    (11, 'qwerty 1', 12, 12, 'Selesai', 5, 1, '2024-08-25 17:36:59', '2024-08-25 17:44:20'),
    (12, '12', 12, 12, 'Selesai', 5, 2, '2024-08-25 17:36:59', '2024-08-25 17:44:27'),
    (13, 'qwerty 1', 2, 2, 'Selesai', 6, 1, '2024-08-25 17:48:51', '2024-08-25 17:51:07'),
    (14, 'qwerty 1', 2, 2, 'Selesai', 6, 2, '2024-08-25 17:48:51', '2024-08-25 17:51:11');

-- Dumping structure for table order_projectflow.persiapan
CREATE TABLE IF NOT EXISTS `persiapan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(255) NOT NULL,
  `status` enum('Belum Siap','Siap','Dalam Proses') NOT NULL,
  `project_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `row_status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `persiapan_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.persiapan: ~6 rows (approximately)
INSERT INTO `persiapan` (`id`, `nama_barang`, `status`, `project_id`, `created_at`, `updated_at`, `row_status`) VALUES
    (4, 'qwerty', 'Siap', 3, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (5, 'qwerty 1', 'Siap', 3, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (6, 'qwerty 2', 'Belum Siap', 3, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (7, 'qwerty', 'Siap', 4, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (8, 'qwerty 2', 'Siap', 4, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (9, 'qwerty ku', 'Siap', 5, '2024-08-25 17:36:59', '2024-08-25 17:36:59', 1),
    (10, 'qwerty', 'Siap', 6, '2024-08-25 17:48:51', '2024-08-25 17:50:59', 1),
    (11, 'qwerty', 'Siap', 6, '2024-08-25 17:48:51', '2024-08-25 17:54:29', 2);

-- Dumping structure for table order_projectflow.project
CREATE TABLE IF NOT EXISTS `project` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_project` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `user_id` int NOT NULL,
  `foto_persiapan` varchar(255) DEFAULT NULL,
  `catatan_persiapan` varchar(255) DEFAULT '',
  `foto_pekerjaan` varchar(255) DEFAULT NULL,
  `catatan_pekerjaan` varchar(255) DEFAULT '',
  `foto_finish` varchar(255) DEFAULT NULL,
  `catatan_finish` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_project` (`user_id`),
  CONSTRAINT `fk_user_project` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.project: ~1 rows (approximately)
INSERT INTO `project` (`id`, `nama_project`, `deskripsi`, `code`, `user_id`, `foto_persiapan`, `catatan_persiapan`, `foto_pekerjaan`, `catatan_pekerjaan`, `foto_finish`, `catatan_finish`, `created_at`, `updated_at`) VALUES
    (3, 'tes1', 'tes1', '1234', 1, '', '', '', '', '', '2024-08-25 17:34:34', '2024-08-25 17:34:34'),
    (4, 'tes2', 'tes2', '1234', 1, '', '', '', '', '', '2024-08-25 17:34:34', '2024-08-25 17:34:34'),
    (5, 'tes3', 'tes3', '5678', 1, '', '', '', '', '', '2024-08-25 17:34:34', '2024-08-25 17:34:34'),
    (6, 'tes4', 'tes4', '6789', 1, '', '', '', '', '', '2024-08-25 17:34:34', '2024-08-25 17:34:34');

-- Dumping structure for table order_projectflow.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table order_projectflow.user: ~2 rows (approximately)
INSERT INTO `user` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
    (1, 'admin', 'admin@admin.com', NULL, '$2y$10$H4Di3KwIczruIoRna46Xn.QehOX/TgeNmXhYpYxL6NdpFAJd2rBfi', NULL, '2024-08-25 17:34:34', '2024-08-25 17:34:34'),
    (4, 'admin', 'admin@admin.com', NULL, '$2y$10$H4Di3KwIczruIoRna46Xn.QehOX/TgeNmXhYpYxL6NdpFAJd2rBfi', NULL, '2024-08-25 17:34:34', '2024-08-25 17:34:34');

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
