-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2026 at 12:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `color` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_new` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `featured_products`
--

CREATE TABLE `featured_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_products`
--

INSERT INTO `featured_products` (`id`, `product_id`, `sort_order`, `created_at`) VALUES
(8, 149, 0, '2026-08-12 13:11:33');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `product_code`, `rating`, `message`, `created_at`) VALUES
(4, 7, 'SP0001', 5, 'TỐT', '2026-08-12 12:36:19');

-- --------------------------------------------------------

--
-- Table structure for table `home`
--

CREATE TABLE `home` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home`
--

INSERT INTO `home` (`id`, `title`, `description`, `image`) VALUES
(1, '', '', 'Banner Sale Khuyến mãi Giáng sinh Năm mới Ấn tượng Minh họa Tối giản Đỏ vàng.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity_change` int(11) DEFAULT NULL,
  `import_price` decimal(15,2) DEFAULT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_history`
--

INSERT INTO `inventory_history` (`id`, `product_id`, `product_code`, `color`, `quantity_change`, `import_price`, `sale_price`, `note`, `created_at`, `type`) VALUES
(195, 152, 'SP0002', 'Mặc định', 100, 10000.00, 320000.00, 'Thêm màu mới: 100 SL, giá nhập 10.000 VND, tổng giá bán 32.000.000 VND', '2026-08-12 14:07:01', 'Nhập hàng'),
(196, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 14:07:17', 'Bán hàng'),
(197, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 14:44:46', 'Bán hàng'),
(198, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 14:51:27', 'Bán hàng'),
(199, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 15:38:49', 'Bán hàng'),
(200, 152, 'SP0002', 'Mặc định', -96, 10000.00, 320000.00, 'Xóa toàn bộ màu này', '2026-08-12 19:03:59', 'Xóa hàng'),
(201, 152, 'SP0002', 'Mặc định', 100, 10000.00, 320000.00, 'Thêm màu mới: 100 SL, giá nhập 10.000 VND, tổng giá bán 32.000.000 VND', '2026-08-12 20:41:13', 'Nhập hàng'),
(202, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 20:41:18', 'Bán hàng'),
(203, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 21:55:13', 'Bán hàng'),
(204, 152, 'SP0002', 'Mặc định', 1, 10000.00, 320000.00, 'Trừ tồn kho khi đặt hàng (User: U0VF688E)', '2026-08-12 22:07:12', 'Bán hàng');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `sender_role` enum('user','admin') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `user_name`, `sender_role`, `content`, `created_at`) VALUES
(170, 7, 'Hoài Nam', 'user', 'xin chào', '2026-08-12 13:37:30'),
(171, 7, 'Admin', 'admin', 'z', '2026-08-12 23:09:39'),
(172, 7, 'Admin', 'admin', 'dd', '2026-08-12 23:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`) VALUES
(1, 'Namdo2003hp@gmail.com', '2c09889cf334e1bc3d48f2886d6c4ca7128c0f0dcaf772ef92bae11ce6a610a04fcbc4660a216268e5f7f08cbf1bb3583745', '2026-07-18 02:15:40'),
(2, 'Namdo2003hp@gmail.com', '14790cfdc3beca0e4aa111192e1601e404547c3c58b81675706919fef5e9991e52f874f1a0404d14d7b3126fe7ce2075d142', '2026-07-18 02:18:23'),
(3, 'Namdo2003hp@gmail.com', '1d919b6fb7157d8df786cb95ee3f522519690298b21530cfb230dc9195266d27c67e44bd85b44eee48b179639d578183b163', '2026-07-18 02:23:50'),
(4, 'Namdo2003hp@gmail.com', 'fc92b882dc975336b149d52deaca795686134fb07ed0d6c6eae3a8a41bf92d0f9353e08f6bf05d17ee260e0c45f45315a54d', '2026-07-18 02:25:48'),
(6, 'Nguyenlehoainam@gmail.com', '91d09ed6950125db8b1f479d62cc4dec0551c05d85c5ed0f52c1d65fd156eaa9ceb5130d7fe624dc70dcbfc0987437feaf9f', '2026-07-18 02:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_quantity` int(11) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `category` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Chờ xử lý','Chờ thanh toán','Đã thanh toán','Đang xử lý','Đang giao hàng','Đã giao hàng','Đã hủy') DEFAULT 'Chờ xử lý',
  `shipper_id` int(11) DEFAULT NULL,
  `receive_date` timestamp NULL DEFAULT NULL,
  `is_deducted` tinyint(1) NOT NULL DEFAULT 1,
  `is_restored` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `user_code`, `product_code`, `product_name`, `image`, `product_quantity`, `total_price`, `category`, `color`, `order_date`, `status`, `shipper_id`, `receive_date`, `is_deducted`, `is_restored`) VALUES
