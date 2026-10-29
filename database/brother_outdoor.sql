-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 05:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brother_outdoor`
--

-- --------------------------------------------------------

--
-- Table structure for table `alat`
--

CREATE TABLE `alat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kategori_id` bigint(20) UNSIGNED NOT NULL,
  `nama_alat` varchar(255) NOT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `jenis_alat` enum('sewa','aksesoris','jual') NOT NULL DEFAULT 'sewa',
  `kode_alat` varchar(255) NOT NULL,
  `stok_total` int(11) NOT NULL,
  `stok_tersedia` int(11) NOT NULL,
  `stok_pencucian` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `harga_sewa` int(11) NOT NULL,
  `harga_jual` int(10) UNSIGNED DEFAULT NULL,
  `kondisi` enum('baik','rusak','perbaikan') NOT NULL DEFAULT 'baik',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alat`
--

INSERT INTO `alat` (`id`, `kategori_id`, `nama_alat`, `ukuran`, `jenis_alat`, `kode_alat`, `stok_total`, `stok_tersedia`, `stok_pencucian`, `harga_sewa`, `harga_jual`, `kondisi`, `keterangan`, `created_at`, `updated_at`) VALUES
(7, 6, 'JAKET PUFFER', 'L', 'sewa', 'SYS-270ccdab-e3f9-4930-b00a-f5a27d60de56', 20, 20, 0, 15000, NULL, 'baik', NULL, '2026-10-07 04:02:49', '2026-10-21 13:10:13'),
(8, 6, 'JAKET PUFFER', 'M', 'sewa', 'SYS-b508630a-1d79-4a02-8a8c-9f7eaa2cdbca', 30, 30, 0, 15000, NULL, 'baik', NULL, '2026-10-07 04:03:31', '2026-10-21 13:10:20'),
(9, 7, 'SEPATU RUNNING', '39', 'sewa', 'SYS-72302708-7dda-4731-95d3-e8fe3633075d', 10, 10, 0, 25000, NULL, 'baik', NULL, '2026-10-07 04:18:06', '2026-10-21 13:10:09'),
(10, 9, 'KACAMATA', NULL, 'aksesoris', 'SYS-5fb07bdf-3beb-4cf0-b985-b0c9c0993667', 10, 10, 0, 10000, NULL, 'baik', NULL, '2026-10-12 19:46:58', '2026-10-21 13:10:26'),
(11, 10, 'JAS HUJAN', NULL, 'jual', 'SYS-d4bad33c-5cbb-4b28-8622-2d5e7a766bdb', 20, 20, 0, 0, 10000, 'baik', NULL, '2026-10-13 14:46:37', '2026-10-13 14:46:37');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_rental`
--

CREATE TABLE `detail_rental` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rental_id` bigint(20) UNSIGNED NOT NULL,
  `alat_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(11) NOT NULL,
  `jenis_item` enum('sewa','gratis','jual') NOT NULL DEFAULT 'sewa',
  `ukuran` varchar(50) DEFAULT NULL,
  `harga_sewa` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_rental`
--

