-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 08, 2023 lúc 02:46 PM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `unitop_db_place`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `province`
--

CREATE TABLE `province` (
  `province_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tỉnh thành';

--
-- Đang đổ dữ liệu cho bảng `province`
--

INSERT INTO `province` (`province_id`, `name`) VALUES
(1, ' Hà Nội'),
(2, ' Hà Giang'),
(3, ' Cao Bằng'),
(4, ' Bắc Kạn'),
(5, ' Tuyên Quang'),
(6, ' Lào Cai'),
(7, ' Điện Biên'),
(8, ' Lai Châu'),
(9, ' Sơn La'),
(10, ' Yên Bái'),
(11, ' Hoà Bình'),
(12, ' Thái Nguyên'),
(13, ' Lạng Sơn'),
(14, ' Quảng Ninh'),
(15, ' Bắc Giang'),
(16, ' Phú Thọ'),
(17, ' Vĩnh Phúc'),
(18, ' Bắc Ninh'),
(19, ' Hải Dương'),
(20, ' Hải Phòng'),
(21, ' Hưng Yên'),
(22, ' Thái Bình'),
(23, ' Hà Nam'),
(24, ' Nam Định'),
(25, ' Ninh Bình'),
(26, ' Thanh Hóa'),
(27, ' Nghệ An'),
(28, ' Hà Tĩnh'),
(29, ' Quảng Bình'),
(30, ' Quảng Trị'),
(31, ' Thừa Thiên Huế'),
(32, ' Đà Nẵng'),
(33, ' Quảng Nam'),
(34, ' Quảng Ngãi'),
(35, ' Bình Định'),
(36, ' Phú Yên'),
(37, ' Khánh Hòa'),
(38, ' Ninh Thuận'),
(39, ' Bình Thuận'),
(40, ' Kon Tum'),
(41, ' Gia Lai'),
(42, ' Đắk Lắk'),
(43, ' Đắk Nông'),
(44, ' Lâm Đồng'),
(45, ' Bình Phước'),
(46, ' Tây Ninh'),
(47, ' Bình Dương'),
(48, ' Đồng Nai'),
(49, ' Bà Rịa - Vũng Tàu'),
(50, ' Hồ Chí Minh'),
(51, ' Long An'),
(52, ' Tiền Giang'),
(53, ' Bến Tre'),
(54, ' Trà Vinh'),
(55, ' Vĩnh Long'),
(56, ' Đồng Tháp'),
(57, ' An Giang'),
(58, ' Kiên Giang'),
(59, ' Cần Thơ'),
(60, ' Hậu Giang'),
(61, ' Sóc Trăng'),
(62, ' Bạc Liêu'),
(63, ' Cà Mau');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `province`
--
ALTER TABLE `province`
  ADD PRIMARY KEY (`province_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `province`
--
ALTER TABLE `province`
  MODIFY `province_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