(52, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG', 'U0VF688E', 'SP0002', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6a7c7ba04c460.png', 1, 320000.00, 'cáp sạc', 'Mặc định', '2026-08-12 20:41:18', 'Đã giao hàng', 19, '2026-08-12 21:25:56', 1, NULL),
(53, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG', 'U0VF688E', 'SP0002', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6a7c7ba04c460.png', 1, 320000.00, 'cáp sạc', 'Mặc định', '2026-08-12 21:55:13', 'Đã giao hàng', 19, '2026-08-12 21:55:42', 1, NULL),
(54, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG', 'U0VF688E', 'SP0002', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6a7c7ba04c460.png', 1, 320000.00, 'cáp sạc', 'Mặc định', '2026-08-12 22:07:12', 'Đã giao hàng', 19, '2026-08-12 22:07:30', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category` enum('tai nghe','cáp sạc','ốp lưng','kính cường lực') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Active/Enabled, 0 = Inactive/Disabled',
  `product_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `price`, `color`, `image`, `category`, `is_active`, `product_code`) VALUES
(149, 'JBL Soundgear Sense', 'JBL', 3190000.00, 'Đen', 'prod_6a7c7b8d5f6e7.jpg', 'tai nghe', 1, 'SP0001'),
(152, 'Baseus Crystal Shine Type-C to Lightning 2M', 'Baseus', 320000.00, 'Mặc định', 'prod_6a7c7ba04c460.png', 'cáp sạc', 1, 'SP0002');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `before_insert_products` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    DECLARE new_code VARCHAR(10);
    
    -- Tạo mã sản phẩm tự động
    SELECT CONCAT('SP', LPAD(IFNULL(MAX(SUBSTRING(product_code, 3)) + 1, 1), 4, '0'))
    INTO new_code FROM products;

    SET NEW.product_code = new_code;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `sync_inventory_price` AFTER UPDATE ON `products` FOR EACH ROW BEGIN
    IF NEW.price <> OLD.price THEN
        UPDATE product_inventory
        SET sale_price = NEW.price
        WHERE product_id = NEW.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `detail_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `compatibility` varchar(255) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `features` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_inventory`
--

CREATE TABLE `product_inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `import_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_inventory`
--

INSERT INTO `product_inventory` (`id`, `product_id`, `product_code`, `color`, `quantity`, `import_price`, `created_at`, `updated_at`, `sale_price`) VALUES
(60, 152, 'SP0002', 'Mặc định', 97, 10000.00, '2026-08-12 20:41:13', '2026-08-12 22:07:12', 320000.00);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `image`, `link`, `created_at`) VALUES
(1, '', 'THÔNG TIN CHI TIẾT TẠI:', 'khmai.jpg', 'https://cellphones.com.vn/phu-kien.html', '2026-07-16 18:46:12');

-- --------------------------------------------------------

--
-- Table structure for table `shipper`
--

CREATE TABLE `shipper` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `cmt` varchar(20) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipper`
--

