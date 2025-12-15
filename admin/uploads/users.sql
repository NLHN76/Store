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
-- Cấu trúc bảng cho bảng `users`
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
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `user_code`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@example.com', 'ADMIN001', 'e10adc3949ba59abbe56e057f20f883e', '2025-10-15 15:53:48'),
(14, 'Hiền ', 'Hien@gmail.com', '749RRNLX', '$2y$10$krVYb2PZxDF0ii1ILwWc0OAmH991Qny9O2p.GZ/5K2HV6/kx7JXMO', '2025-10-15 16:04:18'),
(15, 'Huy', 'Duongquanghuy@gmail.com', '9AAG09I0', '$2y$10$oBZh6ek3IN.t10CjTvxmJugdu3.XalKh51WTZDV/EvM7VtTAzKXRm', '2025-10-23 07:37:27'),
(16, 'Nam', 'Namdo2003hp@gmail.com', 'TPLAHAMY', '$2y$10$PWvAPhVtFul.mFnTQxby6e6Uo8GUwzmRdAR8cQmI0XVf873/6tra6', '2025-10-30 03:25:59'),
(18, 'Nam123', 'Nammac2003@gmail.com', '44C25ACD', '$2y$10$Y3LuNN/J3lXPjh6..STEduRnPN9B5HSa9rLAtGuXSgM6V8mDHFz9C', '2025-11-25 16:34:51'),
(19, 'Hoàng', 'Hoang@gmail.com', '5O82M5I7', '$2y$10$R5DdlvED9z3lnAWMVfkn4.k4LNxGS3GJLJoLCid6bN8XkwdQ5yMLe', '2025-12-08 15:38:33');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_code` (`user_code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
