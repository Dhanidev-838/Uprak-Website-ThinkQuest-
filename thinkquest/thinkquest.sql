-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Des 2025 pada 16.29
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
-- Database: `thinkquest`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `user_id`, `body`, `image`, `created_at`) VALUES
(1, 1, 2, 'karena cn hijau', NULL, '2025-12-10 01:55:02'),
(2, 1, 1, 'ora tau mana tau', NULL, '2025-12-10 14:07:43'),
(4, 2, 1, 'adwad', NULL, '2025-12-10 14:57:23'),
(5, 2, 1, 'wadawd', NULL, '2025-12-10 14:58:10'),
(6, 2, 1, 'erno', 'uploads/69398c1c75331.png', '2025-12-10 15:05:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `questions`
--

INSERT INTO `questions` (`id`, `user_id`, `title`, `body`, `image`, `created_at`) VALUES
(1, 1, 'Kenapa cn berwarna hijau', 'bla bla bla bla bla', 'uploads/6938d183a4e11.png', '2025-12-10 01:48:51'),
(2, 2, 'prefdgjh', 'dryh', 'uploads/6938d2a43ec7e.png', '2025-12-10 01:53:40'),
(3, 4, 'presiden korsel?', 'gatauuu', NULL, '2025-12-10 02:04:05'),
(4, 4, 'gatauuu', 'pak kodir udh ada istri', NULL, '2025-12-10 02:06:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `avatar`, `bio`, `created_at`) VALUES
(1, 'dhani', 'dhani@gmail.com', '$2y$10$Z2FKedwtYfBZBHBimCuE5OrbbFAr8GajBpmxJit3H0KzZ6IYCP9li', 'uploads/6939816499f81.png', 'cinta cn', '2025-12-10 01:36:11'),
(2, 'rima', 'rima@gmal.com1', '$2y$10$xSDt4ppduFomtElqW176NOfHB0gWhFwkBIIQhfbZv6b3cCN/SwVBC', NULL, 'rima cinta cn', '2025-12-10 01:52:10'),
(3, 'adip', 'adip12@gmail.com', '$2y$10$FSe7rhYByGl.w4I5OCze2eaJELUkZEcSrzuxrG4afi10dAdy73MMC', NULL, 'adip gfhjdd', '2025-12-10 02:00:43'),
(4, 'syifa', 'syifa2@gmail.com', '$2y$10$1EtQPGezSOQzc3weYCCMBO0ABTJZGC.dJ8bFQgwwWKj2EQ4w415SC', NULL, 'sayang cn', '2025-12-10 02:02:00'),
(5, 'Anonim', 'Anonim@gmail.com', '$2y$10$OsvjJTUSKUd7wbmjhsgaw.vq6JQMQPg75OYMipIicX9jqWb4TNC.G', 'uploads/693987db42d2e.png', 'Halo', '2025-12-10 14:46:51');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
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
-- AUTO_INCREMENT untuk tabel `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
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
-- Ketidakleluasaan untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
