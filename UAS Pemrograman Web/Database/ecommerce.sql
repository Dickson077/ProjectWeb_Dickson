-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 03:35 PM
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
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_login` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_login`, `nama`, `email`, `password`, `no_telp`, `alamat`, `role`) VALUES
(1, 'Jeffry', 'jeffryheriyanto493@gmail.com', '$2y$10$pTtfxz/5tQEDp.6tbNriGeZ3akgxAkhLm2xBK9nVw0VtsnK6.EEdG', '082285508675', 'Batam', 'admin'),
(2, 'pelanggan1', 'pelanggan1@gmail.com', '$2y$10$UGa2Ozu3uSVxUScd.H9vT.3s5lqBaFmNEoVF1AvyDRdwzTV.VxtRq', '081122226666', 'Bengkong', 'pelanggan'),
(3, 'pelanggan2', 'pelanggan2@gmail.com', '$2y$10$NvGOqXrZE64kG7v0abdQnOmWi1tAGLzVhMIii77bYwCVMvbMyRG6a', '08112222555', 'Batu Ampar', 'pelanggan'),
(4, 'pelanggan3', 'pelanggan3@gmail.com', '$2y$10$8D32Vokg9nA7hk6KSAgBz.w04WUVo./3BSBg68HNUWqmjp0zXrXHa', 'aaa', 'Bengkong\r\n', 'pelanggan'),
(17, 'pelanggan4', 'pelanggan4@gmail.com', '$2y$10$JwV8K2pCYd5w4F85tZft.uV04KmU1RJx34uahT2HfMMN0zoyL.qhe', 'aaa', 'aaa', 'pelanggan'),
(18, 'windy', 'windy@gmail.com', '$2y$10$196ec.cXKBAVasR6CLviTuXZIlGJRFCQc.j8A5czTOz/iX7YvAvAG', '081343421783', 'Taman Baloi Mas', 'admin'),
(19, 'windi', 'windi@gmail.com', '$2y$10$ChpfQjlKWgH/ik6SOe5IU.I1bg.AOZ9uF1qFjLtFJibb8ixSZavpm', '081592520382', 'Batam Center', 'pelanggan'),
(20, 'win', 'win@gmail.com', '$2y$10$uFfb8qyqAvWic7O5vqbCfOu3i0uMPge4qJhiLAOZSwmWKofKXc3cq', '083741938291', 'Duta Mas', 'pelanggan'),
(22, 'windii', 'windii@gmail.com', '$2y$10$rHkaFsCuxj57B8M8L/fwa.O62hZN/V6j9qA038kA1yKa0Czlx1Die', '083138921023', 'Sakuran Anpan', 'pelanggan');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `id_login` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id`, `total_harga`, `tanggal`, `metode_pembayaran`, `status`, `bukti_pembayaran`, `id_login`) VALUES
(8, 750000, '2025-06-27 15:39:57', 'COD', 'rejected', NULL, 1),
(9, 120000, '2025-06-27 15:41:23', 'COD', 'rejected', NULL, 1),
(10, 750000, '2025-06-27 15:41:23', 'COD', 'rejected', NULL, 1),
(11, 200000, '2025-06-27 15:41:23', 'COD', 'rejected', NULL, 1),
(12, 1000000, '2025-06-28 19:57:42', 'COD', 'rejected', NULL, 1),
(19, NULL, '2025-06-28 20:16:57', 'Transfer', 'rejected', NULL, 1),
(20, NULL, '2025-06-28 20:23:45', 'Transfer', 'rejected', NULL, 1),
(21, NULL, '2025-06-28 23:28:00', 'COD', 'rejected', NULL, 3),
(22, NULL, '2025-06-28 23:51:35', 'COD', 'rejected', NULL, 2),
(23, NULL, '2025-06-28 23:53:00', 'COD', 'rejected', NULL, 3),
(24, NULL, '2025-06-29 13:58:32', 'COD', 'rejected', NULL, 1),
(25, NULL, '2025-06-29 14:01:47', 'COD', 'rejected', NULL, 1),
(27, 750000, '2025-07-12 19:56:01', 'COD', 'rejected', NULL, 19),
(28, 750000, '2025-07-12 15:03:58', 'Kartu Kredit', 'rejected', NULL, 19),
(29, 1500000, '2025-07-12 15:04:58', 'Transfer', 'rejected', NULL, 19),
(30, 2250000, '2025-07-12 15:06:20', 'COD', 'rejected', NULL, 19),
(31, 4500000, '2025-07-12 15:13:35', 'COD', 'rejected', NULL, 19),
(32, 3000000, '2025-07-12 20:25:17', 'Cash On Delivery', 'rejected', NULL, 19),
(33, 750000, '2025-07-12 20:42:36', 'Cash On Delivery', 'rejected', NULL, 19),
(34, 5250000, '2025-07-12 23:11:04', 'COD', 'rejected', NULL, 19),
(35, 2250000, '2025-07-12 23:29:09', 'Transfer', 'rejected', NULL, 19),
(36, 3750000, '2025-07-13 09:06:54', 'COD', 'rejected', NULL, 19),
(37, 750000, '2025-07-13 09:09:26', 'COD', 'rejected', NULL, 19),
(38, 750000, '2025-07-13 09:11:40', 'Transfer', 'rejected', NULL, 19),
(39, 750000, '2025-07-13 09:15:33', 'Transfer', 'rejected', NULL, 19),
(41, NULL, '2025-07-13 11:40:18', 'COD', 'rejected', NULL, 19),
(42, 550000, '2025-07-13 13:43:53', 'COD', 'rejected', NULL, 19),
(43, 2250000, '2025-07-13 14:02:55', 'Transfer', 'rejected', NULL, 19),
(44, 2250000, '2025-07-13 14:03:52', 'COD', 'rejected', NULL, 19),
(45, 3000000, '2025-07-13 14:05:44', 'COD', 'rejected', NULL, 18),
(46, 3000000, '2025-07-13 14:06:37', 'COD', 'rejected', NULL, 18),
(47, 3000000, '2025-07-13 14:15:07', 'Transfer', 'rejected', NULL, 18),
(48, 3000000, '2025-07-13 14:15:53', 'COD', 'rejected', NULL, 18),
(49, 3000000, '2025-07-13 14:16:46', 'Transfer', 'rejected', NULL, 18),
(50, 3000000, '2025-07-13 14:20:56', 'COD', 'rejected', NULL, 18),
(51, 3000000, '2025-07-13 14:21:38', 'Transfer', 'rejected', NULL, 18),
(52, 3000000, '2025-07-13 14:25:40', 'COD', 'rejected', NULL, 18),
(53, 1500000, '2025-07-13 14:26:32', 'COD', 'rejected', NULL, 18),
(54, 2250000, '2025-07-13 14:26:48', 'COD', 'rejected', NULL, 18),
(55, 1500000, '2025-07-13 14:29:49', 'COD', 'rejected', NULL, 18),
(56, 750000, '2025-07-13 14:31:31', 'COD', 'rejected', NULL, 18),
(57, 750000, '2025-07-13 14:31:51', 'COD', 'rejected', NULL, 18),
(58, 2250000, '2025-07-13 14:33:45', 'COD', 'rejected', NULL, 18),
(59, 1500000, '2025-07-13 15:13:53', 'Transfer', 'rejected', NULL, 19),
(60, 12000000, '2025-07-13 15:41:24', 'Transfer', 'rejected', NULL, 19),
(61, 7000000, '2025-07-13 15:51:35', 'COD', 'rejected', NULL, 19),
(62, 3500000, '2025-07-13 15:55:04', 'COD', 'rejected', NULL, 19),
(63, 1100000, '2025-07-13 16:04:51', 'COD', 'rejected', NULL, 19),
(64, 6000000, '2025-07-13 16:07:19', 'COD', 'rejected', NULL, 19),
(65, 1100000, '2025-07-13 19:29:06', 'COD', 'verified', NULL, 19),
(66, 2500000, '2025-07-13 20:24:18', 'COD', 'verified', NULL, 19),
(67, 2500000, '2025-07-13 22:28:18', 'COD', 'verified', NULL, 19),
(68, 170000, '2025-07-15 22:24:23', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_687672a71fadd5.43897736.jpg', 19),
(69, 850000, '2025-07-15 22:48:24', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_6876784876b8f7.05207911.jpg', 19),
(70, 170000, '2025-07-15 22:54:04', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_6876799c6eb575.08810345.jpg', 19),
(71, 2500000, '2025-07-15 22:57:04', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_68767a50c7f840.23506807.jpg', 19),
(72, 2500000, '2025-07-15 22:58:16', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_68767a98936e99.27768181.jpg', 19),
(73, 5000000, '2025-07-16 22:51:26', 'Transfer', 'rejected', 'uploads/bukti_pembayaran/proof_6877ca7e593010.57508789.jpg', 19),
(74, 170000, '2025-07-16 23:00:08', 'COD', 'verified', NULL, 19),
(75, 7000000, '2025-07-16 23:14:07', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_6877cfcf836d40.19183954.jpg', 19),
(76, 170000, '2025-07-17 20:29:23', 'Transfer', 'verified', 'uploads/bukti_pembayaran/proof_6878fab3b2abc9.16513151.jpg', 19);

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_detail`
--

