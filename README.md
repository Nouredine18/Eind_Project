-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Gegenereerd op: 01 jun 2025 om 17:16
-- Serverversie: 10.11.10-MariaDB
-- PHP-versie: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u578783310_ecoligo_data`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `community_messages`
--

CREATE TABLE `community_messages` (
  `message_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message_content` text NOT NULL,
  `parent_message_id` int(11) DEFAULT NULL,
  `message_type` varchar(10) NOT NULL DEFAULT 'text',
  `image_url` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `community_messages`
--

INSERT INTO `community_messages` (`message_id`, `user_id`, `message_content`, `parent_message_id`, `message_type`, `image_url`, `is_deleted`, `edited_at`, `timestamp`) VALUES
(1, 41, 'Hey', NULL, 'text', NULL, 0, NULL, '2025-05-12 09:02:38'),
(2, 41, 'ef', NULL, 'text', NULL, 0, NULL, '2025-05-12 09:03:14'),
(3, 41, 'Message deleted by user.', NULL, 'text', NULL, 1, NULL, '2025-05-12 09:05:47'),
(4, 41, 'Message deleted by user.', NULL, 'text', NULL, 1, '2025-05-12 09:10:35', '2025-05-12 09:05:51'),
(5, 41, 'Yaz', NULL, 'text', NULL, 0, '2025-05-12 09:11:45', '2025-05-12 09:06:00'),
(6, 41, 'Bro', 4, 'text', NULL, 0, NULL, '2025-05-12 09:10:45'),
(7, 41, 'Whatsyp', 5, 'text', NULL, 0, NULL, '2025-05-12 09:11:54'),
(8, 312, 'd', 5, 'text', NULL, 0, NULL, '2025-05-12 09:18:20'),
(9, 41, '', NULL, 'image', 'uploads/community_chat_images/img_6821bd0895a688.69440341.jpg', 0, NULL, '2025-05-12 09:19:05'),
(10, 41, 'as you can see airplanes use are crazy used over time', NULL, 'text', NULL, 0, NULL, '2025-05-12 09:19:29'),
(11, 41, 'hi', NULL, 'text', NULL, 0, NULL, '2025-05-12 09:20:47'),
(12, 312, 'yo', 11, 'text', NULL, 0, NULL, '2025-05-12 09:21:04'),
(13, 41, '', NULL, 'image', 'uploads/community_chat_images/img_6821bda04a8bd1.37844943.jpg', 0, NULL, '2025-05-12 09:21:36'),
(14, 312, 'Air planes arent used much', 13, 'text', NULL, 0, NULL, '2025-05-12 09:21:51'),
(15, 41, 'yo whatsup', 14, 'text', NULL, 0, NULL, '2025-05-12 09:27:59'),
(16, 41, 'lol', NULL, 'text', NULL, 0, NULL, '2025-05-12 09:28:21'),
(17, 312, 'yo', 16, 'text', NULL, 0, NULL, '2025-05-12 09:28:30'),
(18, 312, '', NULL, 'image', 'uploads/community_chat_images/img_6822f3cd7659b1.18859025.jpg', 0, NULL, '2025-05-13 07:25:01'),
(19, 312, 'This can\'t be real they have invested more CO2 within the community between airports than in genera, even tho there are more celebrities taking airplanes than regular people which is crazy', NULL, 'text', NULL, 0, NULL, '2025-05-13 09:19:38'),
(20, 41, 'yo', NULL, 'text', NULL, 0, NULL, '2025-05-13 09:50:03'),
(21, 41, 'whatsup', NULL, 'text', NULL, 0, NULL, '2025-05-13 09:50:11'),
(22, 312, 'yo', 21, 'text', NULL, 0, NULL, '2025-05-13 09:50:38'),
(23, 41, 'yo', 22, 'text', NULL, 0, NULL, '2025-05-13 19:09:15'),
(24, 41, 'rf', NULL, 'text', NULL, 0, NULL, '2025-05-15 09:49:58'),
(25, 41, 'Message deleted by user.', NULL, 'image', NULL, 1, NULL, '2025-05-15 09:56:52'),
(26, 41, 'hi', NULL, 'text', NULL, 0, NULL, '2025-05-19 08:46:12'),
(27, 41, 'hallo', 22, 'text', NULL, 0, NULL, '2025-05-19 08:46:30'),
(28, 41, 'yo', NULL, 'text', NULL, 0, NULL, '2025-05-19 09:42:42'),
(29, 41, 'yo whatsup', NULL, 'text', NULL, 0, NULL, '2025-05-19 09:42:49'),
(30, 41, 'Message deleted by user.', NULL, 'text', NULL, 1, NULL, '2025-05-21 15:16:15'),
(31, 41, 'Message deleted by user.', 22, 'text', NULL, 1, NULL, '2025-05-21 15:16:27'),
(32, 41, 'Yeah', 22, 'text', NULL, 0, NULL, '2025-05-21 15:16:31'),
(33, 312, 'zd', 32, 'text', NULL, 0, NULL, '2025-05-30 20:17:18'),
(34, 312, 'lol', 32, 'text', NULL, 0, NULL, '2025-05-30 20:18:39'),
(35, 41, 'Ja ma', 34, 'text', NULL, 0, NULL, '2025-06-01 12:48:18'),
(36, 312, 'hzo ja ma ?', 35, 'text', NULL, 0, NULL, '2025-06-01 12:48:27');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `compensationprojects`
--

CREATE TABLE `compensationprojects` (
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `project_type` varchar(50) DEFAULT NULL,
  `effectiveness` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `compensationProjectImage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `compensationprojects`
