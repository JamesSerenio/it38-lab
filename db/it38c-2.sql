-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2025 at 04:34 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `it38c-2`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username_attempted` varchar(50) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `status` enum('success','failed','hacker_attempt') NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username_attempted`, `ip_address`, `status`, `attempt_time`) VALUES
(1, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:30:55'),
(2, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:52:53'),
(3, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:53:33'),
(4, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:53:48'),
(5, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:58:04'),
(6, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 14:58:22'),
(7, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:16:30'),
(8, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:16:52'),
(9, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:17:50'),
(10, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:17:53'),
(11, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:17:59'),
(12, 'james', '::1', 'failed', '2025-02-26 15:22:35'),
(13, 'james', '::1', 'failed', '2025-02-26 15:22:54'),
(14, 'james', '::1', 'failed', '2025-02-26 15:23:03'),
(15, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:28:47'),
(16, 'admin\' --', '::1', 'hacker_attempt', '2025-02-26 15:30:24');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `login_time`) VALUES
(0, 0, '2025-02-06 14:57:11'),
(0, 0, '2025-02-06 14:59:17'),
(0, 0, '2025-02-26 21:40:19'),
(0, 0, '2025-02-26 21:40:25'),
(0, 0, '2025-02-26 21:41:53'),
(0, 0, '2025-02-26 21:45:10'),
(0, 0, '2025-02-26 21:49:04'),
(0, 0, '2025-02-26 21:49:09'),
(0, 0, '2025-02-26 21:52:36'),
(0, 0, '2025-02-26 21:53:15'),
(0, 0, '2025-02-26 22:01:50'),
(0, 0, '2025-02-26 22:01:57'),
(0, 0, '2025-02-26 22:02:25'),
(0, 0, '2025-02-26 22:05:52'),
(0, 0, '2025-02-26 22:09:59'),
(0, 0, '2025-02-26 22:10:23'),
(0, 0, '2025-02-26 22:16:06'),
(0, 0, '2025-02-26 22:27:22'),
(0, 0, '2025-02-26 22:41:13'),
(0, 0, '2025-02-26 22:53:17'),
(0, 0, '2025-02-26 22:56:15'),
(0, 0, '2025-02-26 23:23:42'),
(0, 0, '2025-02-26 23:23:47'),
(0, 0, '2025-02-26 23:23:54'),
(0, 0, '2025-02-26 23:24:12'),
(0, 0, '2025-02-26 23:24:50'),
(0, 0, '2025-02-26 23:28:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `user_type`, `last_login`, `created_at`) VALUES
(0, 'james', '$2y$10$wzNrwPIUS1OGKZzS6Zl33uJloINQ9v5ljxs46io353cKGwmnF1Gsi', 'user', '2025-02-26 23:28:34', '2025-02-06 06:57:06'),
(0, 'admin', '$2y$10$S4tM3aysnuNZgvq1uK7/U.oglp2Yo3Rk5UoxblY1ZiIM9x1l3.kd2', 'admin', '2025-02-26 23:28:34', '2025-02-06 06:59:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
