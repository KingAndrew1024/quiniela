-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 03, 2026 at 02:15 AM
-- Server version: 9.1.0
-- PHP Version: 7.4.33

USE dbs15262321;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbs15262321`
--

-- --------------------------------------------------------

--
-- Table structure for table `forecasts`
--

DROP TABLE IF EXISTS `forecasts`;
CREATE TABLE IF NOT EXISTS `forecasts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `match_id` int NOT NULL,
  `team1_goals` tinyint NOT NULL,
  `team2_goals` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `match_id_fk` (`match_id`),
  KEY `user_id_fk` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL,
  `team1_id` int NOT NULL,
  `team2_id` int NOT NULL,
  `team1_goals` tinyint DEFAULT '0',
  `team2_goals` tinyint DEFAULT '0',
  `played` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `team1_id_fk` (`team1_id`),
  KEY `team2_id_fk` (`team2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `date`, `team1_id`, `team2_id`, `team1_goals`, `team2_goals`, `played`) VALUES
(1, '2026-06-11', 27, 35, 0, 0, 0),
(2, '2026-06-11', 24, 43, 0, 0, 0),
(3, '2026-06-12', 41, 32, 0, 0, 0),
(4, '2026-06-12', 7, 44, 0, 0, 0),
(5, '2026-06-13', 3, 45, 0, 0, 0),
(6, '2026-06-13', 34, 38, 0, 0, 0),
(7, '2026-06-13', 20, 36, 0, 0, 0),
(8, '2026-06-13', 6, 26, 0, 0, 0),
(9, '2026-06-14', 46, 39, 0, 0, 0),
(10, '2026-06-14', 18, 12, 0, 0, 0),
(11, '2026-06-14', 28, 23, 0, 0, 0),
(12, '2026-06-14', 8, 13, 0, 0, 0),
(13, '2026-06-15', 5, 14, 0, 0, 0),
(14, '2026-06-15', 21, 30, 0, 0, 0),
(15, '2026-06-15', 16, 10, 0, 0, 0),
(16, '2026-06-15', 25, 40, 0, 0, 0),
(17, '2026-06-16', 4, 22, 0, 0, 0),
(18, '2026-06-16', 2, 1, 0, 0, 0),
(19, '2026-06-16', 47, 29, 0, 0, 0),
(20, '2026-06-16', 17, 37, 0, 0, 0),
(21, '2026-06-17', 42, 9, 0, 0, 0),
(22, '2026-06-17', 33, 48, 0, 0, 0),
(23, '2026-06-17', 15, 11, 0, 0, 0),
(24, '2026-06-17', 19, 31, 0, 0, 0),
(25, '2026-06-18', 7, 34, 0, 0, 0),
(26, '2026-06-18', 38, 44, 0, 0, 0),
(27, '2026-06-18', 27, 24, 0, 0, 0),
(28, '2026-06-18', 43, 35, 0, 0, 0),
(29, '2026-06-19', 41, 3, 0, 0, 0),
(30, '2026-06-19', 45, 32, 0, 0, 0),
(31, '2026-06-19', 36, 26, 0, 0, 0),
(32, '2026-06-19', 6, 20, 0, 0, 0),
(33, '2026-06-20', 39, 23, 0, 0, 0),
(34, '2026-06-20', 28, 46, 0, 0, 0),
(35, '2026-06-20', 13, 12, 0, 0, 0),
(36, '2026-06-20', 18, 8, 0, 0, 0),
(37, '2026-06-21', 30, 14, 0, 0, 0),
(38, '2026-06-21', 5, 21, 0, 0, 0),
(39, '2026-06-21', 16, 25, 0, 0, 0),
(40, '2026-06-21', 40, 10, 0, 0, 0),
(41, '2026-06-22', 22, 1, 0, 0, 0),
(42, '2026-06-22', 2, 4, 0, 0, 0),
(43, '2026-06-22', 17, 47, 0, 0, 0),
(44, '2026-06-22', 29, 37, 0, 0, 0),
(45, '2026-06-23', 9, 48, 0, 0, 0),
(46, '2026-06-23', 33, 42, 0, 0, 0),
(47, '2026-06-23', 31, 11, 0, 0, 0),
(48, '2026-06-23', 15, 19, 0, 0, 0),
(49, '2026-06-24', 38, 7, 0, 0, 0),
(50, '2026-06-24', 44, 34, 0, 0, 0),
(51, '2026-06-24', 43, 27, 0, 0, 0),
(52, '2026-06-24', 35, 24, 0, 0, 0),
(53, '2026-06-24', 26, 20, 0, 0, 0),
(54, '2026-06-24', 36, 6, 0, 0, 0),
(55, '2026-06-25', 32, 3, 0, 0, 0),
(56, '2026-06-25', 45, 41, 0, 0, 0),
(57, '2026-06-25', 23, 46, 0, 0, 0),
(58, '2026-06-25', 39, 28, 0, 0, 0),
(59, '2026-06-25', 12, 8, 0, 0, 0),
(60, '2026-06-25', 13, 18, 0, 0, 0),
(61, '2026-06-26', 30, 5, 0, 0, 0),
(62, '2026-06-26', 14, 21, 0, 0, 0),
(63, '2026-06-26', 40, 16, 0, 0, 0),
(64, '2026-06-26', 10, 25, 0, 0, 0),
(65, '2026-06-26', 37, 47, 0, 0, 0),
(66, '2026-06-26', 29, 17, 0, 0, 0),
(67, '2026-06-27', 22, 2, 0, 0, 0),
(68, '2026-06-27', 1, 4, 0, 0, 0),
(69, '2026-06-27', 48, 42, 0, 0, 0),
(70, '2026-06-27', 9, 33, 0, 0, 0),
(71, '2026-06-27', 11, 19, 0, 0, 0),
(72, '2026-06-27', 31, 15, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8mb4_spanish_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `code`, `name`) VALUES
(1, 'ALG', 'ARGELIA'),
(2, 'ARG', 'ARGENTINA'),
(3, 'AUS', 'AUSTRALIA'),
(4, 'AUT', 'AUSTRIA'),
(5, 'BEL', 'BELGICA'),
(6, 'BRA', 'BRASIL'),
(7, 'CAN', 'CANADA'),
(8, 'CIV', 'COSTA DE MARFIL'),
(9, 'COL', 'COLOMBIA'),
(10, 'CPV', 'CABO VERDE'),
(11, 'CRO', 'CROACIA'),
(12, 'CUW', 'CURAZAO'),
(13, 'ECU', 'ECUADOR'),
(14, 'EGY', 'EGIPTO'),
(15, 'ENG', 'INGLATERRA'),
(16, 'ESP', 'ESPAÑA'),
(17, 'FRA', 'FRANCIA'),
(18, 'GER', 'ALEMANIA'),
(19, 'GHA', 'GANA'),
(20, 'HAI', 'HAITI'),
(21, 'IRN', 'IRAN'),
(22, 'JOR', 'JORDANIA'),
(23, 'JPN', 'JAPÓN'),
(24, 'KOR', 'KOREA'),
(25, 'KSA', 'ARABIA SAUDI'),
(26, 'MAR', 'MARRUECOS'),
(27, 'MEX', 'MÉXICO'),
(28, 'NED', 'PAISES BAJOS'),
(29, 'NOR', 'NORUEGA'),
(30, 'NZL', 'NUEVA ZELANDA'),
(31, 'PAN', 'PANAMA'),
(32, 'PAR', 'PARAGUAY'),
(33, 'POR', 'PORTUGAL'),
(34, 'QAT', 'CATAR'),
(35, 'RSA', 'SUDAFRICA'),
(36, 'SCO', 'ESCOCIA'),
(37, 'SEN', 'SENEGAL'),
(38, 'SUI', 'SUIZA'),
(39, 'TUN', 'TUNEZ'),
(40, 'URU', 'URUGUAY'),
(41, 'USA', 'ESTADOS UNIDOS'),
(42, 'UZB', 'UZBEKISTAN'),
(43, 'X1', 'X1'),
(44, 'X2', 'X2'),
(45, 'X3', 'X3'),
(46, 'X4', 'X4'),
(47, 'X5', 'X5'),
(48, 'X6', 'X6');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(64) COLLATE utf8mb4_spanish_ci NOT NULL,
  `password` varchar(12) COLLATE utf8mb4_spanish_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_spanish_ci NOT NULL,
  `points` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forecasts`
--
ALTER TABLE `forecasts`
  ADD CONSTRAINT `match_id_fk` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `team1_id_fk` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `team2_id_fk` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
