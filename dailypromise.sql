-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 01, 2010 at 04:44 PM
-- Server version: 5.1.33
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dailypromise`
--

-- --------------------------------------------------------

--
-- Table structure for table `promises`
--

CREATE TABLE IF NOT EXISTS `promises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `promise` varchar(500) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `promises`
--

INSERT INTO `promises` (`id`, `uid`, `pid`, `promise`, `active`) VALUES
(1, 1, 1, 'avoided chocolate', 1),
(2, 1, 2, 'drunk no coffee', 1),
(3, 1, 3, 'eaten 4 portions of fruit & veg', 1),
(4, 1, 4, 'avoided sausage rolls', 1),
(5, 1, 5, 'eaten leftovers if available', 1);

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `date` date NOT NULL,
  `kept` enum('YES','NO','WAITING') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=76 ;

--
-- Dumping data for table `records`
--

INSERT INTO `records` (`id`, `uid`, `pid`, `date`, `kept`) VALUES
(1, 1, 1, '2010-11-29', 'YES'),
(2, 1, 2, '2010-11-29', 'YES'),
(3, 1, 4, '2010-11-29', 'NO'),
(4, 1, 5, '2010-11-29', 'NO'),
(5, 1, 1, '2010-11-28', 'YES'),
(73, 1, 2, '2010-11-28', 'YES'),
(7, 1, 3, '2010-11-28', 'YES'),
(8, 1, 4, '2010-11-28', 'YES'),
(9, 1, 5, '2010-11-28', 'YES'),
(50, 1, 5, '2010-11-30', 'YES'),
(49, 1, 4, '2010-11-30', 'YES'),
(48, 1, 3, '2010-11-30', 'NO'),
(47, 1, 2, '2010-11-30', 'YES'),
(74, 1, 3, '2010-12-01', 'YES'),
(45, 1, 3, '2010-11-29', 'YES'),
(63, 1, 2, '2010-12-01', 'YES'),
(65, 1, 4, '2010-12-01', 'YES'),
(66, 1, 5, '2010-12-01', 'NO'),
(69, 1, 1, '2010-12-01', 'YES'),
(71, 1, 1, '2010-11-30', 'YES');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(200) DEFAULT NULL,
  `auth_token` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `username`, `password`, `auth_token`) VALUES
(1, 'tsuki_chama', '0571749e2ac330a7455809c6b0e7af90', 'N;'),
(2, 'test', 'd41d8cd98f00b204e9800998ecf8427e', NULL);
