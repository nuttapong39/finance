-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2015 at 05:00 PM
-- Server version: 5.6.24
-- PHP Version: 5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `states`
--

-- --------------------------------------------------------

--
-- Table structure for table `province`
--

CREATE TABLE IF NOT EXISTS `province` (
  `province_id` int(11) NOT NULL,
  `province_name` varchar(200) CHARACTER SET tis620 NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `province`
--

INSERT INTO `province` (`province_id`, `province_name`) VALUES
(1, 'กระบี่'),
(2, 'กรุงเทพมหานคร'),
(3, 'กาญจนบุรี'),
(4, 'กาฬสินธุ์'),
(5, 'กำแพงเพชร'),
(6, 'ขอนแก่น'),
(7, 'จันทบุรี'),
(8, 'ฉะเชิงเทรา'),
(9, 'ชลบุรี'),
(10, 'ชัยนาท'),
(11, 'ชัยภูมิ'),
(12, 'ชุมพร'),
(13, 'เชียงราย'),
(14, 'เชียงใหม่'),
(15, 'ตรัง'),
(16, 'ตราด'),
(17, 'ตาก'),
(18, 'นครนายก'),
(19, 'นครปฐม'),
(20, 'นครพนม'),
(21, 'นครราชสีมา'),
(22, 'นครศรีธรรมราช'),
(23, 'นครสวรรค์'),
(24, 'นนทบุรี'),
(25, 'นราธิวาส'),
(26, 'น่าน'),
(27, 'บุรีรัมย์'),
(28, 'ปทุมธานี'),
(29, 'ประจวบคีรีขันธ์'),
(30, 'ปราจีนบุรี'),
(31, 'ปัตตานี'),
(32, 'พระนครศรีอยุธยา'),
(33, 'พะเยา'),
(34, 'พังงา'),
(35, 'พัทลุง'),
(36, 'พิจิตร'),
(37, 'พิษณุโลก'),
(38, 'เพชรบุรี'),
(39, ' เพชรบูรณ์'),
(40, 'แพร่'),
(41, 'ภูเก็ต'),
(42, 'มหาสารคาม'),
(43, 'มุกดาหาร'),
(44, 'แม่ฮ่องสอน'),
(45, 'ยโสธร'),
(46, 'ยะลา'),
(47, 'ร้อยเอ็ด'),
(48, 'ระนอง'),
(49, 'ระยอง'),
(50, 'ราชบุรี'),
(51, 'ลพบุรี'),
(52, 'ลำปาง'),
(53, 'ลำพูน'),
(54, 'เลย'),
(55, 'ศรีสะเกษ'),
(56, 'สกลนคร'),
(57, 'สงขลา'),
(58, 'สตูล'),
(59, 'สมุทรปราการ'),
(60, 'สมุทรสงคราม'),
(61, 'สมุทรสาคร'),
(62, 'สระแก้ว'),
(63, 'สระบุรี'),
(64, 'สิงห์บุรี'),
(65, 'สุโขทัย'),
(66, 'สุพรรณบุรี'),
(67, 'สุราษฎร์ธานี'),
(68, 'สุรินทร์'),
(69, 'หนองคาย'),
(70, 'หนองบัวลำภู'),
(71, 'อ่างทอง'),
(72, 'อำนาจเจริญ'),
(73, 'อุดรธานี'),
(74, 'อุตรดิตถ์'),
(75, 'อุทัยธานี'),
(76, 'อุบลราชธานี');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `province`
--
ALTER TABLE `province`
  ADD PRIMARY KEY (`province_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `province`
--
ALTER TABLE `province`
  MODIFY `province_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=77;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
