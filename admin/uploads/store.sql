-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 15, 2025 lúc 02:42 PM
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
-- Cấu trúc bảng cho bảng `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `featured_products`
--

CREATE TABLE `featured_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `featured_products`
--

INSERT INTO `featured_products` (`id`, `product_id`, `sort_order`, `created_at`) VALUES
(9, 71, 0, '2025-12-14 21:06:12'),
(10, 70, 0, '2025-12-14 21:06:22'),
(11, 69, 0, '2025-12-14 21:06:24'),
(12, 68, 0, '2025-12-14 21:06:26'),
(13, 81, 0, '2025-12-14 21:06:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `product_code`, `rating`, `message`, `created_at`) VALUES
(20, 15, 'SP0006', 5, 'TỐT', '2025-11-24 07:59:11'),
(39, 30, 'SP0005', 5, 'TỐT', '2025-12-15 13:20:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `home`
--

CREATE TABLE `home` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `home`
--

INSERT INTO `home` (`id`, `title`, `description`, `image`) VALUES
(1, '', '', 'Banner 2026.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `import_price` decimal(15,2) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory_history`
--

INSERT INTO `inventory_history` (`id`, `product_id`, `product_code`, `color`, `quantity_change`, `import_price`, `note`, `created_at`, `type`) VALUES
(196, 70, 'SP0006', 'Đen', 100, 800000.00, 'Thêm màu mới', '2025-11-27 01:22:15', 'Nhập hàng'),
(197, 68, 'SP0004', 'Đen', 100, 700000.00, 'Thêm màu mới', '2025-11-27 01:22:27', 'Nhập hàng'),
(198, 69, 'SP0005', 'Đen', 100, 900000.00, 'Thêm màu mới', '2025-11-27 01:22:49', 'Nhập hàng'),
(199, 71, 'SP0007', 'Đen', 50, 2000000.00, 'Thêm màu mới', '2025-11-27 01:23:08', 'Nhập hàng'),
(200, 81, 'SP0010', 'Đen', 50, 700000.00, 'Thêm màu mới', '2025-11-27 01:23:32', 'Nhập hàng'),
(201, 81, 'SP0010', 'Đen', -50, 700000.00, 'Xóa toàn bộ màu này', '2025-11-27 01:23:39', 'Xóa hàng'),
(202, 81, 'SP0010', 'Đen', 50, 70000.00, 'Thêm màu mới', '2025-11-27 01:23:55', 'Nhập hàng'),
(203, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho từ đơn đã thanh toán (Payment ID: 90)', '2025-11-27 01:31:19', 'Bán hàng'),
(223, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 91)', '2025-11-27 06:31:04', 'Hoàn trả'),
(301, 70, 'SP0006', 'Đen', 2, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-12 14:02:56', 'Bán hàng'),
(302, 71, 'SP0007', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-12 14:02:56', 'Bán hàng'),
(303, 70, 'SP0006', 'Đen', 2, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 142)', '2025-12-14 22:56:24', 'Hoàn trả'),
(304, 71, 'SP0007', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 142)', '2025-12-14 22:56:24', 'Hoàn trả'),
(305, 81, 'SP0010', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 00:00:20', 'Bán hàng'),
(306, 71, 'SP0007', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 00:00:20', 'Bán hàng'),
(307, 81, 'SP0010', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 143)', '2025-12-15 00:00:50', 'Hoàn trả'),
(308, 71, 'SP0007', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 143)', '2025-12-15 00:00:50', 'Hoàn trả'),
(309, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 09:23:08', 'Bán hàng'),
(310, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 144)', '2025-12-15 09:25:07', 'Hoàn trả'),
(311, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 09:25:42', 'Bán hàng'),
(312, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 09:29:42', 'Bán hàng'),
(313, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 145)', '2025-12-15 09:35:08', 'Hoàn trả'),
(314, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 146)', '2025-12-15 09:35:08', 'Hoàn trả'),
(316, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 09:37:47', 'Bán hàng'),
(317, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 148)', '2025-12-15 09:38:51', 'Hoàn trả'),
(318, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 11:55:30', 'Bán hàng'),
(319, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 149)', '2025-12-15 11:57:04', 'Hoàn trả'),
(320, 70, 'SP0006', 'Đen', 1, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 12:00:23', 'Bán hàng'),
(321, 71, 'SP0007', 'Đen', 2, 0.00, 'Trừ tồn kho khi đặt hàng (User: TPLAHAMY)', '2025-12-15 12:00:23', 'Bán hàng'),
(322, 70, 'SP0006', 'Đen', 1, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 150)', '2025-12-15 12:09:53', 'Hoàn trả'),
(323, 71, 'SP0007', 'Đen', 2, 0.00, 'Hoàn lại tồn kho từ đơn hủy (Payment ID: 150)', '2025-12-15 12:09:53', 'Hoàn trả');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `sender_role` enum('user','admin') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(107, 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310', '9AAG09I0', 'SP0011', 'THY (x12)', 12, 1800000.00, 'ốp lưng', 'Mặc định', '2025-11-27 06:22:51', 'Đã hủy', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('tai nghe','cáp sạc','ốp lưng','kính cường lực') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Active/Enabled, 0 = Inactive/Disabled',
  `product_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `color`, `image`, `category`, `is_active`, `product_code`) VALUES
(68, 'JBL Wave Beam', 1200000.00, 'Đen,Trắng,Vàng,Xanh', 'prod_69149987e535d7.28187096.jpg', 'tai nghe', 1, 'SP0004'),
(69, 'JBL Wave Buds 2', 1300000.00, 'Đen,Trắng', 'prod_691499b3822bb6.32063747.jpg', 'tai nghe', 1, 'SP0005'),
(70, 'JBL Live Pro 2', 1500000.00, 'Đen,Trắng', 'prod_69149a1d2b2b44.12463294.jpg', 'tai nghe', 1, 'SP0006'),
(71, 'Sony WF-1000XM5', 4000000.00, 'Đen,Trắng', 'prod_691520dee469a9.26207248.jpg', 'tai nghe', 1, 'SP0007'),
(81, 'Baseus Crystal Shine Type-C to Lightning 2M', 150000.00, 'Đen,Trắng,Xanh', 'prod_6927a6d39c3c1.png', 'cáp sạc', 1, 'SP0010');

--
-- Bẫy `products`
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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_details`
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

--
-- Đang đổ dữ liệu cho bảng `product_details`
--

INSERT INTO `product_details` (`detail_id`, `product_id`, `description`, `material`, `compatibility`, `warranty`, `origin`, `features`) VALUES
(15, 68, 'Tai nghe không dây JBL Wave Beam sở hữu thiết kế công thái học nhỏ gọn cùng âm thanh vượt trội. Mẫu tai nghe JBL này hứa hẹn sẽ mang lại cho người dùng những trải nghiệm âm thanh vượt trội.', 'Nhựa cao cấp chống bám vân tay', 'IOS , Android..........', 'Bảo hành 12 tháng 1 đổi 1 trong vòng 15 ngày nếu phát sinh lỗi phần cứng từ nhà sản xuất ', 'JBL', 'JBL Deep Bass Sound /               \r\nChống nước IP54'),
(16, 69, 'Chiếc tai nghe JBL Wave Buds 2 là sản phẩm âm thanh nổi bật với công nghệ âm thanh JBL Pure Bass, nhờ đó mà nó mang đến âm bass mạnh mẽ, sống động. Tính năng Khử tiếng ồn chủ động (ANC) giúp loại bỏ tiếng ồn xung quanh, trong khi Smart Ambient cho phép điều chỉnh âm thanh môi trường. Với 4 micro, cuộc gọi luôn rõ ràng và chuẩn IP54 đảm bảo khả năng kháng nước và bụi. Thời lượng pin lên đến 40 giờ cùng khả năng sạc nhanh, và tính năng Multi-point giúp tai nghe kết nối hai thiết bị Bluetooth cùng lúc.', 'Nhựa cao cấp ', 'IOS , Android .......', 'Bảo hành 12 tháng 1 đổi 1 trong 15 ngày nếu phát sinh lỗi phần cứng từ nhà sản xuất ', 'JBL', 'Chống nước và bụi IP54'),
(17, 70, 'Sản phẩm JBL Live Pro 2 có thiết kế độc đáo, hút mắt cùng khả năng loại bỏ tiếng ồn tuyệt vời đã thu hút sự quan tâm của không ít người dùng. Dòng tai nghe không dây có thể thưởng thức âmnhạc, thực hiện cuộc gọi và không bị ảnh hưởng bởi bất kỳ tiếng ồn nào từ môi trường xung quanh. ', 'Nhựa cao cấp chống bám dính vân tay', 'IOS , Android ..................', 'Bảo hành 12 tháng 1 đổi 1 trong 15 ngày nếu có phát sinh lỗi phần cứng từ nhà sản xuất', 'JBL', 'Kháng nước IPX5'),
(18, 71, 'Sony WF-1000XM5 là mẫu tai nghe không dây với ba lựa chọn màu hông khói, bạc bạch kim và đen sang trọng cũng như màng loa 8.4mm ấn tượng. Tai nghe sở hữu pin dung lượng mang lại thời lượng sử dụng lên đến tối đa 8 tiếng khi tắt chế độ chống ồn.', 'Nhựa chống bám vân tay cao cấp', 'IOS , Android ..........', 'Bảo hành 12 tháng 1 đổi 1 trong vòng 15 ngày nếu phát sinh lỗi phần cứng từ nhà sản xuất', 'Sony', 'Chức năng theo dõi đầu -\r\nChống ồn đàm thoại -\r\nChống nước IPX4'),
(19, 81, 'Cáp Type-C to Lightning Baseus Crystal Shine 2m hỗ trợ nạp lại đầy năng lượng cho thiết bị di động nhanh chóng nhờ sở hữu mức công suất lên đến 20W. Đồng thời, dòng cáp sạc Baseus Crystal Shine cao cấp này cũng được trang bị chiều dài lên đến 2m, đảm bảo sự tiện lợi khi sạc máy ở khoảng cách xa. Đặc biệt hơn, cáp sạc Type C to Lightning Baseus còn được chế tạo từ chất liệu Nylon Carbon chất lượng cao, đảm bảo độ bền bỉ cao, hạn chế đứt gãy ấn tượng.', 'Nylon Carbon chất lượng cao', 'Iphone', 'Bảo hành 12 tháng 1 đổi 1 trong 15 ngày nếu có phát sinh lỗi phần cứng từ nhà sản xuất', 'Baseus', '	\r\nSạc nhanh\r\nTruyền tải dữ liệu lên đến 480Mbps - \r\nĐầu cáp được thiết kế đặc biệt, chống hiện tượng gảy đầu cáp\r\n- Kiểm soát nhiệt độ thông minh\r\n- Cáp chống rối và chống kéo');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_inventory`
--

CREATE TABLE `product_inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'Liên kết tới products.id',
  `product_code` varchar(50) NOT NULL,
  `color` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho',
  `import_price` decimal(10,2) NOT NULL COMMENT 'Giá nhập sản phẩm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_inventory`
--

INSERT INTO `product_inventory` (`id`, `product_id`, `product_code`, `color`, `quantity`, `import_price`, `created_at`, `updated_at`) VALUES
(103, 70, 'SP0006', 'Đen', 100, 800000.00, '2025-11-27 01:22:15', '2025-12-15 12:09:53'),
(104, 68, 'SP0004', 'Đen', 100, 700000.00, '2025-11-27 01:22:27', '2025-11-27 01:22:27'),
(105, 69, 'SP0005', 'Đen', 100, 900000.00, '2025-11-27 01:22:49', '2025-11-27 01:22:49'),
(106, 71, 'SP0007', 'Đen', 50, 2000000.00, '2025-11-27 01:23:08', '2025-12-15 12:09:53'),
(108, 81, 'SP0010', 'Đen', 50, 70000.00, '2025-11-27 01:23:55', '2025-12-15 00:00:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `image`, `link`, `created_at`) VALUES
(1, 'TƯNG BỪNG KHUYẾN MÃI CÁC SẢN PHẨM SAU', 'TỪ NGÀY 20/11/2025 ', 'Vua-Phu-Kien-dien-thoai-Thai-Ha-Ha-Noi.jpg', 'https://www.facebook.com/', '2025-11-05 00:21:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipper`
--

CREATE TABLE `shipper` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `cmt` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shipper`
--

INSERT INTO `shipper` (`id`, `name`, `email`, `phone`, `dob`, `cmt`, `avatar`, `password`) VALUES
(2, 'Nguyễn Lê Hoài Nam', 'Namdo2003hp@gmail.com', '0587911287', '2003-06-07', '031203001868', 'uploads/shipper_2.jpg', '$2y$10$CVC9aIcrry1LrBYyxzWQ1.K5bQxQnv9ghavpyCEVYj2j09GVQDF.a'),
(3, 'Dương Quang Huy', 'Duongquanghuy@gmail.com', '0933585789', '2025-11-02', '0312555666', 'uploads/shipper_3.jpg', '$2y$10$oxzLJOgXPnTs9LeI8.GDROOrd5LLxH7U8P98P3Plg0lBR6duiP9e2');

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
(14, 'Hiền ', 'Hien@gmail.com', '749RRNLX', '$2y$10$krVYb2PZxDF0ii1ILwWc0OAmH991Qny9O2p.GZ/5K2HV6/kx7JXMO', '2025-10-15 16:04:18'),
(15, 'Huy', 'Duongquanghuy@gmail.com', '9AAG09I0', '$2y$10$oBZh6ek3IN.t10CjTvxmJugdu3.XalKh51WTZDV/EvM7VtTAzKXRm', '2025-10-23 07:37:27'),
(18, 'Nam123', 'Nammac2003@gmail.com', '44C25ACD', '$2y$10$Y3LuNN/J3lXPjh6..STEduRnPN9B5HSa9rLAtGuXSgM6V8mDHFz9C', '2025-11-25 16:34:51'),
(19, 'Hoàng', 'Hoang@gmail.com', '5O82M5I7', '$2y$10$R5DdlvED9z3lnAWMVfkn4.k4LNxGS3GJLJoLCid6bN8XkwdQ5yMLe', '2025-12-08 15:38:33'),
(30, 'Nam', 'Namdo2003hp@gmail.com', 'ON3HTJPB', '$2y$10$lNE1YUYAVjcA1Qvj27rcauA0.f1Ko9PxzYBMMN4cMuJeWnjTHPZMq', '2025-12-15 13:00:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_profile`
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
-- Đang đổ dữ liệu cho bảng `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `user_code`, `name`, `email`, `phone`, `address`) VALUES
(14, '749RRNLX', 'Hiền ', 'Hien@gmail.com', NULL, NULL),
(15, '9AAG09I0', 'Huy', 'Duongquanghuy@gmail.com', '0948003196', '310'),
(18, '44C25ACD', 'Nam123', 'Nammac2003@gmail.com', NULL, NULL),
(19, '5O82M5I7', 'Hoàng', 'Hoang@gmail.com', NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `featured_products`
--
ALTER TABLE `featured_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_product` (`product_id`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_code` (`product_code`);

--
-- Chỉ mục cho bảng `home`
--
ALTER TABLE `home`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_history_ibfk_1` (`product_id`);

--
-- Chỉ mục cho bảng `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_shipper` (`shipper_id`),
  ADD KEY `fk_payment_user` (`user_code`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `product_code_2` (`product_code`);

--
-- Chỉ mục cho bảng `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product_inventory`
--
ALTER TABLE `product_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_color_unique` (`product_id`,`color`),
  ADD KEY `fk_product_code` (`product_code`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `shipper`
--
ALTER TABLE `shipper`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_code` (`user_code`);

--
-- Chỉ mục cho bảng `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `featured_products`
--
ALTER TABLE `featured_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `home`
--
ALTER TABLE `home`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;

--
-- AUTO_INCREMENT cho bảng `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT cho bảng `product_details`
--
ALTER TABLE `product_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `product_inventory`
--
ALTER TABLE `product_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `shipper`
--
ALTER TABLE `shipper`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `featured_products`
--
ALTER TABLE `featured_products`
  ADD CONSTRAINT `fk_featured_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_shipper` FOREIGN KEY (`shipper_id`) REFERENCES `shipper` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_code`) REFERENCES `users` (`user_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_details`
--
ALTER TABLE `product_details`
  ADD CONSTRAINT `product_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_inventory`
--
ALTER TABLE `product_inventory`
  ADD CONSTRAINT `fk_product_code` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `fk_user_profile_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
