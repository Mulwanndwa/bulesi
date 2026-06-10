-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2026 at 07:12 AM
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

INSERT INTO `auth_users` (`id`, `username`, `group_id`, `company_id`, `email`, `password`, `is_active`, `last_login`, `remember_token`, `api_token`, `push_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 1, NULL, 'admin@mulwai.za', '$2y$10$STzCyR5RLF8mPAWm0o3M1Org2M96u4LauoanT768iVPgKxeCLhqf.', 1, '2026-06-10 06:59:54', NULL, '96e79602c36278b2799800c79276a48c933466713930194bd59cc1c3b42534f1', NULL, '2026-05-26 04:48:02', '2026-06-10 06:59:54'),
(3, 'staff', 3, 1, 'ndivho.mulwanndwa@gmail.com', '$2y$10$xgnd9dtbkGkW5NBw5/9b8eH3nSlgLMzsSezanAr/jY7s6KMP2MCWK', 1, '2026-05-26 05:24:32', NULL, NULL, NULL, '2026-05-26 05:24:18', '2026-05-31 10:01:13'),
(4, 'Cashier', 5, 5, 'cash@m.co.za', '$2y$10$0c7B8dIjURkiGT89y9UrBedW/QUU0pXSWHb64OaS7hpuoOaZmDG0G', 1, '2026-05-31 12:23:38', NULL, '2ce3743211e5b19e817812b366612cb73076e0abed618819323806ca393e4a32', NULL, '2026-05-31 08:52:16', '2026-05-31 12:23:38');

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
(7, 'QT-2026-0007', '47720c19', 1, 4, 'Walk-in Customer', '', '', 'POS Sale', 'completed', '280.00', '15.00', '42.00', '322.00', '2026-05-31', NULL, '', 'data:image/png;base64,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', 'Walk-in Customer', '2026-06-10 07:05:49', 0, NULL, NULL, NULL, NULL, '2026-05-31 09:45:36', '2026-06-10 07:05:49');

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
(86, 7, 'Brake Pads — Front', 'set', '1.00', '280.00', '280.00', 0);

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
-- Indexes for table `house_plans`
--
ALTER TABLE `house_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_plan_user` (`user_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `house_plans`
--
ALTER TABLE `house_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

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
-- Constraints for table `house_plans`
--
ALTER TABLE `house_plans`
  ADD CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`);

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
