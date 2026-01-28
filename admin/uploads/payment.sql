-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 28, 2026 lúc 10:03 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `store`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `product_code` text NOT NULL,
  `product_name` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_quantity` int(11) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `category` text NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Chờ xử lý','Chờ thanh toán','Đã thanh toán','Đang xử lý','Đang giao hàng','Đã giao hàng','Đã hủy') NOT NULL DEFAULT 'Chờ xử lý',
  `shipper_id` int(11) DEFAULT NULL,
  `receive_date` timestamp NULL DEFAULT NULL,
  `is_deducted` tinyint(1) DEFAULT NULL,
  `is_restored` tinyint(1) DEFAULT NULL COMMENT 'Đã hoàn trả tồn kho chưa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `user_code`, `product_code`, `product_name`, `image`, `product_quantity`, `total_price`, `category`, `color`, `order_date`, `status`, `shipper_id`, `receive_date`, `is_deducted`, `is_restored`) VALUES
(216, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 01:56:35', 'Đã giao hàng', 2, '2025-12-19 01:57:44', NULL, NULL),
(217, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 02:03:41', 'Đã giao hàng', 2, '2025-12-19 02:04:20', NULL, NULL),
(218, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 02:07:32', 'Đã giao hàng', 2, '2025-12-19 02:08:04', NULL, NULL),
(219, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 02:09:03', 'Đã giao hàng', 2, '2025-12-19 02:09:44', NULL, NULL),
(220, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-19 02:13:38', 'Đã giao hàng', 2, '2025-12-19 02:14:08', NULL, NULL),
(221, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2025-12-19 02:16:56', 'Đã giao hàng', 2, '2025-12-19 02:17:32', NULL, NULL),
(222, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010, SP0004', 'Baseus Crystal Shine Type-C to Lightning 2M (x1), JBL Wave Beam (x1)', 'prod_6927a6d39c3c1.png, prod_69149987e535d7.28187096.jpg', 2, 1350000.00, 'cáp sạc, tai nghe', 'Đen, Đen', '2025-12-19 02:18:36', 'Đã giao hàng', 2, '2025-12-19 02:20:23', NULL, NULL),
(223, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe', 'Đen, Đen', '2025-12-19 02:22:29', 'Đã giao hàng', 2, '2025-12-19 02:23:53', NULL, NULL),
(224, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2025-12-19 02:24:42', 'Đã giao hàng', 3, '2025-12-19 02:25:16', NULL, NULL),
(225, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0006', 'JBL Live Pro 2 (x1)', 'prod_69149a1d2b2b44.12463294.jpg', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-19 02:25:50', 'Đã giao hàng', 3, '2025-12-19 02:26:42', NULL, NULL),
(226, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0004, SP0005, SP0006', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1), JBL Live Pro 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg, prod_69149a1d2b2b44.12463294.jpg', 3, 4000000.00, 'tai nghe', 'Đen, Đen, Đen', '2025-12-19 02:29:04', 'Đã giao hàng', 3, '2025-12-19 02:29:29', NULL, NULL),
(227, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-19 02:36:13', 'Đã giao hàng', 3, '2025-12-19 02:36:38', NULL, NULL),
(228, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 02:39:09', 'Đã giao hàng', 3, '2025-12-19 02:40:04', NULL, NULL),
(229, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 02:51:19', 'Đã giao hàng', 2, '2025-12-19 02:52:20', NULL, NULL),
(230, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 03:08:57', 'Đã giao hàng', 2, '2025-12-19 03:09:30', NULL, NULL),
(231, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-19 03:14:38', 'Đã giao hàng', 3, '2025-12-19 03:15:06', NULL, NULL),
(232, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-19 03:17:42', 'Đã giao hàng', 3, '2025-12-19 03:18:01', NULL, NULL),
(233, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-19 03:20:02', 'Đã giao hàng', 3, '2025-12-19 03:20:19', NULL, NULL),
(235, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-19 20:33:29', 'Đã giao hàng', 3, '2025-12-19 20:37:48', NULL, NULL),
(237, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x2)', 'prod_691520dee469a9.26207248.jpg', 2, 8000000.00, 'tai nghe', 'Đen', '2025-12-20 01:22:36', 'Đã giao hàng', 2, '2025-12-20 01:24:00', NULL, NULL),
(239, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-21 14:19:19', 'Đã giao hàng', 2, '2025-12-21 14:20:07', NULL, NULL),
(240, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Đen', '2025-12-21 14:49:23', 'Đã giao hàng', 2, '2025-12-23 12:05:46', NULL, NULL),
(241, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0007, SP0010', 'Sony WF-1000XM5 (x1), Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_691520dee469a9.26207248.jpg, prod_6927a6d39c3c1.png', 2, 4150000.00, 'tai nghe, cáp sạc', 'Đen, Đen', '2025-12-23 23:20:02', 'Đã giao hàng', 3, '2025-12-23 23:21:10', NULL, NULL),
(242, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-24 17:17:13', 'Đã hủy', NULL, NULL, NULL, 1),
(243, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0006', 'JBL Live Pro 2 (x1)', 'prod_69149a1d2b2b44.12463294.jpg', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-25 03:06:41', 'Đã hủy', NULL, NULL, NULL, 1),
(244, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'đen', '2025-12-25 03:11:28', 'Đã hủy', NULL, NULL, NULL, 1),
(245, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-25 03:12:49', 'Đã hủy', NULL, NULL, NULL, 1),
(246, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-25 03:14:29', 'Đã hủy', NULL, NULL, NULL, 1),
(247, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'mặc định', '2025-12-25 03:16:12', 'Đã hủy', NULL, NULL, NULL, 1),
(248, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Mặc định', '2025-12-25 03:18:47', 'Đã hủy', NULL, NULL, NULL, 1),
(249, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Mặc định', '2025-12-25 03:23:03', 'Đã thanh toán', NULL, NULL, NULL, NULL),
(250, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2025-12-25 03:23:22', 'Đã giao hàng', NULL, NULL, NULL, NULL),
(253, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0006', 'JBL Live Pro 2 (x1)', 'prod_69149a1d2b2b44.12463294.jpg', 1, 1500000.00, 'tai nghe', 'Trắng', '2025-12-29 06:59:50', 'Đã giao hàng', NULL, NULL, NULL, NULL),
(256, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Mặc định', '2025-12-29 15:51:23', 'Đã giao hàng', NULL, NULL, NULL, NULL),
(257, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0011', 'Bluetooth True Wireless Havit TW976 (x1)', 'prod_695cbabc5f082.png', 1, 1000000.00, 'tai nghe', 'Đen', '2026-01-07 16:39:51', 'Đã hủy', NULL, NULL, NULL, 1),
(258, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2026-01-07 16:47:08', 'Đã giao hàng', 2, '2026-01-07 16:55:22', NULL, NULL),
(259, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0010', 'Baseus Crystal Shine Type-C to Lightning 2M (x1)', 'prod_6927a6d39c3c1.png', 1, 150000.00, 'cáp sạc', 'Mặc định', '2026-01-07 16:48:39', 'Đã hủy', NULL, NULL, NULL, 1),
(260, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0011', 'Bluetooth True Wireless Havit TW976 (x1)', 'prod_695cbabc5f082.png', 1, 1000000.00, 'tai nghe', 'Đen', '2026-01-07 16:56:55', 'Đã hủy', NULL, NULL, NULL, 1),
(261, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0026', 'Baseus Explorer Series Type-C to iPhone 20W 1M (x1)', 'prod_6975dc8ff2a89.jpg', 1, 150000.00, 'cáp sạc', 'Mặc định', '2026-01-25 09:08:53', 'Đã giao hàng', 2, '2026-01-25 09:09:29', NULL, NULL),
(262, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0006', 'JBL Live Pro 2 (x1)', 'prod_69149a1d2b2b44.12463294.jpg', 1, 1500000.00, 'tai nghe', 'Đen', '2026-01-27 08:24:34', 'Đã hủy', NULL, NULL, NULL, NULL),
(263, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0007', 'Sony WF-1000XM5 (x1)', 'prod_691520dee469a9.26207248.jpg', 1, 4000000.00, 'tai nghe', 'Đen', '2026-01-27 18:26:20', 'Đã hủy', NULL, NULL, NULL, NULL),
(264, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0011', 'Havit TW976 (x1)', 'prod_695cbabc5f082.png', 1, 1000000.00, 'tai nghe', 'Đen', '2026-01-27 19:36:23', 'Đã hủy', NULL, NULL, NULL, NULL),
(265, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0011', 'Havit TW976 (x1)', 'prod_695cbabc5f082.png', 1, 1000000.00, 'tai nghe', 'Đen', '2026-01-27 19:48:19', 'Đã hủy', NULL, NULL, NULL, NULL),
(266, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-27 19:57:44', 'Đã hủy', NULL, NULL, NULL, NULL),
(267, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-27 20:07:26', 'Đã hủy', NULL, NULL, NULL, NULL),
(268, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0006', 'JBL Live Pro 2 (x1)', 'prod_69149a1d2b2b44.12463294.jpg', 1, 1500000.00, 'tai nghe', 'Đen', '2026-01-28 03:45:18', 'Đã hủy', NULL, NULL, NULL, NULL),
(269, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-28 04:21:28', 'Đã hủy', NULL, NULL, NULL, NULL),
(270, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0005', 'JBL Wave Buds 2 (x1)', 'prod_691499b3822bb6.32063747.jpg', 1, 1300000.00, 'tai nghe', 'Đen', '2026-01-28 04:47:35', 'Đã hủy', NULL, NULL, NULL, NULL),
(271, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0005', 'JBL Wave Buds 2 (x1)', 'prod_691499b3822bb6.32063747.jpg', 1, 1300000.00, 'tai nghe', 'Đen', '2026-01-28 06:33:35', 'Đã hủy', NULL, NULL, NULL, NULL),
(272, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe', 'Đen, Đen', '2026-01-28 06:55:53', 'Đã hủy', NULL, NULL, NULL, NULL),
(273, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 07:20:54', 'Đã hủy', NULL, NULL, NULL, NULL),
(274, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005, SP0006', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1), JBL Live Pro 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg, prod_69149a1d2b2b44.12463294.jpg', 3, 4000000.00, 'tai nghe, ', 'Đen, Đen, Đen', '2026-01-28 07:23:38', 'Đã thanh toán', NULL, NULL, NULL, NULL),
(275, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:06:53', 'Đã hủy', NULL, NULL, NULL, NULL),
(276, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:22:06', 'Đã hủy', NULL, NULL, NULL, NULL),
(277, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-28 08:23:30', 'Đã hủy', NULL, NULL, NULL, NULL),
(278, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:23:46', 'Đã hủy', NULL, NULL, NULL, NULL),
(279, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-28 08:24:26', 'Đã hủy', NULL, NULL, NULL, NULL),
(280, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:24:54', 'Đã hủy', NULL, NULL, NULL, NULL),
(281, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:36:20', 'Chờ xử lý', NULL, NULL, NULL, NULL),
(282, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-28 08:36:54', 'Chờ xử lý', NULL, NULL, NULL, NULL),
(283, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:40:17', 'Chờ xử lý', NULL, NULL, NULL, NULL),
(284, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0005', 'JBL Wave Buds 2 (x1)', 'prod_691499b3822bb6.32063747.jpg', 1, 1300000.00, '', 'Đen', '2026-01-28 08:43:09', 'Chờ xử lý', NULL, NULL, NULL, NULL),
(285, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004', 'JBL Wave Beam (x1)', 'prod_69149987e535d7.28187096.jpg', 1, 1200000.00, 'tai nghe', 'Đen', '2026-01-28 08:48:06', 'Chờ xử lý', NULL, NULL, NULL, NULL),
(286, 'Nam', 'Namdo2003hp@gmail.com', '0948303968', '198 Lê Duẩn Kiến An Hải Phòng', '8BHF5H1K', 'SP0004, SP0005', 'JBL Wave Beam (x1), JBL Wave Buds 2 (x1)', 'prod_69149987e535d7.28187096.jpg, prod_691499b3822bb6.32063747.jpg', 2, 2500000.00, 'tai nghe, ', 'Đen, Đen', '2026-01-28 08:48:19', 'Chờ xử lý', NULL, NULL, NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_shipper` (`shipper_id`),
  ADD KEY `fk_payment_user` (`user_code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_shipper` FOREIGN KEY (`shipper_id`) REFERENCES `shipper` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_code`) REFERENCES `users` (`user_code`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