--

INSERT INTO `compensationprojects` (`project_id`, `name`, `description`, `project_type`, `effectiveness`, `created_at`, `updated_at`, `compensationProjectImage`) VALUES
(1, 'Reforestation in Amazon', 'Support reforestation efforts in the Amazon rainforest to absorb CO2 and restore ecosystems.', 'Reforestation', 95.00, '2025-01-21 13:29:45', '2025-02-04 21:48:10', 'assets/img/compensationProjectImages/refor1.jpg'),
(2, 'Wind Energy in Europe', 'Invest in wind energy projects across Europe to reduce reliance on fossil fuels.', 'Renewable Energy', 90.00, '2025-01-21 13:29:45', '2025-02-04 21:44:26', 'assets/img/compensationProjectImages/windEnergyEurope.jpg'),
(3, 'Solar Power in Africa', 'Promote solar power installations in African communities to provide clean energy.', 'Renewable Energy', 85.00, '2025-01-21 13:29:45', '2025-02-04 21:40:24', 'assets/img/compensationProjectImages/solarPanel.jpg'),
(4, 'Energy Efficiency in Asia', 'Implement energy efficiency initiatives in Asian cities to lower CO2 emissions.', 'Energy Efficiency', 80.00, '2025-01-21 13:29:45', '2025-02-04 21:50:04', 'assets/img/compensationProjectImages/energyeff.jpg'),
(5, 'Mangrove Restoration in Southeast Asia', 'Restore mangrove forests in Southeast Asia to protect coastlines and absorb CO2.', 'Reforestation', 88.00, '2025-01-21 13:29:45', '2025-02-04 21:46:15', 'assets/img/compensationProjectImages/reforIndia.jpg'),
(6, 'Biogas Projects in India', 'Develop biogas projects in rural India to provide clean cooking fuel and reduce CO2 emissions.', 'Renewable Energy', 92.00, '2025-01-21 13:29:45', '2025-02-04 21:49:05', 'assets/img/compensationProjectImages/cleanEnergy.jpg');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `donations`
--