CREATE TABLE `penjualan_detail` (
  `id` int(11) NOT NULL,
  `id_penjualan` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `kuantitas` int(11) DEFAULT NULL,
  `ukuran` varchar(10) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan_detail`
--

INSERT INTO `penjualan_detail` (`id`, `id_penjualan`, `id_produk`, `kuantitas`, `ukuran`, `subtotal`) VALUES
(4, 19, 2, 1, '40', 100000),
(5, 20, 2, 1, '40', 100000),
(6, 21, 8, 1, '40', 500000),
(7, 22, 3, 1, '40', 180000),
(8, 23, 6, 1, '40', 400000),
(9, 24, 8, 1, '40', 500000),
(10, 25, 8, 1, '40', 500000),
(11, 27, 1, 1, 'N/A', 750000),
(12, 28, 1, 1, NULL, 750000),
(13, 29, 1, 2, 'N/A', 1500000),
(14, 30, 1, 3, 'N/A', 2250000),
(15, 31, 1, 6, 'N/A', 4500000),
(16, 32, 1, 4, 'N/A', 3000000),
(17, 33, 1, 1, 'N/A', 750000),
(18, 34, 1, 7, '38', 5250000),
(19, 35, 1, 3, '38', 2250000),
(20, 36, 1, 5, '39', 3750000),
(21, 37, 1, 1, '40', 750000),
(22, 38, 1, 1, '40', 750000),
(23, 39, 1, 1, '40', 750000),
(24, 41, 2, 1, '38', 170000),
(25, 41, 1, 2, '38', 1500000),
(26, 42, 11, 1, '38', 550000),
(27, 43, 1, 3, '40', 2250000),
(28, 44, 1, 3, '40', 2250000),
(29, 45, 1, 4, '40', 3000000),
(30, 46, 1, 4, '40', 3000000),
(31, 47, 1, 4, '40', 3000000),
(32, 48, 1, 4, '40', 3000000),
(33, 49, 1, 4, '40', 3000000),
(34, 50, 1, 4, '40', 3000000),
(35, 51, 1, 4, '40', 3000000),
(36, 52, 1, 4, '40', 3000000),
(37, 53, 1, 2, '38', 1500000),
(38, 54, 1, 3, '38', 2250000),
(39, 55, 1, 2, '39', 1500000),
(40, 56, 1, 1, '39', 750000),
(41, 57, 1, 1, '39', 750000),
(42, 58, 1, 3, '39', 2250000),
(43, 59, 1, 2, '39', 1500000),
(44, 60, 12, 2, '38', 12000000),
(45, 61, 9, 2, '38', 7000000),
(46, 62, 9, 1, '39', 3500000),
(47, 63, 11, 2, '38', 1100000),
(48, 64, 12, 1, '38', 6000000),
(49, 65, 11, 2, '39', 1100000),
(50, 66, 1, 1, '39', 2500000),
(51, 67, 1, 1, '39', 2500000),
(52, 68, 2, 1, '38', 170000),
(53, 69, 2, 5, '39', 850000),
(54, 70, 2, 1, '38', 170000),
(55, 71, 1, 1, '38', 2500000),
(56, 72, 1, 1, '38', 2500000),
(57, 73, 1, 2, '39', 5000000),
(58, 74, 2, 1, '39', 170000),
(59, 75, 9, 2, '40', 7000000),
(60, 76, 2, 1, '39', 170000);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `harga_beli` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `stok` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `harga_beli`, `harga`, `deskripsi`, `gambar`, `is_deleted`, `stok`) VALUES
(1, 'Adizero EVO SL Shoes', 1200000, 2500000, 'Rasakan sensasi cepat dengan Adizero Evo SL. Terinspirasi inovasi sepatu yang memecahkan rekor dalam keluarga lari Adizero - dan khususnya Pro Evo 1 - Evo SL didesain untuk kamu yang berlari dengan memakainya, atau tidak sama sekali. Mengombinasikan teknologi Adizero dengan estetika unik yang terinspirasi kompetisi lari, ini adalah evolusi kecepatan dalam semua aspek kehidupan. Lapisan busa LIGHTSTRIKE PRO yang responsif di bagian midsole memberikan kenyamanan dan bantalan untuk pengembalian energi yang optimal.\r\n\r\nMengenai Ukuran: Selisih 1-2 cm mungkin terjadi dikarenakan proses pengembangan dan produksi.\r\n\r\nMengenai Warna: Warna sesungguhnya mungkin dapat berbeda. Hal ini dikarenakan setiap layar memiliki kemampuan yang berbeda-beda untuk menampilkan warna, kami tidak dapat menjamin bahwa warna yang Anda lihat adalah warna akurat dari produk tersebut.', 'sepatu1.jpg', 0, 11),
(2, 'Sepatu New Balance', 100000, 170000, 'Seri 1906R, seperti sepupunya 2002R dan 860v2, dikenali dari unit sol dengan fitur kombinasi bantalan ACTEVA LITE yang fleksibel, N-ergy yang meredam hentakan, dan ABZORB SBS tersegmen di bagian tumit. Pendekatan berteknologi tinggi ini juga terlihat dari desain upper 1906R, yang dilengkapi fitur mesh dengan lubang terbuka dan sejumlah lapisan luar sintetis melengkung. Sentuhan unik dari desain era khas ini menawarkan hasil terbaik dari warisan berkualitas tinggi.', 'sepatu2.jpg', 0, 0),
(3, 'Sepatu New Balance', 100000, 180000, 'Sepatu signature ketiga LaMelo Ball, MB.03, membawa kita ke dunia alternatif yang belum pernah terlihat sebelumnya — dunia Melo. Sepatu ini menonjol dengan desain lapisan karet bertema slime dan bagian atas rajut rekayasa dengan potongan berbentuk cakaran, menjadikan MB.03 benar-benar terasa \"Not From Here\" (bukan dari dunia ini).\r\n\r\nSol bertema slime dan teknologi futuristik PUMA Hoops memberikan tampilan yang siap untuk turnamen, namun tampak seperti berasal dari luar angkasa.', 'sepatu3.jpg', 0, 0),
(4, 'Sepatu Nike', 150000, 250000, 'Nike P-6000 adalah perpaduan dari berbagai model sepatu Pegasus terdahulu, yang membawa gaya lari awal 2000-an ke level modern. Dengan material mesh yang ringan dan garis desain sporty, sepatu ini menawarkan kombinasi sempurna antara tampilan mencolok dan kenyamanan yang breathable.', 'sepatu4.webp', 0, 0),
(5, 'Sepatu Converse', 100000, 250000, 'Run Star Hike favorite untuk pakaian jalanan mendapatkan tampilan low-profile. Kanvas bertali menjadikannya tetap klasik dari bagian depan, dengan bintang berwarna kontras pada bagian heel yang menjadikan mereka tahu apa yang baru. Blok warna menarik perhatian ke midsole platform dan outsole saw-tooth mencoloknya.', '01-CONVERSE-FFSSBCONA-CON168816C-White.webp', 0, 0),
(6, 'Sepatu Hoka', 150000, 400000, 'For Road Running. Telah hadir generasi ke 9 dari tipe Clifton, lebih ringan dan lebih empuk dari seri sebelum nya. Bertambah tinggi 3mm dengan bobot yang justru berkurang. Rasakan pengalaman terbaru untuk kaki mu dengan Clifton 9 yang menggunakan foam yang lebih responsive dan desain outsole terkini. Heel yang lebih empuk, reflektif heel panel dan lidah sepatu dengan medial gusset satu sisi.\r\n\r\nMengenai Ukuran: Selisih 1-2 cm mungkin terjadi dikarenakan proses pengembangan dan produksi.\r\n\r\nMengenai Warna: Warna sesungguhnya mungkin dapat berbeda. Hal ini dikarenakan setiap layar memiliki kemampuan yang berbeda-beda untuk menampilkan warna, kami tidak dapat menjamin bahwa warna yang Anda lihat adalah warna akurat dari produk tersebut.', 'sepatuhoka.webp', 0, 0),
(7, 'Sepatu Vans', 130000, 250000, 'Memperkenalkan Hylane, sebuah perpaduan dari masa lalu dengan desain terkini yang terinspirasi dari era Y2K dan dibuat berdasarkan dari siluet Upland dari tahun 1999.\n\nVans Hylane mengubah estetika nostalgia tahun 90an menjadi bagian yang tak tepisahkan dari masa kini.', 'sepatuvans.webp', 0, 0),
(8, 'Sepatu Under Armour', 250000, 500000, 'Bagian atas sepatu terbuat dari rajutan lembut yang breathable, memberikan kenyamanan dan kekuatan ringan dengan efek seperti kompresi, sehingga pas di kaki dan tetap fleksibel saat bergerak. Panel tengah kaki dengan bentuk 3D dan lubang laser memberikan sirkulasi udara tambahan untuk menjaga kaki tetap sejuk.\r\n\r\nKerah pergelangan kaki berbahan rajut dirancang pas seperti kaus kaki, memberikan sensasi nyaman dan menyatu dengan kaki. Penyangga tumit eksternal menambah stabilitas maksimal, sementara sol karet padat dengan struktur tonjolan unik meningkatkan daya cengkeram dan ketahanan sepatu secara keseluruhan.', 'sepatuunderarmor.webp', 0, 0),
(9, 'Nike Air Jordan Retro High', 3000000, 3500000, 'Sepatu yang sangat ditunggu-tunggu dan kehadirannya pasti membuat para sneakerhead bersemangat. Dengan desain yang terinspirasi dari model klasik Chicago yang pertama kali dirilis pada 1985, sepatu ini tidak hanya menyajikan tampilan ikonik, tetapi juga membawa cerita nostalgia yang kuat. Dibuat untuk mengenang temuan sepatu-sepatu lama yang ditemukan kembali di gudang toko, desain sepatu ini menunjukkan detailing vintage yang menonjol, seperti kulit yang tampak terkikis, sol yang sedikit pudar, dan kotak dengan scuff marks yang memberi kesan sepatu ini seakan sudah bertahun-tahun disimpan.', 'nike1.jpg', 0, 10),
(11, 'Nike Air Max', 500000, 550000, 'kdfsk', 'nike air_1752385425.jpg', 0, 0),
(12, 'Nike Air Jordan ', 5000000, 6000000, 'in iadalah contoh dari sesuatu yang tidak bisa didapatkan oleh seseorang', 'nike jordan.jpg', 1, 0),
(14, 'Nike Dunk Low', 1000000, 1500000, 'Dibuat untuk lapangan basket namun dibawa ke jalanan, ikon basket tahun 80-an ini kembali dengan lapisan luar yang mengilap sempurna dan warna-warna khas tim klasik. Dengan desain khas olahraga hoop-nya, Nike Dunk Low menghadirkan kembali nuansa vintage tahun 80-an ke jalanan, sementara kerah rendah yang empuk memberikan kenyamanan untuk membawa gaya Anda ke mana saja.\r\n', 'nike dunk.jpg', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `produk_ukuran`
--

CREATE TABLE `produk_ukuran` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `ukuran` enum('38','39','40') NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_ukuran`
--

INSERT INTO `produk_ukuran` (`id`, `produk_id`, `ukuran`, `stok`) VALUES
(1, 1, '38', 1),
(2, 1, '39', 3),
(3, 1, '40', 8),
(7, 2, '38', 0),
(8, 2, '39', 0),
(9, 2, '40', 3),
(19, 3, '38', 0),
(20, 3, '39', 0),
(21, 3, '40', 0),
(25, 4, '38', 0),
(26, 4, '39', 0),
(27, 4, '40', 0),
(43, 5, '38', 0),
(44, 5, '39', 0),
(45, 5, '40', 0),
(70, 6, '38', 0),
(71, 6, '39', 0),
(72, 6, '40', 0),
(73, 7, '38', 0),
(74, 7, '39', 0),
(75, 7, '40', 0),
(76, 8, '38', 0),
(77, 8, '39', 0),
(78, 8, '40', 0),
(229, 9, '38', 1),
(230, 9, '39', 1),
(231, 9, '40', 3),
(259, 11, '38', 0),
(260, 11, '39', 1),
(261, 11, '40', 8),
(265, 12, '38', 4),
(266, 12, '39', 3),
(267, 12, '40', 10),
(277, 13, '38', 4),
(278, 13, '39', 3),
(279, 13, '40', 2),
(292, 14, '38', 5),
(293, 14, '39', 8),
(294, 14, '40', 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_penjualan` (`id_penjualan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk_ukuran`
--
ALTER TABLE `produk_ukuran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produk_id` (`produk_id`,`ukuran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `produk_ukuran`
--
ALTER TABLE `produk_ukuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD CONSTRAINT `penjualan_detail_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`),
  ADD CONSTRAINT `penjualan_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Constraints for table `produk_ukuran`
--
ALTER TABLE `produk_ukuran`
  ADD CONSTRAINT `produk_ukuran_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
