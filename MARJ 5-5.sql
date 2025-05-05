-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 05, 2025 at 04:18 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marj`
--
CREATE DATABASE IF NOT EXISTS `marj` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `marj`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Main Dishes'),
(2, 'Sides'),
(3, 'Desserts'),
(4, 'Beverages'),
(5, 'Packages');

-- --------------------------------------------------------

--
-- Table structure for table `catering_orders`
--

DROP TABLE IF EXISTS `catering_orders`;
CREATE TABLE IF NOT EXISTS `catering_orders` (
  `catering_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `num_persons` int NOT NULL,
  `venue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `occasion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `needs_tablesandchairs` tinyint(1) DEFAULT '0',
  `needs_setup` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `needs_decoration` tinyint(1) DEFAULT '0',
  `menu_package` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `special_requests` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`catering_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `catering_orders`
--

INSERT INTO `catering_orders` (`catering_id`, `user_id`, `full_name`, `phone`, `email`, `event_date`, `num_persons`, `venue`, `occasion`, `needs_tablesandchairs`, `needs_setup`, `needs_decoration`, `menu_package`, `special_requests`, `total_amount`, `payment_method`, `status`, `created_at`) VALUES
(1, 10, 'Jasper Sergio', '09288231320', '', '2025-05-17 02:30:00', 20, '112\r\n12he', 'asd', 0, '0', 1, '', '', 3500.00, 'pending', 'pending', '2025-05-05 03:05:18'),
(2, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 14:30:00', 20, '112\r\n12he', 'bday', 1, '0', 0, '', '', 2000.00, 'pending', 'pending', '2025-05-05 03:14:08'),
(3, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 13:30:00', 20, '112\r\n12he', 'bday', 0, NULL, 0, '0', 'something', 2000.00, 'pending', 'pending', '2025-05-05 03:19:50'),
(4, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 14:00:00', 20, '112\r\n12he', 'bday', 1, NULL, 1, '0', '', 15500.00, 'pending', 'pending', '2025-05-05 03:25:34'),
(5, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 11:31:00', 20, '112\r\n12he', 'asd', 0, NULL, 0, '0', '', 7000.00, 'pending', 'pending', '2025-05-05 03:31:57'),
(6, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 13:45:00', 20, '112\r\n12he', 'asd', 0, '1', 0, '0', 'asd', 15000.00, 'pending', 'pending', '2025-05-05 03:41:33'),
(7, 10, 'Jasper Sergio', '09288231320', '', '2025-05-05 14:50:00', 32, '12he', 'asd', 0, '1', 0, 'Basic Filipino Package', '', 10000.00, 'cash', 'pending', '2025-05-05 03:45:23'),
(8, 10, 'Jasper  Sergio', '09288231320', 'jasper.sergio@adamson.edu.ph', '2025-05-05 14:30:00', 10, '112\r\n12he', 'asd', 0, '1', 0, 'Basic Filipino Package', 'aaa', 4500.00, 'cash', 'pending', '2025-05-05 04:15:54'),
(9, 10, 'Jasper  Sergio', '09288231320', 'jasper.sergio@adamson.edu.ph', '2025-05-05 14:30:00', 10, '112\r\n12he', 'asd', 0, '1', 0, 'Basic Filipino Package', 'aaa', 4500.00, 'cash', 'pending', '2025-05-05 04:18:01');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE IF NOT EXISTS `ingredients` (
  `ingredient_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `type_id` int DEFAULT NULL,
  PRIMARY KEY (`ingredient_id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`, `unit`, `type_id`) VALUES
(1, 'Pork', 'kg', 1),
(2, 'Toyo', 'oz', 9),
(3, 'Sitaw', 'kg', 3);

-- --------------------------------------------------------

--
-- Table structure for table `ingredient_types`
--

DROP TABLE IF EXISTS `ingredient_types`;
CREATE TABLE IF NOT EXISTS `ingredient_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ingredient_types`
--