INSERT INTO `donations` (`donation_id`, `user_id`, `project_id`, `amount`, `donation_date`) VALUES
(40, 41, 1, 20.00, '2025-03-07 19:28:15'),
(41, 41, 1, 100.00, '2025-03-07 19:30:16'),
(42, 41, 2, 140.00, '2025-03-07 19:38:34'),
(43, 41, 2, 100.00, '2025-03-07 21:49:57'),
(44, 41, 2, 2.00, '2025-03-07 21:59:32'),
(45, 41, 1, 20.00, '2025-03-07 23:21:03'),
(46, 41, 2, 30.00, '2025-03-09 15:54:11'),
(47, 41, 1, 400.00, '2025-03-09 16:07:41'),
(48, 41, 1, 20.00, '2025-03-09 16:32:02'),
(49, 41, 1, 20.00, '2025-03-09 16:33:02'),
(50, 41, 1, 2.00, '2025-03-09 17:19:04'),
(51, 41, 1, 2.00, '2025-03-09 18:17:46'),
(52, 41, 1, 15.00, '2025-03-21 13:07:36'),
(53, 41, 1, 23.00, '2025-05-12 08:50:30'),
(54, 41, 1, 20.00, '2025-05-13 11:22:01'),
(55, 41, 1, 60.00, '2025-05-13 12:03:04'),
(56, 41, 1, 60.00, '2025-05-13 12:08:56'),
(57, 41, 1, 40.00, '2025-05-13 12:14:47'),
(58, 41, 1, 40.00, '2025-05-13 12:20:26'),
(59, 41, 1, 19.00, '2025-05-15 09:52:23'),
(60, 41, 2, 8.00, '2025-05-19 08:45:09'),
(61, 41, 1, 13.00, '2025-05-19 09:08:52'),
(62, 312, 1, 20.00, '2025-05-19 09:15:54'),
(63, 41, 1, 20.00, '2025-05-30 20:01:53'),
(64, 41, 1, 5.00, '2025-05-30 20:04:37');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `event_time`, `location`, `image_url`, `registration_deadline`, `is_active`, `created_at`) VALUES
(1, 'Workshop: Intro to Sustainable Travel', 'Learn the basics of sustainable travel, reducing your carbon footprint, and making ethical choices. Covers planning, packing, transport, accommodation, and activities.', '2025-07-15', '14:00 - 16:00 CET', 'Online (Zoom)', 'assets/img/examples/workshop1.jpg', '2025-07-10 23:59:59', 1, '2025-05-13 19:04:56'),
(2, 'Webinar: The Future of Eco-Tourism', 'Join experts discussing trends, innovations, and challenges in eco-tourism. Discover how tech and community initiatives shape sustainable travel.', '2025-08-05', '18:00 - 19:30 CET', 'Online (Webinar Platform)', 'assets/img/examples/webinar1.jpg', '2025-08-01 23:59:59', 1, '2025-05-13 19:04:56'),
(3, 'Community Meetup: Local Green Initiatives', 'Connect with eco-conscious individuals. Share ideas, learn about local projects, and explore collaboration opportunities.', '2025-09-10', '10:00 - 12:00 Local Time', 'City Park Pavilion', 'assets/img/examples/meetup1.jpg', '2025-09-05 23:59:59', 1, '2025-05-13 19:04:56');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `faq`
--

