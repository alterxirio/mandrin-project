-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Feb 09, 2026 at 04:08 PM
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
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `chinese_text` text NOT NULL,
  `pinyin_text` text DEFAULT NULL,
  `meaning` text DEFAULT NULL,
  `character_name` varchar(232) NOT NULL,
  `audio_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dialogues`
--

INSERT INTO `dialogues` (`id`, `topic_id`, `chinese_text`, `pinyin_text`, `meaning`, `character_name`, `audio_path`) VALUES
(8, 2, '你好！你好吗 小猫在森林里跳舞，风吹过河面，带来花香。 风吹过河面，带来花香。月亮轻轻照亮树叶，风吹过河面，带来花香', 'sad', 'ha ha', 'GA', '../media/audio/dialogue/dialogue-2/2.mp3');

-- --------------------------------------------------------

--
-- Table structure for table `homework`
--

CREATE TABLE `homework` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `homework_id` int(11) NOT NULL,
  `type` enum('mcq','listening','picture','truefalse','rearrange') NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(50) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `image_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `topik` int(100) NOT NULL,
  `topic_name` varchar(100) NOT NULL,
  `chinese_character` varchar(100) NOT NULL,
  `pinyin` varchar(100) NOT NULL,
  `banner_path` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `topik`, `topic_name`, `chinese_character`, `pinyin`, `banner_path`) VALUES
(1, 1, 'Pengenalan Asas Bahasa Mandarin', '汉 语 简 介', 'hàn yǔ jiǎn jiè', '../media/graphic/Banner - 1.png'),
(2, 2, 'Ucapan Salam', '问 候 语\r\n ', 'Wèn hòu yǔ', '../media/graphic/Banner - 2.png'),
(3, 3, 'Memperkenalkan Diri ', '自 我 介 绍', 'Zì wǒ jiè shào', '../media/graphic/Banner - 3.png'),
(4, 4, 'Keluarga Saya ', '我 的 家 人', 'Wǒ de jiā rén', '../media/graphic/Banner - 4.png'),
(5, 5, 'Kolej Saya ', '我 的 学 院\r\n ', 'Wǒ de xué yuàn', '../media/graphic/Banner - 5.png'),
(6, 6, 'Masa & Waktu ', '时 间', 'Shí jiān', '../media/graphic/Banner - 6.png'),
(7, 7, 'Hari & Tarikh', '星 期 和 日 期', 'xīng qī hé rì qī', '../media/graphic/Banner - 7.png'),
(13, 8, 'test 1', '我 的 学 院 ', 'test 1', '../media/graphic/Banner - 8.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `angkagiliran` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('Pensyarah','Pelajar') NOT NULL,
  `current_score` int(11) DEFAULT 0,
  `topic_done` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `angkagiliran`, `password`, `nama`, `role`, `current_score`, `topic_done`, `created_at`) VALUES
(1, 'BKV0425KA008', '0465', 'Aqif', 'Pelajar', 0, 0, '2025-12-10 12:52:15'),
(2, '071130020463', '0465', 'Umirah', 'Pensyarah', 0, 0, '2025-12-17 06:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `words`
--

CREATE TABLE `words` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `chinese` varchar(100) NOT NULL,
  `pinyin` varchar(100) DEFAULT NULL,
  `meaning` varchar(100) DEFAULT NULL,
  `audio_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `words`
--

INSERT INTO `words` (`id`, `topic_id`, `chinese`, `pinyin`, `meaning`, `audio_path`) VALUES
(1, 1, '你好', 'nǐ hǎo', 'Hello', 'audio/topic1/nihao.mp3'),
(2, 1, '谢谢', 'xiè xie', 'Thank you', 'audio/topic1/xiexie.mp3'),
(3, 1, '对不起', 'duì bu qǐ', 'Sorry', 'audio/topic1/duibuqi.mp3'),
(4, 1, '再见', 'zài jiàn', 'Goodbye', 'audio/topic1/zaijian.mp3'),
(5, 1, '请', 'qǐng', 'Please', 'audio/topic1/qing.mp3'),
(6, 1, '是', 'shì', 'Yes / To be', 'audio/topic1/shi.mp3'),
(7, 1, '不是', 'bú shì', 'No / Is not', 'audio/topic1/bushi.mp3'),
(8, 1, '我', 'wǒ', 'I / Me', 'audio/topic1/wo.mp3'),
(9, 1, '你', 'nǐ', 'You', 'audio/topic1/ni.mp3'),
(10, 1, '他', 'tā', 'He / Him', 'audio/topic1/ta.mp3'),
(13, 2, 'test', 'test', 'test', '../media/audio/topik-2/topik 2 - test.mp3'),
(14, 2, '你好', 'test', 'hello', '../media/audio/topik-2/topik 2 - 你好.mp3'),
(15, 2, '明天', 'míngtiān', 'esok', '../media/audio/topik-2/topik 2 - 明天.mp3'),
(27, 2, 'bkl', 'hahakj', 'bvm', '../media/audio/topik-2/topik 2 - bla.mp3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dialogues`
--
ALTER TABLE `dialogues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `angkagiliran` (`angkagiliran`);

--
-- Indexes for table `words`
--
ALTER TABLE `words`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dialogues`
--
ALTER TABLE `dialogues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `words`
--
ALTER TABLE `words`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dialogues`
--
ALTER TABLE `dialogues`
  ADD CONSTRAINT `dialogues_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`);

--
-- Constraints for table `words`
--
ALTER TABLE `words`
  ADD CONSTRAINT `words_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
