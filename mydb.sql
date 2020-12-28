-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: mariadb_cont
-- Generation Time: Dec 22, 2020 at 04:44 PM
-- Server version: 10.5.8-MariaDB-1:10.5.8+maria~focal
-- PHP Version: 7.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydb`
--
CREATE DATABASE IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mydb`;

-- --------------------------------------------------------

--
-- Table structure for table `p1_userinfo`
--

CREATE TABLE `p1_userinfo` (
  `uid` int(5) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `p1_userinfo`
--

INSERT INTO `p1_userinfo` (`uid`, `fullname`, `gender`, `username`, `password`, `ts`) VALUES
(1, 'Sivadarshan', 'Male', 'siva', '202cb962ac59075b964b07152d234b70', '2020-12-08 07:14:17'),
(3, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', '2020-12-08 07:24:17'),
(4, 'Sivadarshan1', 'Male', 'siva1', '202cb962ac59075b964b07152d234b70', '2020-12-08 07:38:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `p1_userinfo`
--
ALTER TABLE `p1_userinfo`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `p1_userinfo`
--
ALTER TABLE `p1_userinfo`
  MODIFY `uid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
