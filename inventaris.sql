-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 03:20 PM
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
-- Database: `inventaris`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `kode_inventaris` varchar(20) NOT NULL,
  `nama_inventaris` varchar(100) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `jumlah_tersedia` int(11) NOT NULL DEFAULT 0,
  `foto` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventaris`
--

INSERT INTO `inventaris` (`id`, `kode_inventaris`, `nama_inventaris`, `kategori_id`, `jumlah_tersedia`, `foto`, `deskripsi`) VALUES
(1, 'INV-00001', 'MacBook', 1, 100, '6970dab3154b5_1769003699.jpg', 'MacBook Air M4'),
(2, 'INV-00002', 'Porche', 2, 10, '6970db4ed9581_1769003854.jpg', 'Porche 911 Turbo S'),
(3, 'INV-00003', 'Buku', 3, 499, '6970dc9b921da_1769004187.jpg', 'Buku Catatan Kantor'),
(4, 'INV-00004', 'Printer', 1, 50, '6970dd2f72073_1769004335.jpg', 'Printer Canon Pixma G5070');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Elektronik'),
(2, 'Kendaraan'),
(3, 'Alat Tulis Kantor');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `nomor_peminjaman` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali_rencana` date NOT NULL,
  `tanggal_kembali_aktual` date DEFAULT NULL,
  `status` enum('diajukan','disetujui','dikembalikan','terlambat','dibatalkan') NOT NULL DEFAULT 'diajukan',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `nomor_peminjaman`, `user_id`, `tanggal_pinjam`, `tanggal_kembali_rencana`, `tanggal_kembali_aktual`, `status`, `catatan`, `created_at`) VALUES
(1, 'PJM-00001', 1, '2026-01-31', '2026-02-07', '2026-01-31', 'dikembalikan', 'pinjam', '2026-01-31 02:36:28'),
(2, 'PJM-00002', 1, '2026-01-31', '2026-02-07', '2026-01-31', 'dikembalikan', 'mobil', '2026-01-31 02:44:09'),
(3, 'PJM-00003', 1, '2026-01-31', '2026-02-07', '2026-01-31', 'dikembalikan', 'pinjam', '2026-01-31 02:58:00'),
(4, 'PJM-00004', 1, '2026-01-31', '2026-02-07', NULL, 'dibatalkan', 'pinjam', '2026-01-31 03:09:58'),
(5, 'PJM-00005', 1, '2026-01-31', '2026-02-07', '2026-02-09', 'terlambat', 'pinjam\n\n--- CATATAN PENGEMBALIAN ---\nTanggal Pengembalian: 09/02/2026\nKondisi Barang: Baik\nCatatan: baik', '2026-01-31 03:17:45'),
(6, 'PJM-00006', 1, '2026-01-31', '2026-02-07', NULL, 'disetujui', 'pinjam', '2026-01-31 03:26:08'),
(7, 'PJM-00007', 2, '2026-01-31', '2026-02-07', NULL, 'dibatalkan', '123', '2026-01-31 03:27:32'),
(8, 'PJM-00008', 1, '2026-01-31', '2026-02-07', '2026-02-09', 'terlambat', 'pinjam\n\n--- CATATAN PENGEMBALIAN ---\nTanggal Pengembalian: 09/02/2026\nKondisi Barang: Baik\nCatatan: telat', '2026-01-31 03:42:34');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman_detail`
--

CREATE TABLE `peminjaman_detail` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `inventaris_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman_detail`
--

INSERT INTO `peminjaman_detail` (`id`, `peminjaman_id`, `inventaris_id`, `jumlah`) VALUES
(1, 1, 2, 1),
(2, 2, 2, 1),
(3, 3, 1, 1),
(4, 3, 3, 1),
(5, 4, 1, 1),
(6, 5, 3, 1),
(7, 6, 3, 1),
(8, 7, 3, 1),
(9, 8, 4, 1),
(10, 8, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai') NOT NULL DEFAULT 'pegawai',
  `departemen` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `departemen`, `created_at`) VALUES
(1, 'admin', '$2a$12$f/4YUdeNA6dZX.ueEoswWe4iCCCJ1iSoysviJXpP56bG2MmJhgT2e', 'admin', NULL, '2026-01-21 10:30:04'),
(2, 'surya', '$2y$10$sDFtVWnteAzqqS6LGJceZ.daOVnWu0ppVoac2ibyr5jX.q8AfIg9e', 'pegawai', 'IT', '2026-01-21 10:47:09'),
(3, 'mahesa', '$2y$10$FIuG9m8DOKoSVDdhCYiXhu4h7o26fSUOKtksTlqWUa0iKLY7mjj0i', 'pegawai', 'IT', '2026-01-31 03:09:09'),
(4, 'ayuu', '$2y$10$8/RpxdIvZ7/fTp.S9I5a4OfL8eTf1ejVcL2RDAI0HmV.L87XeDHKi', 'pegawai', 'IT', '2026-01-31 03:41:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_inventaris` (`kode_inventaris`),
  ADD KEY `fk_inventaris_kategori` (`kategori_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peminjaman` (`nomor_peminjaman`),
  ADD KEY `fk_peminjaman_user` (`user_id`);

--
-- Indexes for table `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_peminjaman` (`peminjaman_id`),
  ADD KEY `fk_detail_inventaris` (`inventaris_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `fk_inventaris_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD CONSTRAINT `fk_detail_inventaris` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_peminjaman` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
