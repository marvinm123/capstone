-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 08:48 AM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_list`
--

CREATE TABLE `booking_list` (
  `id` int(30) NOT NULL,
  `ref_code` varchar(100) NOT NULL,
  `client_id` int(30) NOT NULL,
  `facility_id` int(30) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0 = Pending,\r\n1 = Confirmed,\r\n2 = Done,\r\n3 = Cancelled',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `payment_proof` varchar(255) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `booking_list`
--

INSERT INTO `booking_list` (`id`, `ref_code`, `client_id`, `facility_id`, `date_from`, `date_to`, `time_from`, `time_to`, `status`, `date_created`, `date_updated`, `payment_proof`, `paid_amount`) VALUES
(1, '202511-00001', 2, 1, '2025-11-05', '2025-11-05', NULL, NULL, 0, '2025-10-01 10:15:22', NULL, NULL, 0.00),
(2, '202511-00002', 3, 3, '2025-11-12', '2025-11-12', NULL, NULL, 0, '2025-10-03 14:30:45', NULL, NULL, 0.00),
(3, '202511-00003', 1, 2, '2025-11-20', '2025-11-21', NULL, NULL, 0, '2025-10-05 09:22:18', NULL, NULL, 0.00),
(4, '202512-00001', 2, 1, '2025-12-03', '2025-12-03', NULL, NULL, 0, '2025-10-02 11:45:30', NULL, NULL, 0.00),
(5, '202512-00002', 3, 2, '2025-12-10', '2025-12-10', NULL, NULL, 0, '2025-10-04 15:20:10', NULL, NULL, 0.00),
(6, '202512-00003', 1, 3, '2025-12-18', '2025-12-18', NULL, NULL, 0, '2025-10-06 08:55:40', NULL, NULL, 0.00);


-- --------------------------------------------------------

--
-- Table structure for table `category_list`
--

CREATE TABLE `category_list` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category_list`
--

