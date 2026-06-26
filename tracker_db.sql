-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 26, 2026 at 08:50 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tracker_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_users`
--

CREATE TABLE `auth_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(75) DEFAULT NULL,
  `last_name` varchar(75) DEFAULT NULL,
  `group_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `api_token` varchar(64) DEFAULT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_users`
--

INSERT INTO `auth_users` (`id`, `username`, `first_name`, `last_name`, `group_id`, `company_id`, `email`, `password`, `is_active`, `last_login`, `remember_token`, `api_token`, `push_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Bulesi', 'P', 1, NULL, 'admin@mulwai.za', '$2y$10$STzCyR5RLF8mPAWm0o3M1Org2M96u4LauoanT768iVPgKxeCLhqf.', 1, '2026-06-26 08:25:23', NULL, '96e79602c36278b2799800c79276a48c933466713930194bd59cc1c3b42534f1', NULL, '2026-05-26 04:48:02', '2026-06-26 08:25:23'),
(3, 'staff', 'Mpho', 'M', 3, 1, 'ndivho.mulwanndwa@gmail.com', '$2y$10$xgnd9dtbkGkW5NBw5/9b8eH3nSlgLMzsSezanAr/jY7s6KMP2MCWK', 1, '2026-05-26 05:24:32', NULL, NULL, NULL, '2026-05-26 05:24:18', '2026-06-22 15:29:00'),
(4, 'Cashier', NULL, NULL, 5, 5, 'cash@m.co.za', '$2y$10$I5xfcNgTB.W6X6e9SiiI6OmKsZ34u9OZRNAxom0etxY9FQospwaBa', 1, '2026-06-12 17:20:42', NULL, '2ce3743211e5b19e817812b366612cb73076e0abed618819323806ca393e4a32', NULL, '2026-05-31 08:52:16', '2026-06-12 17:20:42'),
(5, 'mulwanndwa.mpho@gmail.com', 'Mpho', 'Mulwanndwa', 2, 5, 'mulwanndwa.mpho@gmail.com', '$2y$10$6vbpnMN0r1IAczsn5O.lyeXOzV5dozgIW.jPJfVjiyW3YuoVORfSG', 1, NULL, NULL, '69ed7c82a56d3629f7f61de0329c90b480c24facbfe3f267d2125aff33ec0796', NULL, '2026-06-22 15:27:09', '2026-06-22 15:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `phone`, `email`, `logo`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Demo Construction', '12 Industrial Road, Johannesburg, 2000', '+27 11 123 4567', 'info@mulwaiconstruction.co.za', NULL, 1, '2026-05-31 09:57:36', '2026-06-08 15:38:50'),
(5, 'Demo Properties', '88 Nelson Rd, Polokwane, 0699', '+27 15 222 3344', 'admin@dlaminiproperties.co.za', 'uploads/companies/company_1780218129_9d008134.png', 1, '2026-05-31 09:57:36', '2026-06-08 15:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `created_at`, `updated_at`) VALUES
(1, '2026-06-22 14:29:12', '2026-06-22 14:52:17'),
(2, '2026-06-22 14:29:31', '2026-06-22 14:36:57'),
(3, '2026-06-22 15:29:45', '2026-06-22 15:29:52'),
(4, '2026-06-22 15:30:11', '2026-06-26 08:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `last_read_at` datetime DEFAULT NULL,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_participants`
--

INSERT INTO `conversation_participants` (`id`, `conversation_id`, `user_id`, `last_read_at`, `joined_at`) VALUES
(1, 1, 1, '2026-06-22 15:22:44', '2026-06-22 14:29:12'),
(2, 1, 4, NULL, '2026-06-22 14:29:12'),
(3, 2, 1, '2026-06-22 14:51:23', '2026-06-22 14:29:31'),
(4, 2, 3, NULL, '2026-06-22 14:29:31'),
(5, 3, 5, '2026-06-22 15:56:23', '2026-06-22 15:29:45'),
(6, 3, 1, NULL, '2026-06-22 15:29:45'),
(7, 4, 5, '2026-06-26 08:47:10', '2026-06-22 15:30:11'),
(8, 4, 3, NULL, '2026-06-22 15:30:11');

-- --------------------------------------------------------

--
-- Table structure for table `house_plans`
--

CREATE TABLE `house_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT 'Untitled Plan',
  `plan_data` longtext DEFAULT NULL COMMENT 'JSON shapes array',
  `grid_size` tinyint(3) UNSIGNED NOT NULL DEFAULT 20,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `house_plans`
--

INSERT INTO `house_plans` (`id`, `user_id`, `title`, `plan_data`, `grid_size`, `created_at`, `updated_at`) VALUES
(1, 1, 'Untitled Plan', '[{\"type\":\"room\",\"x\":200,\"y\":200,\"w\":200,\"h\":200,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":400,\"y\":120,\"w\":200,\"h\":180,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":400,\"y\":360,\"w\":200,\"h\":180,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":660,\"y\":400,\"w\":200,\"h\":200,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":600,\"y\":140,\"w\":220,\"h\":180,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":440,\"y\":540,\"w\":220,\"h\":40,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"room\",\"x\":240,\"y\":400,\"w\":140,\"h\":40,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"door\",\"hx\":540,\"hy\":540,\"r\":40,\"ang\":-1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":500,\"hy\":360,\"r\":40,\"ang\":-1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":500,\"hy\":300,\"r\":40,\"ang\":-1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":720,\"hy\":320,\"r\":40,\"ang\":-1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":820,\"hy\":340,\"r\":40,\"ang\":0,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":720,\"hy\":400,\"r\":40,\"ang\":1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":720,\"hy\":420,\"r\":40,\"ang\":0,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":400,\"hy\":320,\"r\":40,\"ang\":3.141592653589793,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"door\",\"hx\":280,\"hy\":400,\"r\":40,\"ang\":-1.5707963267948966,\"dir\":1,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":200,\"y1\":260,\"x2\":220,\"y2\":260,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":220,\"y1\":260,\"x2\":220,\"y2\":340,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":220,\"y1\":340,\"x2\":200,\"y2\":340,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":260,\"y1\":200,\"x2\":260,\"y2\":220,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":260,\"y1\":220,\"x2\":340,\"y2\":220,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":340,\"y1\":220,\"x2\":340,\"y2\":200,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":440,\"y1\":120,\"x2\":440,\"y2\":140,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":440,\"y1\":140,\"x2\":560,\"y2\":140,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":560,\"y1\":140,\"x2\":560,\"y2\":120,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":680,\"y1\":160,\"x2\":660,\"y2\":160,\"color\":\"#1a1a2e\"},{\"type\":\"room\",\"x\":820,\"y\":240,\"w\":220,\"h\":160,\"color\":\"#1a1a2e\",\"fill\":\"#dbeafe\",\"opacity\":25,\"lw\":3,\"label\":\"\"},{\"type\":\"window\",\"x1\":1040,\"y1\":260,\"x2\":1020,\"y2\":260,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":1020,\"y1\":260,\"x2\":1020,\"y2\":380,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":1020,\"y1\":380,\"x2\":1040,\"y2\":380,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":880,\"y1\":240,\"x2\":880,\"y2\":260,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":880,\"y1\":260,\"x2\":980,\"y2\":260,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":980,\"y1\":260,\"x2\":980,\"y2\":240,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":680,\"y1\":160,\"x2\":760,\"y2\":160,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":760,\"y1\":160,\"x2\":760,\"y2\":140,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":660,\"y1\":160,\"x2\":660,\"y2\":140,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":400,\"y1\":420,\"x2\":420,\"y2\":420,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":420,\"y1\":420,\"x2\":420,\"y2\":500,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":420,\"y1\":500,\"x2\":400,\"y2\":500,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":720,\"y1\":600,\"x2\":720,\"y2\":580,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":720,\"y1\":580,\"x2\":800,\"y2\":580,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":800,\"y1\":580,\"x2\":800,\"y2\":600,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":860,\"y1\":460,\"x2\":820,\"y2\":460,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":820,\"y1\":460,\"x2\":820,\"y2\":540,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":820,\"y1\":540,\"x2\":880,\"y2\":540,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":620,\"y1\":540,\"x2\":620,\"y2\":520,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":620,\"y1\":520,\"x2\":640,\"y2\":520,\"color\":\"#1a1a2e\"},{\"type\":\"window\",\"x1\":640,\"y1\":520,\"x2\":640,\"y2\":540,\"color\":\"#1a1a2e\"},{\"type\":\"roof\",\"x\":400,\"y\":120,\"w\":200,\"h\":180,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":200,\"y\":200,\"w\":200,\"h\":200,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":240,\"y\":400,\"w\":140,\"h\":40,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":400,\"y\":360,\"w\":200,\"h\":180,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":440,\"y\":540,\"w\":220,\"h\":40,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":400,\"y\":300,\"w\":200,\"h\":60,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":600,\"y\":320,\"w\":220,\"h\":80,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":600,\"y\":400,\"w\":60,\"h\":140,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":660,\"y\":400,\"w\":200,\"h\":200,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":600,\"y\":140,\"w\":220,\"h\":180,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10},{\"type\":\"roof\",\"x\":820,\"y\":240,\"w\":220,\"h\":160,\"color\":\"#1a1a2e\",\"roof_type\":\"gable\",\"pitch\":30,\"direction\":\"ew\",\"overhang\":10}]', 20, '2026-05-26 07:18:13', '2026-05-26 07:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `quote_id` int(10) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `body`, `quote_id`, `image_path`, `created_at`) VALUES
(1, 2, 1, 'testing', NULL, NULL, '2026-06-22 14:36:57'),
(2, 1, 1, 'testing', NULL, NULL, '2026-06-22 14:37:17'),
(3, 1, 1, 'see this', 9, NULL, '2026-06-22 14:40:45'),
(4, 1, 1, 'QT-2026-0010', 10, NULL, '2026-06-22 14:52:17'),
(5, 3, 5, 'testing', NULL, NULL, '2026-06-22 15:29:52'),
(6, 4, 5, 'test', NULL, NULL, '2026-06-22 15:30:15'),
(7, 4, 5, 'hey see', NULL, 'uploads/chat/chat_4_1782455881_496c1ae6.png', '2026-06-26 08:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `quote_number` varchar(20) NOT NULL,
  `public_token` varchar(64) DEFAULT NULL,
  `type_id` smallint(5) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','sent','accepted','rejected','in_progress','completed','invoiced','cancelled') NOT NULL DEFAULT 'draft',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quote_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cust_sig_data` mediumtext DEFAULT NULL,
  `cust_sig_name` varchar(150) DEFAULT NULL,
  `cust_signed_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `image_4` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quote_number`, `public_token`, `type_id`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `description`, `status`, `subtotal`, `vat_rate`, `vat_amount`, `total`, `quote_date`, `valid_until`, `notes`, `cust_sig_data`, `cust_sig_name`, `cust_signed_at`, `is_read`, `image_1`, `image_2`, `image_3`, `image_4`, `created_at`, `updated_at`) VALUES
