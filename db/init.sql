CREATE DATABASE IF NOT EXISTS taskhell CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE taskhell;

CREATE TABLE IF NOT EXISTS `nouns` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `noun` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(256) COLLATE utf8mb4_general_ci NOT NULL UNIQUE,
  `password` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `task` varchar(512) COLLATE utf8mb4_general_ci NOT NULL,
  `due` datetime DEFAULT NULL,
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usertasks_FK` (`user_id`),
  CONSTRAINT `fk_user_task` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `verbs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `verb` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
