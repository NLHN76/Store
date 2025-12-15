-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 15, 2025 lúc 10:44 AM
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

INSERT INTO `payment` (`id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `user_code`, `product_code`, `product_name`, `product_quantity`, `total_price`, `category`, `color`, `order_date`, `status`, `shipper_id`, `receive_date`, `is_deducted`, `is_restored`) VALUES
(90, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-11-27 01:30:34', 'Đã giao hàng', 2, '2025-11-27 01:32:01', 1, NULL),
(91, 'Nam', 'Namdo2003hp@gmail.com', '0948003196', '310 ', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-11-27 01:33:29', 'Đã hủy', NULL, NULL, NULL, 1),
(92, 'Nam', 'Namdo2003hp@gmail.com', '0948003196', '310 ', 'TPLAHAMY', 'SP0011', 'THY (x3)', 3, 450000.00, 'ốp lưng', 'Mặc định', '2025-11-27 01:44:47', 'Đã giao hàng', NULL, NULL, 1, NULL),
(107, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0011', 'THY (x12)', 12, 1800000.00, 'ốp lưng', 'Mặc định', '2025-11-27 06:22:51', 'Đã hủy', NULL, NULL, NULL, 1),
(108, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0011', 'THY (x5)', 5, 750000.00, 'ốp lưng', 'Mặc định', '2025-11-27 06:32:01', 'Đã hủy', NULL, NULL, 1, 1),
(109, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0011', 'THY (x2)', 2, 300000.00, 'ốp lưng', 'Mặc định', '2025-11-27 06:34:19', 'Đã hủy', NULL, NULL, NULL, 1),
(110, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0011', 'THY (x2)', 2, 300000.00, 'ốp lưng', 'Mặc định', '2025-11-27 06:37:29', 'Đã hủy', NULL, NULL, NULL, 1),
(116, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN', 'TPLAHAMY', 'SP0011', 'THY (x3)', 3, 450000.00, 'ốp lưng', 'Mặc định', '2025-12-03 19:53:13', 'Đã hủy', NULL, NULL, NULL, 1),
(139, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 ', 'TPLAHAMY', 'SP0011', 'THY (x1)', 1, 150000.00, 'ốp lưng', 'Mặc định', '2025-12-08 13:54:46', 'Đã hủy', NULL, NULL, NULL, 1),
(140, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0011', 'THY (x1)', 1, 150000.00, 'ốp lưng', 'Mặc định', '2025-12-08 14:34:45', 'Đã giao hàng', NULL, NULL, NULL, NULL),
(142, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG', 'TPLAHAMY', 'SP0006, SP0007', 'JBL Live Pro 2 (x2), Sony WF-1000XM5 (x1)', 3, 7000000.00, 'tai nghe', 'Đen, Đen', '2025-12-12 14:02:56', 'Đã hủy', NULL, NULL, NULL, 1),
(143, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LE DUAN KIEN AN HAI PHONG', 'TPLAHAMY', 'SP0010, SP0007', 'Baseus Crystal Shine Type-C to Lightning 2M (x1), Sony WF-1000XM5 (x1)', 2, 4150000.00, 'cáp sạc, tai nghe', 'Đen, Đen', '2025-12-15 00:00:20', 'Đã hủy', NULL, NULL, NULL, 1),
(144, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-15 09:23:08', 'Đã hủy', NULL, NULL, NULL, 1),
(145, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LE DUAN KIEN AN HAI PHONG', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-15 09:25:42', 'Đã hủy', NULL, NULL, NULL, 1),
(146, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310 LE DUAN KIEN AN HAI PHONG', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-15 09:29:42', 'Đã hủy', NULL, NULL, NULL, 1),
(148, 'Nam', 'Namdo2003hp@gmail.com', '0587911287', '310', 'TPLAHAMY', 'SP0006', 'JBL Live Pro 2 (x1)', 1, 1500000.00, 'tai nghe', 'Đen', '2025-12-15 09:37:47', 'Đã hủy', NULL, NULL, NULL, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

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