INSERT INTO `detail_rental` (`id`, `rental_id`, `alat_id`, `jumlah`, `jenis_item`, `ukuran`, `harga_sewa`, `subtotal`, `created_at`, `updated_at`) VALUES
(6, 6, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-07 04:05:04', '2026-10-07 04:05:04'),
(7, 6, 8, 2, 'sewa', 'M', 15000, 30000, '2026-10-07 04:05:04', '2026-10-07 04:05:04'),
(8, 7, 8, 5, 'sewa', 'M', 15000, 75000, '2026-10-07 04:12:46', '2026-10-07 04:12:46'),
(9, 8, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-09 22:02:08', '2026-10-09 22:02:08'),
(10, 8, 8, 1, 'sewa', 'M', 15000, 15000, '2026-10-09 22:02:08', '2026-10-09 22:02:08'),
(11, 8, 9, 2, 'sewa', '39', 25000, 50000, '2026-10-09 22:02:08', '2026-10-09 22:02:08'),
(15, 10, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-09 22:43:42', '2026-10-09 22:43:42'),
(16, 11, 9, 1, 'sewa', '39', 25000, 25000, '2026-10-09 22:45:44', '2026-10-09 22:45:44'),
(17, 12, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-12 19:46:05', '2026-10-12 19:46:05'),
(18, 13, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-12 19:47:30', '2026-10-12 19:47:30'),
(19, 13, 8, 1, 'sewa', 'M', 15000, 15000, '2026-10-12 19:47:30', '2026-10-12 19:47:30'),
(20, 13, 10, 1, 'sewa', NULL, 10000, 10000, '2026-10-12 19:47:30', '2026-10-12 19:47:30'),
(21, 13, 9, 1, 'sewa', '39', 25000, 25000, '2026-10-12 19:47:30', '2026-10-12 19:47:30'),
(22, 14, 11, 1, 'jual', NULL, 10000, 10000, '2026-10-13 14:48:21', '2026-10-13 14:48:21'),
(23, 14, 10, 1, 'gratis', NULL, 0, 0, '2026-10-13 14:48:21', '2026-10-13 14:48:21'),
(24, 14, 9, 1, 'sewa', '39', 25000, 25000, '2026-10-13 14:48:21', '2026-10-13 14:48:21'),
(25, 15, 7, 1, 'sewa', 'L', 15000, 15000, '2026-10-16 03:16:10', '2026-10-16 03:16:10'),
(26, 15, 9, 1, 'sewa', '39', 25000, 25000, '2026-10-16 03:16:10', '2026-10-16 03:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `keterangan`, `created_at`, `updated_at`) VALUES
(6, 'JAKET', 'Pakaian hangan untuk cuaca dingin gunung', '2026-10-07 03:58:42', '2026-10-17 06:29:07'),
(7, 'SEPATU', NULL, '2026-10-07 03:58:50', '2026-10-07 03:58:50'),
(8, 'SLEEPING BAG', NULL, '2026-10-12 19:37:23', '2026-10-13 14:38:11'),
(9, 'KACAMATA', NULL, '2026-10-12 19:38:34', '2026-10-13 14:37:53'),
(10, 'JAS HUJAN', NULL, '2026-10-13 14:38:25', '2026-10-13 14:38:25'),
(11, 'HAND WARMER', 'Penghangat Tangan', '2026-10-13 14:38:50', '2026-10-17 06:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_09_140233_create_kategori_table', 1),
(5, '2026_05_09_140253_create_alat_table', 1),
(6, '2026_05_09_140308_create_pelanggan_table', 1),
(7, '2026_05_09_140321_create_rental_table', 1),
(8, '2026_05_09_140335_create_detail_rental_table', 1),
(9, '2026_10_07_000001_add_identity_tracking_to_rental_table', 2),
(10, '2026_10_07_000002_add_size_to_alat_and_detail_rental_tables', 2),
(11, '2026_10_07_000003_add_transaction_type_to_rental_table', 3),
(12, '2026_10_09_000001_add_cleaning_stock_to_alat_table', 4),
(13, '2026_10_10_000001_add_item_types_to_alat_and_detail_rental', 5),
(14, '2026_10_13_000001_add_no_hp_to_users_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_pelanggan` varchar(255) NOT NULL,
  `no_hp` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama_pelanggan`, `no_hp`, `nik`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 'Arga Pandyansa', '081575774796', '123456', 'tunggoro', '2026-05-11 04:27:51', '2026-05-11 04:27:51'),
(2, 'Narendra Bima', '11111111111', '000000', 'sawangan', '2026-05-11 04:30:52', '2026-05-11 04:30:52'),
(3, 'ikma', '22222222', '333333', 'wsb', '2026-05-17 21:34:57', '2026-05-17 21:34:57'),
(4, 'wawan', '987654', 'gdushc', 'jetis', '2026-10-01 22:02:49', '2026-10-01 22:02:49'),
(5, 'engga', '111111111', '0000', 'garung', '2026-10-05 00:28:21', '2026-10-05 00:28:21'),
(6, 'dhea', '000000', '99999', 'sawangan', '2026-10-07 03:44:28', '2026-10-07 03:44:28'),
(7, 'tukiman', '000000', '2222', 'pulu', '2026-10-09 22:01:13', '2026-10-09 22:01:13'),
(8, 'kiman', '099988886666', '1234567891012345', 'kertek', '2026-10-09 22:03:32', '2026-10-21 03:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `rental`
--

CREATE TABLE `rental` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_rental` varchar(255) NOT NULL,
  `pelanggan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_rental` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `tgl_kembali_aktual` date DEFAULT NULL,
  `total_bayar` int(11) NOT NULL,
  `denda` int(11) NOT NULL DEFAULT 0,
  `identitas_dititipkan` tinyint(1) NOT NULL DEFAULT 0,
  `identitas_dikembalikan` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('dipinjam','dikembalikan','terlambat') NOT NULL DEFAULT 'dipinjam',
  `jenis_transaksi` varchar(20) NOT NULL DEFAULT 'langsung',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rental`
--

INSERT INTO `rental` (`id`, `kode_rental`, `pelanggan_id`, `user_id`, `tgl_rental`, `tgl_kembali`, `tgl_kembali_aktual`, `total_bayar`, `denda`, `identitas_dititipkan`, `identitas_dikembalikan`, `status`, `jenis_transaksi`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'RNT-20260511-150', 1, 1, '2026-05-11', '2026-05-13', '2026-05-13', 100000, 0, 0, 0, 'dikembalikan', 'langsung', 'barang sudah kembali', '2026-05-11 04:31:37', '2026-05-11 04:32:03'),
(2, 'RNT-20260511-967', 2, 1, '2026-05-07', '2026-05-08', '2026-05-08', 100000, 50000, 0, 0, 'dikembalikan', 'langsung', 'barang sudah kembali', '2026-05-11 04:34:08', '2026-10-05 00:30:54'),
(3, 'RNT-20260518-147', 3, 1, '2026-05-18', '2026-05-20', '2026-05-20', 40000, 0, 0, 0, 'dikembalikan', 'langsung', 'harus tepat waktu', '2026-05-17 21:36:00', '2026-10-01 21:10:23'),
(4, 'RNT-20261002-212', 4, 1, '2026-10-02', '2026-10-03', '2026-10-03', 40000, 0, 0, 0, 'dikembalikan', 'langsung', NULL, '2026-10-01 22:14:17', '2026-10-02 06:34:25'),
(5, 'RNT-20261005-874', 5, 1, '2026-10-05', '2026-10-07', '2026-10-07', 60000, 0, 0, 0, 'dikembalikan', 'langsung', NULL, '2026-10-05 00:29:53', '2026-10-07 02:21:14'),
(6, 'RNT-20261007-381', 6, 1, '2026-10-07', '2026-10-09', '2026-10-09', 45000, 0, 1, 1, 'dikembalikan', 'langsung', NULL, '2026-10-07 04:05:04', '2026-10-09 06:08:25'),
(7, 'RNT-20261007-515', 5, 1, '2026-10-08', '2026-10-10', '2026-10-09', 75000, 0, 0, 0, 'dikembalikan', 'booking', NULL, '2026-10-07 04:12:46', '2026-10-09 06:08:37'),
(8, 'RNT-20261010-320', 7, 1, '2026-10-10', '2026-10-12', '2026-10-10', 80000, 0, 1, 0, 'dikembalikan', 'langsung', NULL, '2026-10-09 22:02:08', '2026-10-09 22:27:25'),
(10, 'RNT-20261010-179', 6, 1, '2026-10-10', '2026-10-11', '2026-10-11', 15000, 50000, 1, 1, 'dikembalikan', 'booking', NULL, '2026-10-09 22:43:42', '2026-10-11 03:38:36'),
(11, 'RNT-20261010-364', 5, 1, '2026-10-12', '2026-10-13', '2026-10-15', 25000, 50000, 0, 0, 'dikembalikan', 'booking', NULL, '2026-10-09 22:45:44', '2026-10-15 12:28:22'),
(12, 'RNT-20261013-320', 8, 1, '2026-10-13', '2026-10-14', '2026-10-15', 15000, 50000, 1, 0, 'dikembalikan', 'langsung', NULL, '2026-10-12 19:46:05', '2026-10-15 12:28:35'),
(13, 'RNT-20261013-856', 6, 1, '2026-10-13', '2026-10-14', '2026-10-16', 65000, 50000, 1, 0, 'dikembalikan', 'langsung', NULL, '2026-10-12 19:47:30', '2026-10-16 03:04:37'),
(14, 'RNT-20261013-454', 2, 1, '2026-10-13', '2026-10-15', '2026-10-16', 35000, 50000, 1, 1, 'dikembalikan', 'langsung', NULL, '2026-10-13 14:48:21', '2026-10-16 03:06:09'),
(15, 'RNT-20261016-805', 6, 1, '2026-10-17', '2026-10-18', '2026-10-21', 40000, 50000, 1, 1, 'dikembalikan', 'booking', NULL, '2026-10-16 03:16:10', '2026-10-21 03:35:36');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4ZUGKiDSj3yxrep4HlPOIJpvn6wVxWV7S2rxhtUO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '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', 1793290155),
('Ga7Jf49dTyzR8FhZ2kr3OuNig0REpvDOX2cQWs4c', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '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', 1792412878),
('Qx5mifzIEwDwIE0PHKuHVnh6I39XZURSRbJ0xl7d', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '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', 1792588543),
('S4HV624gNrZJg936m7ygA75FVfxbSUcazwy5OXhi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSm9WdXRndVdCWHVSdmxlR0pLVXhQdDh0RXgwS2hENWhWWkxSbjBmWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC91c2VyIjtzOjU6InJvdXRlIjtzOjEwOiJ1c2VyLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo0OiJ1c2VyIjtPOjE1OiJBcHBcTW9kZWxzXFVzZXIiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6Im15c3FsIjtzOjg6IgAqAHRhYmxlIjtzOjU6InVzZXJzIjtzOjEzOiIAKgBwcmltYXJ5S2V5IjtzOjI6ImlkIjtzOjEwOiIAKgBrZXlUeXBlIjtzOjM6ImludCI7czoxMjoiaW5jcmVtZW50aW5nIjtiOjE7czo3OiIAKgB3aXRoIjthOjA6e31zOjEyOiIAKgB3aXRoQ291bnQiO2E6MDp7fXM6MTk6InByZXZlbnRzTGF6eUxvYWRpbmciO2I6MDtzOjEwOiIAKgBwZXJQYWdlIjtpOjE1O3M6NjoiZXhpc3RzIjtiOjE7czoxODoid2FzUmVjZW50bHlDcmVhdGVkIjtiOjA7czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO3M6MTM6IgAqAGF0dHJpYnV0ZXMiO2E6ODp7czoyOiJpZCI7aToxO3M6NDoibmFtYSI7czo1OiJBZG1pbiI7czo4OiJ1c2VybmFtZSI7czo1OiJhZG1pbiI7czo1OiJub19ocCI7czoxMjoiMDgxMTkwOTAwMDQ2IjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTIkL280NnVMRmtBTEt4Z09UcUgwWjdGT1dmUExTVU8zZ2VlT2pvc0E5cU5yTy9JVWIzRkEueWUiO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czoxMDoiY3JlYXRlZF9hdCI7TjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjE5OiIyMDI2LTEwLTE3IDEzOjI4OjAwIjt9czoxMToiACoAb3JpZ2luYWwiO2E6ODp7czoyOiJpZCI7aToxO3M6NDoibmFtYSI7czo1OiJBZG1pbiI7czo4OiJ1c2VybmFtZSI7czo1OiJhZG1pbiI7czo1OiJub19ocCI7czoxMjoiMDgxMTkwOTAwMDQ2IjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTIkL280NnVMRmtBTEt4Z09UcUgwWjdGT1dmUExTVU8zZ2VlT2pvc0E5cU5yTy9JVWIzRkEueWUiO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czoxMDoiY3JlYXRlZF9hdCI7TjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjE5OiIyMDI2LTEwLTE3IDEzOjI4OjAwIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6MDp7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MTp7aTowO3M6ODoicGFzc3dvcmQiO31zOjEwOiIAKgB2aXNpYmxlIjthOjA6e31zOjExOiIAKgBmaWxsYWJsZSI7YTo1OntpOjA7czo0OiJuYW1hIjtpOjE7czo4OiJ1c2VybmFtZSI7aToyO3M6NToibm9faHAiO2k6MztzOjg6InBhc3N3b3JkIjtpOjQ7czo0OiJyb2xlIjt9czoxMDoiACoAZ3VhcmRlZCI7YToxOntpOjA7czoxOiIqIjt9fX0=', 1792553891);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','owner') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `no_hp`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', '088827276154', '$2y$12$WdpwAuVT3Z6lR2ORO0Aj5uQUiyX1ePaZpbwZuIUychlLWS.xGjzca', 'admin', NULL, '2026-10-21 03:29:02'),
(4, 'Owner', 'Owner', '081575774796', '$2y$12$iSNlebMXoqKNigQoNi34q.0ol6gCHGR/eqY2kPMI4qFOq8k/TCndy', 'owner', '2026-10-16 03:28:45', '2026-10-16 03:30:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alat`
--
ALTER TABLE `alat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alat_kode_alat_unique` (`kode_alat`),
  ADD KEY `alat_kategori_id_foreign` (`kategori_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `detail_rental`
--
ALTER TABLE `detail_rental`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_rental_rental_id_foreign` (`rental_id`),
  ADD KEY `detail_rental_alat_id_foreign` (`alat_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pelanggan_nik_unique` (`nik`);

--
-- Indexes for table `rental`
--
ALTER TABLE `rental`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rental_kode_rental_unique` (`kode_rental`),
  ADD KEY `rental_pelanggan_id_foreign` (`pelanggan_id`),
  ADD KEY `rental_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alat`
--
ALTER TABLE `alat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `detail_rental`
--
ALTER TABLE `detail_rental`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rental`
--
ALTER TABLE `rental`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alat`
--
ALTER TABLE `alat`
  ADD CONSTRAINT `alat_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_rental`
--
ALTER TABLE `detail_rental`
  ADD CONSTRAINT `detail_rental_alat_id_foreign` FOREIGN KEY (`alat_id`) REFERENCES `alat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_rental_rental_id_foreign` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rental`
--
ALTER TABLE `rental`
  ADD CONSTRAINT `rental_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
