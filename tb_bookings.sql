-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2025 at 07:15 AM
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
-- Database: `tb_bookings`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `TableID` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `batch` varchar(255) DEFAULT NULL,
  `gradYear` int(11) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `slip_path` varchar(255) DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `approved_by_admin_id` int(11) DEFAULT NULL,
  `approved_by` varchar(20) NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `TableID`, `name`, `lastName`, `phone`, `batch`, `gradYear`, `payment_amount`, `slip_path`, `transfer_date`, `transfer_time`, `status`, `approved_by_admin_id`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 'K1', 'sciweek', 'สุขเกษม', '06123153', '12', 123, 360.00, 'slip_68c3a08525fec.jpg', '2025-09-12', '14:24:00', 'verified', 1, '', '2025-09-12 05:13:17', '2025-09-12 04:24:37'),
(2, 'J1', 'sciweek', 'สุขเกษม', '06123153', '12', 123, 3600.00, 'slip_68c3a0ce65b57.jpg', '2025-09-12', '14:28:00', 'verified', 1, 'Admin', '2025-09-12 05:12:18', '2025-09-12 04:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `tb_admins`
--

CREATE TABLE `tb_admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `admin_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admins`
--

INSERT INTO `tb_admins` (`admin_id`, `username`, `password_hash`, `admin_name`) VALUES
(1, 'admin', '$2y$10$1rg2Uysu2soBIERNds62me5GgxSVpB7unvtZC/pknX8bWgOyg2/OK', 'ครูนิด');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_approved_by_admin` (`approved_by_admin_id`);

--
-- Indexes for table `tb_admins`
--
ALTER TABLE `tb_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_admins`
--
ALTER TABLE `tb_admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_approved_by_admin` FOREIGN KEY (`approved_by_admin_id`) REFERENCES `tb_admins` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
