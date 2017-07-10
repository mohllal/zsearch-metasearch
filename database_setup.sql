-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2017 at 09:32 AM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `z-search`
--
CREATE DATABASE IF NOT EXISTS `z-search` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `z-search`;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
  `result_id` int(10) NOT NULL,
  `result_keywords` varchar(255) NOT NULL,
  `result_source` varchar(255) NOT NULL,
  `result_type` varchar(255) NOT NULL,
  `result_offset` int(10) NOT NULL,
  `result_score` int(100) NOT NULL,
  `result_title` varchar(255) NOT NULL,
  `result_link` varchar(255) NOT NULL,
  `result_snippet` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `results`
--

TRUNCATE TABLE `results`;
--
-- Indexes for dumped tables
--

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `result_link` (`result_link`),
  ADD KEY `result_link_2` (`result_link`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(10) NOT NULL AUTO_INCREMENT;
DELIMITER $$
--
-- Events
--
DROP EVENT `delete_results_tbl`$$
CREATE DEFINER=`root`@`localhost` EVENT `delete_results_tbl` ON SCHEDULE EVERY 3 HOUR STARTS '2017-07-10 08:17:01' ON COMPLETION NOT PRESERVE ENABLE DO TRUNCATE TABLE results$$

DELIMITER ;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
