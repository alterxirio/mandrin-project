-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 05, 2026 at 05:11 PM
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
-- Table structure for table `class_students`
--

CREATE TABLE `class_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `class` varchar(20) NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_students`
--

INSERT INTO `class_students` (`id`, `class`, `student_id`, `created_at`) VALUES
(1, '2A', 2, '2026-03-04 20:00:00'),
(2, '2A', 3, '2026-03-04 20:00:00'),
(3, '2A', 5, '2026-03-04 20:00:00'),
(4, '2B', 6, '2026-03-04 20:00:00');

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
(5, 3, '我喜欢吃米饭。', 'wǒ xǐ huān chī mǐ fàn', 'Saya suka makan nasi.', 'B', ''),
(7, 2, '你好！你好吗 小猫在森林里跳舞，风吹过河面，带来花香。 风吹过河面，', 'bla bla ', 'ha hasdaasdasd', 'PC', '../media/audio/dialogue/dialogue-2/1.mp3'),
(8, 4, '风吹过河面，带来花香', 'bla bla ', 'ha hasdaasdasd', 'PH', '../media/audio/dialogue/dialogue-4/1.mp3');

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
(23, 'Latihan Bab 11', NULL, '2A', '2026-03-03', '2026-03-03 08:42:02'),
(25, 'Latihan penguasaan Bahasa Mandrin', NULL, '2A', '2026-03-27', '2026-03-03 09:49:35'),
(26, 'Latihan Bab 10', NULL, '1B', '2026-03-23', '2026-03-03 09:50:44'),
(27, 'Latihan penguasaan Bahasa Mandrin', NULL, '2A', '2026-03-28', '2026-03-03 10:13:56'),
(28, 'Latihan penguasaan Bahasa Mandrin', NULL, '1B', '2026-03-03', '2026-03-03 10:24:14'),
(29, 'Latihan Bab 11', NULL, '1A', '2026-03-03', '2026-03-03 10:33:47'),
(30, 'Latihan Bab 10', NULL, '1A', '2026-03-21', '2026-03-03 10:36:18'),
(31, 'Latihan Bab 10', NULL, '2A', '2026-03-28', '2026-03-03 14:48:03'),
(32, 'Latihan penguasaan Bahasa Mandrin', NULL, '2B', '2026-03-28', '2026-03-03 15:38:23'),
(33, 'Latihan Bab 10', NULL, '2A', '2026-03-04', '2026-03-03 16:26:23'),
(34, 'Latihan Bab 10', NULL, '1B', '2026-03-06', '2026-03-03 16:27:45'),
(35, 'Latihan Bab 12', NULL, '2B', '2026-03-28', '2026-03-03 16:30:21'),
(41, 'Latihan Bab 12', NULL, '2A', '2026-03-04', '2026-03-05 04:30:13'),
(42, 'Latihan Bab 14', NULL, '2B', '2026-03-28', '2026-03-05 06:36:21'),
(43, ' Bab 13', NULL, '2A', '2026-04-02', '2026-03-05 06:37:11'),
(44, 'Latihan Bab 13', NULL, '2A', '2026-03-06', '2026-03-05 14:33:22'),
(46, 'Latihan penguasaan Bahasa Mandrin', NULL, '2A', '0555-05-05', '2026-03-05 15:28:37'),
(47, 'hello', NULL, '2A', '6666-06-06', '2026-03-05 15:35:48');

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
(107, 23, 'picture', 'Soalan 1', '', NULL, NULL, NULL, NULL, NULL, '', NULL, ''),
(109, 25, 'rearrange', 'Soalan 1', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL),
(110, 25, 'listening', 'hello', '', NULL, NULL, NULL, NULL, 'nefer 2,nefer 1', 'nefer 1', 'media/homework/audio/hw_69a6aeaf16a1c7.25391709.mp3', 'media/homework/images/hw_69a6aeaf150cc5.24301861.jpg,media/homework/images/hw_69a6aeaf15d071.42199643.jpg'),
(111, 25, 'picture', 'helllo', '', NULL, NULL, NULL, NULL, NULL, 'nefer 1,nefer 2,bruh', NULL, 'media/homework/images/hw_69a6aeaf1761d6.45876635.jpg,media/homework/images/hw_69a6aeaf180945.81241307.jpg,media/homework/images/hw_69a6aeaf18cec9.70519003.png'),
(112, 25, 'mcq', 'haiwan berbisa', '', 'ayam', 'ular', 'ulat', 'ikan', NULL, 'ular', NULL, NULL),
(113, 25, 'truefalse', 'ikan sedang makan', '', NULL, NULL, NULL, NULL, NULL, 'false', NULL, 'media/homework/images/hw_69a6aeaf19fe78.14597947.jpg'),
(114, 26, 'rearrange', 'Soalan 1', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL),
(115, 27, 'rearrange', 'Soalan 1', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL),
(116, 28, 'rearrange', 'Soalan 1', '', NULL, NULL, NULL, NULL, NULL, 'saya suka,makan,ayam', NULL, NULL),
(117, 29, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya,makan ayam,masak kicap', NULL, NULL),
(118, 30, 'picture', 'Padankan perkataan dengan gambar yang betul.', '', NULL, NULL, NULL, NULL, NULL, 'nefer 2,nefer 1,bruh,ducati', NULL, 'media/homework/images/hw_69a6b9a24e8cb7.57942538.jpg,media/homework/images/hw_69a6b9a24f2618.21497883.jpg,media/homework/images/hw_69a6b9a24fe5a3.29771826.png,media/homework/images/hw_69a6b9a250b9b8.58380502.jpg'),
(119, 30, 'listening', 'hello', '', NULL, NULL, NULL, NULL, 'ducati,nefer 1,bruh', 'nefer 1', 'media/homework/audio/hw_69a6b9a2539c51.22613305.mp3', 'media/homework/images/hw_69a6b9a2518b97.22479109.jpg,media/homework/images/hw_69a6b9a2521fc1.81402306.jpg,media/homework/images/hw_69a6b9a252b778.80986676.png'),
(120, 30, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya,sangat,pandai', NULL, NULL),
(121, 31, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya,merupakan pelajar,yang sangat,cerdik dan pand', NULL, NULL),
(122, 32, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya,makan,ayam goreng', NULL, NULL),
(123, 33, 'listening', 'hello', '', NULL, NULL, NULL, NULL, 'bruh,ducati', 'ducati', 'media/homework/audio/hw_69a70baf606a62.91020115.mp3', 'media/homework/images/hw_69a70baf5a32e0.56765555.png,media/homework/images/hw_69a70baf5a4b46.97387669.jpg'),
(124, 34, 'picture', 'helllo', '', NULL, NULL, NULL, NULL, NULL, 'angel,ducati,nefer 2,nefer 1', NULL, 'media/homework/images/hw_69a70c015858d4.34727410.jpg,media/homework/images/hw_69a70c0158c520.15961772.jpg,media/homework/images/hw_69a70c01591315.46488978.jpg,media/homework/images/hw_69a70c01595002.63072235.jpg'),
(125, 35, 'mcq', 'Planet', '', 'bumi', 'bulan', 'matahari', 'Bima Sakti', NULL, 'bumi', NULL, NULL),
(126, 35, 'truefalse', 'Gambar rajah di bawah Menunjukkan seseornag sedang duduk', '', NULL, NULL, NULL, NULL, NULL, 'true', NULL, 'media/homework/images/hw_69a70c9d266706.83132301.jpg'),
(136, 41, 'picture', 'Padankan perkataan dengan gambar yang betul.', '', NULL, NULL, NULL, NULL, NULL, 'nefer 2,nefer 1,raya', NULL, 'media/homework/images/hw_69a906d527fda9.95242284.jpg,media/homework/images/hw_69a906d5290301.53568507.jpg,media/homework/images/hw_69a906d529e059.21699268.png'),
(137, 42, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya,makan,daging lembu', NULL, NULL),
(138, 43, 'rearrange', 'hello', '', NULL, NULL, NULL, NULL, NULL, 'saya makan,daging,lembu,wagyu', NULL, NULL),
(139, 44, 'picture', 'helllo', '', NULL, NULL, NULL, NULL, NULL, 'nefer 1,nefer 2,kad raya,ducati', NULL, 'media/homework/images/hw_69a99432326697.60843511.jpg,media/homework/images/hw_69a9943232b985.26538741.jpg,media/homework/images/hw_69a9943238bff3.47966110.png,media/homework/images/hw_69a99432395b18.89678646.jpg'),
(141, 46, 'truefalse', 'hi', '', NULL, NULL, NULL, NULL, NULL, 'true', NULL, 'media/homework/images/hw_69a9a14f19d994.52560261.png'),
(142, 47, 'rearrange', 'susun perkataan ini menjadi ayat yang betul', '', NULL, NULL, NULL, NULL, NULL, 'saya,suka,belajar mandarin', NULL, NULL),
(143, 47, 'listening', 'dengar audio dan pilih gambar yang betul', '', NULL, NULL, NULL, NULL, 'hello,hi', 'hello', 'media/homework/audio/hw_69a9a2d4bd0ef8.36477631.mp3', 'media/homework/images/hw_69a9a2d4ba8a34.66444480.png,media/homework/images/hw_69a9a2d4bb28c9.11406382.jpg'),
(144, 47, 'picture', 'padankan dengar perkataan yang betul', '', NULL, NULL, NULL, NULL, NULL, 'ni hao,hi,hello', NULL, 'media/homework/images/hw_69a9a2d4be0248.77715368.jpg,media/homework/images/hw_69a9a2d4be7f24.45719144.jpg,media/homework/images/hw_69a9a2d4bef973.76233328.png'),
(145, 47, 'mcq', 'pilih satu', '', 'hi', 'hello', 'bye', '', NULL, 'bye', NULL, NULL),
(146, 47, 'truefalse', 'siapa saya', '', NULL, NULL, NULL, NULL, NULL, 'true', NULL, 'media/homework/images/hw_69a9a2d4bfd771.38636335.png');

-- --------------------------------------------------------

--
-- Table structure for table `student_homework_answers`
--

CREATE TABLE `student_homework_answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_homework_answers`
--

INSERT INTO `student_homework_answers` (`id`, `submission_id`, `question_id`, `answer_text`, `created_at`) VALUES
(1, 1, 122, 'saya makan ayam goreng', '2026-03-03 15:47:37'),
(2, 2, 122, 'saya makan ayam goreng', '2026-03-03 16:23:14'),
(3, 3, 125, 'bumi', '2026-03-03 16:30:33'),
(4, 3, 126, 'true', '2026-03-03 16:30:33'),
(5, 4, 123, 'ducati', '2026-03-03 16:31:54'),
(6, 5, 125, 'bumi', '2026-03-03 16:32:36'),
(7, 5, 126, 'true', '2026-03-03 16:32:36'),
(11, 7, 121, 'saya merupakan pelajar yang sangat cerdik dan pand', '2026-03-04 03:39:00'),
(22, 14, 136, 'nefer 2,nefer 3,raya', '2026-03-05 04:34:17'),
(23, 15, 136, 'nefer 2,nefer 1,raya', '2026-03-05 04:36:16'),
(25, 17, 138, 'saya makan daging lembu wagyu', '2026-03-05 06:45:36'),
(26, 18, 138, 'saya makan daging lembu wagyu', '2026-03-05 14:22:34'),
(27, 19, 139, 'nefer 1,nefer 2,kad raya,ducati', '2026-03-05 14:50:54'),
(28, 20, 139, 'nefer 2,nefer 1,kad raya,ducati', '2026-03-05 14:54:53'),
(29, 21, 142, 'saya suka belajar mandarin', '2026-03-05 15:39:10'),
(30, 21, 143, 'hi', '2026-03-05 15:39:10'),
(31, 21, 144, 'hello,ni hao,hi', '2026-03-05 15:39:10'),
(32, 21, 145, 'bye', '2026-03-05 15:39:10'),
(33, 21, 146, 'false', '2026-03-05 15:39:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_homework_submissions`
--

CREATE TABLE `student_homework_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `homework_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('draft','submitted') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `score` int(11) DEFAULT 0,
  `incorrect` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_homework_submissions`
--

INSERT INTO `student_homework_submissions` (`id`, `homework_id`, `student_id`, `status`, `submitted_at`, `created_at`, `updated_at`, `score`, `incorrect`) VALUES
(1, 32, 4, 'submitted', '2026-03-03 23:47:37', '2026-03-03 15:47:37', '2026-03-03 15:47:37', 0, 0),
(2, 32, 3, 'submitted', '2026-03-04 00:23:14', '2026-03-03 16:23:14', '2026-03-03 16:23:14', 0, 0),
(3, 35, 3, 'submitted', '2026-03-04 00:30:33', '2026-03-03 16:30:33', '2026-03-03 16:30:33', 2, 0),
(4, 33, 3, 'submitted', '2026-03-04 00:31:54', '2026-03-03 16:31:54', '2026-03-03 16:31:54', 1, 0),
(5, 35, 4, 'submitted', '2026-03-04 00:32:36', '2026-03-03 16:32:36', '2026-03-03 16:32:36', 2, 0),
(7, 31, 3, 'submitted', '2026-03-04 11:39:00', '2026-03-04 03:38:48', '2026-03-04 03:39:00', 0, 1),
(14, 41, 3, 'submitted', '2026-03-05 12:34:17', '2026-03-05 04:34:17', '2026-03-05 04:34:17', 0, 1),
(15, 41, 2, 'submitted', '2026-03-05 12:36:16', '2026-03-05 04:36:16', '2026-03-05 04:36:16', 1, 0),
(17, 43, 2, 'submitted', '2026-03-05 14:45:36', '2026-03-05 06:45:36', '2026-03-05 06:45:36', 1, 0),
(18, 43, 3, 'submitted', '2026-03-05 22:22:34', '2026-03-05 14:22:34', '2026-03-05 14:22:34', 1, 0),
(19, 44, 3, 'submitted', '2026-03-05 22:50:54', '2026-03-05 14:50:54', '2026-03-05 14:50:54', 1, 0),
(20, 44, 5, 'submitted', '2026-03-05 22:54:53', '2026-03-05 14:54:53', '2026-03-05 14:54:53', 0, 1),
(21, 47, 5, 'submitted', '2026-03-05 23:39:10', '2026-03-05 15:39:10', '2026-03-05 15:39:10', 2, 3);

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
(3, 'Ali Pelajar', 'student02', 'student02', 'Pelajar'),
(4, 'Umirah Syamina', 'BKV0425KA008', '1245', 'Pensyarah'),
(5, 'Mei Ling', 'student03', 'student03', 'Pelajar'),
(6, 'Hakim', 'student04', 'student04', 'Pelajar');

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
(6, 3, '面条', 'miàn tiáo', 'Mee', '../media/audio/default-audio.mp3'),
(7, 4, 'bla1', 'haha', 'bv', '../media/audio/topik-4/topik 4 - bla.mp3'),
(10, 4, 'blammm', 'haha', 'bv', '../media/audio/topik-4/topik 4 - blammm.mp3'),
(12, 4, 'blammmssd', 'bla', 'bv', '../media/audio/topik-4/topik 4 - blammmssd.mp3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class_students`
--
ALTER TABLE `class_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class_students` (`class`,`student_id`),
  ADD KEY `idx_class_students_student_id` (`student_id`);

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
-- Indexes for table `student_homework_answers`
--
ALTER TABLE `student_homework_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_submission_question` (`submission_id`,`question_id`),
  ADD KEY `idx_student_answer_question` (`question_id`);

--
-- Indexes for table `student_homework_submissions`
--
ALTER TABLE `student_homework_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_submission_student_homework` (`homework_id`,`student_id`),
  ADD KEY `idx_submission_student` (`student_id`);

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
-- AUTO_INCREMENT for table `class_students`
--
ALTER TABLE `class_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dialogues`
--
ALTER TABLE `dialogues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `student_homework_answers`
--
ALTER TABLE `student_homework_answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `student_homework_submissions`
--
ALTER TABLE `student_homework_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `words`
--
ALTER TABLE `words`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_students`
--
ALTER TABLE `class_students`
  ADD CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dialogues`
--
ALTER TABLE `dialogues`
  ADD CONSTRAINT `fk_dialogues_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_homework_answers`
--
ALTER TABLE `student_homework_answers`
  ADD CONSTRAINT `fk_answer_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_answer_submission` FOREIGN KEY (`submission_id`) REFERENCES `student_homework_submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_homework_submissions`
--
ALTER TABLE `student_homework_submissions`
  ADD CONSTRAINT `fk_submission_homework` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_submission_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `words`
--
ALTER TABLE `words`
  ADD CONSTRAINT `fk_words_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
