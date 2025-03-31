-- Create the database
CREATE DATABASE IF NOT EXISTS `taskhell` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_general_ci;

USE `taskhell`;

-- Create nouns table
CREATE TABLE IF NOT EXISTS `nouns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `noun` VARCHAR(150) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;

INSERT INTO `nouns` (`id`, `noun`) VALUES
  (1, 'chair'),
  (2, 'phone'),
  (3, 'cup'),
  (4, 'desk'),
  (5, 'glass'),
  (6, 'key'),
  (7, 'pen'),
  (8, 'shirt'),
  (9, 'plate'),
  (10, 'wallet');

-- Create verbs table
CREATE TABLE IF NOT EXISTS `verbs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `verb` VARCHAR(150) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;

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

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(256) COLLATE utf8mb4_general_ci NOT NULL UNIQUE,
  `password` VARCHAR(256) COLLATE utf8mb4_general_ci NOT NULL,
  `image` VARCHAR(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `refresh_token` VARCHAR(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;

-- Create lists table
CREATE TABLE IF NOT EXISTS `lists` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(256) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_list` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;

-- Create tasks table
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `list_id` BIGINT UNSIGNED NOT NULL,
  `task` VARCHAR(512) COLLATE utf8mb4_general_ci NOT NULL,
  `due` DATETIME DEFAULT NULL,
  `finished` TINYINT(1) NOT NULL DEFAULT 0,
  `random` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_list_task` 
    FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;
