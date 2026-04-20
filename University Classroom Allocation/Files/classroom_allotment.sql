-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2024 at 06:39 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `classroom_allotment`
--

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `capacity` int(11) NOT NULL,
  `projector` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `name`, `capacity`, `projector`) VALUES
(2, 'CR1', 50, '1'),
(3, 'CR2', 50, '1'),
(4, 'CR3', 50, '1'),
(8, 'CR4', 50, '1'),
(9, 'CR5', 50, '1');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `credits` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `lecture_hours` int(11) NOT NULL,
  `tutorial_hours` int(11) NOT NULL,
  `practical_hours` int(11) NOT NULL,
  `students_enrolled` int(11) NOT NULL,
  `projector` varchar(250) NOT NULL,
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `credits`, `professor_id`, `lecture_hours`, `tutorial_hours`, `practical_hours`, `students_enrolled`, `projector`, `semester_id`) VALUES
(1, 'CO312', 3, 8, 3, 3, 1, 40, '', 5),
(2, 'CO309', 3, 5, 3, 3, 1, 40, '', 5),
(3, 'CO315', 3, 4, 3, 3, 0, 35, '', 6),
(4, 'CO214', 4, 7, 4, 3, 1, 50, '', 4),
(5, 'CO314', 4, 3, 4, 3, 0, 40, '', 6);

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `name`, `email`, `password`) VALUES
(3, 'Dr. Utpal Sharma', '', ''),
(4, 'Dr. Nabajyoti Medhi', '', ''),
(5, 'Dr. Jyotismita Talukdar', '', ''),
(6, 'Dr. Shobhanjana Kalita', '', ''),
(7, 'Dr. Sanghamitra Nath', '', ''),
(8, 'Dr. Sarat Saharia', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `day` varchar(250) NOT NULL,
  `time_slot` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `course_id`, `classroom_id`, `day`, `time_slot`, `semester_id`) VALUES
