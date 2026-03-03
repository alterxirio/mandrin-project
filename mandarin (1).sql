-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 01, 2026 at 08:04 AM
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
-- Database: `mandarin`
--

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--
-- Mandrin Project database bootstrap
-- Generated from application code usage in backend/ and frontend/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `mandarin`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `mandarin`;

DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `homework`;
DROP TABLE IF EXISTS `dialogues`;
DROP TABLE IF EXISTS `words`;
DROP TABLE IF EXISTS `topics`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(120) NOT NULL,
  `angkagiliran` VARCHAR(60) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Pensyarah','Pelajar') NOT NULL DEFAULT 'Pelajar',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_angkagiliran` (`angkagiliran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `topik` INT UNSIGNED NOT NULL,
  `topic_name` VARCHAR(200) NOT NULL,
  `chinese_character` VARCHAR(200) NOT NULL,
  `pinyin` VARCHAR(200) NOT NULL,
  `banner_path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_topics_topik` (`topik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `words` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_id` INT UNSIGNED NOT NULL,
  `chinese` VARCHAR(200) NOT NULL,
  `pinyin` VARCHAR(200) NOT NULL,
  `meaning` VARCHAR(255) NOT NULL,
  `audio_path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_words_topic_id` (`topic_id`),
  CONSTRAINT `fk_words_topic`
    FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dialogues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_id` INT UNSIGNED NOT NULL,
  `chinese_text` TEXT NOT NULL,
  `pinyin_text` TEXT NOT NULL,
  `meaning` TEXT NOT NULL,
  `character_name` VARCHAR(120) NOT NULL,
  `audio_path` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_dialogues_topic_id` (`topic_id`),
  CONSTRAINT `fk_dialogues_topic`
    FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `homework` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `class` VARCHAR(20) NOT NULL,
  `due_date` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `homework_id` int(11) NOT NULL,
  `type` enum('mcq','listening','picture','truefalse','rearrange') NOT NULL,
  `question_text` text NOT NULL,
  `user_answer` varchar(232) NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(50) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `image_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `homework_id`, `type`, `question_text`, `user_answer`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `audio_file`, `image_file`) VALUES
(10, 4, 'rearrange', 'susun perkataan menjadi ayat', '', 'saya', 'suka', 'ayam', NULL, 'saya suka ayam', NULL, NULL),
(11, 4, 'listening', 'ayam,ikan', '', 'ayam', 'ikan', NULL, NULL, 'ayam', '../media/homework/audio/hw_69a3e3f990cfd8.13724558-default-audio.mp3', NULL),
(12, 5, 'rearrange', 'susun perkataan menjadi ayat', '', 'saya', 'suka', 'ayam', 'goreng', 'saya suka ayam goreng lemak', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homework_id` (`homework_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `homework_id` INT UNSIGNED NOT NULL,
  `type` ENUM('mcq','listening','picture','truefalse','rearrange') NOT NULL,
  `question_text` TEXT NOT NULL,
  `user_answer` TEXT NULL,
  `option_a` VARCHAR(255) NULL,
  `option_b` VARCHAR(255) NULL,
  `option_c` VARCHAR(255) NULL,
  `option_d` VARCHAR(255) NULL,
  `audioImage_label` TEXT NULL,
  `correct_answer` TEXT NULL,
  `audio_file` VARCHAR(255) NULL,
  `image_file` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_questions_homework_id` (`homework_id`),
  CONSTRAINT `fk_questions_homework`
    FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;