(1, 'QT-2026-0001', '4740bc0d', 1, 1, 'Mpho Mulwanndwa', '0742414294', 'mulwanndwa.mpho@gmail.com', '', 'sent', '1900.00', '15.00', '285.00', '2185.00', '2026-05-27', '2026-06-10', 'Updated via API', NULL, NULL, NULL, 0, 'uploads/quotations/0f9691e2032e6b308f110a98868f7f1f.png', 'uploads/quotations/sample_2.jpg', 'uploads/quotations/sample_3.jpg', 'uploads/quotations/sample_4.jpg', '2026-05-26 05:07:07', '2026-06-10 07:00:43'),
(2, 'QT-2026-0002', 'c01d7c43', 1, 1, 'Bulesi', '022000000', '', '', 'draft', '398.00', '15.00', '59.70', '457.70', '2026-05-27', '2026-05-28', '', NULL, NULL, NULL, 0, 'uploads/quotations/quote_2_1_17798851170.png', NULL, NULL, NULL, '2026-05-27 11:08:21', '2026-06-10 07:00:43'),
(3, 'QT-2026-0003', 'ead139ab', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '220.00', '15.00', '33.00', '253.00', '2026-05-31', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-05-31 09:29:08', '2026-06-10 07:00:43'),
(4, 'QT-2026-0004', '55bdac15', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '65.00', '15.00', '9.75', '74.75', '2026-05-31', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-05-31 09:30:29', '2026-06-10 07:00:43'),
(5, 'QT-2026-0005', 'ec40afeb', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '220.00', '15.00', '33.00', '253.00', '2026-05-31', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-05-31 09:32:01', '2026-06-10 07:00:43'),
(6, 'QT-2026-0006', '9c0a6bde', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '490.00', '15.00', '73.50', '563.50', '2026-05-31', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-05-31 09:43:32', '2026-06-10 07:00:43'),
(7, 'QT-2026-0007', '47720c19', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '280.00', '15.00', '42.00', '322.00', '2026-05-31', NULL, '', 'data:image/png;base64,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', 'Walk-in Customer', '2026-06-10 07:05:49', 0, NULL, NULL, NULL, NULL, '2026-05-31 09:45:36', '2026-06-10 07:05:49'),
(8, 'QT-2026-0008', '73e083828bc27fafd9b712e997e0d3e6b5541de196960962', 5, 1, 'Mpho Mulwanndwa', '0742414294', 'mulwanndwa.mpho@gmail.com', '', 'draft', '120.00', '15.00', '18.00', '138.00', '2026-06-12', NULL, '', 'data:image/png;base64,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', 'Mpho Mulwanndwa', '2026-06-15 05:05:24', 0, NULL, NULL, NULL, NULL, '2026-06-12 17:16:22', '2026-06-15 05:05:24'),
(9, 'QT-2026-0009', 'cdd24d1b4efddf0b5816031c82403ed405ab3b916d023a6b', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '220.00', '15.00', '33.00', '253.00', '2026-06-12', NULL, '', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-06-12 17:21:18', '2026-06-22 14:26:31'),
(10, 'QT-2026-0010', 'fc4e7c92c7b789da74ed870aa7ea020a6ccfd79f7f98bfcf', 1, 1, 'test', '0777777777', '', '', 'draft', '0.00', '15.00', '0.00', '0.00', '2026-06-22', '2026-06-24', '', NULL, NULL, NULL, 0, 'uploads/quotations/quote_10_1_17821327370.png', NULL, NULL, NULL, '2026-06-22 14:52:17', '2026-06-22 14:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `quotation_id` int(10) UNSIGNED NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `unit` varchar(30) DEFAULT NULL COMMENT 'e.g. meters, hours, piece, kg',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `item_description`, `unit`, `quantity`, `unit_price`, `line_total`, `sort_order`) VALUES
(71, 1, 'Major service', '', '1.00', '1000.00', '1000.00', 0),
(72, 1, 'Oil change', '', '1.00', '400.00', '400.00', 1),
(73, 1, 'Filters', '', '1.00', '200.00', '200.00', 2),
(74, 1, 'Diagnosis', '', '1.00', '300.00', '300.00', 3),
(75, 2, 'Item 1', '', '101.00', '1.00', '101.00', 0),
(76, 2, 'Item 2', '', '200.00', '1.00', '200.00', 1),
(77, 2, 'Item 3', '', '100.00', '0.97', '97.00', 2),
(78, 3, 'Red Oxide Primer 5L', 'tin', '1.00', '220.00', '220.00', 0),
(79, 4, 'Oil Filter', 'pcs', '1.00', '65.00', '65.00', 0),
(80, 5, 'Red Oxide Primer 5L', 'tin', '1.00', '220.00', '220.00', 0),
(81, 6, 'Nut M10 (x100)', 'box', '1.00', '48.00', '48.00', 0),
(82, 6, 'Hex Bolt M10x50 (x50)', 'box', '1.00', '75.00', '75.00', 1),
(83, 6, 'Welding Rods 2.5mm (5kg)', 'kg', '1.00', '185.00', '185.00', 2),
(84, 6, 'Grinding Disc 115mm (x25)', 'box', '1.00', '150.00', '150.00', 3),
(85, 6, 'Spring Washer M10 (x100)', 'box', '1.00', '32.00', '32.00', 4),
(86, 7, 'Brake Pads — Front', 'set', '1.00', '280.00', '280.00', 0),
(87, 8, 'Test items', '1', '1.00', '100.00', '100.00', 0),
(88, 8, 'oil change', '', '1.00', '10.00', '10.00', 1),
(89, 8, 'filters', '', '1.00', '10.00', '10.00', 2),
(90, 9, 'Red Oxide Primer 5L', 'tin', '1.00', '220.00', '220.00', 0),
(91, 10, 'test', '2', '1.00', '0.00', '0.00', 0),
(92, 10, 'test 2', '1', '1.00', '0.00', '0.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_types`
--

CREATE TABLE `quotation_types` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_types`
--

INSERT INTO `quotation_types` (`id`, `name`, `description`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Vehicle', NULL, 1, 1, '2026-05-26 05:44:02'),
(2, 'Steel Work', NULL, 1, 2, '2026-05-26 05:44:02'),
(3, 'Delivery', NULL, 1, 3, '2026-05-26 05:44:02'),
(4, 'Building', NULL, 1, 4, '2026-05-26 05:44:02'),
(5, 'House Plan', '', 1, 0, '2026-05-26 06:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `stock_categories`
--

CREATE TABLE `stock_categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_categories`
--

INSERT INTO `stock_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Steel & Metal', 'Angle iron, tubing, flat bar, sheet metal', '2026-05-26 05:38:57'),
(2, 'Vehicle Parts', 'Mechanical and body parts for vehicles', '2026-05-26 05:38:57'),
(3, 'Tools & Equipment', 'Hand tools, power tools, workshop equipment', '2026-05-26 05:38:57'),
(4, 'Hardware', 'Bolts, nuts, fasteners, fittings', '2026-05-26 05:38:57'),
(5, 'Paint & Finish', 'Primers, paints, coatings, thinners', '2026-05-26 05:38:57'),
(6, 'Consumables', 'Welding rods, grinding discs, sandpaper, gas', '2026-05-26 05:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(30) NOT NULL DEFAULT 'pcs',
  `quantity_on_hand` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`id`, `category_id`, `code`, `name`, `description`, `unit`, `quantity_on_hand`, `reorder_level`, `unit_cost`, `unit_price`, `location`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 6, 'STK-0001', 'Cashier 1', '', 'pcs', '0.00', '0.00', '0.00', '0.00', '', 1, '2026-05-26 05:54:15', '2026-05-26 05:54:15'),
(2, 1, 'STK-0002', 'Angle Iron 25x25x3mm', '25x25x3mm mild steel angle iron, 6m length', 'm', '48.00', '10.00', '85.00', '120.00', 'Rack A1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(3, 1, 'STK-0003', 'Flat Bar 25x6mm', '25x6mm mild steel flat bar, 6m length', 'm', '60.00', '10.00', '45.00', '70.00', 'Rack A2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(4, 1, 'STK-0004', 'Square Tubing 25x25x2mm', '25x25x2mm square hollow section, 6m length', 'm', '35.00', '8.00', '110.00', '160.00', 'Rack A3', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(5, 1, 'STK-0005', 'Sheet Metal 1.6mm', '1.6mm mild steel sheet, 2400x1200mm', 'pcs', '12.00', '3.00', '380.00', '520.00', 'Bay B1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(6, 2, 'STK-0006', 'Brake Pads — Front', 'Universal front brake pads, ceramic compound', 'set', '20.00', '5.00', '180.00', '280.00', 'Shelf C1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(7, 2, 'STK-0007', 'Oil Filter', 'Universal spin-on oil filter', 'pcs', '45.00', '10.00', '35.00', '65.00', 'Shelf C2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(8, 2, 'STK-0008', 'Spark Plugs (x4)', 'Iridium spark plugs, set of 4', 'set', '30.00', '8.00', '90.00', '150.00', 'Shelf C3', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(9, 2, 'STK-0009', 'Alternator Belt', 'Ribbed drive belt, multi-fit', 'pcs', '18.00', '4.00', '55.00', '95.00', 'Shelf C4', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(10, 3, 'STK-0010', 'Angle Grinder 115mm', '115mm angle grinder, 750W', 'pcs', '8.00', '2.00', '480.00', '750.00', 'Shelf D1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(11, 3, 'STK-0011', 'Combination Spanner Set', '12-piece metric combination spanner set', 'set', '10.00', '3.00', '220.00', '350.00', 'Shelf D2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(12, 3, 'STK-0012', 'Tap & Die Set', '40-piece metric tap and die set', 'set', '5.00', '2.00', '340.00', '520.00', 'Shelf D3', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(13, 3, 'STK-0013', 'Socket Set 1/2\" Drive', '26-piece 1/2\" drive socket set, metric', 'set', '6.00', '2.00', '390.00', '580.00', 'Shelf D4', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(14, 4, 'STK-0014', 'Hex Bolt M10x50 (x50)', 'M10x50mm hex head bolts, grade 8.8, box of 50', 'box', '40.00', '10.00', '45.00', '75.00', 'Bin E1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(15, 4, 'STK-0015', 'Nut M10 (x100)', 'M10 hex nuts, grade 8, box of 100', 'box', '40.00', '10.00', '28.00', '48.00', 'Bin E2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(16, 4, 'STK-0016', 'Spring Washer M10 (x100)', 'M10 spring washers, galvanised, box of 100', 'box', '35.00', '10.00', '18.00', '32.00', 'Bin E3', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(17, 4, 'STK-0017', 'Self-Tapping Screw 5x40', 'Pan head self-tapping screws 5x40mm, box 200', 'box', '25.00', '5.00', '32.00', '55.00', 'Bin E4', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(18, 5, 'STK-0018', 'Red Oxide Primer 5L', 'Oil-based red oxide primer, 5 litre tin', 'tin', '14.00', '3.00', '145.00', '220.00', 'Shelf F1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(19, 5, 'STK-0019', 'Hammerite Smooth Black 1L', 'Direct-to-metal smooth black paint, 1 litre', 'tin', '20.00', '5.00', '85.00', '130.00', 'Shelf F2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(20, 6, 'STK-0020', 'Welding Rods 2.5mm (5kg)', 'E6013 general purpose welding electrodes, 5kg', 'kg', '30.00', '5.00', '120.00', '185.00', 'Bin G1', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17'),
(21, 6, 'STK-0021', 'Grinding Disc 115mm (x25)', '115mm metal grinding discs, box of 25', 'box', '22.00', '5.00', '95.00', '150.00', 'Bin G2', 1, '2026-05-31 09:25:17', '2026-05-31 09:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `stock_item_id` int(10) UNSIGNED NOT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `quantity_before` decimal(10,2) NOT NULL,
  `quantity_after` decimal(10,2) NOT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Admin', 'Full system access', '2026-05-26 05:15:19'),
(2, 'Manager', 'Manage quotes and view reports', '2026-05-26 05:15:19'),
(3, 'Staff', 'Create and edit quotations', '2026-05-26 05:15:19'),
(4, 'Viewer', 'Read-only access', '2026-05-26 05:15:19'),
(5, 'Cashier', 'POS sales access on the front-end app', '2026-05-31 08:50:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_users`
--
ALTER TABLE `auth_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_group` (`group_id`),
  ADD KEY `fk_user_company` (`company_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conv_user` (`conversation_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `house_plans`
--
ALTER TABLE `house_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_plan_user` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conv` (`conversation_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `fk_msg_quote` (`quote_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quote_number` (`quote_number`),
  ADD UNIQUE KEY `public_token` (`public_token`),
  ADD KEY `fk_quot_type` (`type_id`),
  ADD KEY `fk_quote_user` (`user_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_quote` (`quotation_id`);

--
-- Indexes for table `quotation_types`
--
ALTER TABLE `quotation_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_categories`
--
ALTER TABLE `stock_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_stock_cat` (`category_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mov_item` (`stock_item_id`),
  ADD KEY `fk_mov_user` (`user_id`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_users`
--
ALTER TABLE `auth_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `house_plans`
--
ALTER TABLE `house_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `quotation_types`
--
ALTER TABLE `quotation_types`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_categories`
--
ALTER TABLE `stock_categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_users`
--
ALTER TABLE `auth_users`
  ADD CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_group` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`);

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `fk_cp_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cp_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `house_plans`
--
ALTER TABLE `house_plans`
  ADD CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_quote` FOREIGN KEY (`quote_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `auth_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `fk_quot_type` FOREIGN KEY (`type_id`) REFERENCES `quotation_types` (`id`),
  ADD CONSTRAINT `fk_quote_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `fk_item_quote` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD CONSTRAINT `fk_stock_cat` FOREIGN KEY (`category_id`) REFERENCES `stock_categories` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_mov_item` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mov_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
