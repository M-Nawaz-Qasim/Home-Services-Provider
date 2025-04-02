-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2025 at 02:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `home_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `address` text NOT NULL,
  `additional_info` text DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `booking_status` enum('pending','approved','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `service_id`, `provider_id`, `booking_date`, `address`, `additional_info`, `payment_status`, `booking_status`, `created_at`) VALUES
(11, 17, 5, 24, '2025-02-09', 'Mohalla Tahir Khel, Wan Bhachran, Mianwali', '', 'pending', 'completed', '2025-02-09 08:56:08'),
(12, 75, 3, 22, '2025-02-09', 'Mohalla Tahir Khel, Wan Bhachran, Mianwali', '', 'pending', 'completed', '2025-02-09 12:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `dispute_reason` text NOT NULL,
  `dispute_status` enum('open','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `booking_id`, `customer_id`, `provider_id`, `rating`, `review`, `created_at`, `response`) VALUES
(5, 11, 17, 24, 5, 'Good', '2025-02-09 08:57:00', 'Thanks You'),
(6, 11, 17, 24, 5, 'Good', '2025-02-09 09:18:13', 'Thank you'),
(7, 12, 75, 22, 5, 'Good', '2025-02-09 12:08:01', 'Thank You');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bank_transfer','cash_on_delivery') NOT NULL,
  `payment_status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `customer_id`, `amount`, `payment_method`, `payment_status`, `created_at`) VALUES
(10, 11, 17, 100.00, 'cash_on_delivery', 'completed', '2025-02-09 08:56:08'),
(11, 12, 75, 100.00, 'cash_on_delivery', 'completed', '2025-02-09 12:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`) VALUES
(1, 'Appliances Repair'),
(2, 'Carpentry Services'),
(3, 'Cleaning Services'),
(4, 'Computer And IT Support'),
(5, 'Laundry And Dry Cleaning'),
(6, 'Electric Services'),
(7, 'Fencing And Deck Services'),
(8, 'Flooring Services'),
(9, 'Gardening And Landscape'),
(10, 'Home Automation Setup'),
(11, 'HVAC Maintenance'),
(12, 'Painting Services'),
(13, 'Personal Chef or Catering'),
(14, 'Pest Control'),
(15, 'Plumbing Services'),
(16, 'Pool Maintenance'),
(17, 'Renovation And Remodeling'),
(18, 'Roofing Services'),
(19, 'Security System Installation'),
(20, 'Window Cleaning');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('customer','service_provider') NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `expertise_proof` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `contact_number`, `email`, `password`, `user_type`, `service_id`, `expertise_proof`, `created_at`, `approval_status`) VALUES
(20, 'provider2', '12345678911', 'provider2@gmail.com', '$2y$10$45hmD65wHuScYyYc1ERnP.9HvlU6q2Av2oAFP0jAXsYxubFkoj3qy', 'service_provider', 2, '../Uploads/FC.png', '2025-02-09 08:32:11', 'approved'),
(22, 'provider3', '12345678912', 'provider3@gmail.com', '$2y$10$zDPbWw99p7ToCObB7buy3udiN7DKxMgfz/XeOL.PD1UCLCUIcr7HK', 'service_provider', 3, '../Uploads/FC.png', '2025-02-09 08:33:01', 'approved'),
(23, 'provider4', '12345678913', 'provider4@gmail.com', '$2y$10$U698Rs8/ACTJ03mZBYW3ZeZD8j2fXYfjz.5yjIo80WLd2cRD3dNFW', 'service_provider', 4, '../Uploads/FC.png', '2025-02-09 08:46:27', 'approved'),
(24, 'provider5', '12345678914', 'provider5@gmail.com', '$2y$10$r2CzzDx6y1vWRf23KJ3SZeIMgAJGjgUrrGySUNCzdIc2vyK9G3CTi', 'service_provider', 5, '../Uploads/FC.png', '2025-02-09 08:55:23', 'approved'),
(75, 'user1', '1122334455', 'user1@gmail.com', '$2y$10$a.FZr9DBdOTibif.HFzUD.xDYS9bPw2jkhg5bxQqbwFOcjVouAK4K', 'customer', 1, NULL, '2025-02-09 10:02:12', 'approved'),
(76, 'provider6', '12345678915', 'provider6@gmail.com', '$2y$10$2kR4hfUSsWMDbhiQm6A6jOhFZnfDjvNyrrGB4CWP8Ys7.b0g9C6we', 'service_provider', 6, '../Uploads/FC.png', '2025-02-09 11:53:59', 'approved'),
(77, 'provider7', '12345678916', 'provider7@gmail.com', '$2y$10$ZmkTo7y1oxGXvJqRjkd9wOIKQrcxMD.MxFxPrAtqDwbbt0Peet7Sy', 'service_provider', 7, '../Uploads/FC.png', '2025-02-09 12:01:55', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `disputes_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
