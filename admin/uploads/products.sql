-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 28, 2026 lúc 07:21 AM
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
(95, 'Havit TW976', 1000000.00, 'Đen,Xanh', 'prod_695cbabc5f082.png', 'tai nghe', 1, 'SP0011'),
(96, 'Samsung Galaxy Buds 3', 2500000.00, 'Đen,Trắng', 'prod_6975ceb703641.jpg', 'tai nghe', 1, 'SP0012'),
(97, 'HUAWEI FreeClip 2', 3000000.00, 'Đen,Trắng,Xanh', 'prod_6975d12e8cbec.jpg', 'tai nghe', 1, 'SP0013'),
(98, 'JBL Tour Pro 2', 2500000.00, 'Đen,Vàng', 'prod_6975d22dd8d3d.jpg', 'tai nghe', 1, 'SP0014'),
(99, 'Huawei Freebuds SE 4', 500000.00, 'Đen,Trắng', 'prod_6975d2ecc34f3.jpg', 'tai nghe', 1, 'SP0015'),
(100, 'SoundPEATS Capsule 3 Pro Plus', 900000.00, 'Đen', 'prod_6975d474cac1d.jpg', 'tai nghe', 1, 'SP0016'),
(101, 'Beats Solo Buds', 1000000.00, 'Đen,Trắng', 'prod_6975d52212c7c.jpg', 'tai nghe', 1, 'SP0017'),
(102, 'StarGO Tune Pro (ANC)', 150000.00, 'Đen', 'prod_6975d5b331a41.jpg', 'tai nghe', 1, 'SP0018'),
(103, 'QCY Ailybuds Lite (T29)', 190000.00, 'Đen,Trắng', 'prod_6975d697caf0d.jpg', 'tai nghe', 1, 'SP0019'),
(104, 'SoundPeats Air 5', 900000.00, 'Đen,Trắng', 'prod_6975d75846584.jpg', 'tai nghe', 1, 'SP0020'),
(105, 'Sony WF-C510', 850000.00, 'Đen,Trắng,Vàng,Xanh', 'prod_6975d81128132.jpg', 'tai nghe', 1, 'SP0021'),
(106, 'HUAWEI FreeClip', 2800000.00, 'Đen,Vàng', 'prod_6975d928aba0b.jpg', 'tai nghe', 1, 'SP0022'),
(107, 'Marshall Minor IV', 2900000.00, 'Đen,Trắng', 'prod_6975d9cd28693.jpg', 'tai nghe', 1, 'SP0023'),
(108, 'JBL Soundgear Sense', 2950000.00, 'Đen', 'prod_6975da5a6cc60.jpg', 'tai nghe', 1, 'SP0024'),
(109, 'Baseus Explorer Series Type-C to Lightning 20W 2m-Tím', 150000.00, 'Mặc định', 'prod_6975db11083aa.jpg', 'cáp sạc', 1, 'SP0025'),
(110, 'Baseus Explorer Series Type-C to iPhone 20W 1M', 150000.00, 'Mặc định', 'prod_6975dc8ff2a89.jpg', 'cáp sạc', 1, 'SP0026'),
(111, 'Cáp Ugreen Uno Usb-C To Usb-C 100W dài 1M L509 35501', 150000.00, 'Mặc định', 'prod_6975e34c04d13.jpg', 'cáp sạc', 1, 'SP0027'),
(112, 'Cáp sạc chuyển đổi Momax Silicon USB-C to Lightning 30W dài 1.2m - Cũ', 220000.00, 'Đen,Trắng,Hồng', 'prod_6975e44f14794.jpg', 'cáp sạc', 1, 'SP0028'),
(113, 'Cáp sạc Anker Nylon 2 trong 1 USB-C to USB-C 140W dài 1.2m A8895', 300000.00, 'Đen', 'prod_6975e5488a97a.jpg', 'cáp sạc', 1, 'SP0029'),
(114, 'Cáp sạc nhanh Baseus Cafule PD 2.0 100W Type-C to Type-C (20V 5A) 2M', 100000.00, 'Đen', 'prod_6975e5eb7b731.jpg', 'cáp sạc', 1, 'SP0030'),
(115, 'iPhone 17 Pro Slimcase Unique Clear', 400000.00, 'Mặc định', 'prod_6975e711c5e31.jpg', 'ốp lưng', 1, 'SP0031'),
(116, 'Ốp lưng Silicon Samsung Galaxy S25 Ultra chính hãng', 490000.00, 'Đen,Xanh', 'prod_6975e8ce0325d.jpg', 'ốp lưng', 1, 'SP0032'),
(117, 'Ốp lưng iPhone 15 Likgus PC', 100000.00, 'Đen,Trắng,Xanh', 'prod_6975e9aa2f3f6.jpg', 'ốp lưng', 1, 'SP0033'),
(118, 'Ốp lưng Samsung Galaxy S24 Ultra Araree Nukin Clear', 250000.00, 'Mặc định', 'prod_6975ea96cc020.jpg', 'ốp lưng', 1, 'SP0034'),
(119, 'Ốp lưng iPhone 17 Pro Max Slimcase Classic 2 With Magsafe Clear', 495000.00, 'Mặc định', 'prod_6975eb80a7083.jpg', 'ốp lưng', 1, 'SP0035'),
(120, 'Ốp lưng iPhone 16 Pro Max Anker With Magsafe And Ring Stand Titanium Golden', 495000.00, 'Vàng', 'prod_6975ec958665b.jpg', 'ốp lưng', 1, 'SP0036'),
(121, 'Dán kính cường lực màn hình Samsung Galaxy A16 Zagg Full cao cấp', 350000.00, 'Mặc định', 'prod_6975ede36b837.jpg', 'kính cường lực', 1, 'SP0037');

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

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `product_code_2` (`product_code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
