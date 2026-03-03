-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 02, 2026 at 06:02 PM
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
-- Table structure for table `dialogues`
--

CREATE TABLE `dialogues` (
  `id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `chinese_text` text NOT NULL,
  `pinyin_text` text NOT NULL,
  `meaning` text NOT NULL,
  `character_name` varchar(120) NOT NULL,
  `audio_path` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dialogues`
--

INSERT INTO `dialogues` (`id`, `topic_id`, `chinese_text`, `pinyin_text`, `meaning`, `character_name`, `audio_path`) VALUES
(1, 1, '你好！', 'nǐ hǎo', 'Hello!', 'A', ''),
(2, 1, '你好吗？', 'nǐ hǎo ma', 'Apa khabar?', 'B', ''),
(3, 1, '我很好。', 'wǒ hěn hǎo', 'Saya baik.', 'A', ''),
(4, 3, '你喜欢吃什么？', 'nǐ xǐ huān chī shén me', 'Awak suka makan apa?', 'A', ''),
(5, 3, '我喜欢吃米饭。', 'wǒ xǐ huān chī mǐ fàn', 'Saya suka makan nasi.', 'B', '');

-- --------------------------------------------------------

--
-- Table structure for table `homework`
--

CREATE TABLE `homework` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `class` varchar(20) NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homework`
--

INSERT INTO `homework` (`id`, `title`, `description`, `class`, `due_date`, `created_at`) VALUES
(1, 'Latihan Topik 1', 'Generated from add-work form', '1A', '2026-03-10', '2026-03-02 16:55:22'),
(2, 'Latihan Topik 2', 'Generated from add-work form', '1B', '2026-03-12', '2026-03-02 16:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

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
  `audioImage_label` varchar(232) DEFAULT NULL,
  `correct_answer` varchar(50) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `image_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `homework_id`, `type`, `question_text`, `user_answer`, `option_a`, `option_b`, `option_c`, `option_d`, `audioImage_label`, `correct_answer`, `audio_file`, `image_file`) VALUES
(1, 1, 'mcq', 'Apakah maksud 你好?', '', 'Hello', 'Goodbye', 'Terima kasih', 'Maaf', NULL, 'Hello', NULL, NULL),
(2, 1, 'truefalse', '“谢谢” bermaksud terima kasih.', '', NULL, NULL, NULL, NULL, NULL, 'true', NULL, '../media/homework/images/sample-truefalse.jpg'),
(3, 1, 'rearrange', 'Susun perkataan menjadi ayat: 我 / 喜欢 / 米饭', '', '我', '喜欢', '米饭', NULL, NULL, '我,喜欢,米饭', NULL, NULL),
(4, 2, 'listening', 'Dengar audio dan pilih jawapan betul.', '', NULL, NULL, NULL, NULL, 'nǐ hǎo,xiè xie', 'nǐ hǎo', '../media/homework/audio/sample-listening.mp3', '../media/homework/images/choice1.jpg,../media/homework/images/choice2.jpg'),
(5, 2, 'picture', 'Padankan gambar dengan perkataan.', '', NULL, NULL, NULL, NULL, NULL, '米饭,面条', NULL, '../media/homework/images/food1.jpg,../media/homework/images/food2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(10) UNSIGNED NOT NULL,
  `topik` int(10) UNSIGNED NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `chinese_character` varchar(200) NOT NULL,
  `pinyin` varchar(200) NOT NULL,
  `banner_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `topik`, `topic_name`, `chinese_character`, `pinyin`, `banner_path`) VALUES
(1, 1, 'Salam', '你好', 'nǐ hǎo', '../media/graphic/Banner - 1.png'),
(2, 2, 'Nombor', '数字', 'shù zì', '../media/graphic/Banner - 2.png'),
(3, 3, 'Makanan', '食物', 'shí wù', '../media/graphic/Banner - 3.png'),
(4, 4, 'Topik - 2', 'bla', 'bla', '../media/graphic/Banner - 4.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(120) NOT NULL,
  `angkagiliran` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Pensyarah','Pelajar') NOT NULL DEFAULT 'Pelajar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `angkagiliran`, `password`, `role`) VALUES
(1, 'Admin Pensyarah', 'admin01', 'admin01', 'Pensyarah'),
(2, 'Siti Pelajar', 'student01', 'student01', 'Pelajar'),
(3, 'Ali Pelajar', 'student02', 'student02', 'Pelajar');

-- --------------------------------------------------------

--
-- Table structure for table `words`
--

CREATE TABLE `words` (
  `id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `chinese` varchar(200) NOT NULL,
  `pinyin` varchar(200) NOT NULL,
  `meaning` varchar(255) NOT NULL,
  `audio_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `words`
--

INSERT INTO `words` (`id`, `topic_id`, `chinese`, `pinyin`, `meaning`, `audio_path`) VALUES
(1, 1, '你好', 'nǐ hǎo', 'Hello', '../media/audio/default-audio.mp3'),
(2, 1, '谢谢', 'xiè xie', 'Terima kasih', '../media/audio/default-audio.mp3'),
(3, 2, '一', 'yī', 'Satu', '../media/audio/default-audio.mp3'),
(4, 2, '二', 'èr', 'Dua', '../media/audio/default-audio.mp3'),
(5, 3, '米饭', 'mǐ fàn', 'Nasi', '../media/audio/default-audio.mp3'),
(6, 3, '面条', 'miàn tiáo', 'Mee', '../media/audio/default-audio.mp3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dialogues`
--
ALTER TABLE `dialogues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dialogues_topic_id` (`topic_id`);

--
-- Indexes for table `homework`
--
ALTER TABLE `homework`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homework_id` (`homework_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_topics_topik` (`topik`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_angkagiliran` (`angkagiliran`);

--
-- Indexes for table `words`
--
ALTER TABLE `words`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_words_topic_id` (`topic_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dialogues`
--
ALTER TABLE `dialogues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `words`
--
ALTER TABLE `words`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dialogues`
--
ALTER TABLE `dialogues`
  ADD CONSTRAINT `fk_dialogues_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `words`
--
ALTER TABLE `words`
  ADD CONSTRAINT `fk_words_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