INSERT INTO `ingredient_types` (`type_id`, `type_name`) VALUES
(1, 'Meat'),
(2, 'Seafood'),
(3, 'Vegetables'),
(4, 'Fruits'),
(5, 'Dairy'),
(6, 'Eggs'),
(7, 'Pasta & Noodles'),
(8, 'Rice & Grains'),
(9, 'Sauces & Condiments'),
(10, 'Spices & Seasoning'),
(11, 'Baked Goods'),
(12, 'Sweets & Desserts'),
(13, 'Beverage Components'),
(15, 'Others'),
(14, 'Alcohol');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `notes` text,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '50.00',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `full_name`, `phone`, `address`, `notes`, `payment_method`, `total_amount`, `delivery_fee`, `status`, `created_at`) VALUES
(1, 2, 'Cruz', '09617616907', '356 Adarna St. ', '', '0', 850.00, 50.00, 'pending', '2025-04-17 14:13:37'),
(2, 2, 'Cruz', '09617616907', '356 Adarna St. ', '', '0', 450.00, 50.00, 'pending', '2025-04-17 14:18:23'),
(3, 2, 'Cruzdasdsa', '09617616907', '356 Adarna St. ', '', '0', 650.00, 50.00, 'pending', '2025-04-17 14:23:55'),
(4, 4, 'emi ', '1213213', 'gg', '', '0', 1850.00, 50.00, 'pending', '2025-04-17 14:29:31'),
(5, 14, 'Jasper Sergio', '09288231320', '112\n12he', '', '0', 710.00, 50.00, 'pending', '2025-04-17 16:02:16'),
(6, 10, 'Jasper Sergio', '09288231320', '112\n12he', '', '0', 170.00, 50.00, 'pending', '2025-04-18 13:10:11'),
(7, 10, 'Jasper Sergio', '09288231320', '112\n12he', '', '0', 450.00, 50.00, 'pending', '2025-04-18 15:25:13'),
(8, 11, 'Luigi Rey', '123', 'Taguig', '', '0', 2250.00, 50.00, 'pending', '2025-04-18 15:33:00'),
(9, 10, 'Jasper Sergio', '09288231320', '112\n12he', '', '0', 1850.00, 50.00, 'pending', '2025-04-19 07:03:17'),
(10, 12, 'Patricia Joy Relente Sergio', '214', 'here', '', '0', 2250.00, 50.00, 'pending', '2025-04-19 07:09:18'),
(11, 10, 'Jasper Sergio', '09288231320', '112\n12he', 'a', '0', 1680.00, 50.00, 'pending', '2025-05-05 03:06:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_checklist`
--

DROP TABLE IF EXISTS `order_checklist`;
CREATE TABLE IF NOT EXISTS `order_checklist` (
  `order_id` int DEFAULT NULL,
  `ingredient_id` int DEFAULT NULL,
  `quantity_needed` decimal(10,2) NOT NULL,
  `is_ready` tinyint(1) DEFAULT '0',
  `checked_by` int DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  KEY `order_id` (`order_id`),
  KEY `ingredient_id` (`ingredient_id`),
  KEY `checked_by` (`checked_by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 9, 2, 6, 220.00),
(2, 9, 3, 4, 120.00),
(3, 10, 2, 10, 220.00),
(4, 11, 7, 1, 650.00),
(5, 11, 4, 2, 80.00),
(6, 11, 6, 1, 450.00),
(7, 11, 5, 1, 250.00),
(8, 11, 3, 1, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `prod_name` varchar(250) NOT NULL,
  `prod_price` int NOT NULL,
  `prod_desc` varchar(2000) NOT NULL,
  `prod_img` varchar(250) NOT NULL,
  `prod_cat_id` int NOT NULL,
  `qty_sold` int NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `prod_name`, `prod_price`, `prod_desc`, `prod_img`, `prod_cat_id`, `qty_sold`) VALUES
(1, 'Adobo', 180, 'Classic Filipino dish with chicken or pork marinated in vinegar, soy sauce, and spices.', 'adobs.jpg', 1, 0),
(2, 'Sinigang', 220, 'Sour soup with pork, shrimp, or fish and various vegetables.', 'sinigang.jpg', 1, 16),
(3, 'Lumpia', 120, 'Filipino spring rolls filled with ground meat and vegetables.', 'lumpia.jpg', 2, 5),
(4, 'Calamansi Juice', 80, 'Refreshing Filipino citrus juice similar to lemonade.', 'CJ.jpg', 4, 2),
(5, 'Basic Filipino Package', 250, 'Budget-friendly Filipino classics including rice, 2 main dishes, 1 vegetable dish, and dessert', 'basic_package.jpg', 5, 1),
(6, 'Premium Filipino Package', 450, 'Premium selection with rice, 3 main dishes, 2 vegetable dishes, soup, and dessert', 'premium_package.jpg', 5, 1),
(7, 'Executive Package', 650, 'Luxury package with rice, 4 main dishes, 2 vegetable dishes, soup, appetizers, and premium desserts', 'executive_package.jpg', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_ingredients`
--

DROP TABLE IF EXISTS `product_ingredients`;
CREATE TABLE IF NOT EXISTS `product_ingredients` (
  `product_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  PRIMARY KEY (`product_id`,`ingredient_id`),
  KEY `ingredient_id` (`ingredient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_ingredients`
--

INSERT INTO `product_ingredients` (`product_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, 1.00),
(2, 1, 1.00),
(3, 1, 0.25);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(250) NOT NULL,
  `mname` varchar(250) DEFAULT NULL,
  `lname` varchar(250) NOT NULL,
  `email_add` varchar(250) NOT NULL,
  `mobile_num` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `emailv` enum('Yes','No') NOT NULL,
  `OTPC` int DEFAULT NULL,
  `OTPE` datetime DEFAULT NULL,
  `role_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fname`, `mname`, `lname`, `email_add`, `mobile_num`, `password`, `emailv`, `OTPC`, `OTPE`, `role_id`, `created_at`) VALUES
(1, 'James', '', 'Dayao', 'james.dayao@adamson.edu.ph', '1', '$2y$10$5cHx59VZ2XEPA2Dc3KZRwO7afxBtdryn/h6sAgFsKGAJDmGBoKF5y', 'Yes', 0, NULL, 1, '2025-04-17 04:33:47'),
(2, 'Justin', '', 'Cruz', 'justin.cruz876@adamson.edu.ph', '2', '$2y$10$qkJe1UaqJFzYZT7CX6DnwO7s96rroLk.mFRNHXF8/QL7GomWLJ8dW', 'Yes', 0, NULL, 2, '2025-04-17 04:33:47'),
(3, 'test', '', 'test', 'test', '3', '$2y$10$IA9WMahntUWGuUIz0USCN.oKxsp33i3EY0VCgo.7C2CeD2k29zE/e', 'Yes', 0, NULL, 2, '2025-04-17 04:33:47'),
(4, 'Emin', 'De Velleres', 'Imura', 'aaron@gmail.com', '12345', '$2y$10$OS.X5i.cTXtTxHP.NV3tqO6YHBIxW/6AhWcG4XWTq0YJsMpnHGyBm', 'Yes', 0, NULL, 2, '2025-04-17 04:33:47'),
(8, 'nigga', '', 'bobo', 'aaronpaulmugot@gmail.com', '12345678', '$2y$10$hf4JM1oiaoME33lz8VCLlOjyL5wy.xOn9H03nIrwGhR2bWEs0qwna', 'Yes', NULL, NULL, 2, '2025-04-17 04:33:47'),
(10, 'Jasper', '', 'Sergio', 'jasper.sergio@adamson.edu.ph', '09288231320', '$2y$10$4.kF8g/jQQLLHy6bkOabX.D0sT5FX3TxUrXTKEz2NHVq/4btt1Hc2', 'Yes', NULL, NULL, 2, '2025-04-18 13:05:02'),
(11, 'Luigi', 'Revelar', 'Rey', 'luigi.rey@adamson.edu.ph', '123', '$2y$10$mTADm1dPOU/WLJAeAYYK0uX.F9Lhpt7kyEqHE/wE4hRzTvdTk2Ixa', 'Yes', NULL, NULL, 2, '2025-04-18 15:30:10'),
(12, 'Patricia Joy', 'Cuizon', 'Relente', 'patriciarelente03@gmail.com', '214', '$2y$10$1MmGxud0Y3Cbs3oxJR99EuahgWieGYFvkGhS.ZqjOIKL3wpcruU22', 'Yes', NULL, NULL, 2, '2025-04-19 07:07:39'),
(13, 'Jasper', '', 'Sergio', 'jdeguzmansergio@gmail.com', '09677455508', '$2y$10$cajWsGkRZNHK7UC1Z27keeeKjyulqCDUMrQLF5Qqm.9qy7xwqsxaG', 'Yes', NULL, NULL, 1, '2025-04-28 09:06:27');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

DROP TABLE IF EXISTS `user_cart`;
CREATE TABLE IF NOT EXISTS `user_cart` (
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`user_id`, `product_id`, `quantity`, `added_at`) VALUES
(11, 2, 20, '2025-04-18 15:51:52'),
(12, 3, 100, '2025-04-19 07:13:51');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
