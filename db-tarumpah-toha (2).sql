-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 29, 2026 at 05:39 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db-tarumpah-toha`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id_detail_pembelian` int NOT NULL,
  `id_pembelian` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_ukuran` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`id_detail_pembelian`, `id_pembelian`, `id_produk`, `id_ukuran`, `jumlah`, `harga_beli`, `subtotal`) VALUES
(1, 1, 12, 6, 5, '150000.00', '750000.00'),
(2, 2, 3, 3, 1, '150000.00', '150000.00'),
(3, 3, 3, 4, 1, '150000.00', '150000.00'),
(4, 4, 3, 8, 1, '150000.00', '150000.00'),
(5, 5, 3, 8, 1, '150000.00', '150000.00'),
(6, 6, 11, 7, 1, '0.00', '0.00'),
(7, 6, 4, 8, 1, '0.00', '0.00'),
(8, 8, 3, 8, 1, '0.00', '0.00'),
(9, 8, 3, 7, 1, '0.00', '0.00'),
(10, 8, 3, 6, 1, '0.00', '0.00'),
(11, 8, 3, 3, 1, '0.00', '0.00'),
(12, 8, 3, 5, 1, '0.00', '0.00'),
(13, 8, 3, 2, 1, '0.00', '0.00'),
(14, 8, 3, 4, 1, '0.00', '0.00'),
(15, 8, 3, 1, 1, '0.00', '0.00'),
(17, 11, 11, 8, 1, '0.00', '0.00');

--
-- Triggers `detail_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_tambah_stok` AFTER INSERT ON `detail_pembelian` FOR EACH ROW UPDATE stok_produk
SET stok = stok + NEW.jumlah
WHERE id_produk = NEW.id_produk
AND id_ukuran = NEW.id_ukuran
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_ukuran` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `id_ukuran`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(3, 2, 8, 1, 1, '393993.00', '393993.00'),
(4, 3, 8, 1, 1, '393993.00', '393993.00'),
(5, 4, 8, 1, 1, '393993.00', '393993.00'),
(6, 5, 8, 1, 1, '393993.00', '393993.00'),
(7, 6, 8, 1, 1, '393993.00', '393993.00'),
(8, 6, 9, 6, 1, '20000.00', '20000.00'),
(9, 7, 4, 1, 1, '150000.00', '150000.00'),
(10, 7, 4, 4, 1, '150000.00', '150000.00'),
(11, 8, 4, 4, 1, '150000.00', '150000.00'),
(12, 8, 4, 1, 1, '150000.00', '150000.00'),
(13, 9, 8, 1, 1, '393993.00', '393993.00'),
(14, 10, 11, 3, 1, '300000.00', '300000.00'),
(15, 10, 11, 4, 1, '300000.00', '300000.00'),
(16, 10, 4, 4, 1, '150000.00', '150000.00'),
(17, 11, 11, 1, 3, '300000.00', '900000.00'),
(18, 12, 3, 2, 1, '20000.00', '20000.00'),
(19, 12, 4, 4, 1, '150000.00', '150000.00'),
(20, 12, 11, 3, 1, '300000.00', '300000.00'),
(21, 13, 11, 1, 1, '300000.00', '300000.00'),
(22, 14, 12, 1, 1, '250000.00', '250000.00'),
(23, 15, 3, 1, 1, '20000.00', '20000.00'),
(24, 16, 3, 2, 1, '20000.00', '20000.00'),
(25, 17, 4, 8, 1, '150000.00', '150000.00'),
(26, 18, 3, 3, 1, '20000.00', '20000.00'),
(27, 19, 3, 5, 1, '20000.00', '20000.00'),
(28, 19, 11, 7, 1, '300000.00', '300000.00'),
(29, 19, 11, 8, 1, '300000.00', '300000.00'),
(30, 20, 3, 4, 2, '20000.00', '40000.00'),
(31, 21, 8, 1, 1, '393993.00', '393993.00'),
(32, 22, 3, 6, 1, '20000.00', '20000.00');

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `trg_kurang_stok` AFTER INSERT ON `detail_transaksi` FOR EACH ROW UPDATE stok_produk
SET stok = stok - NEW.jumlah
WHERE id_produk = NEW.id_produk
AND id_ukuran = NEW.id_ukuran
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Tarumpah Pria', 'Tarumpah untuk pria dewasa'),
(2, 'Tarumpah Wanita', 'Tarumpah untuk wanita'),
(3, 'Tarumpah Anak', 'Tarumpah untuk anak-anak'),
(4, 'Tarumpah Custom', 'Tarumpah pesanan khusus'),
(6, 'dewasa', '');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` int NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_supplier` int DEFAULT NULL,
  `total_pembelian` decimal(12,2) NOT NULL,
  `biaya_tambahan` decimal(12,2) DEFAULT '0.00',
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `tanggal`, `id_supplier`, `total_pembelian`, `biaya_tambahan`, `keterangan`) VALUES
(1, '2026-01-28 17:11:43', 1, '750000.00', '0.00', ''),
(2, '2026-01-28 17:52:49', 1, '150000.00', '0.00', ''),
(3, '2026-01-28 17:54:17', 1, '150000.00', '0.00', ''),
(4, '2026-01-28 17:58:36', 1, '150000.00', '0.00', ''),
(5, '2026-01-28 17:59:09', 1, '150000.00', '0.00', ''),
(6, '2026-01-28 18:15:34', 1, '0.00', '0.00', ''),
(7, '2026-01-28 18:30:21', 1, '599009.00', '599009.00', 'beli kulit domba'),
(8, '2026-01-28 18:53:36', 1, '0.00', '0.00', ''),
(11, '2026-01-29 00:04:17', 1, '0.00', '0.00', ''),
(12, '2026-01-29 01:19:16', 1, '700000.00', '700000.00', 'beli bahan baku');

