-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2026 at 06:36 AM
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
-- Database: `fitness_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `session_type` enum('personal_training','consultation','assessment') DEFAULT NULL,
  `status` enum('pending','scheduled','completed','cancelled','no_show','expired') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `trainer_id`, `user_id`, `appointment_date`, `duration_minutes`, `session_type`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-11-11 14:31:00', 90, 'personal_training', 'completed', 'kjhgf Session Notes: ', '2025-11-10 12:32:19', '2025-11-13 04:49:07'),
(2, 1, 2, '2025-11-13 11:44:00', 60, 'consultation', '', 'mjnhbgv', '2025-11-13 04:45:44', '2025-11-13 04:45:44'),
(3, 1, 2, '2025-11-14 10:51:00', 60, 'personal_training', '', 'jhkjhgf', '2025-11-13 04:52:09', '2025-11-13 04:52:09'),
(4, 1, 2, '2025-11-15 15:10:00', 60, 'assessment', 'expired', 'juhygtf', '2025-11-13 05:11:06', '2025-11-19 05:58:24'),
(5, 1, 2, '2025-11-13 16:17:00', 30, 'personal_training', 'expired', 'jhgfdcx', '2025-11-13 05:18:02', '2025-11-19 05:58:24'),
(6, 1, 2, '2025-11-19 14:00:00', 120, 'personal_training', 'completed', 'kjhbg Session Notes: ,lmknjbhvg', '2025-11-19 04:52:26', '2025-11-19 06:26:19'),
(7, 1, 2, '2025-12-17 14:21:00', 120, 'assessment', 'completed', 'no', '2025-12-17 12:22:04', '2025-12-17 12:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `class_bookings`
--

CREATE TABLE `class_bookings` (
  `booking_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `status` enum('confirmed','cancelled','completed','no_show') DEFAULT 'confirmed',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_bookings`
--

INSERT INTO `class_bookings` (`booking_id`, `class_id`, `user_id`, `booking_date`, `status`, `notes`) VALUES
(1, 1, 2, '2025-11-10 13:02:28', 'cancelled', NULL),
(2, 2, 2, '2025-11-10 13:11:56', 'no_show', NULL),
(3, 3, 2, '2026-01-05 15:39:34', 'confirmed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dietary_plans`
--

