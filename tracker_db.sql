-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 26, 2026 at 06:22 AM
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
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_users`
--

INSERT INTO `auth_users` (`id`, `username`, `group_id`, `email`, `password`, `is_active`, `last_login`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 1, 'admin@mulwai.za', '$2y$10$STzCyR5RLF8mPAWm0o3M1Org2M96u4LauoanT768iVPgKxeCLhqf.', 1, '2026-05-26 06:01:53', NULL, '2026-05-26 04:48:02', '2026-05-26 06:01:53'),
(3, 'staff', 3, 'ndivho.mulwanndwa@gmail.com', '$2y$10$xgnd9dtbkGkW5NBw5/9b8eH3nSlgLMzsSezanAr/jY7s6KMP2MCWK', 1, '2026-05-26 05:24:32', NULL, '2026-05-26 05:24:18', '2026-05-26 05:24:32');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `quote_number` varchar(20) NOT NULL,
  `type_id` smallint(5) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
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

INSERT INTO `quotations` (`id`, `quote_number`, `type_id`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `description`, `status`, `subtotal`, `vat_rate`, `vat_amount`, `total`, `quote_date`, `valid_until`, `notes`, `image_1`, `image_2`, `image_3`, `image_4`, `created_at`, `updated_at`) VALUES
(1, 'QT-2026-0001', 1, 1, 'Mpho Mulwanndwa', '0742414294', 'mulwanndwa.mpho@gmail.com', '', 'draft', '1900.00', '15.00', '285.00', '2185.00', '2026-05-26', '2026-05-30', '', 'uploads/quotations/sample_1.jpg', 'uploads/quotations/sample_2.jpg', 'uploads/quotations/sample_3.jpg', 'uploads/quotations/sample_4.jpg', '2026-05-26 05:07:07', '2026-05-26 06:12:38');

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
(9, 1, 'major service', '', '1.00', '1000.00', '1000.00', 0),
(10, 1, 'oil change', '', '1.00', '400.00', '400.00', 1),
(11, 1, 'filters', '', '1.00', '200.00', '200.00', 2),
(12, 1, 'diagnosis', '', '1.00', '300.00', '300.00', 3);

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
(1, 6, 'STK-0001', 'Cashier 1', '', 'pcs', '0.00', '0.00', '0.00', '0.00', '', 1, '2026-05-26 05:54:15', '2026-05-26 05:54:15');

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
(4, 'Viewer', 'Read-only access', '2026-05-26 05:15:19');

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
  ADD KEY `fk_user_group` (`group_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quote_number` (`quote_number`),
  ADD KEY `fk_quote_user` (`user_id`),
  ADD KEY `fk_quot_type` (`type_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_users`
--
ALTER TABLE `auth_users`
  ADD CONSTRAINT `fk_user_group` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `fk_quot_type` FOREIGN KEY (`type_id`) REFERENCES `quotation_types` (`id`),
  ADD CONSTRAINT `fk_quote_user` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`);

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
