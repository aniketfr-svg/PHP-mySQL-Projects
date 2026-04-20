-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 07:40 PM
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
-- Database: `timetable`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '482c811da5d5b4bc6d497ffa98491e38'),
(2, 'suraj', 'subba');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `name`, `capacity`) VALUES
(1, 'CR1', 30),
(2, 'CR2', 45),
(3, 'CR3', 50),
(4, 'CR4', 55),
(5, 'CR5', 55);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `credits` int(11) NOT NULL,
  `lecture_hours` int(11) NOT NULL,
  `tutorial_hours` int(11) NOT NULL,
  `practical_hours` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `students_enrolled` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `credits`, `lecture_hours`, `tutorial_hours`, `practical_hours`, `professor_id`, `semester_id`, `students_enrolled`) VALUES
(1, 'Operating System', 3, 3, 0, 30, 2, 1, 35),
(2, 'System Software and Compiler Design', 3, 2, 1, 32, 1, 1, 43),
(3, 'Computer Graphics ', 4, 3, 1, 0, 3, 1, 43),
(4, 'Mathematics', 3, 3, 0, 30, 6, 1, 50),
(5, 'Natural Language Processing', 3, 2, 1, 30, 1, 2, 25),
(6, 'Fundamental of speech processing', 4, 3, 1, 40, 5, 2, 25),
(7, 'Formal Language and Automata', 3, 3, 0, 28, 4, 1, 52),
(8, 'Digital Logic Design', 4, 2, 2, 35, 1, 1, 35);

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `name`) VALUES
(1, 'Utpal Sharma'),
(2, 'Jyotismita Talukdar'),
(3, 'Rosy Sharma'),
(4, 'Nityananda Sharma'),
(5, 'Sanghamitra Nath'),
(6, 'Debojit Boro');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `time_slot` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `course_id`, `classroom_id`, `semester_id`, `day`, `time_slot`) VALUES
(1, 1, 2, 1, 'Monday', '9:00-10:00'),
(2, 1, 2, 1, 'Tuesday', '9:00-10:00'),
(3, 1, 2, 1, 'Wednesday', '9:00-10:00'),
(4, 2, 2, 1, 'Monday', '10:00-11:00'),
(5, 2, 2, 1, 'Tuesday', '10:00-11:00'),
(6, 2, 2, 1, 'Wednesday', '10:00-11:00'),
(7, 3, 2, 1, 'Monday', '11:00-12:00'),
(8, 3, 2, 1, 'Tuesday', '11:00-12:00'),
(9, 3, 2, 1, 'Wednesday', '11:00-12:00'),
(10, 3, 2, 1, 'Thursday', '9:00-10:00'),
(11, 4, 3, 1, 'Monday', '12:00-1:00'),
(12, 4, 3, 1, 'Tuesday', '12:00-1:00'),
(13, 4, 3, 1, 'Wednesday', '12:00-1:00'),
(14, 5, 1, 2, 'Monday', '9:00-10:00'),
(15, 5, 1, 2, 'Tuesday', '9:00-10:00'),
(16, 5, 1, 2, 'Wednesday', '9:00-10:00'),
(17, 6, 1, 2, 'Monday', '10:00-11:00'),
(18, 6, 1, 2, 'Tuesday', '10:00-11:00'),
(19, 6, 1, 2, 'Wednesday', '10:00-11:00'),
(20, 6, 1, 2, 'Thursday', '9:00-10:00'),
(21, 7, 4, 1, 'Monday', '2:00-3:00'),
(22, 7, 4, 1, 'Tuesday', '2:00-3:00'),
(23, 7, 4, 1, 'Wednesday', '2:00-3:00'),
(24, 8, 2, 1, 'Monday', '3:00-4:00'),
(25, 8, 2, 1, 'Tuesday', '3:00-4:00'),
(26, 8, 2, 1, 'Wednesday', '3:00-4:00'),
(27, 8, 2, 1, 'Thursday', '10:00-11:00');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`id`, `name`) VALUES
(1, 'I'),
(2, 'II'),
(3, 'III'),
(4, 'IV'),
(5, 'V'),
(6, 'VI'),
(7, 'VII'),
(8, 'VIII');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `classroom_id` (`classroom_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