INSERT INTO `shipper` (`id`, `name`, `email`, `phone`, `dob`, `cmt`, `avatar`, `password`) VALUES
(18, 'Hoài Nam', 'Nguyenlehoainamhp@gmail.com', '0948003196', '2006-02-18', '03120001878', 'uploads/shipper_18.jpg', '$2y$10$YAE.KVfAeXGK0rdWt5hv9eo9pRhEQOg1Pes/oK5irw76KeWwVT8aK'),
(19, 'HLN Nam', 'Nammac2003@gmail.com', '0587911287', '2003-06-07', '031203001868', 'uploads/shipper_19.jpg', '$2y$10$wdFXGZwQ22vOLXRdxKRN4eWx3J3oVSTEWmifquu2jImNTzUdyL5.m'),
(25, 'Nam', 'Namdo2003hp@gmail.com', '0949003196', '2026-07-27', '031204001868', NULL, '$2y$10$WMVxJjbFLxmz6HdGGDwMcO5tbvXGUalpf/720jqs4fl6yWjpsd2IK');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `user_code`, `password`, `created_at`) VALUES
(7, 'Hoài Nam', 'Namdo2003hp@gmail.com', 'U0VF688E', '$2y$10$8uULxIjDHEZ.q0mf6UpF3ONNGOH.WOl1oBS34UgPQNeFHvMbXYadq', '2026-08-08 02:26:04');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_create_profile` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_profile(
        user_id,
        user_code,
        name,
        email
    )
    VALUES(
        NEW.id,
        NEW.user_code,
        NEW.name,
        NEW.email
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_last_seen_message`
--

CREATE TABLE `user_last_seen_message` (
  `user_id` int(11) NOT NULL,
  `last_seen_id` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_last_seen_message`
--

INSERT INTO `user_last_seen_message` (`user_id`, `last_seen_id`, `updated_at`) VALUES
(7, 172, '2026-08-12 23:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `user_id` int(11) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `user_code`, `name`, `email`, `phone`, `address`) VALUES
(7, 'U0VF688E', 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_cart_product_color` (`cart_id`,`product_id`,`color`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `featured_products`
--
ALTER TABLE `featured_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_featured_product` (`product_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_user` (`user_id`),
  ADD KEY `fk_feedback_product` (`product_code`);

--
-- Indexes for table `home`
--
ALTER TABLE `home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inventory_history` (`product_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_user` (`user_code`),
  ADD KEY `fk_payment_shipper` (`shipper_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `product_code_2` (`product_code`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `fk_product_details` (`product_id`);

--
-- Indexes for table `product_inventory`
--
ALTER TABLE `product_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inventory_product` (`product_id`),
  ADD KEY `fk_inventory_product_code` (`product_code`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipper`
--
ALTER TABLE `shipper`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `cmt` (`cmt`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_code` (`user_code`);

--
-- Indexes for table `user_last_seen_message`
--
ALTER TABLE `user_last_seen_message`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=353;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `featured_products`
--
ALTER TABLE `featured_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `home`
--
ALTER TABLE `home`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_inventory`
--
ALTER TABLE `product_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipper`
--
ALTER TABLE `shipper`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_item_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_profile` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `featured_products`
--
ALTER TABLE `featured_products`
  ADD CONSTRAINT `fk_featured_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_product` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `fk_inventory_history` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_shipper` FOREIGN KEY (`shipper_id`) REFERENCES `shipper` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_code`) REFERENCES `users` (`user_code`) ON UPDATE CASCADE;

--
-- Constraints for table `product_details`
--
ALTER TABLE `product_details`
  ADD CONSTRAINT `fk_product_details` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_inventory`
--
ALTER TABLE `product_inventory`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_product_code` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_last_seen_message`
--
ALTER TABLE `user_last_seen_message`
  ADD CONSTRAINT `fk_user_last_seen_message_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `fk_user_profile_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