-- --------------------------------------------------------

--
-- Table structure for table `produk_kategori`
--

CREATE TABLE `produk_kategori` (
  `id_produk_kategori` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_kategori` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_kategori`
--

INSERT INTO `produk_kategori` (`id_produk_kategori`, `id_produk`, `id_kategori`) VALUES
(4, 4, 2),
(5, 12, 2),
(6, 9, 3),
(8, 14, 2),
(9, 14, 4),
(10, 8, 1),
(12, 3, 2),
(13, 15, 2),
(14, 15, 4),
(15, 15, 6),
(16, 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `produk_tarumpah`
--

CREATE TABLE `produk_tarumpah` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `deskripsi` text,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_tarumpah`
--

INSERT INTO `produk_tarumpah` (`id_produk`, `nama_produk`, `harga`, `id_kategori`, `deskripsi`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Tarumpah Kulit Ular', '20000.00', 2, 'untuk wanita', '1769580076_TarumpahKulitUlar.jpeg', 'aktif', '2026-01-28 13:01:16', '2026-01-28 13:01:16'),
(4, 'sepatu tarumpah', '150000.00', 2, 'sepatu tarumpah awet', '1769580139_sepatutarumpah.jpeg', 'nonaktif', '2026-01-28 13:02:19', '2026-01-29 12:38:25'),
(8, 'hhaoGOG', '393993.00', 1, 'hauiogfqgo', '1769580603_hhaoGOG.jpeg', 'aktif', '2026-01-28 13:10:03', '2026-01-29 01:18:37'),
(9, 'Tarumpah Kulit Ular', '20000.00', 3, 'hhifgwua', '1769580659_TarumpahKulitUlar.jpeg', 'aktif', '2026-01-28 13:10:59', '2026-01-28 21:07:34'),
(11, 'tarumpah pria', '300000.00', 1, 'gouououas', '1769649346_edit_tarumpahpria.jpeg', 'aktif', '2026-01-28 13:16:32', '2026-01-29 08:15:46'),
(12, 'sepatu tarumpah cewe', '250000.00', 2, 'jbaojdwkllk', '1769589669_sepatutarumpahcewe.jpeg', 'aktif', '2026-01-28 15:41:09', '2026-01-28 21:07:31'),
(14, 'sepatu cewe tarumpah abu', '300000.00', NULL, '', '1769603431_sepatucewetarumpahabu.jpeg', 'aktif', '2026-01-28 19:30:31', '2026-01-28 19:30:31'),
(15, 'tarumpah cewe', '375000.00', NULL, '', '1769620966_tarumpahcewe.jpeg', 'aktif', '2026-01-29 00:22:46', '2026-01-29 01:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `stok_produk`
--

CREATE TABLE `stok_produk` (
  `id_stok` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_ukuran` int NOT NULL,
  `stok` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stok_produk`
--

INSERT INTO `stok_produk` (`id_stok`, `id_produk`, `id_ukuran`, `stok`) VALUES
(5, 3, 1, 0),
(6, 3, 3, 2),
(7, 3, 2, 0),
(8, 4, 1, 1),
(9, 4, 4, 0),
(13, 8, 1, 0),
(15, 11, 1, 0),
(16, 11, 3, 0),
(17, 11, 2, 0),
(18, 11, 4, 0),
(20, 11, 6, 0),
(21, 12, 6, 0),
(22, 12, 1, 0),
(23, 9, 2, 0),
(24, 9, 4, 0),
(25, 12, 8, 0),
(26, 12, 7, 0),
(27, 3, 4, 0),
(28, 3, 8, 3),
(29, 11, 7, 0),
(30, 4, 8, 0),
(31, 3, 7, 1),
(32, 3, 6, 1),
(33, 3, 5, 0),
(34, 14, 8, 0),
(35, 14, 7, 0),
(36, 14, 6, 0),
(37, 14, 1, 0),
(38, 14, 3, 0),
(39, 14, 5, 0),
(40, 14, 2, 0),
(41, 14, 4, 0),
(42, 8, 3, 0),
(43, 8, 5, 0),
(44, 11, 8, 0),
(45, 15, 8, 0),
(46, 15, 1, 0),
(47, 15, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'apa ', '098765447675', '', '2026-01-28 17:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_harga` decimal(12,2) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `status` enum('selesai','diproses','dibatalkan') DEFAULT 'selesai',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `total_harga`, `metode_pembayaran`, `status`, `created_at`) VALUES
(2, '2026-01-28 13:43:26', '393993.00', 'online', 'selesai', '2026-01-28 13:43:26'),
(3, '2026-01-28 13:43:54', '393993.00', 'online', 'selesai', '2026-01-28 13:43:54'),
(4, '2026-01-28 13:45:25', '393993.00', 'online', 'dibatalkan', '2026-01-28 13:45:25'),
(5, '2026-01-28 15:49:41', '393993.00', 'online', 'dibatalkan', '2026-01-28 15:49:41'),
(6, '2026-01-28 15:51:06', '413993.00', 'offline', 'selesai', '2026-01-28 15:51:06'),
(7, '2026-01-28 15:57:23', '300000.00', 'online', 'selesai', '2026-01-28 15:57:23'),
(8, '2026-01-28 15:58:22', '300000.00', 'offline', 'selesai', '2026-01-28 15:58:22'),
(9, '2026-01-28 15:58:46', '393993.00', 'online', 'selesai', '2026-01-28 15:58:46'),
(10, '2026-01-28 16:30:48', '750000.00', 'offline', 'selesai', '2026-01-28 16:30:48'),
(11, '2026-01-28 16:32:07', '900000.00', 'online', 'dibatalkan', '2026-01-28 16:32:07'),
(12, '2026-01-28 16:33:45', '470000.00', 'online', 'selesai', '2026-01-28 16:33:45'),
(13, '2026-01-28 16:40:35', '270000.00', 'online', 'selesai', '2026-01-28 16:40:35'),
(14, '2026-01-28 17:12:21', '250000.00', 'online', 'selesai', '2026-01-28 17:12:21'),
(15, '2026-01-28 23:43:03', '20000.00', 'offline', 'selesai', '2026-01-28 23:43:03'),
(16, '2026-01-29 00:12:12', '20000.00', 'online', 'selesai', '2026-01-29 00:12:12'),
(17, '2026-01-29 00:18:19', '150000.00', 'online', 'selesai', '2026-01-29 00:18:19'),
(18, '2026-01-29 00:26:21', '20000.00', 'online', 'dibatalkan', '2026-01-29 00:26:21'),
(19, '2026-01-29 01:12:25', '620000.00', 'online', 'selesai', '2026-01-29 01:12:25'),
(20, '2026-01-29 01:21:08', '40000.00', 'online', 'diproses', '2026-01-29 01:21:08'),
(21, '2026-01-29 08:08:43', '393993.00', 'online', 'selesai', '2026-01-29 08:08:43'),
(22, '2026-01-29 08:32:14', '20000.00', 'online', 'dibatalkan', '2026-01-29 08:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `ukuran`
--

CREATE TABLE `ukuran` (
  `id_ukuran` int NOT NULL,
  `ukuran` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ukuran`
--

INSERT INTO `ukuran` (`id_ukuran`, `ukuran`) VALUES
(1, '40'),
(2, '43'),
(3, '41'),
(4, '45'),
(5, '42'),
(6, '39'),
(7, '38'),
(8, '37'),
(9, '44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id_detail_pembelian`),
  ADD KEY `id_pembelian` (`id_pembelian`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_ukuran` (`id_ukuran`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_ukuran` (`id_ukuran`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `id_supplier` (`id_supplier`);

--
-- Indexes for table `produk_kategori`
--
ALTER TABLE `produk_kategori`
  ADD PRIMARY KEY (`id_produk_kategori`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `produk_tarumpah`
--
ALTER TABLE `produk_tarumpah`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `produk_tarumpah_ibfk_kategori` (`id_kategori`);

--
-- Indexes for table `stok_produk`
--
ALTER TABLE `stok_produk`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_ukuran` (`id_ukuran`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- Indexes for table `ukuran`
--
ALTER TABLE `ukuran`
  ADD PRIMARY KEY (`id_ukuran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id_detail_pembelian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id_pembelian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `produk_kategori`
--
ALTER TABLE `produk_kategori`
  MODIFY `id_produk_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `produk_tarumpah`
--
ALTER TABLE `produk_tarumpah`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `stok_produk`
--
ALTER TABLE `stok_produk`
  MODIFY `id_stok` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `ukuran`
--
ALTER TABLE `ukuran`
  MODIFY `id_ukuran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk_tarumpah` (`id_produk`),
  ADD CONSTRAINT `detail_pembelian_ibfk_3` FOREIGN KEY (`id_ukuran`) REFERENCES `ukuran` (`id_ukuran`);

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk_tarumpah` (`id_produk`),
  ADD CONSTRAINT `detail_transaksi_ibfk_3` FOREIGN KEY (`id_ukuran`) REFERENCES `ukuran` (`id_ukuran`);

--
-- Constraints for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL;

--
-- Constraints for table `produk_kategori`
--
ALTER TABLE `produk_kategori`
  ADD CONSTRAINT `produk_kategori_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk_tarumpah` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `produk_kategori_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

--
-- Constraints for table `produk_tarumpah`
--
ALTER TABLE `produk_tarumpah`
  ADD CONSTRAINT `produk_tarumpah_ibfk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `stok_produk`
--
ALTER TABLE `stok_produk`
  ADD CONSTRAINT `stok_produk_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk_tarumpah` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `stok_produk_ibfk_2` FOREIGN KEY (`id_ukuran`) REFERENCES `ukuran` (`id_ukuran`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