CREATE TABLE `faq` (
  `faq_id` int(11) NOT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `leaderboard`
--

CREATE TABLE `leaderboard` (
  `leaderboard_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_points` decimal(10,2) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notification_type`, `message`, `is_read`, `created_at`) VALUES
(23, 30, 'profile_update', 'You have updated your profile. <a href=\"profile.php\" target=\"_blank\">Check your profile</a>', 0, '2024-11-13 09:47:59'),
(24, 30, 'profile_update', 'You have updated your profile. <a href=\"profile.php\" target=\"_blank\">Check your profile</a>', 0, '2024-11-13 09:48:04'),
(25, 30, 'profile_update', 'You have updated your profile, Check your profile</a>', 0, '2024-11-13 09:48:38'),
(26, 30, 'profile_update', 'You have updated your profile, Check your profile</a>', 0, '2024-11-13 09:48:43'),
(27, 30, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 09:49:22'),
(28, 30, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 09:49:24'),
(29, 31, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:24:11'),
(30, 31, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:24:16'),
(31, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:54:31'),
(32, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:54:34'),
(33, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:54:41'),
(34, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:54:55'),
(35, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:55:19'),
(36, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:55:25'),
(37, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:55:33'),
(38, 36, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 10:56:03'),
(39, 33, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 11:10:07'),
(40, 33, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 11:10:12'),
(41, 33, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 11:10:16'),
(47, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 23:03:53'),
(48, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-13 23:04:43'),
(49, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 13:07:24'),
(50, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:45:57'),
(51, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:46:08'),
(52, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:46:11'),
(53, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:47:48'),
(54, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:51:55'),
(55, 42, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-14 18:52:30'),
(56, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-18 23:05:07'),
(76, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-21 21:44:38'),
(77, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-21 22:05:54'),
(78, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-21 22:13:29'),
(79, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-21 22:18:16'),
(80, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-21 22:18:20'),
(81, 41, 'Travel Destination Added', 'You have added a new travel destination: Zebi to Zebiland', 0, '2024-11-21 22:25:58'),
(82, 41, 'Travel Destination Added', 'Your original travel destination has been updated to Amsterdam to New York Citydz. Original travel: Amsterdam to New York Citydz', 1, '2024-11-21 22:31:25'),
(83, 41, 'Travel Destination Added', 'You have added a new travel destination: Mechelen to Spanje', 0, '2024-11-22 13:31:11'),
(84, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 13:33:00'),
(85, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:13:42'),
(86, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:14:28'),
(87, 54, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:25:29'),
(88, 54, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:25:31'),
(89, 54, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:25:46'),
(90, 54, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-22 14:26:07'),
(91, 54, 'Travel Destination Added', 'You have added a new travel destination: Amsterdam to Parijs', 0, '2024-11-22 14:26:59'),
(92, 54, 'Travel Destination Added', 'Your original travel destination has been updated to Amsterdam to Parijs. Original travel: Amsterdam to Parijs', 0, '2024-11-22 14:27:59'),
(93, 54, 'Travel Destination Added', 'You have added a new travel destination: Amsterdam to Parijs', 0, '2024-11-22 14:28:39'),
(94, 55, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-23 14:25:43'),
(95, 55, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-11-23 14:25:47'),
(96, 55, 'Travel Destination Added', 'You have added a new travel destination: Turkije to Afghanistan', 0, '2024-11-23 14:26:41'),
(97, 55, 'Travel Destination Added', 'You have added a new travel destination: Parijs to Engeland', 0, '2024-11-23 14:27:24'),
(98, 63, 'Travel Destination Added', 'You have added a new travel destination: Amsterdam to Parijs', 0, '2024-12-12 18:51:08'),
(99, 63, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-12 18:54:19'),
(100, 63, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-12 18:55:18'),
(101, 63, 'Travel Destination Added', 'You have added a new travel destination: Amsterdam to New York', 0, '2024-12-12 18:59:33'),
(102, 63, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-12 19:02:10'),
(103, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-12 22:31:19'),
(104, 72, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-13 00:03:33'),
(105, 72, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-13 00:03:41'),
(106, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-13 09:12:18'),
(107, 41, 'profile_update', 'You have updated your profile, Check your profile.', 0, '2024-12-13 09:12:24'),
(108, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-07 21:57:21'),
(111, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-07 23:15:42'),
(116, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-09 16:07:49'),
(117, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-09 18:59:33'),
(118, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-09 18:59:33'),
(119, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-09 18:59:33'),
(120, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-09 19:33:12'),
(121, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-09 19:33:12'),
(122, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-09 19:33:12'),
(123, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-10 07:39:27'),
(124, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-10 07:39:27'),
(125, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-10 07:39:27'),
(126, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-17 10:56:38'),
(127, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-17 10:56:39'),
(128, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-17 10:56:39'),
(129, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-03-21 13:03:30'),
(130, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-03-21 13:03:30'),
(131, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-03-21 13:03:30'),
(132, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-05-12 09:12:39'),
(133, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-12 09:12:39'),
(134, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 1, '2025-05-12 09:12:40'),
(135, 41, 'profile_update', 'You have updated your profile. Check your profile.', 0, '2025-05-13 09:34:59'),
(136, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-05-13 09:37:10'),
(137, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-13 09:37:10'),
(138, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-13 09:37:10'),
(139, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-05-13 19:11:47'),
(140, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-13 19:11:47'),
(141, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-13 19:11:47'),
(142, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-13 19:11:47'),
(143, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 1, '2025-05-15 09:54:33'),
(144, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-15 09:54:33'),
(145, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-15 09:54:33'),
(146, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-15 09:54:33'),
(147, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-05-18 20:15:19'),
(148, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-18 20:15:19'),
(149, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-18 20:15:19'),
(150, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-18 20:15:19'),
(151, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 1, '2025-05-19 08:54:03'),
(152, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 1, '2025-05-19 08:54:03'),
(153, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 1, '2025-05-19 08:54:03'),
(154, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-19 08:54:03'),
(155, 312, 'Travel Destination Added', 'You have added a new travel destination: Amsterdam to Paris', 0, '2025-05-19 09:16:39'),
(156, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-05-19 09:45:07'),
(157, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-19 09:45:07'),
(158, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-19 09:45:07'),
(159, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-19 09:45:07'),
(160, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 1, '2025-05-21 15:14:32'),
(161, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-05-21 15:14:32'),
(162, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-05-21 15:14:32'),
(163, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-05-21 15:14:32'),
(164, 315, 'profile_update', 'You have updated your profile. Check your profile.', 0, '2025-05-30 20:11:03'),
(165, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 100 kg.', 0, '2025-06-01 12:46:43'),
(166, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 200 kg.', 0, '2025-06-01 12:46:43'),
(167, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 500 kg.', 0, '2025-06-01 12:46:43'),
(168, 41, 'compensation_level', 'Congratulations! You have reached a new compensation level of 1000 kg.', 0, '2025-06-01 12:46:43');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `passwordresets`
--

CREATE TABLE `passwordresets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `amount`, `payment_method`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 41, 2.00, 'PayPal', 'Pending', '2025-03-10 08:24:41', '2025-03-10 08:24:41'),
(2, 41, 2.00, 'PayPal', 'Pending', '2025-03-10 08:31:39', '2025-03-10 08:31:39'),
(3, 41, 20.00, 'PayPal', 'Pending', '2025-03-21 11:44:05', '2025-03-21 11:44:05'),
(4, 41, 20.00, 'PayPal', 'Pending', '2025-03-21 11:46:09', '2025-03-21 11:46:09'),
(5, 41, 2.00, 'PayPal', 'Pending', '2025-05-19 07:57:22', '2025-05-19 07:57:22'),
(6, 41, 1.00, 'PayPal', 'Pending', '2025-05-19 07:57:46', '2025-05-19 07:57:46'),
(7, 41, 2.00, 'PayPal', 'Pending', '2025-05-19 07:59:00', '2025-05-19 07:59:00'),
(8, 41, 2.00, 'PayPal', 'Pending', '2025-05-19 08:03:43', '2025-05-19 08:03:43');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `project_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 41, 2, 3, 'd', '2025-03-07 19:54:36'),
(2, 41, 2, 3, 'd', '2025-03-07 19:55:35'),
(3, 41, 2, 3, 'I don\'t like their service', '2025-03-07 20:01:01'),
(4, 41, 2, 3, 'I don\'t like their service', '2025-03-07 20:02:22'),
(5, 41, 2, 3, 'I don\'t like their service', '2025-03-07 20:03:44'),
(6, 41, 2, 5, 'Good', '2025-03-07 20:04:09'),
(7, 41, 1, 3, 'Best', '2025-03-07 20:05:31'),
(8, 41, 3, 3, 'd', '2025-03-07 21:10:52'),
(9, 41, 1, 2, 'Nice', '2025-03-21 13:06:10'),
(10, 312, 1, 4, 'Good', '2025-05-19 09:15:22');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `next_payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `travelhistory`
--

CREATE TABLE `travelhistory` (
  `travel_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `travel_date` date NOT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `transport_mode` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `co2_emissions` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `travelhistory`
--

INSERT INTO `travelhistory` (`travel_id`, `user_id`, `travel_date`, `origin`, `destination`, `distance_km`, `transport_mode`, `created_at`, `updated_at`, `co2_emissions`) VALUES
(8, 41, '2024-02-18', 'Amsterdam', 'New York Citydz', 200.00, '0', '2024-11-20 15:48:52', '2024-11-21 22:31:25', 385.5),
(9, 41, '2024-12-12', 'London', 'Parijs', 576.00, 'vliegtuig', '2024-11-20 15:55:02', '2024-11-20 15:55:02', 148.032),
(10, 41, '2024-06-18', 'Marokko', 'Parijs', 650.00, 'vliegtuig', '2024-11-20 22:00:07', '2024-11-20 22:00:07', 167.05),
(16, 41, '2024-03-02', 'zdzd', 'zdzd', 232.00, 'auto', '2024-11-21 22:13:05', '2024-11-21 22:13:05', 44.544000000000004),
(17, 41, '2024-02-02', 'zdzd', 'zdzd', 23.00, 'auto', '2024-11-21 22:15:35', '2024-11-21 22:15:35', 4.416),
(18, 41, '2024-09-02', 'amsterdam', 'parijs', 245.00, 'auto', '2024-11-21 22:19:43', '2024-11-21 22:19:43', 47.04),
(19, 41, '2024-02-09', 'zd', 'zdzdzd', 500.00, 'vliegtuig', '2024-11-21 22:21:29', '2024-11-21 22:21:29', 128.5),
(20, 41, '2024-02-23', 'Zebi', 'Zebiland', 450.00, 'auto', '2024-11-21 22:25:58', '2024-11-21 22:25:58', 86.4),
(21, 41, '2024-11-23', 'Mechelen', 'Spanje', 2400.00, 'auto', '2024-11-22 13:31:11', '2024-11-22 13:31:11', 460.8),
(23, 54, '2024-11-23', 'Amsterdam', 'Parijs', 140000.00, 'vliegtuig', '2024-11-22 14:28:39', '2024-11-22 14:28:39', 35980),
(24, 55, '2024-11-24', 'Turkije', 'Afghanistan', 1025.00, 'vliegtuig', '2024-11-23 14:26:41', '2024-11-23 14:26:41', 263.425),
(25, 55, '2024-02-18', 'Parijs', 'Engeland', 2584578.00, 'vliegtuig', '2024-11-23 14:27:24', '2024-11-23 14:27:24', 664236.546),
(26, 63, '2024-12-13', 'Amsterdam', 'Parijs', 500.00, 'vliegtuig', '2024-12-12 18:51:08', '2024-12-12 18:51:08', 128.5),
(27, 63, '2024-03-12', 'Amsterdam', 'New York', 2323232.00, 'vliegtuig', '2024-12-12 18:59:33', '2024-12-12 18:59:33', 597070.6240000001),
(28, 312, '2025-05-19', 'Amsterdam', 'Paris', 700.00, 'auto', '2025-05-19 09:16:39', '2025-05-19 09:16:39', 134.4);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `userachievements`
--

CREATE TABLE `userachievements` (
  `achievement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_type` varchar(255) DEFAULT NULL,
  `achievement_value` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `code` text NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `travel_preferences` text DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `profile_picture_url` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `delete_token` varchar(64) DEFAULT NULL,
  `delete_token_expiry` datetime DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `preferences` text DEFAULT NULL,
  `communication_preferences` text DEFAULT NULL,
  `total_points` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `code`, `google_id`, `facebook_id`, `first_name`, `last_name`, `birthdate`, `address`, `phone_number`, `travel_preferences`, `email_verified`, `profile_picture_url`, `security_question`, `security_answer_hash`, `created_at`, `updated_at`, `verification_token`, `reset_token`, `reset_token_expiry`, `delete_token`, `delete_token_expiry`, `last_login`, `preferences`, `communication_preferences`, `total_points`) VALUES
(41, 'userinfowebsite@gmail.com', '$2y$10$54v5yjuQ.r.mZsbvooMm.Ow3uXad1YzdGRS/QQAW3L4fGo/wQDGuO', '', NULL, NULL, 'Nouredin', 'Tahrioui', '2006-02-18', 'Kleine nieuwedijkstraat 163', NULL, NULL, 1, 'profile_directory/674091c4571f0-images.png', 'How old are you?', '18', '2024-11-13 22:50:06', '2024-11-13 22:50:06', NULL, NULL, NULL, NULL, NULL, '2025-06-01 15:45:19', '{\"transport\":\"vliegtuig\",\"destination\":\"Parijs\"}', '{\"email\":\"Ja\",\"sms\":\"Ja\"}', 351.20),
(296, 'gcornman@ci.lacey.wa.us', '$2y$10$Wy0IGSMdD8eAT/0Eh6o9ROVtIEpBksRETnibAG8P0lSO95thcQM1C', '', NULL, NULL, 'Hello', 'TestUser', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$Kgr4DSvOySG8c.ggIYtgYesQNVbDT9lvsjUY1U7MkL9YkhyPR18XS', '2025-03-08 00:16:44', '2025-03-08 00:16:44', 'ca7337968083435906902b8f6bf7258709950d46a861635fe8b63f164b3632bb', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(297, 'markparrel@yahoo.com', '$2y$10$uGFCcWlxbEx4JE.6bLQnRu8TXKBdMSQvGAaOHYew6YS6iGTLS67rS', '', NULL, NULL, 'Alice', 'Hello', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$L8ynqzsZMSBZ1u49tNtEfutMl4tPclK/Bon4sq6XShAKGqKYKHE8e', '2025-03-08 01:10:35', '2025-03-08 01:10:35', 'e632fa5c7191c95f71ce69e8d8fac01bdeb2276ad0f5a1aa8619c29af3120014', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(298, 'jwightmn@outlook.com', '$2y$10$lBvSEt6ZlYHmE3bjKDtq/ukWF7aIG59Hv5BcEmb.13TRYU.cl/Zb6', '', NULL, NULL, 'MyName', 'John', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$XT/hgA3W/3cklL/nmdmDnOkmnDURUz/3SA4Z1avukFKQHDOxOYCYC', '2025-03-08 02:31:00', '2025-03-08 02:31:00', 'cc7e9ee8ab1753241242f65749cf09c9c383a3f4a1bc7660a543613f81b17279', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(299, 'gjbuzz17@gmail.com', '$2y$10$X/lsa33pKPqBtMK1chB43.5VZwP2lGZ6WD/z2i8o0/NDbg7DRvrou', '', NULL, NULL, 'John', 'TestUser', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$FWlZf904nxPXcGc.XFbA4e0IwlUkx3SZQGUXNZlu03ciTk3Ud.2ni', '2025-03-08 09:08:55', '2025-03-08 09:08:55', '45f22a40f150ed4f1b637a7544e9c72f8f73a395e9cbbde4133aa8de93aff7c6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(300, 'amanda.egner@phminc.com', '$2y$10$5zw3pvZ.zWXNKnSWe5iPkuF8ohhcQ3Wd7k2vg8dNeX5CBvi6jcNve', '', NULL, NULL, 'MyName', 'MyName', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$lxS/1wPxHTHv6uewaGd36egzE6aHhfr0VX6SfGWWY3uXG7F3hYdSC', '2025-03-08 10:53:12', '2025-03-08 10:53:12', '8fa5d2051b75ad15909ac3de5e6d4e9755615dcef199c6e0f3de926ea78f4c10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(301, 'mike@gergtoolanddie.com', '$2y$10$NYxrajvyOQu9k08dJq81Z.01Xz6fJ8cl5iQk6BiKSkzc9Ex8RnOee', '', NULL, NULL, 'MyName', 'Hello', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$iFcE979xLGe6maBYRklUKuWw5ewO1SbDXyGzpdwclKpc8p1AClqCy', '2025-03-08 13:24:01', '2025-03-08 13:24:01', 'a15613d566583ec4cac6cdd0d451b9c709d5ede1512afc73216b227384757dd1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(302, 'jessjgold@hotmail.com', '$2y$10$ge5oepKpoEbUtXakhF1nce9GM3Q5CbUc8/D0oyN.bRqCuJtgsjcpm', '', NULL, NULL, 'Hello', 'John', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$bn5IUwvqZv/LlecyCC1O7eqYZfbFy8Z1g46t91SU1SW4L0yKuqcJm', '2025-03-08 20:25:27', '2025-03-08 20:25:27', 'f331d3612b7d0fb7bde8e68f319b2a4dd357d5d85371e315282eb3f8ccf07e2b', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(303, 'jaydastarr02@gmail.com', '$2y$10$RCYLpd.AzftRH27uCLgjz.dhTOEUfitNZd8u/ccL6hKJ6Aoh/go02', '', NULL, NULL, 'Alice', 'TestUser', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$Tfq56M/ERbylHxh9YfG0P.KGdzyMUFPApTP2p6NgKXsLMmZ4ymhFe', '2025-03-09 04:16:46', '2025-03-09 04:16:46', 'a569d5557a2d945b14728e3f1733090b4f5bcf451f0e3b7ac53cf5effa65c8bd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(304, 'jleonicar@gmail.com', '$2y$10$neX8lMhCdaImGTxh2RO24Oao0AUaAVR7PbHbya/cIwAME.X4yrjHW', '', NULL, NULL, 'John', 'MyName', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$swnkSBNMRAbiMI.gOc.2J.gl/GukmvoDC0m.ird4Zc5ETTSDFLky.', '2025-03-09 05:14:40', '2025-03-09 05:14:40', '18f133dc3171cd49193801fcc978139b4a2b088fe0085ea4728bd4bed677f9cc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(305, 'olivierdebuire@gmx.fr', '$2y$10$kY9DgR19l5DSjDqC0Urw7O0w60cIjfAoeRoFzXu0IDJKB3Sbouyvm', '', NULL, NULL, 'Hello', 'MyName', NULL, NULL, NULL, NULL, 0, NULL, 'randomValue', '$2y$10$kAfnGrXXkbSV34UVqk1r/eRU.G7hSIuKDc2cxvMJSvnU3a6nK3ele', '2025-03-09 12:49:46', '2025-03-09 12:49:46', 'ca9b19a2bbeffa3ed01f35cac26675cc65f555cf2afc6a5e6dc392322af4dc58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(312, 'nouredinetahrioui@gmail.com', '$2y$10$8muxe//SD5hJNcUUr3Au8uXq7Yeq2vSrj8.nJdTNRoC1rdX9Df0BO', '', NULL, NULL, 'Nouredine', 'Tahrioui', NULL, NULL, NULL, NULL, 1, NULL, 'HiWhat', '$2y$10$6Vo42eqc9hAxdQQH0ZXPdOcN6qp/zTGMlS4yh7b98i73d4ax3zyHS', '2025-05-12 09:16:47', '2025-05-12 09:16:47', NULL, NULL, NULL, NULL, NULL, '2025-05-30 20:08:23', NULL, NULL, 51.40);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `usersessions`
--

CREATE TABLE `usersessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_eco_recommendations_completed`
--

CREATE TABLE `user_eco_recommendations_completed` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `recommendation_id` int(11) NOT NULL COMMENT 'Corresponds to the key in the $recommendations array',
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `user_eco_recommendations_completed`
--

INSERT INTO `user_eco_recommendations_completed` (`user_id`, `recommendation_id`, `completed_at`) VALUES
(41, 0, '2025-05-13 18:55:59'),
(41, 1, '2025-05-13 18:58:50'),
(41, 2, '2025-05-13 18:55:59'),
(41, 3, '2025-05-19 08:51:49'),
(41, 4, '2025-05-21 15:13:57');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_event_registrations`
--

CREATE TABLE `user_event_registrations` (
  `registration_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_followers`
--

CREATE TABLE `user_followers` (
  `follower_id` bigint(20) UNSIGNED NOT NULL,
  `following_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `user_followers`
--

INSERT INTO `user_followers` (`follower_id`, `following_id`, `created_at`) VALUES
(312, 41, '2025-05-12 09:53:18'),
(312, 300, '2025-05-13 09:00:39');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_weekly_challenges_completed`
--

CREATE TABLE `user_weekly_challenges_completed` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `challenge_id` int(11) NOT NULL COMMENT 'Corresponds to the key in the $current_week_challenges array',
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `user_weekly_challenges_completed`
--

INSERT INTO `user_weekly_challenges_completed` (`user_id`, `challenge_id`, `completed_at`) VALUES
(41, 1, '2025-05-13 18:55:59'),
(41, 2, '2025-05-13 18:56:24'),
(41, 3, '2025-05-18 20:14:04'),
(41, 4, '2025-05-19 08:52:13'),
(312, 1, '2025-05-13 18:57:12');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `community_messages`
--
ALTER TABLE `community_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_community_messages_user_id` (`user_id`),
  ADD KEY `fk_community_parent_message` (`parent_message_id`);

--
-- Indexen voor tabel `compensationprojects`
--
ALTER TABLE `compensationprojects`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexen voor tabel `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `fk_donations_user_id` (`user_id`),
  ADD KEY `fk_donations_project_id` (`project_id`);

--
-- Indexen voor tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexen voor tabel `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexen voor tabel `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`leaderboard_id`),
  ADD KEY `fk_leaderboard_user_id` (`user_id`);

--
-- Indexen voor tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_user_id` (`user_id`);

--
-- Indexen voor tabel `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `fk_password_resets_user_id` (`user_id`);

--
-- Indexen voor tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payments_user_id` (`user_id`);

--
-- Indexen voor tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexen voor tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD UNIQUE KEY `unique_subscription` (`user_id`,`project_id`);

--
-- Indexen voor tabel `travelhistory`
--
ALTER TABLE `travelhistory`
  ADD PRIMARY KEY (`travel_id`),
  ADD KEY `fk_travel_history_user_id` (`user_id`);

--
-- Indexen voor tabel `userachievements`
--
ALTER TABLE `userachievements`
  ADD PRIMARY KEY (`achievement_id`),
  ADD KEY `fk_user_achievements_user_id` (`user_id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexen voor tabel `usersessions`
--
ALTER TABLE `usersessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `fk_user_sessions_user_id` (`user_id`);

--
-- Indexen voor tabel `user_eco_recommendations_completed`
--
ALTER TABLE `user_eco_recommendations_completed`
  ADD PRIMARY KEY (`user_id`,`recommendation_id`);

--
-- Indexen voor tabel `user_event_registrations`
--
ALTER TABLE `user_event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `user_event_unique` (`user_id`,`event_id`),
  ADD KEY `fk_event_event` (`event_id`);

--
-- Indexen voor tabel `user_followers`
--
ALTER TABLE `user_followers`
  ADD PRIMARY KEY (`follower_id`,`following_id`),
  ADD KEY `fk_user_followers_following` (`following_id`);

--
-- Indexen voor tabel `user_weekly_challenges_completed`
--
ALTER TABLE `user_weekly_challenges_completed`
  ADD PRIMARY KEY (`user_id`,`challenge_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `community_messages`
--
ALTER TABLE `community_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT voor een tabel `compensationprojects`
--
ALTER TABLE `compensationprojects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT voor een tabel `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT voor een tabel `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT voor een tabel `faq`
--
ALTER TABLE `faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT voor een tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT voor een tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT voor een tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT voor een tabel `travelhistory`
--
ALTER TABLE `travelhistory`
  MODIFY `travel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=316;

--
-- AUTO_INCREMENT voor een tabel `user_event_registrations`
--
ALTER TABLE `user_event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `community_messages`
--
ALTER TABLE `community_messages`
  ADD CONSTRAINT `fk_community_messages_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_community_parent_message` FOREIGN KEY (`parent_message_id`) REFERENCES `community_messages` (`message_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_project_id` FOREIGN KEY (`project_id`) REFERENCES `compensationprojects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donations_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_project` FOREIGN KEY (`project_id`) REFERENCES `compensationprojects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `user_eco_recommendations_completed`
--
ALTER TABLE `user_eco_recommendations_completed`
  ADD CONSTRAINT `fk_user_eco_recommendations` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `user_event_registrations`
--
ALTER TABLE `user_event_registrations`
  ADD CONSTRAINT `fk_event_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `user_followers`
--
ALTER TABLE `user_followers`
  ADD CONSTRAINT `fk_user_followers_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_followers_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `user_weekly_challenges_completed`
--
ALTER TABLE `user_weekly_challenges_completed`
  ADD CONSTRAINT `fk_user_weekly_challenges` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
