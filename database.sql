-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Nov 2026 pada 05.17
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ecodrop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `setoran`
--

CREATE TABLE `setoran` (
  `id` int(11) NOT NULL,
  `kode_setoran` varchar(25) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kategori` enum('Plastik','Kertas & Kardus','Logam & Kaleng','Kaca','Elektronik') NOT NULL,
  `berat_kg` decimal(6,2) NOT NULL,
  `poin` int(11) NOT NULL,
  `metode_jemput` enum('Antar ke Pos','Jemput Kurir') DEFAULT 'Antar ke Pos',
  `catatan` text DEFAULT NULL,
  `status` enum('Menunggu','Terverifikasi','Ditolak') DEFAULT 'Menunggu',
  `tanggal_setor` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setoran`
--

INSERT INTO `setoran` (`id`, `kode_setoran`, `user_id`, `kategori`, `berat_kg`, `poin`, `metode_jemput`, `catatan`, `status`, `tanggal_setor`, `created_at`) VALUES
(1, 'ECO-20261115-101', 2, 'Plastik', 5.50, 550, 'Antar ke Pos', 'Botol air mineral bersih sudah dipress', 'Terverifikasi', '2026-11-15', '2026-11-19 03:13:03'),
(2, 'ECO-20261116-102', 3, 'Logam & Kaleng', 4.00, 600, 'Jemput Kurir', 'Kaleng bekas susu dan minuman', 'Terverifikasi', '2026-11-16', '2026-11-19 03:13:03'),
(3, 'ECO-20261117-103', 4, 'Kertas & Kardus', 7.50, 450, 'Antar ke Pos', 'Kardus paket dan buku bekas', 'Terverifikasi', '2026-11-17', '2026-11-19 03:13:03'),
(4, 'ECO-20261118-104', 2, 'Elektronik', 2.00, 500, 'Jemput Kurir', 'Kabel rusak dan charger bekas', 'Menunggu', '2026-11-18', '2026-11-19 03:13:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','warga') DEFAULT 'warga',
  `alamat` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `alamat`, `created_at`) VALUES
(1, 'Petugas EcoDrop', 'admin@ecodrop.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Pos Induk Bank Sampah Kec. Depok', '2026-11-19 03:13:03'),
(2, 'Raka Pratama', 'raka@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga', 'Jl. Kaliurang Km 5, Sleman', '2026-11-19 03:13:03'),
(3, 'Nadia Putri', 'nadia@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga', 'Perum Condongcatur Blok B-12', '2026-11-19 03:13:03'),
(4, 'Dimas Saputra', 'dimas@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga', 'Jl. Seturan Raya No. 45', '2026-11-19 03:13:03'),
(5, 'Ismi Anindia', 'afghanifriedzi@gmail.com', '$2y$10$jPhufpJ27wN9lXAml2s45.7O2RG8OvDrH3QZAYazAwcnAxYvw.hWa', 'warga', 'condong catur', '2026-11-19 03:27:47');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `setoran`
--
ALTER TABLE `setoran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_setoran` (`kode_setoran`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `setoran`
--
ALTER TABLE `setoran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `setoran`
--
ALTER TABLE `setoran`
  ADD CONSTRAINT `setoran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;