-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 09:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evoting_osis`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `username`, `password`) VALUES
(1, 'RyuuTA', 'admin', '$2y$10$zJuCMyg.AT/j8WP260mXqey4MyzQwJ4AI45Yk/MlBGXC5bz2MSHci');

-- --------------------------------------------------------

--
-- Table structure for table `kandidat`
--

CREATE TABLE `kandidat` (
  `id_kandidat` int(11) NOT NULL,
  `nomor_urut` int(11) NOT NULL,
  `nama_ketua` varchar(100) NOT NULL,
  `nama_wakil` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `visi` text NOT NULL,
  `misi` text NOT NULL,
  `id_tahun` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kandidat`
--

INSERT INTO `kandidat` (`id_kandidat`, `nomor_urut`, `nama_ketua`, `nama_wakil`, `foto`, `visi`, `misi`, `id_tahun`) VALUES
(1, 1, 'Andi', 'Siti', 'default.png', 'Mewujudkan OSIS Progresif', '1. Disiplin\n2. Kreatif', NULL),
(2, 2, 'Reza', 'Ayu', 'default.png', 'OSIS Berkarakter dan Mandiri', '1. Inovatif\n2. Religius', NULL),
(5, 2, 'WIDY', 'VIVI', 'kandidat_2_1782985703.jpeg', 'Menciptakan lingkungan sekolah yang inovatif, berprestasi, dan nyaman melalui kepemimpinan yang bertanggung jawab dan terbuka.', '1.Mengembangkan program kerja yang sesuai dengan kebutuhan siswa.\r\n2.Menjadi penghubung yang baik antara aspirasi siswa dan pihak sekolah.\r\n3.Mengadakan kegiatan yang dapat meningkatkan prestasi akademik maupun nonakademik.\r\n4.Memanfaatkan teknologi untuk mendukung kegiatan dan informasi OSIS.\r\n5.Menumbuhkan budaya saling menghargai, disiplin, dan kerja sama antarwarga sekolah.', 1),
(8, 1, 'FIRGO', 'SARI', 'kandidat_1_1782985588.jpeg', 'Mewujudkan OSIS yang aktif, kreatif, disiplin, dan menjadi wadah bagi seluruh siswa untuk berkembang serta berprestasi.', '1.Menyelenggarakan kegiatan sekolah yang menarik, edukatif, dan bermanfaat.\r\n2.Meningkatkan kedisiplinan dan rasa tanggung jawab siswa.\r\n3.Mendukung pengembangan bakat dan minat melalui berbagai kegiatan ekstrakurikuler.\r\n4.Menjalin kerja sama yang baik antara OSIS, siswa, guru, dan pihak sekolah.\r\n5.Menumbuhkan semangat kebersamaan dan kepedulian terhadap lingkungan sekolah.', 1),
(9, 3, 'SAPARUDIN', 'SITI', 'kandidat_3_1782985815.jpeg', 'Menjadikan OSIS sebagai organisasi yang inspiratif, peduli, dan mampu menciptakan sekolah yang berkarakter serta berdaya saing.', '1.Mendorong partisipasi aktif seluruh siswa dalam kegiatan sekolah.\r\n2.Menyelenggarakan kegiatan sosial, keagamaan, dan lingkungan secara rutin.\r\n3.Meningkatkan rasa kekeluargaan dan solidaritas antar siswa.\r\n4.Mengembangkan program yang mendukung kreativitas, kepemimpinan, dan jiwa kewirausahaan siswa.\r\n5.Menjalankan organisasi dengan jujur, transparan, dan bertanggung jawab.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id_pengaturan` int(11) NOT NULL,
  `status_voting` enum('Belum Dimulai','Sedang Berlangsung','Ditutup') DEFAULT 'Belum Dimulai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id_pengaturan`, `status_voting`) VALUES
(1, 'Sedang Berlangsung');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status_memilih` enum('0','1') DEFAULT '0',
  `id_tahun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nis`, `nama`, `kelas`, `password`, `status_memilih`, `id_tahun`) VALUES
(2, '1002', 'Budi Santoso', 'XII TKJ 2', '$2y$10$HI82mHeNNQG.3LuVM438eu9UU8hSVQCgByIcTyIUpC1EIrHnAo5Sq', '1', 1),
(3, '1003', 'Josep saputri jaya', 'Xll RPL', '$2y$10$HI82mHeNNQG.3LuVM438eu9UU8hSVQCgByIcTyIUpC1EIrHnAo5Sq', '0', 1),
(4, '1004', 'supri', 'Xll RPL', '$2y$10$HI82mHeNNQG.3LuVM438eu9UU8hSVQCgByIcTyIUpC1EIrHnAo5Sq', '0', 1),
(5, '1001', 'RyuuTA', 'x tkj', '$2y$10$HI82mHeNNQG.3LuVM438eu9UU8hSVQCgByIcTyIUpC1EIrHnAo5Sq', '0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id_tahun` int(11) NOT NULL,
  `nama_tahun` varchar(20) NOT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Tidak Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id_tahun`, `nama_tahun`, `status`) VALUES
(1, '2024/2025', 'Aktif'),
(2, '2023/2024', 'Tidak Aktif'),
(3, '2022/2023', 'Tidak Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `voting`
--

CREATE TABLE `voting` (
  `id_voting` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_kandidat` int(11) NOT NULL,
  `waktu_voting` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_tahun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voting`
--

INSERT INTO `voting` (`id_voting`, `id_siswa`, `id_kandidat`, `waktu_voting`, `id_tahun`) VALUES
(4, 2, 9, '2026-06-28 05:46:02', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `kandidat`
--
ALTER TABLE `kandidat`
  ADD PRIMARY KEY (`id_kandidat`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id_pengaturan`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun`);

--
-- Indexes for table `voting`
--
ALTER TABLE `voting`
  ADD PRIMARY KEY (`id_voting`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_kandidat` (`id_kandidat`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kandidat`
--
ALTER TABLE `kandidat`
  MODIFY `id_kandidat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id_tahun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voting`
--
ALTER TABLE `voting`
  MODIFY `id_voting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `voting`
--
ALTER TABLE `voting`
  ADD CONSTRAINT `voting_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `voting_ibfk_2` FOREIGN KEY (`id_kandidat`) REFERENCES `kandidat` (`id_kandidat`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
