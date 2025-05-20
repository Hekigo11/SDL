-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 20, 2025 at 10:27 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u693869294_marj`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `street_number` varchar(50) NOT NULL,
  `street_name` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `label` varchar(50) DEFAULT 'Home',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `user_id`, `street_number`, `street_name`, `barangay`, `city`, `province`, `zip_code`, `is_default`, `label`, `created_at`, `updated_at`) VALUES
(4, 5, '123', 'Darna', 'Bayan Bago 5', 'Bacoor', 'Cavite', '4102', 1, 'Temporary', '2025-05-19 07:48:59', '2025-05-19 07:49:37'),
(5, 8, '179', 'M.L. Quezon St.', 'New Lower Bicutan', 'Taguig City', 'NCR', '1632', 1, 'Home', '2025-05-20 05:34:01', '2025-05-20 05:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `cancellation_reasons`
--

CREATE TABLE `cancellation_reasons` (
  `id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cancellation_reasons`
--

INSERT INTO `cancellation_reasons` (`id`, `reason`) VALUES
(1, 'Changed my mind'),
(2, 'Ordered by mistake'),
(3, 'Delivery time too long'),
(4, 'Payment issues'),
(5, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Main Dishes'),
(2, 'Sides'),
(3, 'Desserts'),
(4, 'Beverages');

-- --------------------------------------------------------

--
-- Table structure for table `catering_orders`
--

CREATE TABLE `catering_orders` (
  `catering_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `num_persons` int(11) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `occasion` varchar(100) NOT NULL,
  `needs_tablesandchairs` tinyint(1) DEFAULT 0,
  `needs_setup` varchar(50) DEFAULT NULL,
  `needs_decoration` tinyint(1) DEFAULT 0,
  `menu_package` varchar(100) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `staff_notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catering_order_menu_items`
--