INSERT INTO `category_list` (`id`, `name`, `description`, `delete_flag`, `status`, `date_created`, `date_updated`) VALUES
(1, 'BasketBall', 'BasketBall Court', 0, 1, '2025-03-23 10:34:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_list`
-- CLEANED UP - No verification fields needed anymore
--

CREATE TABLE `client_list` (
  `id` int(30) NOT NULL,
  `firstname` text NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` text NOT NULL,
  `gender` text NOT NULL,
  `contact` text NOT NULL,
  `address` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_added` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Dumping data for table `client_list`
-- NOTE: These existing users can login immediately (no verification needed for existing data)
--

INSERT INTO `client_list` (`id`, `firstname`, `middlename`, `lastname`, `gender`, `contact`, `address`, `email`, `password`, `status`, `delete_flag`, `date_created`, `date_added`) VALUES
(1, 'Kenneth Paul', 'D', 'Dragon', '', '09922306706', 'Pangi Yawa', 'Kpsama@gmail.com', '34801f3da79a0317145b91b455cff9d6', 1, 0, '2025-03-23 12:01:47', '2025-03-23 12:01:47'),
(2, 'Archer', 'D', 'Dragon', '', '09606073283', 'Catalunan Grande', 'archer1@gmail.com', 'cf13b1fbedb66438bf4cd9053be087d8', 1, 0, '2025-03-23 12:01:47', '2025-03-23 12:01:47'),
(3, 'Marvin', 'M', 'Baylosis', 'Male', '09606073283', 'Catalunan Grande', 'marvinjr_baylosis@sjp2cd.edu.ph', '25d55ad283aa400af464c76d713c07ad', 1, 0, '2025-03-23 12:01:47', '2025-03-23 12:01:47');

-- --------------------------------------------------------

--
-- Table structure for table `temp_registrations`
-- NEW TABLE - Stores unverified registrations temporarily
--

CREATE TABLE `temp_registrations` (
  `id` int(30) NOT NULL,
  `firstname` text NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` text NOT NULL,
  `gender` text NOT NULL,
  `contact` text NOT NULL,
  `address` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `verification_code` VARCHAR(6) NOT NULL,
  `code_expiry` datetime NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- No initial data for temp_registrations (it's for pending verifications only)
--

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
-- NEW TABLE - Stores password reset tokens for forgot password functionality
--

CREATE TABLE `password_reset_tokens` (
  `id` int(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(6) NOT NULL,
  `token_expiry` datetime NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- No initial data for password_reset_tokens (tokens are created on demand)
--

-- --------------------------------------------------------

--
-- Table structure for table `date_events`
--

CREATE TABLE `date_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_type` enum('holiday','maintenance','special_event','notice') DEFAULT 'notice',
  `color` varchar(7) DEFAULT '#3788d8',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `date_events`
--

INSERT INTO `date_events` (`id`, `title`, `description`, `event_date`, `event_type`, `color`) VALUES
(1, 'Facility Maintenance', 'Monthly maintenance for all courts', '2025-10-16', 'maintenance', '#3788d8'),
(2, 'Sports Festival', 'Annual sports competition', '2025-10-18', 'special_event', '#3788d8'),
(3, 'Holiday Closure', 'National holiday - facility closed', '2025-10-20', 'holiday', '#3788d8'),
(4, 'New Equipment Notice', 'New basketball hoops installed', '2025-10-22', 'notice', '#3788d8'),
(5, 'Basketball Tournament', 'Annual inter-barangay basketball championship', '2025-11-08', 'special_event', '#3788d8'),
(6, 'Court Cleaning Day', 'Scheduled deep cleaning of all facilities', '2025-11-15', 'maintenance', '#3788d8'),
(7, 'Christmas Basketball League', 'Holiday season basketball tournament', '2025-12-12', 'special_event', '#3788d8'),
(8, 'Year-End Closure Notice', 'Facility operating hours during holidays', '2025-12-26', 'notice', '#3788d8');

-- --------------------------------------------------------

--
-- Table structure for table `facility_list`
--

CREATE TABLE `facility_list` (
  `id` int(30) NOT NULL,
  `facility_code` varchar(100) NOT NULL,
  `category_id` int(30) NOT NULL,
  `image_path` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `name` text NOT NULL,
  `description` text NOT NULL,
  `price` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `facility_list`
--

INSERT INTO `facility_list` (`id`, `facility_code`, `category_id`, `image_path`, `status`, `delete_flag`, `date_created`, `date_updated`, `name`, `description`, `price`) VALUES
(1, '202503-00001', 1, 'uploads/facility/4545.png', 1, 0, '2025-03-23 11:07:02', '2025-03-23 15:33:04', '5v5 Exhibition Match', '<p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; color: rgb(0, 0, 0); font-family: \"Open Sans\", Arial, sans-serif; font-size: 14px;\">Our full-sized basketball court is equipped with high-grade flooring, clear boundary markings, and adjustable hoops suitable for both practice and competitive games. The facility is well-lit and ventilated, providing the perfect environment for both casual and league play.</p>', 1000.00),
(2, '202503-00002', 1, 'uploads/facility/12312.png', 1, 0, '2025-03-23 11:44:34', '2025-03-23 15:33:19', '3x3 Competitions', '<p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; color: rgb(0, 0, 0); font-family: \"Open Sans\", Arial, sans-serif; font-size: 14px;\">This indoor basketball court is ideal for training sessions, tournaments, and pickup games. Featuring a professional-grade surface and electronic scoreboard, it caters to players of all levels seeking a quality playing experience in a controlled setting.</p>', 1000.00),
(3, '202503-00003', 1, 'uploads/facility/234235.png', 1, 0, '2025-03-23 11:45:24', '2025-03-23 15:33:37', 'Skills Challenges', '<p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; color: rgb(0, 0, 0); font-family: \"Open Sans\", Arial, sans-serif; font-size: 14px;\">This indoor basketball court is ideal for training sessions, tournaments, and pickup games. Featuring a professional-grade surface and electronic scoreboard, it caters to players of all levels seeking a quality playing experience in a controlled setting.</p>', 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(30) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'WEB-BASED SPORTS COMPLEX MANAGEMENT IN CATALUNAN GRANDE DAVAO CITY '),
(6, 'short_name', 'Sports Complex '),
(11, 'logo', 'uploads/removed2.png?v=1648002319'),
(14, 'cover', 'uploads/13.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `middlename` varchar(250) DEFAULT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `type`, `status`, `date_added`, `date_updated`) VALUES
(1, 'Adminstrator', NULL, 'Admin', 'admin', '0192023a7bbd73250516f069df18b500', 1, 1, '2025-01-20 14:02:37', '2025-01-21 09:55:07'),
(11, 'archer', NULL, 'kenn', 'archer', '97fbdf62d73918bdaf5acb8aaf4a823c', 2, 1, '2025-10-07 02:36:13', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_list`
--
ALTER TABLE `booking_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cab_id` (`facility_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `category_list`
--
ALTER TABLE `category_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_list`
--
ALTER TABLE `client_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- Indexes for table `temp_registrations`
--
ALTER TABLE `temp_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH,
  ADD KEY `verification_code` (`verification_code`),
  ADD KEY `code_expiry` (`code_expiry`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`),
  ADD KEY `token_expiry` (`token_expiry`),
  ADD KEY `email_token_idx` (`email`, `token`);

--
-- Indexes for table `date_events`
--
ALTER TABLE `date_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_list`
--
ALTER TABLE `facility_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_list`
--
ALTER TABLE `booking_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `category_list`
--
ALTER TABLE `category_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_list`
--
ALTER TABLE `client_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `temp_registrations`
--
ALTER TABLE `temp_registrations`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `date_events`
--
ALTER TABLE `date_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `facility_list`
--
ALTER TABLE `facility_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_list`
--
ALTER TABLE `booking_list`
  ADD CONSTRAINT `booking_list_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facility_list` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_list_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `client_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `facility_list`
--
ALTER TABLE `facility_list`
  ADD CONSTRAINT `facility_list_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category_list` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;