-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Mar 20, 2025 at 06:27 PM
-- Server version: 8.0.41
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taskhell`
--

-- --------------------------------------------------------

--
-- Table structure for table `nouns`
--

CREATE TABLE `nouns` (
  `id` bigint UNSIGNED NOT NULL,
  `noun` varchar(150) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nouns`
--

INSERT INTO `nouns` (`id`, `noun`) VALUES
(21, 'chair'),
(22, 'phone'),
(23, 'cup'),
(24, 'desk'),
(25, 'glass'),
(26, 'key'),
(27, 'pen'),
(28, 'shirt'),
(29, 'plate'),
(30, 'wallet');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `task` varchar(512) COLLATE utf8mb4_general_ci NOT NULL,
  `due` datetime DEFAULT NULL,
  `finished` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `user_id`, `task`, `due`, `finished`) VALUES
(9, 6, 'do homework', NULL, 0),
(10, 19, 'Organize a plate', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(256) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(6, 'Bob', 'Bob'),
(7, 'Tonny', 'password'),
(8, 'Ton_ny', 'password'),
(9, 'Bonnie', 'P4$$word'),
(10, 'Bonnie2', 'P4$$word'),
(12, 'Bonnie3', 'P4$$word'),
(13, 'Bonnie5', 'P4$$word'),
(14, 'Bonnie7', 'P4$$word'),
(15, 'Bonnie8', 'P4$$word'),
(16, 'Bonnie9', 'P4$$word'),
(17, 'Bonnie10', 'P4$$word'),
(18, 'Bonnie11', 'P4$$word'),
(19, 'Bonnie12', 'P4*$word'),
(20, 'Gimbo12', '$argon2id$v=19$m=65536,t=4,p=1$aWRtWmhPYkRWc2twMjFWYg$nw8Upps7yfbsH5kRNzMJE1Ej8BuftzsrBvl077QQ7qU');

-- --------------------------------------------------------

--
-- Table structure for table `verbs`
--

CREATE TABLE `verbs` (
  `id` bigint NOT NULL,
  `verb` varchar(150) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verbs`
--

INSERT INTO `verbs` (`id`, `verb`) VALUES
(1, 'clean'),
(2, 'hide'),
(3, 'check'),
(4, 'find'),
(5, 'move'),
(6, 'go out with'),
(7, 'rotate'),
(8, 'don\'t think about'),
(9, 'wash'),
(10, 'organize');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nouns`
--
ALTER TABLE `nouns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usertasks_FK` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`username`);

--
-- Indexes for table `verbs`
--
ALTER TABLE `verbs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nouns`
--
ALTER TABLE `nouns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `verbs`
--
ALTER TABLE `verbs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_user_task` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
