-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 18 Nov 2024 pada 07.28
-- Versi server: 10.11.9-MariaDB
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u529472640_framee`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_description`, `updated_at`) VALUES
(1, 'midtrans_server_key', 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl', 'Midtrans Server Key', '2024-10-28 02:56:25'),
(2, 'midtrans_client_key', 'SB-Mid-client-uJgC77ydf09Kgatf', 'Midtrans Client Key', '2024-10-28 02:56:25'),
(3, 'midtrans_is_production', '0', 'Midtrans Production Mode (0=false, 1=true)', '2024-10-28 02:56:25'),
(4, 'admin_email', 'admin@gmail.com', 'Admin Login Email', '2024-10-28 03:05:41'),
(5, 'admin_password', '1111', 'Admin Login Password', '2024-10-29 07:02:02'),
(6, 'keypad_pin', '0000', 'Keypad Access PIN', '2024-10-28 03:21:47'),
(7, 'success_page_pin', '8888', 'Pin Untuk Halaman Transaksi Berhasil', '2024-11-18 07:27:58');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