(1, 1, 2, 'Monday', 9, 5),
(2, 1, 2, 'Tuesday', 9, 5),
(3, 1, 2, 'Wednesday', 9, 5),
(4, 1, 2, 'Thursday', 9, 5),
(5, 1, 2, 'Friday', 9, 5),
(6, 1, 2, 'Monday', 10, 5),
(7, 2, 2, 'Monday', 11, 5),
(8, 2, 2, 'Tuesday', 10, 5),
(9, 2, 2, 'Wednesday', 10, 5),
(10, 2, 2, 'Thursday', 10, 5),
(11, 2, 2, 'Friday', 10, 5),
(12, 2, 2, 'Monday', 12, 5),
(13, 3, 3, 'Monday', 9, 6),
(14, 3, 3, 'Tuesday', 9, 6),
(15, 3, 3, 'Wednesday', 9, 6),
(16, 3, 3, 'Thursday', 9, 6),
(17, 3, 3, 'Friday', 9, 6),
(18, 3, 3, 'Monday', 10, 6),
(19, 4, 4, 'Monday', 9, 4),
(20, 4, 4, 'Tuesday', 9, 4),
(21, 4, 4, 'Wednesday', 9, 4),
(22, 4, 4, 'Thursday', 9, 4),
(23, 4, 4, 'Friday', 9, 4),
(24, 4, 4, 'Monday', 10, 4),
(25, 4, 3, 'Monday', 11, 4),
(26, 5, 4, 'Monday', 11, 6),
(27, 5, 3, 'Tuesday', 10, 6),
(28, 5, 3, 'Wednesday', 10, 6),
(29, 5, 3, 'Thursday', 10, 6),
(30, 5, 3, 'Friday', 10, 6),
(31, 5, 3, 'Monday', 12, 6),
(32, 5, 2, 'Monday', 2, 6),
(33, 1, 2, 'Monday', 9, 5),
(34, 1, 2, 'Tuesday', 9, 5),
(35, 1, 2, 'Wednesday', 9, 5),
(36, 1, 2, 'Thursday', 9, 5),
(37, 1, 2, 'Friday', 9, 5),
(38, 1, 2, 'Monday', 10, 5),
(39, 2, 2, 'Monday', 11, 5),
(40, 2, 2, 'Tuesday', 10, 5),
(41, 2, 2, 'Wednesday', 10, 5),
(42, 2, 2, 'Thursday', 10, 5),
(43, 2, 2, 'Friday', 10, 5),
(44, 2, 2, 'Monday', 12, 5),
(45, 3, 3, 'Monday', 9, 6),
(46, 3, 3, 'Tuesday', 9, 6),
(47, 3, 3, 'Wednesday', 9, 6),
(48, 3, 3, 'Thursday', 9, 6),
(49, 3, 3, 'Friday', 9, 6),
(50, 3, 3, 'Monday', 10, 6),
(51, 4, 4, 'Monday', 9, 4),
(52, 4, 4, 'Tuesday', 9, 4),
(53, 4, 4, 'Wednesday', 9, 4),
(54, 4, 4, 'Thursday', 9, 4),
(55, 4, 4, 'Friday', 9, 4),
(56, 4, 4, 'Monday', 10, 4),
(57, 4, 3, 'Monday', 11, 4),
(58, 5, 4, 'Monday', 11, 6),
(59, 5, 3, 'Tuesday', 10, 6),
(60, 5, 3, 'Wednesday', 10, 6),
(61, 5, 3, 'Thursday', 10, 6),
(62, 5, 3, 'Friday', 10, 6),
(63, 5, 3, 'Monday', 12, 6),
(64, 5, 2, 'Monday', 2, 6),
(65, 1, 2, 'Monday', 9, 0),
(66, 1, 2, 'Tuesday', 9, 0),
(67, 1, 2, 'Wednesday', 9, 0),
(68, 1, 2, 'Thursday', 9, 0),
(69, 1, 2, 'Friday', 9, 0),
(70, 1, 2, 'Monday', 10, 0),
(71, 2, 3, 'Monday', 9, 0),
(72, 2, 3, 'Tuesday', 9, 0),
(73, 2, 3, 'Wednesday', 9, 0),
(74, 2, 3, 'Thursday', 9, 0),
(75, 2, 3, 'Friday', 9, 0),
(76, 2, 3, 'Monday', 10, 0),
(77, 3, 4, 'Monday', 9, 0),
(78, 3, 4, 'Tuesday', 9, 0),
(79, 3, 4, 'Wednesday', 9, 0),
(80, 3, 4, 'Thursday', 9, 0),
(81, 3, 4, 'Friday', 9, 0),
(82, 3, 4, 'Monday', 10, 0),
(83, 4, 8, 'Monday', 9, 0),
(84, 4, 8, 'Tuesday', 9, 0),
(85, 4, 8, 'Wednesday', 9, 0),
(86, 4, 8, 'Thursday', 9, 0),
(87, 4, 8, 'Friday', 9, 0),
(88, 4, 8, 'Monday', 10, 0),
(89, 4, 2, 'Monday', 11, 0),
(90, 5, 9, 'Monday', 9, 0),
(91, 5, 9, 'Tuesday', 9, 0),
(92, 5, 9, 'Wednesday', 9, 0),
(93, 5, 9, 'Thursday', 9, 0),
(94, 5, 9, 'Friday', 9, 0),
(95, 5, 9, 'Monday', 10, 0),
(96, 5, 3, 'Monday', 11, 0),
(97, 1, 2, 'Monday', 9, 0),
(98, 1, 2, 'Tuesday', 9, 0),
(99, 1, 2, 'Wednesday', 9, 0),
(100, 1, 2, 'Thursday', 9, 0),
(101, 1, 2, 'Friday', 9, 0),
(102, 1, 2, 'Monday', 10, 0),
(103, 2, 3, 'Monday', 9, 0),
(104, 2, 3, 'Tuesday', 9, 0),
(105, 2, 3, 'Wednesday', 9, 0),
(106, 2, 3, 'Thursday', 9, 0),
(107, 2, 3, 'Friday', 9, 0),
(108, 2, 3, 'Monday', 10, 0),
(109, 3, 4, 'Monday', 9, 0),
(110, 3, 4, 'Tuesday', 9, 0),
(111, 3, 4, 'Wednesday', 9, 0),
(112, 3, 4, 'Thursday', 9, 0),
(113, 3, 4, 'Friday', 9, 0),
(114, 3, 4, 'Monday', 10, 0),
(115, 4, 8, 'Monday', 9, 0),
(116, 4, 8, 'Tuesday', 9, 0),
(117, 4, 8, 'Wednesday', 9, 0),
(118, 4, 8, 'Thursday', 9, 0),
(119, 4, 8, 'Friday', 9, 0),
(120, 4, 8, 'Monday', 10, 0),
(121, 4, 2, 'Monday', 11, 0),
(122, 5, 9, 'Monday', 9, 0),
(123, 5, 9, 'Tuesday', 9, 0),
(124, 5, 9, 'Wednesday', 9, 0),
(125, 5, 9, 'Thursday', 9, 0),
(126, 5, 9, 'Friday', 9, 0),
(127, 5, 9, 'Monday', 10, 0),
(128, 5, 3, 'Monday', 11, 0),
(129, 1, 2, 'Monday', 9, 0),
(130, 1, 2, 'Tuesday', 9, 0),
(131, 1, 2, 'Wednesday', 9, 0),
(132, 1, 2, 'Thursday', 9, 0),
(133, 1, 2, 'Friday', 9, 0),
(134, 1, 2, 'Monday', 10, 0),
(135, 2, 3, 'Monday', 9, 0),
(136, 2, 3, 'Tuesday', 9, 0),
(137, 2, 3, 'Wednesday', 9, 0),
(138, 2, 3, 'Thursday', 9, 0),
(139, 2, 3, 'Friday', 9, 0),
(140, 2, 3, 'Monday', 10, 0),
(141, 3, 4, 'Monday', 9, 0),
(142, 3, 4, 'Tuesday', 9, 0),
(143, 3, 4, 'Wednesday', 9, 0),
(144, 3, 4, 'Thursday', 9, 0),
(145, 3, 4, 'Friday', 9, 0),
(146, 3, 4, 'Monday', 10, 0),
(147, 4, 8, 'Monday', 9, 0),
(148, 4, 8, 'Tuesday', 9, 0),
(149, 4, 8, 'Wednesday', 9, 0),
(150, 4, 8, 'Thursday', 9, 0),
(151, 4, 8, 'Friday', 9, 0),
(152, 4, 8, 'Monday', 10, 0),
(153, 4, 2, 'Monday', 11, 0),
(154, 5, 9, 'Monday', 9, 0),
(155, 5, 9, 'Tuesday', 9, 0),
(156, 5, 9, 'Wednesday', 9, 0),
(157, 5, 9, 'Thursday', 9, 0),
(158, 5, 9, 'Friday', 9, 0),
(159, 5, 9, 'Monday', 10, 0),
(160, 5, 3, 'Monday', 11, 0),
(161, 1, 2, 'Monday', 9, 0),
(162, 1, 2, 'Tuesday', 9, 0),
(163, 1, 2, 'Wednesday', 9, 0),
(164, 1, 2, 'Thursday', 9, 0),
(165, 1, 2, 'Friday', 9, 0),
(166, 1, 2, 'Monday', 10, 0),
(167, 2, 3, 'Monday', 9, 0),
(168, 2, 3, 'Tuesday', 9, 0),
(169, 2, 3, 'Wednesday', 9, 0),
(170, 2, 3, 'Thursday', 9, 0),
(171, 2, 3, 'Friday', 9, 0),
(172, 2, 3, 'Monday', 10, 0),
(173, 3, 4, 'Monday', 9, 0),
(174, 3, 4, 'Tuesday', 9, 0),
(175, 3, 4, 'Wednesday', 9, 0),
(176, 3, 4, 'Thursday', 9, 0),
(177, 3, 4, 'Friday', 9, 0),
(178, 3, 4, 'Monday', 10, 0),
(179, 4, 8, 'Monday', 9, 0),
(180, 4, 8, 'Tuesday', 9, 0),
(181, 4, 8, 'Wednesday', 9, 0),
(182, 4, 8, 'Thursday', 9, 0),
(183, 4, 8, 'Friday', 9, 0),
(184, 4, 8, 'Monday', 10, 0),
(185, 4, 2, 'Monday', 11, 0),
(186, 5, 9, 'Monday', 9, 0),
(187, 5, 9, 'Tuesday', 9, 0),
(188, 5, 9, 'Wednesday', 9, 0),
(189, 5, 9, 'Thursday', 9, 0),
(190, 5, 9, 'Friday', 9, 0),
(191, 5, 9, 'Monday', 10, 0),
(192, 5, 3, 'Monday', 11, 0),
(193, 1, 2, 'Monday', 9, 0),
(194, 1, 2, 'Tuesday', 9, 0),
(195, 1, 2, 'Wednesday', 9, 0),
(196, 1, 2, 'Thursday', 9, 0),
(197, 1, 2, 'Friday', 9, 0),
(198, 1, 2, 'Monday', 10, 0),
(199, 2, 3, 'Monday', 9, 0),
(200, 2, 3, 'Tuesday', 9, 0),
(201, 2, 3, 'Wednesday', 9, 0),
(202, 2, 3, 'Thursday', 9, 0),
(203, 2, 3, 'Friday', 9, 0),
(204, 2, 3, 'Monday', 10, 0),
(205, 3, 4, 'Monday', 9, 0),
(206, 3, 4, 'Tuesday', 9, 0),
(207, 3, 4, 'Wednesday', 9, 0),
(208, 3, 4, 'Thursday', 9, 0),
(209, 3, 4, 'Friday', 9, 0),
(210, 3, 4, 'Monday', 10, 0),
(211, 4, 8, 'Monday', 9, 0),
(212, 4, 8, 'Tuesday', 9, 0),
(213, 4, 8, 'Wednesday', 9, 0),
(214, 4, 8, 'Thursday', 9, 0),
(215, 4, 8, 'Friday', 9, 0),
(216, 4, 8, 'Monday', 10, 0),
(217, 4, 2, 'Monday', 11, 0),
(218, 5, 9, 'Monday', 9, 0),
(219, 5, 9, 'Tuesday', 9, 0),
(220, 5, 9, 'Wednesday', 9, 0),
(221, 5, 9, 'Thursday', 9, 0),
(222, 5, 9, 'Friday', 9, 0),
(223, 5, 9, 'Monday', 10, 0),
(224, 5, 3, 'Monday', 11, 0),
(225, 1, 2, 'Monday', 9, 0),
(226, 1, 2, 'Tuesday', 9, 0),
(227, 1, 2, 'Wednesday', 9, 0),
(228, 1, 2, 'Thursday', 9, 0),
(229, 1, 2, 'Friday', 9, 0),
(230, 1, 2, 'Monday', 10, 0),
(231, 2, 3, 'Monday', 9, 0),
(232, 2, 3, 'Tuesday', 9, 0),
(233, 2, 3, 'Wednesday', 9, 0),
(234, 2, 3, 'Thursday', 9, 0),
(235, 2, 3, 'Friday', 9, 0),
(236, 2, 3, 'Monday', 10, 0),
(237, 3, 4, 'Monday', 9, 0),
(238, 3, 4, 'Tuesday', 9, 0),
(239, 3, 4, 'Wednesday', 9, 0),
(240, 3, 4, 'Thursday', 9, 0),
(241, 3, 4, 'Friday', 9, 0),
(242, 3, 4, 'Monday', 10, 0),
(243, 4, 8, 'Monday', 9, 0),
(244, 4, 8, 'Tuesday', 9, 0),
(245, 4, 8, 'Wednesday', 9, 0),
(246, 4, 8, 'Thursday', 9, 0),
(247, 4, 8, 'Friday', 9, 0),
(248, 4, 8, 'Monday', 10, 0),
(249, 4, 2, 'Monday', 11, 0),
(250, 5, 9, 'Monday', 9, 0),
(251, 5, 9, 'Tuesday', 9, 0),
(252, 5, 9, 'Wednesday', 9, 0),
(253, 5, 9, 'Thursday', 9, 0),
(254, 5, 9, 'Friday', 9, 0),
(255, 5, 9, 'Monday', 10, 0),
(256, 5, 3, 'Monday', 11, 0);

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`id`, `name`) VALUES
(1, 'Semester 1'),
(2, 'Semester 2'),
(3, 'Semester 3'),
(4, 'Semester 4'),
(5, 'Semester 5'),
(6, 'Semester 6'),
(7, 'Semester 7'),
(8, 'Semester 8');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=257;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