CREATE TABLE `catering_order_menu_items` (
  `catering_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_catering_orders`
--

CREATE TABLE `custom_catering_orders` (
  `custom_order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `event_date` datetime NOT NULL,
  `num_persons` int(11) NOT NULL,
  `venue` text NOT NULL,
  `occasion` varchar(100) NOT NULL,
  `needs_tablesandchairs` tinyint(1) DEFAULT 0,
  `needs_setup` tinyint(1) DEFAULT 0,
  `needs_decoration` tinyint(1) DEFAULT 0,
  `special_requests` text DEFAULT NULL,
  `menu_preferences` text DEFAULT NULL,
  `estimated_budget` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `staff_notes` text DEFAULT NULL,
  `quote_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cust_catering_order_items`
--

CREATE TABLE `cust_catering_order_items` (
  `custom_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`, `unit`, `type_id`) VALUES
(1, 'Pork', 'kg', 1),
(2, 'Soy Sauce', 'tbsp', 9),
(11, 'Oyster Sauce', 'tbsp', 9),
(4, 'Chicken', 'kg', 1),
(5, 'Beef', 'kg', 1),
(7, 'Broccoli', 'g', 3),
(13, 'Cornstarch', 'tbsp', 8),
(8, 'Bell Peppers', 'g', 3),
(9, 'Carrots', 'g', 3),
(10, 'Corn', 'g', 3),
(12, 'Garlic', 'g', 3),
(14, 'Sesame Oil', 'tbsp', 15),
(15, 'Black Pepper', 'tbsp', 10),
(16, 'Ham', 'g', 1),
(17, 'Cheese', 'g', 5),
(18, 'Flour', 'g', 8),
(19, 'Eggs', 'g', 6),
(20, 'Breadcrumbs', 'g', 15),
(21, 'Butter', 'g', 5),
(22, 'Salt', 'tbsp', 10),
(23, 'Oil', 'oz', 15),
(24, 'Barbecue Sauce', 'tbsp', 9),
(25, 'Onion', 'g', 3),
(26, 'Paprika', 'tbsp', 10),
(27, 'Olive Oil', 'tbsp', 15),
(28, 'Rosemary', 'g', 10),
(29, 'Cream Dory', 'kg', 2),
(30, 'Lemon Juice', 'oz', 13),
(31, 'Tartar Sauce', 'oz', 9),
(32, 'Sweet Chili Sauce', 'oz', 9),
(33, 'Thyme', 'g', 10),
(34, 'Bay Leaves', 'g', 10),
(35, 'Beef Stock', 'oz', 15),
(36, 'Worcestershire Sauce', 'tbsp', 9),
(37, 'Eggplant', 'g', 3),
(38, 'String Beans', 'g', 3),
(39, 'Pechay', 'g', 3),
(40, 'Peanut Butter', 'tbsp', 9),
(41, 'Annatto Seeds', 'tbsp', 10),
(42, 'Bagoong', 'tbsp', 9),
(43, 'Tomato Sauce', 'oz', 9),
(44, 'Potatoes', 'g', 3),
(45, 'Sugar', 'tbsp', 13),
(46, 'Calamansi Juice', 'oz', 13),
(47, 'Long-grain Rice', 'kg', 8),
(48, 'Shrimp', 'kg', 2),
(49, 'Mussels', 'kg', 2),
(50, 'Squid', 'kg', 2),
(51, 'Clams', 'kg', 2),
(52, 'Saffron', 'tbsp', 10),
(53, 'Turmeric', 'tbsp', 10),
(54, 'Chicken Stock', 'oz', 15),
(55, 'Lemon', 'g', 13),
(56, 'Spaghetti Pasta', 'g', 7),
(57, 'Heavy Cream', 'g', 5),
(58, 'Bacon', 'g', 1),
(59, 'Parsley', 'g', 10),
(60, 'Macaroni Pasta', 'g', 7),
(61, 'Ketchup', 'g', 9),
(62, 'Lasagna Noodles', 'g', 7),
(63, 'Béchamel Sauce', 'g', 9),
(64, 'Italian Seasoning', 'tbsp', 10),
(65, 'Mayonnaise', 'g', 9),
(66, 'Pickles', 'g', 3),
(67, 'Celery', 'g', 3),
(68, 'Mustard', 'tbsp', 9),
(69, 'Glass Noodles', 'g', 7),
(70, 'Spinach', 'g', 3),
(71, 'Mushrooms', 'g', 3),
(72, 'Sesame Seeds', 'tbsp', 10),
(73, 'Lumpia Wrappers', 'g', 7),
(74, 'Honey', 'oz', 13),
(75, 'Cream Cheese', 'g', 5),
(76, 'Graham Crackers', 'g', 12),
(77, 'Blueberries', 'g', 4),
(78, 'Mango', 'g', 4),
(79, 'Strawberries', 'g', 4),
(80, 'Baking Powder', 'tbsp', 12),
(81, 'Milk', 'oz', 5),
(82, 'Choux Pastry', 'g', 12),
(83, 'Pastry Cream Filling', 'g', 12),
(84, 'Powdered Sugar', 'g', 12),
(85, 'Ice', 'oz', 13),
(86, 'Banana', 'g', 4),
(87, 'Beans', 'g', 12),
(88, 'Macapuno', 'g', 12),
(89, 'Jackfruit', 'g', 4),
(90, 'Nata de Coco', 'g', 12),
(91, 'Ube', 'g', 12),
(92, 'Whipped Cream', 'g', 5),
(93, 'Vanilla', 'oz', 12),
(94, 'Buttercream', 'g', 5),
(95, 'Chocolate Chips', 'g', 12),
(96, 'Tea', 'oz', 13),
(97, 'Orange Juice', 'oz', 13),
(98, 'Grape Juice', 'oz', 13),
(99, 'Soda', 'oz', 13),
(100, 'Apples', 'g', 4),
(101, 'Oranges', 'g', 4),
(102, 'Lime', 'g', 4),
(103, 'Lime Juice', 'oz', 13),
(104, 'Water', 'oz', 13);

-- --------------------------------------------------------

--
-- Table structure for table `ingredient_types`
--

CREATE TABLE `ingredient_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 50.00,
  `scheduled_delivery` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `paymongo_link_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivery_date` datetime NOT NULL,
  `kitchen_status` enum('pending','in_kitchen','ready_for_delivery','delivering','completed','cancelled') DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `delivery_started_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `status_notes` text DEFAULT NULL,
  `status_updates` text DEFAULT NULL,
  `delivery_tracking_link` varchar(255) DEFAULT NULL,
  `checkout_url` varchar(255) DEFAULT NULL,
  `gcash_reference` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_checklist`
--

CREATE TABLE `order_checklist` (
  `order_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantity_needed` decimal(10,2) NOT NULL,
  `is_ready` tinyint(1) DEFAULT 0,
  `checked_by` int(11) DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_products`
--

CREATE TABLE `package_products` (
  `package_id` int(11) NOT NULL COMMENT 'from packages table',
  `category_id` int(11) NOT NULL COMMENT 'from categories table',
  `amount` int(11) NOT NULL COMMENT 'how many per package'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Dito ililink yung package pati category';

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `prod_name` varchar(250) NOT NULL,
  `prod_price` int(11) NOT NULL,
  `prod_desc` varchar(2000) NOT NULL,
  `prod_img` varchar(250) NOT NULL,
  `prod_cat_id` int(11) NOT NULL,
  `qty_sold` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `prod_name`, `prod_price`, `prod_desc`, `prod_img`, `prod_cat_id`, `qty_sold`) VALUES
(1, 'Beef & Vegetables', 220, 'Tender beef slices cooked with a mix of fresh vegetables in savory sauce.', '682be90d28666.jpg', 1, 0),
(2, 'Chicken Cordon Bleu', 230, 'Breaded chicken breast stuffed with ham and cheese, then fried or baked.', '682be96055a5c.jpg', 1, 0),
(3, 'Chicken & Vegetables', 200, 'Juicy chicken chunks sautéed with mixed vegetables in light seasoning.', '682be97d37876.jpg', 1, 0),
(4, 'Beef Back Ribs', 280, 'Slow-cooked ribs glazed with sweet and smoky barbecue sauce.', '682be99d006f2.jpg', 1, 0),
(5, 'Paella Marinara', 280, 'Spanish rice dish with mixed seafood, vegetables, and rich tomato-saffron flavors.', '682be9c598852.jpg', 1, 0),
(6, 'Fish Fillet', 200, 'Breaded and fried fish fillet, crisp on the outside, tender inside.', '682bebd36e414.jpg', 1, 0),
(7, '6\" Round Cake', 950, 'a beautifully decorated strawberry shortcake style, topped with fresh strawberries and neatly piped whipped cream dollops. The smooth and ridged frosting along the sides gives it a clean and elegant look.', '682bec2042cf2.png', 3, 0),
(8, 'Roast Beef Brisket', 230, 'Slow-roasted beef brisket, tender and juicy, served with rich brown gravy.', '682bec5264c6a.jpg', 1, 0),
(9, 'Halo-Halo Cups', 75, 'Layered Filipino dessert with crushed ice, sweet beans, jelly, leche flan, and milk.', '682bed0e5bac1.png', 3, 0),
(10, 'Kare-Kare', 250, 'Oxtail and vegetables in rich peanut sauce, served with shrimp paste.', '682bed848ade5.jpg', 1, 0),
(11, 'Caldereta', 210, 'Hearty beef stew in tomato sauce with bell peppers, carrots, and potatoes.', '682bedb411e8c.jpg', 1, 0),
(12, 'Beef Misono', 190, 'Japanese-style beef strips cooked in sweet-savory soy glaze.', '682bedcd8e3e2.jpg', 1, 0),
(13, 'Liempo', 190, 'Grilled or roasted pork belly with crispy skin and juicy meat.', '682bede9bcefc.jpg', 1, 0),
(14, 'Carbonara', 145, 'Creamy pasta with bacon and parmesan cheese.', '682bee1a5446b.jpg', 2, 0),
(15, 'Baked Macaroni', 135, 'Macaroni in meaty red sauce, topped with cheese and baked.', '682bee3c732a3.jpg', 2, 0),
(16, 'Baked Lasagna', 160, 'Layered pasta with meat sauce, béchamel, and melted cheese.', '682bee57a8dd0.jpg', 2, 0),
(17, 'Potato Salad', 115, 'Chilled salad with creamy mayo dressing, potatoes, and veggies.', '682bee8b7c906.jpg', 2, 0),
(18, 'Japchae', 130, 'Korean-style stir-fried glass noodles with vegetables and sesame oil.', '682beeb83c2c5.jpg', 2, 0),
(19, 'Shanghai', 120, 'Crisp mini spring rolls filled with seasoned meat.', '682beef82f48c.jpg', 2, 0),
(20, 'Chicken Fingers', 130, 'Breaded chicken strips served with dipping sauce.', '682bef277c119.jpg', 2, 0),
(21, 'Baked Spaghetti', 120, 'Sweet Filipino-style spaghetti baked with cheese on top.', '682bef405ce52.jpg', 2, 0),
(22, 'Iced Tea', 60, 'Classic lemon iced tea, cold and refreshing.', '682bef6e3f833.jpg', 4, 0),
(23, 'Orange Juice', 65, 'Freshly squeezed orange juice served chilled.', '682bef9597cc2.jpg', 4, 0),
(24, 'Calamansi Juice', 45, 'Tangy Filipino citrus juice served over ice.', '682befb0a6019.jpg', 4, 0),
(25, 'Sangria', 75, 'Fruity alcoholic cocktail with citrus juice and fresh fruit bits.', '682beff4db9db.jpeg', 4, 0),
(26, 'Mojito', 75, 'Minty lime cooler with soda and a hint of sweetness.', '682bf0153ee65.jpg', 4, 0),
(27, 'Cheesecake Cups', 150, 'Creamy mini cheesecakes topped with jams and fruit.', '682bfe44ad5d4.jpg', 3, 0),
(28, 'Blueberry Muffins', 120, 'Soft muffins with juicy blueberry bits.', '682bfe8708d94.jpg', 3, 0),
(29, 'Cream Puffs', 110, 'Light pastry filled with sweet cream or custard topped with a chocolate ganache.', '682bfeb56f335.jpg', 3, 0),
(30, 'Chocolate Chip Cookies', 95, 'Chewy cookies packed with chocolate chips', '682bfed769d97.jpg', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_ingredients`
--

CREATE TABLE `product_ingredients` (
  `product_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_orders`
--

CREATE TABLE `temp_orders` (
  `temp_order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL,
  `scheduled_delivery` datetime DEFAULT NULL,
  `payment_reference` varchar(100) NOT NULL,
  `paymongo_link_id` varchar(100) DEFAULT NULL,
  `checkout_url` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'awaiting_payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `temp_orders`
--

INSERT INTO `temp_orders` (`temp_order_id`, `user_id`, `full_name`, `phone`, `address`, `notes`, `payment_method`, `total_amount`, `delivery_fee`, `scheduled_delivery`, `payment_reference`, `paymongo_link_id`, `checkout_url`, `status`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c14541273a1.12558831', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:12', '2025-05-20 05:34:12', NULL),
(2, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146943d774.44193033', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:33', '2025-05-20 05:34:33', NULL),
(3, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146a221269.05312717', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:34', '2025-05-20 05:34:34', NULL),
(4, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146a760bf8.42348677', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:34', '2025-05-20 05:34:34', NULL),
(5, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146ac317c0.25032557', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:34', '2025-05-20 05:34:34', NULL),
(6, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146b2bb5b1.16477763', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:35', '2025-05-20 05:34:35', NULL),
(7, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146b69ce89.35301687', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:35', '2025-05-20 05:34:35', NULL),
(8, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146bb577b1.56626106', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:35', '2025-05-20 05:34:35', NULL),
(9, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146c0a7a36.51248328', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:36', '2025-05-20 05:34:36', NULL),
(10, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146c8fdee1.07584016', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:36', '2025-05-20 05:34:36', NULL),
(11, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146ce91a47.39807374', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:36', '2025-05-20 05:34:36', NULL),
(12, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146d52a340.32286430', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:37', '2025-05-20 05:34:37', NULL),
(13, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146d90c3c5.69223899', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:37', '2025-05-20 05:34:37', NULL),
(14, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146de552b3.16796955', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:37', '2025-05-20 05:34:37', NULL),
(15, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c146e36a432.32418720', NULL, NULL, 'awaiting_payment', '2025-05-20 05:34:38', '2025-05-20 05:34:38', NULL),
(16, 8, 'Taga Burnek Linis', '1', 'Home: 179 M.L. Quezon St., New Lower Bicutan, Taguig City, NCR 1632', '', 'gcash', 330.00, 50.00, '2025-05-20 15:00:00', 'order_682c14870160c2.80653152', NULL, NULL, 'awaiting_payment', '2025-05-20 05:35:03', '2025-05-20 05:35:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `temp_order_items`
--

CREATE TABLE `temp_order_items` (
  `temp_order_item_id` int(11) NOT NULL,
  `temp_order_payment_reference` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `temp_order_items`
--

INSERT INTO `temp_order_items` (`temp_order_item_id`, `temp_order_payment_reference`, `product_id`, `quantity`, `price`) VALUES
(1, 'order_682c14541273a1.12558831', 4, 1, 280.00),
(2, 'order_682c146943d774.44193033', 4, 1, 280.00),
(3, 'order_682c146a221269.05312717', 4, 1, 280.00),
(4, 'order_682c146a760bf8.42348677', 4, 1, 280.00),
(5, 'order_682c146ac317c0.25032557', 4, 1, 280.00),
(6, 'order_682c146b2bb5b1.16477763', 4, 1, 280.00),
(7, 'order_682c146b69ce89.35301687', 4, 1, 280.00),
(8, 'order_682c146bb577b1.56626106', 4, 1, 280.00),
(9, 'order_682c146c0a7a36.51248328', 4, 1, 280.00),
(10, 'order_682c146c8fdee1.07584016', 4, 1, 280.00),
(11, 'order_682c146ce91a47.39807374', 4, 1, 280.00),
(12, 'order_682c146d52a340.32286430', 4, 1, 280.00),
(13, 'order_682c146d90c3c5.69223899', 4, 1, 280.00),
(14, 'order_682c146de552b3.16796955', 4, 1, 280.00),
(15, 'order_682c146e36a432.32418720', 4, 1, 280.00),
(16, 'order_682c14870160c2.80653152', 4, 1, 280.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fname` varchar(250) NOT NULL,
  `mname` varchar(250) DEFAULT NULL,
  `lname` varchar(250) NOT NULL,
  `email_add` varchar(250) NOT NULL,
  `mobile_num` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `emailv` enum('Yes','No') NOT NULL,
  `OTPC` int(11) DEFAULT NULL,
  `OTPE` datetime DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fname`, `mname`, `lname`, `email_add`, `mobile_num`, `password`, `emailv`, `OTPC`, `OTPE`, `role_id`, `created_at`) VALUES
(2, 'Aaron Paul', 'Mazo', 'Ugot', 'aaronpaulmugot@gmail.com', '09338223890', '$2y$10$ydiwFZS8SnMiU7cbsYYG8ujTXGCEBhDdaBRui38hne0GsRK5oDmV6', 'Yes', NULL, NULL, 2, '2025-05-18 13:36:37'),
(1, 'Admin', 'Marj', 'Food Services', 'marjfood@gmail.com', '09953410213', '$2y$10$a5dw/KWFnMT3LYwSElg5KOwuI8MH3ZT4ClZjuy7lsX3Wov/peS9qu', 'Yes', NULL, NULL, 1, '2025-05-18 13:27:29'),
(3, 'Jasper', 'De Guzman', 'Sergio', 'jdeguzmansergio@gmail.com', '09677455508', '$2y$10$ZAkEX/yuMPNH248YaAMpdOQ8RBWrp2qBz4hm4L2vIx/uGXvavyssu', 'Yes', NULL, NULL, 1, '2025-05-18 14:13:06'),
(4, 'asda', 'sad', 'adas', 'n00bzb0t2000@gmail.com', '0944884884', '$2y$10$B1EmHMlYtOpCasldMNgg/OUvUzlWYHPfDo/Zy8oerJOK7eQpejq4C', 'Yes', NULL, NULL, 2, '2025-05-18 14:16:38'),
(5, 'Justin', 'Diesta', 'Cruz', 'justincruz38.jc@gmail.com', '09617616907', '$2y$10$hVgTucqFZX4fUyW.ldufL.TV55RB3IlJidgmVVoUy/twY0I7KlSdm', 'Yes', NULL, NULL, 2, '2025-05-18 14:28:43'),
(6, 'Emin', 'De Velleres', 'Imura', 'emin24imura@gmail.com', '09470872439', '$2y$10$qlkhUd1xB6rBUTbiQWbLIeuwDBuzIah9dySzrmgf4OlpYgjUyEg8S', 'Yes', NULL, NULL, 2, '2025-05-18 14:30:23'),
(7, 'James Bernard', 'Romero', 'Dayao', 'jbdayao.14@gmail.com', '09617077690', '$2y$10$5qCJVtelB/sUykMbWA3Wtebayqg0IFYyE2fy2P/F/Jrrwy8HdOKE2', 'Yes', NULL, NULL, 2, '2025-05-18 14:43:02'),
(8, 'Taga', 'Burnek', 'Linis', 'mc.prem8080@gmail.com', '1', '$2y$10$DTVuoik5ILZ1JR0eZBT8F.jtOJHdBpfG46hkknvoxE1JmAizXftnu', 'Yes', NULL, NULL, 2, '2025-05-20 05:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`user_id`, `product_id`, `quantity`, `added_at`) VALUES
(8, 4, 1, '2025-05-20 05:32:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cancellation_reasons`
--
ALTER TABLE `cancellation_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `catering_orders`
--
ALTER TABLE `catering_orders`
  ADD PRIMARY KEY (`catering_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `catering_order_menu_items`
--
ALTER TABLE `catering_order_menu_items`
  ADD KEY `idx_catering_order` (`catering_order_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `custom_catering_orders`
--
ALTER TABLE `custom_catering_orders`
  ADD PRIMARY KEY (`custom_order_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `ingredient_types`
--
ALTER TABLE `ingredient_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_payment_reference` (`payment_reference`),
  ADD KEY `idx_paymongo_link` (`paymongo_link_id`),
  ADD KEY `idx_gcash_reference` (`gcash_reference`);

--
-- Indexes for table `order_checklist`
--
ALTER TABLE `order_checklist`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `checked_by` (`checked_by`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD PRIMARY KEY (`product_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `temp_orders`
--
ALTER TABLE `temp_orders`
  ADD PRIMARY KEY (`temp_order_id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`);

--
-- Indexes for table `temp_order_items`
--
ALTER TABLE `temp_order_items`
  ADD PRIMARY KEY (`temp_order_item_id`),
  ADD KEY `idx_temp_order_payment_ref` (`temp_order_payment_reference`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cancellation_reasons`
--
ALTER TABLE `cancellation_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `catering_orders`
--
ALTER TABLE `catering_orders`
  MODIFY `catering_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_catering_orders`
--
ALTER TABLE `custom_catering_orders`
  MODIFY `custom_order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `ingredient_types`
--
ALTER TABLE `ingredient_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `temp_orders`
--
ALTER TABLE `temp_orders`
  MODIFY `temp_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `temp_order_items`
--
ALTER TABLE `temp_order_items`
  MODIFY `temp_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