CREATE TABLE `dietary_plans` (
  `diet_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `meal_plan` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dietary_plans`
--

INSERT INTO `dietary_plans` (`diet_id`, `trainer_id`, `appointment_id`, `user_id`, `class_id`, `title`, `description`, `calories`, `meal_plan`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 2, NULL, 'none', 'none', 2, 'none', 'none', '2026-01-06 15:10:33', '2026-01-13 01:59:50');

-- --------------------------------------------------------

--
-- Table structure for table `fitness_classes`
--

CREATE TABLE `fitness_classes` (
  `class_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `class_description` text DEFAULT NULL,
  `class_type` enum('yoga','cardio','strength','hiit','pilates','dance','other') DEFAULT NULL,
  `class_date` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  `price` decimal(8,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fitness_classes`
--

INSERT INTO `fitness_classes` (`class_id`, `trainer_id`, `class_name`, `class_description`, `class_type`, `class_date`, `duration_minutes`, `max_participants`, `current_participants`, `price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'hy', 'poiuytg', 'yoga', '2025-11-11 14:50:00', 60, 20, 1, 100.00, 0, '2025-11-10 12:48:27', '2025-11-19 06:00:05'),
(2, 1, ';plokijuyhgt', 'oiuytr', 'strength', '2025-11-11 13:13:00', 60, 23, 1, 200.00, 0, '2025-11-10 13:10:11', '2025-11-19 06:16:42'),
(3, 1, 'hy', 'none', 'yoga', '2026-01-06 16:30:00', 60, 20, 1, 30.00, 0, '2026-01-05 15:22:45', '2026-01-13 02:06:10'),
(4, 1, ';plokijuyhgt', 'none', 'pilates', '2026-01-08 15:46:00', 120, 30, 0, 30.00, 0, '2026-01-05 15:46:59', '2026-01-13 02:06:10'),
(5, 1, ';plokijuyhgt', 'none', 'pilates', '2026-01-08 15:46:00', 60, 20, 0, 30.00, 0, '2026-01-05 15:55:42', '2026-01-13 02:06:10'),
(6, 1, 'hy', '.l,kmnb', 'strength', '2026-01-13 02:06:00', 60, 20, 0, 20.00, 1, '2026-01-13 02:06:49', '2026-01-13 02:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `membership_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`membership_id`, `name`, `description`, `price`, `duration_months`, `created_at`, `updated_at`) VALUES
(1, 'Basic', '✓ Gym Access\r\n✓ Basic Equipment\r\n✓ Locker Facilities\r\n✓ Progress Tracking', 20.00, 1, '2025-11-08 02:40:08', '2025-11-08 02:40:08'),
(2, 'Premium (POPULAR)', '✓ All Basic Features\r\n✓ Personal Trainer Sessions\r\n✓ Diet Plans\r\n✓ Workout Plans', 50.00, 1, '2025-11-08 02:41:09', '2025-11-08 02:41:09'),
(3, 'Elite', '✓ All Premium Features\r\n✓ Unlimited Trainer Access\r\n✓ Nutrition Counseling\r\n✓ Priority Booking', 90.00, 1, '2025-11-08 02:41:38', '2025-11-08 02:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `member_memberships`
--

CREATE TABLE `member_memberships` (
  `member_membership_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','expired','cancelled') DEFAULT 'active',
  `auto_renew` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_memberships`
--

INSERT INTO `member_memberships` (`member_membership_id`, `user_id`, `membership_id`, `start_date`, `end_date`, `status`, `auto_renew`, `created_at`, `updated_at`) VALUES
(2, 2, 1, '2025-11-08', '2025-12-08', 'active', 0, '2025-11-08 02:59:47', '2025-11-08 02:59:47'),
(3, 2, 1, '2025-12-08', '2026-01-08', 'active', 0, '2025-12-17 12:18:27', '2025-12-17 12:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('credit_card','electronic_transfer','cash','paypal','stripe') NOT NULL,
  `service_type` enum('membership','training_session','class_booking','personal_training') NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('successful','failed','pending','refunded') DEFAULT 'pending',
  `receipt_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `amount`, `payment_date`, `payment_method`, `service_type`, `service_id`, `transaction_id`, `status`, `receipt_url`, `created_at`) VALUES
(1, 2, 20.00, '2025-11-08 02:59:47', 'stripe', 'membership', 2, 'TX17625995872', 'successful', NULL, '2025-11-08 02:59:47'),
(2, 2, 300.00, '2025-11-10 12:32:19', 'stripe', 'personal_training', 1, 'APT17628067392', 'successful', NULL, '2025-11-10 12:32:19'),
(3, 2, 100.00, '2025-11-10 13:02:28', 'paypal', 'class_booking', 1, 'CLS17628085482', 'successful', NULL, '2025-11-10 13:02:28'),
(4, 2, 200.00, '2025-11-10 13:11:56', 'credit_card', 'class_booking', 2, 'CLS17628091162', 'successful', NULL, '2025-11-10 13:11:56'),
(5, 2, 200.00, '2025-11-13 04:45:44', 'stripe', 'personal_training', 2, 'APT17629911442', 'pending', NULL, '2025-11-13 04:45:44'),
(6, 2, 200.00, '2025-11-13 04:52:09', 'paypal', 'personal_training', 3, 'APT17629915292', 'pending', NULL, '2025-11-13 04:52:09'),
(7, 2, 200.00, '2025-11-13 05:11:06', 'credit_card', 'personal_training', 4, 'APT17629926662', 'successful', NULL, '2025-11-13 05:11:06'),
(8, 2, 100.00, '2025-11-13 05:18:02', 'stripe', 'personal_training', 5, 'APT17629930822', 'pending', NULL, '2025-11-13 05:18:02'),
(9, 2, 400.00, '2025-11-19 04:52:26', 'stripe', 'personal_training', 6, 'APT17635099462', 'successful', NULL, '2025-11-19 04:52:26'),
(10, 2, 20.00, '2025-12-17 12:18:27', 'cash', 'membership', 3, 'TX17659559072', 'successful', NULL, '2025-12-17 12:18:27'),
(11, 2, 400.00, '2025-12-17 12:22:04', 'electronic_transfer', 'personal_training', 7, 'APT17659561242', 'pending', NULL, '2025-12-17 12:22:04'),
(12, 2, 30.00, '2026-01-05 15:39:34', 'electronic_transfer', 'class_booking', 3, 'CLS17676095742', 'successful', NULL, '2026-01-05 15:39:34');

-- --------------------------------------------------------

--
-- Table structure for table `trainerreviews`
--

CREATE TABLE `trainerreviews` (
  `Review_id` int(11) NOT NULL,
  `Appointment_id` int(11) NOT NULL,
  `Trainer_id` int(11) NOT NULL,
  `User_id` int(11) NOT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` between 1 and 5),
  `Review` text NOT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainerreviews`
--

INSERT INTO `trainerreviews` (`Review_id`, `Appointment_id`, `Trainer_id`, `User_id`, `Rating`, `Review`, `Created_at`) VALUES
(1, 1, 1, 2, 3, ';lkjhbgv', '2025-11-14 02:19:35');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `trainer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `shift_start` time DEFAULT NULL,
  `shift_end` time DEFAULT NULL,
  `working_days` set('monday','tuesday','wednesday','thursday','friday','saturday','sunday') DEFAULT NULL,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`trainer_id`, `user_id`, `specialty`, `biography`, `shift_start`, `shift_end`, `working_days`, `hourly_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 'exercise', 'nghtfr', '14:00:00', '17:00:00', 'monday,tuesday,wednesday,thursday,friday', 200.00, 1, '2025-11-08 01:56:32', '2025-11-08 01:56:32'),
(2, 6, 'exercise', '.......', '14:00:00', '06:00:00', 'monday,tuesday,wednesday,thursday,friday', 20.00, 1, '2025-12-17 12:14:35', '2026-01-02 10:39:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member','trainer') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `full_name`, `email`, `phone`, `date_of_birth`, `gender`, `address`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$72Q8Tmr7kaBzNC5AwER71eE8tfA457jKfKhIjCtAhrHBow7XlWHsu', 'admin', 'admin', 'admin@gmail.com', NULL, NULL, NULL, NULL, '2025-11-08 01:50:56', '2025-12-17 12:10:14'),
(2, 'ali123', '$2y$10$iG3e3xUrQo91rgZo9ihPWuH9QqDlIZ5z5maOhLjLPVBYIajJ0E0CW', 'member', 'ali', 'ali@gmail.com', '12345678909', '2000-01-01', 'male', 'karachi', '2025-11-08 01:54:22', '2025-11-19 05:23:05'),
(3, 'ali1234', '$2y$10$IT3cgeqUxzahA9NvFKCP.uyF1sYk3GXGOoW08sK529zU4/g07ykPu', 'trainer', 'ali ahmad', 'ali2@gmail.com', '12345678909', '2001-01-01', 'male', 'lahore', '2025-11-08 01:56:32', '2025-11-19 05:26:29'),
(5, 'shazil12345', '$2y$10$i6AybA.7ioSAbgz1PxJVHOpRpTErtzoCZGtWsOKsNw6hwXG2w7xLm', 'member', 'shahzil khan', 'shazil@gmail.com', '03023324375', '2000-07-17', 'male', 'lahore', '2025-12-17 12:09:43', '2025-12-17 12:09:43'),
(6, 'ali12345', '$2y$10$Q8.NjJRT8z4lAvJbXqpXruuRX3gOaYlUMmYRHFiJTqbf6HoUvVIiy', 'trainer', 'ali ahmad', 'ali3@gmail.com', '03023324375', '2000-01-01', 'male', 'lahore', '2025-12-17 12:14:35', '2025-12-17 12:14:35'),
(7, 'ali123456', '$2y$10$bIuyjGyFQLwR06cBC8o78eGHJ4EOLnXNdYYkMJVlPfwJbC4StHjiO', 'member', 'shahzil khan', 'ali4@gmail.com', '12345678909', '2025-06-02', 'male', 'lahore', '2026-01-02 10:34:58', '2026-01-02 10:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

CREATE TABLE `workout_plans` (
  `plan_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`plan_id`, `trainer_id`, `appointment_id`, `user_id`, `class_id`, `title`, `description`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 2, NULL, 'workout', 'none ,', '2026-01-02', '2026-01-30', '2026-01-02 06:20:58', '2026-01-02 06:35:07'),
(2, 1, NULL, 2, NULL, 'new', 'none', '2026-01-02', '2026-01-30', '2026-01-02 06:37:41', '2026-01-02 06:37:41'),
(3, 1, NULL, NULL, 3, 'no', 'none', '0000-00-00', '0000-00-00', '2026-01-05 16:18:34', '2026-01-05 16:18:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `class_bookings`
--
ALTER TABLE `class_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `unique_class_booking` (`class_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dietary_plans`
--
ALTER TABLE `dietary_plans`
  ADD PRIMARY KEY (`diet_id`),
  ADD UNIQUE KEY `unique_diet_appointment` (`appointment_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `fitness_classes`
--
ALTER TABLE `fitness_classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`membership_id`);

--
-- Indexes for table `member_memberships`
--
ALTER TABLE `member_memberships`
  ADD PRIMARY KEY (`member_membership_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trainerreviews`
--
ALTER TABLE `trainerreviews`
  ADD PRIMARY KEY (`Review_id`),
  ADD KEY `Appointment_id` (`Appointment_id`),
  ADD KEY `Trainer_id` (`Trainer_id`),
  ADD KEY `User_id` (`User_id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`trainer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD UNIQUE KEY `unique_appointment_plan` (`appointment_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_id` (`class_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `class_bookings`
--
ALTER TABLE `class_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dietary_plans`
--
ALTER TABLE `dietary_plans`
  MODIFY `diet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fitness_classes`
--
ALTER TABLE `fitness_classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `member_memberships`
--
ALTER TABLE `member_memberships`
  MODIFY `member_membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `trainerreviews`
--
ALTER TABLE `trainerreviews`
  MODIFY `Review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `trainer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`trainer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `class_bookings`
--
ALTER TABLE `class_bookings`
  ADD CONSTRAINT `class_bookings_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `fitness_classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `dietary_plans`
--
ALTER TABLE `dietary_plans`
  ADD CONSTRAINT `dietary_plans_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`trainer_id`),
  ADD CONSTRAINT `dietary_plans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `dietary_plans_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `fitness_classes` (`class_id`);

--
-- Constraints for table `fitness_classes`
--
ALTER TABLE `fitness_classes`
  ADD CONSTRAINT `fitness_classes_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`trainer_id`) ON DELETE CASCADE;

--
-- Constraints for table `member_memberships`
--
ALTER TABLE `member_memberships`
  ADD CONSTRAINT `member_memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `member_memberships_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`membership_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `trainerreviews`
--
ALTER TABLE `trainerreviews`
  ADD CONSTRAINT `trainerreviews_ibfk_1` FOREIGN KEY (`Appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainerreviews_ibfk_2` FOREIGN KEY (`Trainer_id`) REFERENCES `trainers` (`trainer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainerreviews_ibfk_3` FOREIGN KEY (`User_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `trainers`
--
ALTER TABLE `trainers`
  ADD CONSTRAINT `trainers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `fk_workout_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`trainer_id`),
  ADD CONSTRAINT `workout_plans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `workout_plans_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `fitness_classes` (`class_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
