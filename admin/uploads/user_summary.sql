-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 15, 2025 lúc 11:18 AM
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
-- Cấu trúc bảng cho bảng `user_summary`
--

CREATE TABLE `user_summary` (
  `user_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user_summary`
--

INSERT INTO `user_summary` (`user_code`, `name`, `email`, `phone`, `address`) VALUES
('44C25ACD', 'Nam123', 'Nammac2003@gmail.com', NULL, NULL),
('5O82M5I7', 'Hoàng', 'Hoang@gmail.com', NULL, NULL),
('749RRNLX', 'Hiền ', 'Hien@gmail.com', NULL, NULL),
('9AAG09I0', 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310'),
('ADMIN001', 'Admin', 'admin@example.com', NULL, NULL),
('TPLAHAMY', 'Nam', 'Namdo2003hp@gmail.com', '0948003196', '310 LÊ DUẨN KIẾN AN HẢI PHÒNG');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `user_summary`
--
ALTER TABLE `user_summary`
  ADD PRIMARY KEY (`user_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
