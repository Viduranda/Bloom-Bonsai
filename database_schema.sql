-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.infinityfree.com
-- Generation Time: Sep 03, 2026 at 12:45 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42757057_bloombonsaidatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(14, 6, 16, 1, '2026-09-01 07:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `created_at`) VALUES
(1, 'Flowers', 'flowers', 'https://images.unsplash.com/photo-1496062031456-07b8f162a322?w=400', '2026-08-27 10:34:13'),
(2, 'Bonsai Trees', 'bonsai', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=400', '2026-08-27 10:34:13'),
(3, 'Accessories', 'accessories', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=400', '2026-08-27 10:34:13');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `discount_amount`, `min_spend`, `expiry_date`, `is_active`, `created_at`) VALUES
(6, 'BONSAI20', '20.00', '0.00', '1500.00', NULL, 0, '2026-08-30 04:44:21');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','packed','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `shipping_address` varchar(255) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `status_updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `shipping_address`, `customer_name`, `phone`, `city`, `pincode`, `payment_method`, `expected_delivery`, `status_updated_at`, `created_at`, `coupon_code`, `discount_amount`) VALUES
(1, 2, '2026.00', 'packed', 'Ganeshi, University of Jaffna, Jaffna - 123456', 'Ganeshi', '0774016026', 'Jaffna', '123456', 'cod', '2026-09-01', '2026-08-31 07:40:58', '2026-08-27 16:15:20', '', '0.00'),
(2, 2, '2026.00', 'shipped', 'Ganeshi, University of Jaffna, Jaffna - 123456', 'Ganeshi', '0774016026', 'Jaffna', '123456', 'cod', '2026-09-01', '2026-08-31 07:41:02', '2026-08-27 16:15:27', '', '0.00'),
(3, 2, '350.00', 'out_for_delivery', 'Ganeshi, University of Jaffna, Jaffna - 123456', 'Ganeshi', '+94774016026', 'Jaffna', '123456', 'cod', '2026-09-01', '2026-08-31 07:41:10', '2026-08-27 16:16:29', 'BLOOM10', '1499.00'),
(4, 3, '13400.00', 'confirmed', 'sethul, 871/4.nugod, nugo - 010115', 'sethul', '766747804', 'nugo', '010115', 'cod', '2026-09-07', '2026-09-02 09:52:40', '2026-09-02 16:52:40', '', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 1, 7, 1, '399.00'),
(2, 1, 6, 1, '279.00'),
(3, 1, 13, 1, '249.00'),
(4, 1, 14, 1, '749.00'),
(5, 2, 7, 1, '399.00'),
(6, 2, 6, 1, '279.00'),
(7, 2, 13, 1, '249.00'),
(8, 2, 14, 1, '749.00'),
(9, 3, 8, 1, '1499.00'),
(10, 4, 22, 1, '6200.00'),
(11, 4, 19, 1, '450.00'),
(12, 4, 23, 1, '6750.00');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `note`, `created_at`) VALUES
(1, 1, 'confirmed', 'Order placed successfully', '2026-08-27 09:15:20'),
(2, 2, 'confirmed', 'Order placed successfully', '2026-08-27 09:15:27'),
(3, 3, 'confirmed', 'Order placed successfully', '2026-08-27 09:16:29'),
(4, 2, 'cancelled', 'Cancelled by customer within 24-hour window', '2026-08-27 09:16:39'),
(5, 3, 'out_for_delivery', 'Status updated by administrator', '2026-08-31 07:40:42'),
(6, 3, 'shipped', 'Status updated by administrator', '2026-08-31 07:40:48'),
(7, 3, 'confirmed', 'Status updated by administrator', '2026-08-31 07:40:55'),
(8, 1, 'packed', 'Status updated by administrator', '2026-08-31 07:40:58'),
(9, 2, 'shipped', 'Status updated by administrator', '2026-08-31 07:41:02'),
(10, 3, 'out_for_delivery', 'Status updated by administrator', '2026-08-31 07:41:10'),
(11, 4, 'confirmed', 'Order placed successfully', '2026-09-02 09:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `plant_care_plans`
--

CREATE TABLE `plant_care_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `week_number` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `plant_care_plans`
--

INSERT INTO `plant_care_plans` (`id`, `product_id`, `week_number`, `title`, `content`) VALUES
(1, 3, 1, 'Unboxing & First Watering', 'Remove packaging gently. Check the soil — if dry, water thoroughly until water drains from the bottom. Keep in bright, indirect light for the first week. Do not repot unless the roots are coming out of the drainage holes.'),
(2, 4, 1, 'Unboxing & First Watering', 'Remove packaging gently. Check the soil — if dry, water thoroughly until water drains from the bottom. Keep in bright, indirect light for the first week. Do not repot unless the roots are coming out of the drainage holes.'),
(4, 7, 1, 'Unboxing & First Watering', 'Remove packaging gently. Check the soil — if dry, water thoroughly until water drains from the bottom. Keep in bright, indirect light for the first week. Do not repot unless the roots are coming out of the drainage holes.'),
(5, 3, 2, 'Finding Its Spot', 'Move your plant to its permanent location. Rotate the pot a quarter turn every few days so it grows evenly. Skip fertilizer this week — let the roots settle first.'),
(6, 4, 2, 'Finding Its Spot', 'Move your plant to its permanent location. Rotate the pot a quarter turn every few days so it grows evenly. Skip fertilizer this week — let the roots settle first.'),
(8, 7, 2, 'Finding Its Spot', 'Move your plant to its permanent location. Rotate the pot a quarter turn every few days so it grows evenly. Skip fertilizer this week — let the roots settle first.'),
(9, 3, 3, 'First Feeding & Checkup', 'Feed with a half-strength liquid fertilizer. Wipe dust off the leaves, check the undersides for pests, and trim any yellow or damaged leaves with clean scissors.'),
(10, 4, 3, 'First Feeding & Checkup', 'Feed with a half-strength liquid fertilizer. Wipe dust off the leaves, check the undersides for pests, and trim any yellow or damaged leaves with clean scissors.'),
(12, 7, 3, 'First Feeding & Checkup', 'Feed with a half-strength liquid fertilizer. Wipe dust off the leaves, check the undersides for pests, and trim any yellow or damaged leaves with clean scissors.'),
(13, 3, 4, 'Bloom & Growth Watch', 'Expect new growth by now. Water only when the top 2 inches of soil feel dry. If it is a flowering plant, buds may begin to appear. Continue feeding once a month.'),
(14, 4, 4, 'Bloom & Growth Watch', 'Expect new growth by now. Water only when the top 2 inches of soil feel dry. If it is a flowering plant, buds may begin to appear. Continue feeding once a month.'),
(16, 7, 4, 'Bloom & Growth Watch', 'Expect new growth by now. Water only when the top 2 inches of soil feel dry. If it is a flowering plant, buds may begin to appear. Continue feeding once a month.'),
(18, 2, 1, 'Unboxing & First Watering', 'Unpack carefully and inspect the roots and leaves. Water with room-temperature water until it drains. Place in bright, indirect light and keep away from AC drafts and direct afternoon sun.'),
(19, 6, 1, 'Unboxing & First Watering', 'Unpack carefully and inspect the roots and leaves. Water with room-temperature water until it drains. Place in bright, indirect light and keep away from AC drafts and direct afternoon sun.'),
(21, 2, 2, 'Establishing Its Spot', 'Set up a humidity tray or mist the leaves 2–3 times a week. Wipe leaves with a damp cloth. Stake the stem if the plant is top-heavy. No fertilizer yet.'),
(22, 6, 2, 'Establishing Its Spot', 'Set up a humidity tray or mist the leaves 2–3 times a week. Wipe leaves with a damp cloth. Stake the stem if the plant is top-heavy. No fertilizer yet.'),
(24, 2, 3, 'Feeding & Pruning', 'Apply balanced liquid fertilizer at half strength. Rotate the pot, prune any brown or damaged leaves, and check for spider mites or mealybugs.'),
(25, 6, 3, 'Feeding & Pruning', 'Apply balanced liquid fertilizer at half strength. Rotate the pot, prune any brown or damaged leaves, and check for spider mites or mealybugs.'),
(27, 2, 4, 'Growth & Bloom Watch', 'Watch for a new leaf unfurling — that means it is happy. If leaf edges brown, increase humidity and reduce watering. Repot only if roots show at the drainage holes.'),
(28, 6, 4, 'Growth & Bloom Watch', 'Watch for a new leaf unfurling — that means it is happy. If leaf edges brown, increase humidity and reduce watering. Repot only if roots show at the drainage holes.'),
(29, 8, 1, 'Unboxing & First Watering', 'Bonsai are delicate — water deeply until it drains, then let the soil dry slightly before the next watering. Keep in bright light. Do not repot or wire for the first week.'),
(30, 9, 1, 'Unboxing & First Watering', 'Bonsai are delicate — water deeply until it drains, then let the soil dry slightly before the next watering. Keep in bright light. Do not repot or wire for the first week.'),
(31, 10, 1, 'Unboxing & First Watering', 'Bonsai are delicate — water deeply until it drains, then let the soil dry slightly before the next watering. Keep in bright light. Do not repot or wire for the first week.'),
(32, 11, 1, 'Unboxing & First Watering', 'Bonsai are delicate — water deeply until it drains, then let the soil dry slightly before the next watering. Keep in bright light. Do not repot or wire for the first week.'),
(33, 8, 2, 'Shape & Structure Check', 'Check existing wires and remove any digging into the bark. Mist the foliage daily. Remove weeds or moss buildup from the soil surface.'),
(34, 9, 2, 'Shape & Structure Check', 'Check existing wires and remove any digging into the bark. Mist the foliage daily. Remove weeds or moss buildup from the soil surface.'),
(35, 10, 2, 'Shape & Structure Check', 'Check existing wires and remove any digging into the bark. Mist the foliage daily. Remove weeds or moss buildup from the soil surface.'),
(36, 11, 2, 'Shape & Structure Check', 'Check existing wires and remove any digging into the bark. Mist the foliage daily. Remove weeds or moss buildup from the soil surface.'),
(37, 8, 3, 'Pruning & Feeding', 'Lightly prune unruly shoots to maintain the silhouette. Feed with a bonsai-specific fertilizer at half strength. Do not prune more than 20% of foliage.'),
(38, 9, 3, 'Pruning & Feeding', 'Lightly prune unruly shoots to maintain the silhouette. Feed with a bonsai-specific fertilizer at half strength. Do not prune more than 20% of foliage.'),
(39, 10, 3, 'Pruning & Feeding', 'Lightly prune unruly shoots to maintain the silhouette. Feed with a bonsai-specific fertilizer at half strength. Do not prune more than 20% of foliage.'),
(40, 11, 3, 'Pruning & Feeding', 'Lightly prune unruly shoots to maintain the silhouette. Feed with a bonsai-specific fertilizer at half strength. Do not prune more than 20% of foliage.'),
(41, 8, 4, 'Pinching & Bud Watch', 'Pinch back new growth to keep the form. Watch for new buds — if the tree is mature, this is when it may bloom. Keep the seasonal watering rhythm.'),
(42, 9, 4, 'Pinching & Bud Watch', 'Pinch back new growth to keep the form. Watch for new buds — if the tree is mature, this is when it may bloom. Keep the seasonal watering rhythm.'),
(43, 10, 4, 'Pinching & Bud Watch', 'Pinch back new growth to keep the form. Watch for new buds — if the tree is mature, this is when it may bloom. Keep the seasonal watering rhythm.'),
(44, 11, 4, 'Pinching & Bud Watch', 'Pinch back new growth to keep the form. Watch for new buds — if the tree is mature, this is when it may bloom. Keep the seasonal watering rhythm.');

-- --------------------------------------------------------

--
-- Table structure for table `plant_scans`
--

CREATE TABLE `plant_scans` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `detected_species` varchar(150) DEFAULT NULL,
  `disease` varchar(150) DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `treatment_advice` text DEFAULT NULL,
  `scanned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `scientific_name` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `badge` varchar(50) DEFAULT NULL,
  `plant_age` varchar(80) DEFAULT NULL,
  `max_height` varchar(80) DEFAULT NULL,
  `bloom_time` varchar(120) DEFAULT NULL,
  `light_needs` varchar(120) DEFAULT NULL,
  `water_needs` varchar(120) DEFAULT NULL,
  `soil_type` varchar(120) DEFAULT NULL,
  `care_level` enum('Easy','Moderate','Expert') NOT NULL DEFAULT 'Moderate',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `care_plan` text DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `scientific_name`, `description`, `price`, `old_price`, `image`, `stock`, `badge`, `plant_age`, `max_height`, `bloom_time`, `light_needs`, `water_needs`, `soil_type`, `care_level`, `created_at`, `updated_at`, `image2`, `image3`, `care_plan`, `is_deleted`) VALUES
(2, 1, 'Anthurium', 'Anthurium andraeanum', 'The Anthurium is an iconic tropical houseplant prized for its glossy, heart-shaped leaves and bright, long-lasting floral spathes. Known for its exotic elegance and air-purifying qualities, it makes a striking statement piece for indoor home spaces and offices.', '350.00', '400.00', 'https://images.unsplash.com/photo-1446071103084-c257b5f70672?w=800', 18, 'sale', '1–2 years', '1.5 - 2 Feet (18 - 24 inches)', NULL, 'Medium to bright, indirect sunlight (Avoid direct harsh sunlight to prevent leaf scorch)', 'Moderate (Water when the top 1–2 inches of soil feel dry to the touch; maintain consistent moisture without waterlogging', NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 16:59:30', 'https://images.unsplash.com/photo-1446071103084-c257b5f70672?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Place in a warm indoor location with plenty of bright, indirect sunlight.\\n\\n(2). Check soil moisture; water thoroughly until excess drains out if the top inch of soil feels dry.\\n\\n(3). Gently wipe the glossy leaves with a soft, damp cloth to remove dust and maintain shine.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Monitor soil moisture and ensure the drainage tray is emptied after watering to prevent root rot.\\n\\n(2). Rotate the pot 180 degrees for balanced, even foliage growth toward the light source.\\n\\n(3). Mist the foliage lightly or place near a humidifier to maintain optimal humidity levels.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced indoor plant fertilizer (diluted to half-strength) rich in phosphorus to encourage continuous blooming.\\n\\n(2). Snip off spent, fading flowers at the base of their stems using clean shears.\\n\\n(3). Check under leaves and along stems for pests like aphids, thrips, or mealybugs.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Perform a regular soil moisture check and water as needed.\\n\\n(2). Trim away any yellowing, damaged, or aged lower leaves to direct energy to new growth.\\n\\n(3). Inspect drainage holes to ensure the soil mix remains well-draining and aerated.\"}]', 0),
(3, 1, 'Sunflower', 'Helianthus annuus', 'Sunflower (Helianthus) is an iconic annual plant known for its large, bright yellow blooms that track the sun throughout the day. Famous for its cheerful appearance and edible seeds, it brings energetic color and height to any sunny garden.', '420.00', '500.00', 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?w=800', 20, 'Bestseller', '1–2 months (Fast-growing Annual)', '6–10 feet (180–300 cm, depending on variety)', NULL, 'Full Sun (Requires 6–8 hours of direct sunlight daily)', 'Moderate (Water deeply 2–3 times a week; tolerates brief dry spells once mature)', NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 15:57:46', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Plant seeds or young seedlings in deep, well-draining soil mixed with organic compost.\\n\\n(2). Place in a location that receives full sun for at least 6\\u20138 hours daily.\\n\\n(3). Water gently and thoroughly to establish strong, deep root systems.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Apply a thin layer of organic mulch around the base to retain moisture and discourage weeds.\\n\\n(2). Inspect stem and leaf undersides for pests like aphids or cutworms.\\n\\n(3). Maintain consistent moisture, ensuring the top layer of soil stays damp but not soggy.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Feed with a balanced fertilizer high in phosphorus to encourage strong stem and flower development.\\n\\n(2). Insert a wooden or bamboo stake next to tall varieties to support the main stem against wind.\\n\\n(3). Deep-water 2\\u20133 times a week, focusing directly at the base of the plant.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Check for developing central flower heads and ensure sturdy stem support.\\n\\n(2). Lightly loosen topsoil and add a fresh top-dressing of rich compost.\\n\\n(3). Keep soil consistently moist to prevent drooping as large petals begin to open.\"}]', 0),
(4, 1, 'Bougainvillea', 'Bougainvillea glabra', 'The Bougainvillea is a fast-growing, tropical climbing vine and shrub renowned for its spectacular, paper-thin colorful bracts. Extremely drought-tolerant and vibrant, it flourishes in warm climates, making it an ideal choice for garden trellises, fences, patio containers, or cascading over walls.', '360.00', '420.00', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', 20, 'Sale', '1 - 2 Years', '10 - 20 Feet (3 - 6 meters, depending on support structure and pruning)', NULL, 'Full sunlight (Requires at least 6 hours of direct, intense outdoor sunlight daily for optimal blooming)', 'Low to Moderate (Drought-tolerant once established; water deeply only when the top 2-3 inches of soil feel completely dr', NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 17:00:06', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Position the plant in a high-sunlight outdoor area receiving direct, uninterrupted sun.\\n\\n(2).Water deeply at the root zone until water drains freely, then let the soil dry out significantly before the next watering.\\n\\n(3). Secure main climbing stems loosely to a trellis, wall, or support frame if growing upright.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Check soil moisture; avoid overwatering, as slightly dry soil stresses the plant into producing more colorful blooms.\\n\\n(2). Inspect the vine stems and undersides of leaves for common garden pests like aphids, caterpillars, or mealybugs.\\n\\n(3). Clear away fallen flowers and dry leaves from the soil surface to maintain ground cleanliness.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced, water-soluble fertilizer or a bloom-boosting fertilizer rich in potassium and phosphorus.\\n\\n(2). Trim back vigorous, non-flowering long green shoots (\\\"water sprouts\\\") to keep the plant bushier and direct energy to blooms.\\n\\n(3). Snip off fading or dried flower bracts to encourage a fresh flush of colorful growth.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Assess overall structure and perform light maintenance pruning to shape the bush or guide vines along the support.\\n\\n(2). Inspect root drainage holes for potted plants to ensure excess water escapes easily and prevents root rot.\\n\\n(3). Check ties on trellises to make sure stems have room to expand as they grow thicker.\"}]', 0),
(6, 1, 'HIBISCUS (China Rose)', 'Hibiscus rosa-sinensis', 'Hibiscus is a tropical flowering shrub famous for its large, trumpet-shaped blooms in vibrant shades of red, pink, yellow, and orange. It adds a lush, exotic look to gardens and brings continuous color throughout the flowering season.', '300.00', '380.00', 'https://images.unsplash.com/photo-1520763185298-1b434c919102?w=800', 16, 'New', '6–12 months (Potted / Established Plant)', '4–8 feet (120–240 cm)', NULL, 'Full Sun (Requires 6–8 hours of direct sunlight daily for best blooming)', 'Moderate to High (Water daily during hot periods; keep soil consistently moist but never waterlogged)', NULL, 'Moderate', '2026-08-27 10:34:13', '2026-09-01 07:24:09', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Plant in well-draining soil mixed with compost.\\n\\n(2). Place in a spot receiving 6\\u20138 hours of direct sunlight daily.\\n\\n(3). Water thoroughly after planting; rewater only when the topsoil feels dry.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Apply organic mulch around the base to retain soil moisture.\\n\\n(2). Inspect leaf undersides for pests like mealybugs; spray neem oil if needed.\\n\\n(3). Trim off any yellowing or damaged leaves.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced or high-potassium fertilizer to boost growth.\\n\\n(2). Pinch back stem tips by an inch to encourage bushier branching.\\n\\n(3). Maintain a regular morning watering schedule.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Check for new flower buds and ensure consistent watering to prevent bud drop.\\n\\n(2). Lightly loosen the topsoil and top-dress with fresh compost.\\n\\n(3). Prune dead blooms (deadheading) to promote continuous flowering.\"}]', 0),
(7, 1, 'PEACE LILY', 'Spathiphyllum wallisii', 'Peace Lily (Spathiphyllum) is a popular indoor plant known for its striking white blooms and glossy green leaves. Excellent for air purification, it thrives in low-light environments and brings a clean, elegant look to homes and offices.', '950.00', '1100.00', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', 25, 'Bestseller', '6–12 months (Mature/Potted Plant)', '1–3 feet (30–90 cm)', NULL, 'Low to Medium Indirect Light (Keep away from direct sunlight to prevent leaf burn)', 'Moderate (Water once a week or when the top inch of soil feels dry; sensitive to overwatering)', NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 15:57:23', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Plant in a well-draining potting mix rich in organic matter.\\n\\n(2). Place in bright, indirect light; keep away from direct harsh sunlight.\\n\\n(3). Water thoroughly until it drains from the bottom, then let excess water drain completely.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Wipe the glossy leaves with a damp cloth to remove dust and improve photosynthesis.\\n\\n(2). Inspect for common pests like spider mites or mealybugs; wipe with soapy water if needed.\\n\\n(3). Monitor soil moisture and keep it consistently damp, but never waterlogged.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a diluted, balanced liquid houseplant fertilizer (half-strength) to boost growth.\\n\\n(2). Trim any yellowing or brown leaf tips using clean, sharp pruning shears.\\n\\n(3). Rotate the pot slightly to ensure all sides get equal light for balanced growth.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Check for white spathe blooms and ensure stable indoor humidity.\\n\\n(2). Gently loosen topsoil and add a small layer of fresh compost or potting mix.\\n\\n(3). Prune faded or browning flower stalks at the base to encourage fresh growth.\"}]', 0),
(8, 2, 'Juniper Bonsai', 'Juniperus chinensis', 'The Juniper Bonsai is an iconic outdoor bonsai variety known for its needle-like foliage, rugged appearance, and flexible branches that are easy to shape. It is a hardy, classic tree that brings a natural, timeless forest feel to any garden, patio, or balcony.', '5400.00', '5750.00', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', 12, 'Sale', '3 - 5 Years', '1 - 2 Feet (12 - 24 inches)', NULL, 'Full sunlight (Requires direct outdoor sunlight for at least 4-6 hours daily)', 'Moderate (Keep soil slightly moist; water thoroughly when the topsoil begins to feel dry, but avoid waterlogging)', NULL, 'Expert', '2026-08-27 10:34:13', '2026-09-01 16:58:57', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Place outdoors in a location receiving full direct sunlight.\\n\\n(2). Check soil moisture daily and water thoroughly when the topsoil feels slightly dry.\\n\\n(3). Mist the foliage gently with water during warm mornings to clean the needles.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Inspect soil moisture and water deeply until excess water drains from the bottom.\\n\\n(2). Rotate the container to ensure all sides receive equal sunlight for uniform growth.\\n\\n(3). Inspect foliage closely for pests such as spider mites or aphids.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced, slow-release outdoor bonsai fertilizer or diluted liquid fertilizer.\\n\\n(2). Pinch back active new shoots (apical tips) using your fingers to encourage denser foliage foliage growth.\\n\\n(3). Clear away any fallen needles or debris from the soil surface to maintain hygiene.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Check soil moisture and maintain a regular watering schedule based on weather conditions.\\n\\n(2). Prune any unwanted or leggy branches to maintain the desired aesthetic shape.\\n\\n(3). Check the roots and drainage holes to ensure proper water flow and prevent root rot.\"}]', 0),
(9, 2, 'Ficus Bonsai (Ginseng)', 'Ficus microcarpa', 'The Ficus Bonsai is a popular, beginner-friendly indoor plant known for its glossy green leaves and thick, striking trunk. It is highly resilient, easy to maintain, and adds a classic, elegant touch to any home or office space.', '4250.00', '4500.00', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=800', 15, 'New', '3 - 5 Years', '1 - 2 Feet (12 - 24 inches)', NULL, 'Bright, indirect sunlight', 'Moderate (Water when the topsoil feels dry to the touch)', NULL, 'Expert', '2026-08-27 10:34:13', '2026-09-01 14:14:13', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Place in a spot with bright, indirect sunlight.\\n\\n(2). Check soil moisture; water thoroughly if the top inch feels dry.\\n\\n(3). Gently wipe the leaves with a damp cloth to remove dust and maximize light absorption.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Check soil moisture levels and water only when needed.\\n\\n(2). Rotate the pot 180 degrees to promote even light exposure and balanced leaf growth.\\n\\n(3). Inspect the trunk and under the leaves for any common pests like spider mites or scale.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced, liquid indoor plant fertilizer diluted to half strength.\\n\\n(2). Trim back any dead, yellowing, or overgrown leaves to help maintain the bonsai shape.\\n\\n(3). Mist the leaves lightly if indoor humidity is low.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Perform a full check on soil moisture and water appropriately.\\n\\n(2). Prune light outer growth to shape the canopy and encourage fresh foliage.\\n\\n(3). Clear away any fallen leaves or debris from the soil surface to prevent fungal growth.\"}]', 0),
(10, 2, 'SAKURA BONSAI ( CHERRY BLOSSOM)', 'Prunus serrulata', 'Sakura (Japanese Cherry Blossom) Bonsai is a miniature tree famed for its breathtaking spring blossom display in shades of pink and white. It combines traditional bonsai art with seasonal beauty, offering delicate flowers, rich green summer foliage, and rustic bark.', '4450.00', '4800.00', 'https://images.unsplash.com/photo-1522383225653-ed111181a951?w=800', 8, 'Bestseller', '3–5 years (Trained Bonsai Starter)', '1–2 feet (30–60 cm, kept restricted via pruning)', NULL, 'Full Sun to Partial Shade (Requires morning direct sunlight; shelter from harsh midday sun)', 'High / Frequent (Water daily when topsoil feels slightly dry; keep soil consistently moist)', NULL, 'Expert', '2026-08-27 10:34:13', '2026-09-01 07:50:48', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Plant in high-drainage bonsai soil mix (akadama, pumice, and lava rock).\\n\\n(2). Place outdoors in a bright spot with 4\\u20136 hours of morning sun, protected from strong winds.\\n\\n(3). Water thoroughly until water drains from bottom holes; never let the root ball dry completely.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Check soil daily as shallow bonsai pots dry out quickly.\\n\\n(2). Inspect shoots for aphids or caterpillar damage; treat with mild insecticidal soap if spotted.\\n\\n(3). Prune dead twigs and remove any weeds growing around the trunk base.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a low-nitrogen organic bonsai fertilizer to support branch strength without over-growing leaves.\\n\\n(2). Use training wire carefully on young branches to shape the canopy, avoiding bark damage.\\n\\n(3). Rotate the pot weekly for even sunlight exposure on all sides.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Monitor bud development and maintain high humidity around the tree on hot days.\\n\\n(2). Top-dress the soil surface with fresh bonsai soil mix or fine moss.\\n\\n(3). Trim back elongated new shoots to 2\\u20133 leaves to maintain the desired bonsai shape.\"}]', 0),
(11, 2, 'Chinese Elm Bonsai', 'Ulmus parvifolia', 'The Chinese Elm (Ulmus parvifolia) is one of the most popular and versatile bonsai species, prized for its small, finely serrated leaves and beautiful, delicate branching. Highly adaptable and forgiving of minor care mistakes, it is an ideal choice for both beginners and experienced growers.', '5620.00', '5800.00', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', 10, 'Sale', '3 - 5 Years', '1 - 2 Feet (12 - 24 inches)', NULL, 'Bright, direct to indirect sunlight (Thrives outdoors in sun/partial shade, but can tolerate bright indoor locations)', 'Moderate (Water thoroughly when the top layer of soil begins to dry out; avoid letting the soil dry completely)', NULL, 'Expert', '2026-08-27 10:34:13', '2026-09-01 16:58:43', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Position in a bright location with direct morning sunlight or bright indirect light.\\n\\n(2). Check soil moisture daily and water deeply until water drains from the bottom holes once topsoil feels dry.\\n\\n(3). Wipe or lightly mist the foliage to clean dust from the small leaves.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Monitor soil moisture and adjust watering based on temperature and light exposure.\\n\\n(2). Rotate the tree 180 degrees for balanced leaf growth across all sides.\\n\\n(3). Check under leaves and along branches for common pests like aphids or scale insects.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced organic or liquid bonsai fertilizer (diluted to half-strength).\\n\\n(2). Trim back long, shoots with 5\\u20136 leaves down to 2 leaves to encourage secondary branching.\\n\\n(3). Clean away any fallen leaves or debris from the soil surface.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Perform a deep watering routine as needed based on soil dampness.\\n\\n(2). Fine-tune the canopy shape by light structural pruning of stray branches.\\n\\n(3). Inspect drainage holes to ensure proper water flow and healthy root aeration.\"}]', 0);
INSERT INTO `products` (`id`, `category_id`, `name`, `scientific_name`, `description`, `price`, `old_price`, `image`, `stock`, `badge`, `plant_age`, `max_height`, `bloom_time`, `light_needs`, `water_needs`, `soil_type`, `care_level`, `created_at`, `updated_at`, `image2`, `image3`, `care_plan`, `is_deleted`) VALUES
(13, 3, 'Organic Potting Soil 10Kg', NULL, 'Nurture your plants with our Premium Organic Potting Soil Mix, formulated to encourage healthy root growth and vibrant foliage. Rich in essential natural nutrients, organic compost, and beneficial microorganisms, this well-draining soil retains optimal moisture while allowing essential root aeration. Perfect for indoor foliage plants, outdoor flowers, potted herbs, vegetables, and container gardening.', '750.00', '820.00', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', 8, 'Sale', NULL, NULL, NULL, NULL, NULL, NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 16:10:47', 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"Keep in bright indirect sunlight. Water thoroughly until water drains.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"Check soil moisture 2 inches deep. Mist foliage 3 times per week.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"Apply organic liquid fertilizer diluted to half-strength. Prune yellow leaves.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"Establish regular weekly watering schedule. Inspect leaf undersides for pests.\"}]', 1),
(14, 3, 'Hanging Flower Pots', NULL, 'Elevate your garden decor with this durable, lightweight Hanging Plastic Flower Pot. Designed with a classic terracotta-colored finish and a sturdy triple-strand hanger, this pot is ideal for displaying trailing plants, flowering vines, and indoor foliage. Perfect for balconies, verandas, patios, or indoor window spaces, it offers a space-saving solution to bring vibrant greenery to eye level.', '175.00', '200.00', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', 15, 'Sale', NULL, NULL, NULL, NULL, NULL, NULL, 'Easy', '2026-08-27 10:34:13', '2026-09-01 16:16:55', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"Keep in bright indirect sunlight. Water thoroughly until water drains.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"Check soil moisture 2 inches deep. Mist foliage 3 times per week.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"Apply organic liquid fertilizer diluted to half-strength. Prune yellow leaves.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"Establish regular weekly watering schedule. Inspect leaf undersides for pests.\"}]', 1),
(16, 3, 'Gardning Tool Set', NULL, 'Upgrade your gardening routine with this essential 3-Piece Mini Garden Tool Set. Crafted with durable metal heads and smooth, ergonomic wooden handles, this compact set includes a hand trowel, cultivator rake, and transplanter trowel. Perfectly designed for potting plants, loosening soil, weeding, and bonsai maintenance, it is an ideal choice for both indoor and outdoor gardening enthusiasts.', '900.00', '1150.00', 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800', 10, 'Sale', NULL, NULL, NULL, NULL, NULL, NULL, 'Moderate', '2026-08-31 17:35:49', '2026-09-01 16:05:01', 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"Keep in bright indirect sunlight. Water thoroughly until water drains.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"Check soil moisture 2 inches deep. Mist foliage 3 times per week.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"Apply organic liquid fertilizer diluted to half-strength. Prune yellow leaves.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"Establish regular weekly watering schedule. Inspect leaf undersides for pests.\"}]', 0),
(19, 1, 'RED ROSE', NULL, 'Classic Red Rose is a timeless flowering shrub known for its vibrant crimson blooms, rich fragrance, and velvety petals. Ideal for gardens, borders, and pots, it symbolises love and adds elegance to any landscape with proper care and full sunlight.', '450.00', '520.00', 'https://images.unsplash.com/photo-1496062031456-07b8f162a322?w=800', 14, 'Bestseller', '6–12 months (Mature/Potted Plant)', '3–5 feet (90–150 cm)', NULL, 'Full Sun (6+ hours of direct sunlight daily)', 'Moderate (Water deeply 2–3 times a week; keep soil moist but well-drained)', NULL, 'Moderate', '2026-09-01 07:58:28', '2026-09-02 16:52:40', NULL, NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Plant in nutrient-rich, well-draining soil mixed with organic compost.\\n\\n(2)Position the plant to get at least 6 hours of full sun daily.\\n\\n(3). Deep-water the roots immediately after planting; avoid wetting the leaves.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Apply a 2-inch layer of organic mulch around the base to keep roots cool and moist.\\n\\n(2).Monitor under leaves for aphids or black spot disease; treat early with neem oil or standard fungicide.\\n\\n(3). Remove any yellowed, diseased, or damaged leaves to maintain airflow.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Feed with a rose-specific fertilizer rich in phosphorus to encourage strong root development.\\n\\n(2). Prune weak stems and pinch stem tips to promote sturdy, lateral growth.\\n\\n(3). Water early in the morning near the base of the plant.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Inspect for developing buds and keep soil moisture steady to support blooming.\\n\\n(2). Gently loosen topsoil and add a fresh layer of compost for nutrients.\\n\\n(3). Snip off faded flowers (deadhead) just above a 5-leaflet stem to encourage new blossoms.\"}]', 0),
(20, 3, 'Flower Pots', NULL, 'Maximize your vertical gardening space with this stylish Wall-Mounted Plastic Flower Pot. Featuring a sleek black finish with a textured pattern and built-in mounting holes, this planter rests flat against walls, fences, or balcony railings. It is perfect for displaying vibrant flowering plants, herbs, or cascading foliage while saving valuable ground space.', '230.00', '280.00', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', 10, 'Sale', NULL, NULL, NULL, NULL, NULL, NULL, 'Moderate', '2026-09-01 16:21:00', '2026-09-01 16:21:00', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"Keep in bright indirect sunlight. Water thoroughly until water drains.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"Check soil moisture 2 inches deep. Mist foliage 3 times per week.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"Apply organic liquid fertilizer diluted to half-strength. Prune yellow leaves.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"Establish regular weekly watering schedule. Inspect leaf undersides for pests.\"}]', 0),
(21, 3, 'Small Flower Pots', NULL, 'Add a clean, classic touch to your garden setup with this durable Black Plastic Flower Pot. Built with a smooth finish and a reinforced rim, this versatile planter provides a stable nursery and growing environment for indoor foliage, outdoor flowering plants, herbs, and young shrubs.', '80.00', '100.00', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', 18, 'Sale', NULL, NULL, NULL, NULL, NULL, NULL, 'Moderate', '2026-09-01 16:25:19', '2026-09-01 16:25:19', 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"Keep in bright indirect sunlight. Water thoroughly until water drains.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"Check soil moisture 2 inches deep. Mist foliage 3 times per week.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"Apply organic liquid fertilizer diluted to half-strength. Prune yellow leaves.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"Establish regular weekly watering schedule. Inspect leaf undersides for pests.\"}]', 0),
(22, 2, 'Bougainvillea Bonsai', 'Bougainvillea glabra', 'The Bougainvillea Bonsai is a breathtaking ornamental plant renowned for its vibrant, papery bracts and dramatic, twisted trunk silhouette. It is a hardy, heat-tolerant species that flowers abundantly, bringing a bright burst of exotic color to any outdoor garden or sunny patio.', '6200.00', '6450.00', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', 11, 'Sale', '3 - 5 Years', '1 - 2 Feet (12 - 24 inches)', NULL, 'Full sunlight (Requires at least 5-6 hours of direct outdoor sunlight daily to trigger heavy blooming)', 'Low to Moderate (Allow the soil to dry out slightly between waterings; slight drying encourages richer flowering)', NULL, 'Expert', '2026-09-01 16:40:20', '2026-09-02 16:52:40', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', NULL, '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Place in the sunniest available outdoor location to maximize bloom brightness.\\n\\n(2). Check soil moisture; water thoroughly until excess drains out only when the top 1\\u20132 inches of soil feel dry.\\n\\n(3). Clear away fallen leaves and faded flower bracts from around the pot base.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Keep watering controlled, avoiding soggy soil to stimulate flower bud formation.\\n\\n(2). Rotate the container 180 degrees for even branch growth and uniform sun exposure.\\n\\n(3). Inspect stems and undersides of leaves for common pests like caterpillars or aphids.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a high-potassium or bloom-boosting liquid fertilizer (diluted to half-strength).\\n\\n(2). Prune long green shoots back to retain the compact bonsai shape.\\n\\n(3). Snip off spent bracts to channel energy toward new vegetative and floral growth.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Ensure bottom drainage holes remain clear so water drains freely after rain or watering.\\n\\n(2). Trim away unwanted suckers along the lower trunk to maintain a clean main stem.\\n\\n(3). Inspect branches for overall balance and adjust structure if needed.\"}]', 0),
(23, 2, 'Tamarin Bonsai', 'Tamarindus indica', 'The Tamarind Bonsai (Tamarindus indica) is a captivating tropical bonsai prized for its delicate, feather-like compound leaves and thick, rugged trunk. Its foliage naturally folds up at night and reopens during the day, adding a unique, dynamic charm to any outdoor bonsai collection.', '6750.00', '7100.00', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=800', 7, 'Bestseller', '3 - 5 Years', '1 - 2 Feet (12 - 24 inches)', NULL, 'Full sunlight (Requires direct outdoor sunlight for at least 5-6 hours daily for dense foliage)', 'Moderate (Water thoroughly when the topsoil begins to dry; prefers consistent moisture but needs well-draining soil to p', NULL, 'Expert', '2026-09-01 16:54:13', '2026-09-02 16:52:40', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=800', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=800', '[{\"week\":\"Week 1\",\"title\":\"Acclimation & Hydration\",\"tasks\":\"(1). Place outdoors in a warm, sunny position receiving direct sunlight.\\n\\n(2). Check soil moisture daily and water thoroughly until excess drains out when the top layer feels dry.\\n\\n(3). Gently mist foliage in the early morning to keep the delicate leaves clean.\"},{\"week\":\"Week 2\",\"title\":\"Root & Moisture Check\",\"tasks\":\"(1). Monitor soil moisture; tamarind roots prefer staying slightly damp but never waterlogged.\\n\\n(2). Rotate the container 180 degrees to promote uniform foliage growth on all sides.\\n\\n(3). Check under the feathery leaves and along young shoots for pests like scale insects or mealybugs.\"},{\"week\":\"Week 3\",\"title\":\"Nutrient Boosting & Pruning\",\"tasks\":\"(1). Apply a balanced, liquid indoor\\/outdoor bonsai fertilizer diluted to half strength.\\n\\n(2). Trim long, leggy shoots back to 2\\u20133 leaf pairs to maintain the compact canopy shape.\\n\\n(3). Remove any fallen leaves or organic debris from the soil surface.\"},{\"week\":\"Week 4\",\"title\":\"Full Maintenance Schedule\",\"tasks\":\"(1). Inspect drainage holes to ensure proper water flow and healthy root aeration.\\n\\n(2). Fine-tune the branch structure by pinching back unwanted new growth.\\n\\n(3). Check root stability and overall foliage health as the plant continues to mature.\"}]', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'vidurandarukmal@gmail.com', '$2y$10$8c5PAwNlGIHzZWPaCjux3O.21PHZkcC73MHr0wqsGNKlrjlyMIhWm', NULL, NULL, 'admin', '2026-08-27 10:34:13', '2026-08-27 10:34:13'),
(2, 'Ganeshi', 'ganeshi@gmail.com', '$2y$10$KuElxdLrRj8kFG7Mm9dGYOkTy8Gd7VgLmDFxGH.kP0TVpMNIge.Iu', '', NULL, 'customer', '2026-08-27 15:20:20', '2026-08-27 15:20:20'),
(3, 'sethul', 'sethullovidu77@gmail.com', '$2y$10$ypQcAI8bEgYub0CEaUEfbO73L3s2dR8MCj9ms6NH4HdGeTxn1HOmy', '', NULL, 'admin', '2026-08-27 16:58:11', '2026-08-31 14:37:14'),
(5, 'Kumarindu Uthpala', 'kumarinduthpala@gmail.com', '$2y$10$DnJJ1XfLgd.6cSGuNfC7p..t7YpgQst1sazKFvEYLQ.mSvSA8LegC', '0742918900', NULL, 'admin', '2026-08-31 16:14:08', '2026-08-31 16:17:30'),
(6, 'Deshan', 'deshandinujaya689@gmail.com', '$2y$10$.2x/oFy68xtwGiB9vVn1eurYWGR2JRPlH0KB/o9p526MBq5ZlPaN6', '0702525433', NULL, 'admin', '2026-09-01 07:49:08', '2026-09-01 07:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_plants`
--

CREATE TABLE `user_plants` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plant_name` varchar(100) NOT NULL,
  `species` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `health_status` enum('healthy','needs_attention','diseased') NOT NULL DEFAULT 'healthy',
  `care_notes` text DEFAULT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_plants`
--

INSERT INTO `user_plants` (`id`, `user_id`, `plant_name`, `species`, `image_url`, `health_status`, `care_notes`, `added_at`) VALUES
(1, 2, 'Peace Lily', 'Spathiphyllum wallisii', 'https://images.unsplash.com/photo-1520763185298-1b434c919102?w=400', 'healthy', NULL, '2026-08-27 16:15:20'),
(2, 2, 'Hibiscus (China Rose)', 'Hibiscus rosa-sinensis', NULL, 'healthy', NULL, '2026-08-27 16:15:20'),
(3, 2, 'Peace Lily', 'Spathiphyllum wallisii', 'https://images.unsplash.com/photo-1520763185298-1b434c919102?w=400', 'healthy', NULL, '2026-08-27 16:15:27'),
(4, 2, 'Hibiscus (China Rose)', 'Hibiscus rosa-sinensis', NULL, 'healthy', NULL, '2026-08-27 16:15:27'),
(5, 2, 'Juniper Bonsai', 'Juniperus chinensis', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=400', 'healthy', NULL, '2026-08-27 16:16:29'),
(6, 3, 'Bougainvillea Bonsai', 'Bougainvillea glabra', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', 'healthy', NULL, '2026-09-02 16:52:40'),
(7, 3, 'RED ROSE', '', 'https://images.unsplash.com/photo-1496062031456-07b8f162a322?w=800', 'healthy', NULL, '2026-09-02 16:52:40'),
(8, 3, 'Tamarin Bonsai', 'Tamarindus indica', 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=800', 'healthy', NULL, '2026-09-02 16:52:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cart` (`user_id`,`product_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orderitems_product` (`product_id`),
  ADD KEY `idx_orderitems_order` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_osh_order` (`order_id`);

--
-- Indexes for table `plant_care_plans`
--
ALTER TABLE `plant_care_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pcp_week` (`product_id`,`week_number`);

--
-- Indexes for table `plant_scans`
--
ALTER TABLE `plant_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scans_user` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_price` (`price`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_user` (`user_id`),
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `user_plants`
--
ALTER TABLE `user_plants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_userplants_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `plant_care_plans`
--
ALTER TABLE `plant_care_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `plant_scans`
--
ALTER TABLE `plant_scans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_plants`
--
ALTER TABLE `user_plants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderitems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plant_care_plans`
--
ALTER TABLE `plant_care_plans`
  ADD CONSTRAINT `fk_pcp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plant_scans`
--
ALTER TABLE `plant_scans`
  ADD CONSTRAINT `fk_scans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_plants`
--
ALTER TABLE `user_plants`
  ADD CONSTRAINT `fk_userplants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
