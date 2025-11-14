-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 04:22 PM
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
-- Database: `moviease_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `ticket_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `movie_ID` int(11) NOT NULL,
  `seat_ID` int(11) NOT NULL,
  `date_booked` date NOT NULL,
  `schedule` datetime NOT NULL,
  `ticket_Quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('Booked','Cancelled','Completed') DEFAULT 'Booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`ticket_ID`, `user_ID`, `movie_ID`, `seat_ID`, `date_booked`, `schedule`, `ticket_Quantity`, `price`, `status`) VALUES
(1, 3, 1, 1, '2025-10-23', '2025-10-25 13:00:00', 2, 700.00, 'Booked'),
(2, 4, 2, 3, '2025-10-23', '2025-10-25 14:00:00', 1, 300.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `calendar`
--

CREATE TABLE `calendar` (
  `calendar_ID` int(11) NOT NULL,
  `movie_ID` int(11) NOT NULL,
  `schedule` datetime NOT NULL,
  `weather` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar`
--

INSERT INTO `calendar` (`calendar_ID`, `movie_ID`, `schedule`, `weather`) VALUES
(1, 1, '2025-10-25 13:00:00', 'Sunny'),
(2, 2, '2025-10-25 14:00:00', 'Cloudy'),
(3, 4, '2025-10-25 18:00:00', 'Rainy');

-- --------------------------------------------------------

--
-- Table structure for table `cinemas`
--

CREATE TABLE `cinemas` (
  `cinema_id` int(11) NOT NULL,
  `cinema_name` enum('SM','Robinsons','Ayala Malls') NOT NULL,
  `cinema_address` varchar(255) NOT NULL,
  `movie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cinemas`
--

INSERT INTO `cinemas` (`cinema_id`, `cinema_name`, `cinema_address`, `movie_id`) VALUES
(1, 'SM', 'SM North EDSA, Quezon City', 1),
(2, 'Robinsons', 'Robinsons Manila, Ermita', 2),
(3, 'Ayala Malls', 'Ayala Glorietta, Makati', 4);

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_ID` int(11) NOT NULL,
  `movie_Name` varchar(255) NOT NULL,
  `movie_Hours` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `movie_Class` enum('G','PG','SPG','R13','R16','R18') NOT NULL,
  `genre` enum('Action','Comedy','Drama','Horror','Romance','Sci-Fi','Thriller','Animation') NOT NULL,
  `movie_Status` enum('Now Showing','Coming Soon') DEFAULT 'Coming Soon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_ID`, `movie_Name`, `movie_Hours`, `price`, `movie_Class`, `genre`, `movie_Status`) VALUES
(1, 'Avengers: Endgame', '03:01:00', 350.00, 'PG', 'Action', 'Now Showing'),
(2, 'Inside Out 2', '01:45:00', 300.00, 'G', 'Animation', 'Now Showing'),
(3, 'The Conjuring 3', '02:10:00', 320.00, 'R13', 'Horror', 'Coming Soon'),
(4, 'Oppenheimer', '03:00:00', 400.00, 'R16', 'Drama', 'Now Showing');

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `recommendation_ID` int(11) NOT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `movie_ID` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `recommended_Date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`recommendation_ID`, `user_ID`, `movie_ID`, `reason`, `recommended_Date`) VALUES
(1, 3, 4, 'Because you watched Action movies', '2025-10-24 21:58:12'),
(2, 4, 2, 'Popular this week', '2025-10-24 21:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_ID` int(11) NOT NULL,
  `user_Email` varchar(100) NOT NULL,
  `user_Role` enum('Admin','Customer','Staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_ID`, `user_Email`, `user_Role`) VALUES
(1, 'admin@moviease.com', 'Admin'),
(2, 'staff@moviease.com', 'Staff'),
(3, 'customer@moviease.com', 'Customer');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_ID` int(11) NOT NULL,
  `cinema_id` int(11) NOT NULL,
  `schedule` datetime NOT NULL,
  `status` enum('Available','NA') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_ID`, `cinema_id`, `schedule`, `status`) VALUES
(1, 1, '2025-10-25 13:00:00', 'Available'),
(2, 1, '2025-10-25 16:00:00', 'Available'),
(3, 2, '2025-10-25 14:00:00', 'Available'),
(4, 3, '2025-10-25 18:00:00', 'NA');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `user_Name` varchar(100) NOT NULL,
  `user_Email` varchar(100) NOT NULL,
  `user_contact` varchar(15) DEFAULT NULL,
  `user_Password` varchar(255) NOT NULL,
  `email_Recovery` varchar(255) NOT NULL,
  `role_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `user_Name`, `user_Email`, `user_contact`, `user_Password`, `email_Recovery`, `role_ID`) VALUES
(1, 'Admin User', 'admin@moviease.com', '09123456789', 'admin123', 'admin_recovery@moviease.com', 1),
(2, 'Staff User', 'staff@moviease.com', '09987654321', 'staff123', 'staff_recovery@moviease.com', 2),
(3, 'Juan Dela Cruz', 'juan@gmail.com', '09111111111', 'password123', 'juan_recovery@gmail.com', 3),
(4, 'Maria Santos', 'maria@gmail.com', '09222222222', 'password123', 'maria_recovery@gmail.com', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`ticket_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `movie_ID` (`movie_ID`),
  ADD KEY `seat_ID` (`seat_ID`);

--
-- Indexes for table `calendar`
--
ALTER TABLE `calendar`
  ADD PRIMARY KEY (`calendar_ID`),
  ADD KEY `movie_ID` (`movie_ID`);

--
-- Indexes for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD PRIMARY KEY (`cinema_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_ID`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`recommendation_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `movie_ID` (`movie_ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_ID`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_ID`),
  ADD KEY `cinema_id` (`cinema_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `user_Email` (`user_Email`),
  ADD KEY `fk_user_role` (`role_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `ticket_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `calendar`
--
ALTER TABLE `calendar`
  MODIFY `calendar_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cinemas`
--
ALTER TABLE `cinemas`
  MODIFY `cinema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`movie_ID`) REFERENCES `movies` (`movie_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`seat_ID`) REFERENCES `seats` (`seat_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `calendar`
--
ALTER TABLE `calendar`
  ADD CONSTRAINT `calendar_ibfk_1` FOREIGN KEY (`movie_ID`) REFERENCES `movies` (`movie_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD CONSTRAINT `cinemas_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`),
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`movie_ID`) REFERENCES `movies` (`movie_ID`);

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_ID`) REFERENCES `roles` (`role_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
