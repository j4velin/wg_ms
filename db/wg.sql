-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 07, 2020 at 10:17 AM
-- Server version: 5.7.30-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wg`
--
CREATE DATABASE IF NOT EXISTS `wg` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `wg`;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_einkaeufe`
--

CREATE TABLE `wg_1_einkaeufe` (
  `id` smallint(6) NOT NULL,
  `datum` varchar(10) CHARACTER SET latin1 NOT NULL,
  `ware` text CHARACTER SET latin1 NOT NULL,
  `kaeufer` smallint(6) NOT NULL,
  `preis` float NOT NULL,
  `anz` smallint(3) NOT NULL DEFAULT '1',
  `verbraucht` varchar(1) CHARACTER SET latin1 NOT NULL DEFAULT '0' COMMENT 'wird nur beachtet wenn anz > 1',
  `gewicht` float NOT NULL DEFAULT '0' COMMENT 'in kg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_einkaeufe_mitzahlung`
--

CREATE TABLE `wg_1_einkaeufe_mitzahlung` (
  `einkauf` smallint(6) NOT NULL,
  `user` smallint(6) NOT NULL,
  `anz` smallint(3) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_einkaufsliste`
--

CREATE TABLE `wg_1_einkaufsliste` (
  `id` smallint(6) NOT NULL,
  `datum` varchar(10) NOT NULL,
  `ware` text NOT NULL,
  `user` smallint(6) NOT NULL,
  `privat` varchar(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_monatlich`
--

CREATE TABLE `wg_1_monatlich` (
  `id` int(11) NOT NULL,
  `bezeichnung` text NOT NULL,
  `kosten` int(11) NOT NULL,
  `turnus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_muell`
--

CREATE TABLE `wg_1_muell` (
  `user` tinyint(6) NOT NULL,
  `datum` char(10) NOT NULL,
  `art` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_shouts`
--

CREATE TABLE `wg_1_shouts` (
  `id` int(11) NOT NULL,
  `userid` tinyint(4) NOT NULL,
  `text` text NOT NULL,
  `stamp` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_1_zahlungen`
--

CREATE TABLE `wg_1_zahlungen` (
  `id` smallint(6) NOT NULL,
  `datum` varchar(10) NOT NULL,
  `absender` smallint(6) NOT NULL,
  `empfaenger` smallint(6) NOT NULL,
  `betrag` float NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wg_user`
--

CREATE TABLE `wg_user` (
  `id` tinyint(6) NOT NULL,
  `name` text CHARACTER SET latin1 NOT NULL,
  `pw` text CHARACTER SET latin1 NOT NULL,
  `konto` float NOT NULL,
  `active` tinyint(1) NOT NULL,
  `wg` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wg_1_einkaeufe`
--
ALTER TABLE `wg_1_einkaeufe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wg_1_einkaeufe_mitzahlung`
--
ALTER TABLE `wg_1_einkaeufe_mitzahlung`
  ADD PRIMARY KEY (`einkauf`,`user`);

--
-- Indexes for table `wg_1_einkaufsliste`
--
ALTER TABLE `wg_1_einkaufsliste`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wg_1_monatlich`
--
ALTER TABLE `wg_1_monatlich`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wg_1_muell`
--
ALTER TABLE `wg_1_muell`
  ADD PRIMARY KEY (`datum`,`art`);

--
-- Indexes for table `wg_1_shouts`
--
ALTER TABLE `wg_1_shouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wg_1_zahlungen`
--
ALTER TABLE `wg_1_zahlungen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wg_user`
--
ALTER TABLE `wg_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wg_1_einkaeufe`
--
ALTER TABLE `wg_1_einkaeufe`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wg_1_einkaufsliste`
--
ALTER TABLE `wg_1_einkaufsliste`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wg_1_monatlich`
--
ALTER TABLE `wg_1_monatlich`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wg_1_shouts`
--
ALTER TABLE `wg_1_shouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wg_1_zahlungen`
--
ALTER TABLE `wg_1_zahlungen`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wg_user`
--
ALTER TABLE `wg_user`
  MODIFY `id` tinyint(6) NOT NULL AUTO_INCREMENT;
  
  
--
-- Add admin user
--  
INSERT INTO `wg_user` (`id`, `name`, `pw`, `konto`, `active`, `wg`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 0.0, 0, '1');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
