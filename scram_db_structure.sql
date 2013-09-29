-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 29, 2013 at 10:35 PM
-- Server version: 5.5.32-0ubuntu0.13.04.1
-- PHP Version: 5.4.9-4ubuntu2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `scram`
--

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE IF NOT EXISTS `availability` (
  `sprint_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `hours` int(11) NOT NULL,
  PRIMARY KEY (`sprint_id`,`resource_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `extended_log`
--
CREATE TABLE IF NOT EXISTS `extended_log` (
`time` datetime
,`type` enum('estimate','move','update')
,`details` varchar(32)
,`name` varchar(50)
,`description` varchar(64)
);
-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `time` datetime NOT NULL,
  `resource_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `details` varchar(32) NOT NULL DEFAULT '',
  `type` enum('estimate','move','update') NOT NULL,
  PRIMARY KEY (`time`,`resource_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `task_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT '1969-10-18',
  `burnt` int(11) NOT NULL,
  `estimate` int(11) NOT NULL,
  `reason` enum('forward','initialize','estimate') NOT NULL DEFAULT 'estimate',
  PRIMARY KEY (`task_id`,`resource_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resource`
--

CREATE TABLE IF NOT EXISTS `resource` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `sprint`
--

CREATE TABLE IF NOT EXISTS `sprint` (
  `sprint_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(64) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`sprint_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `sprint_id` int(11) NOT NULL,
  `description` varchar(64) NOT NULL,
  `status` enum('toDo','inProgress','toBeVerified','done','forwarded') NOT NULL DEFAULT 'toDo',
  `resource_id` int(11) NOT NULL,
  `story` text NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `sprint_id` (`sprint_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure for view `extended_log`
--
DROP TABLE IF EXISTS `extended_log`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `extended_log` AS select `log`.`time` AS `time`,`log`.`type` AS `type`,`log`.`details` AS `details`,`resource`.`name` AS `name`,`task`.`description` AS `description` from ((`log` left join `resource` on((`resource`.`resource_id` = `log`.`resource_id`))) join `task` on((`log`.`task_id` = `task`.`task_id`))) order by `log`.`time` desc;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;