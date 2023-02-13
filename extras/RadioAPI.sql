-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 13, 2023 at 06:55 PM
-- Server version: 10.6.11-MariaDB-1:10.6.11+maria~ubu2004-log
-- PHP Version: 7.4.3-4ubuntu2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `RadioAPI`
--
CREATE DATABASE IF NOT EXISTS `RadioAPI` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `RadioAPI`;

-- --------------------------------------------------------

--
-- Table structure for table `artistdata`
--

DROP TABLE IF EXISTS `artistdata`;
CREATE TABLE `artistdata` (
  `id` int(11) NOT NULL,
  `artistid` int(11) NOT NULL,
  `artist` varchar(255) DEFAULT NULL,
  `ShortDescription` text DEFAULT 'No data',
  `LongDescription` text DEFAULT 'No data',
  `totalplays` int(11) DEFAULT 0,
  `lastplayed` int(11) DEFAULT NULL,
  `compositerating` decimal(2,1) DEFAULT 0.0,
  `voters` int(11) DEFAULT 0,
  `demozoo` varchar(255) DEFAULT NULL,
  `wikipedia` varchar(255) DEFAULT NULL,
  `csdb` varchar(255) DEFAULT NULL,
  `otherurl` varchar(255) DEFAULT NULL,
  `modarchive` varchar(255) DEFAULT NULL,
  `bandcamp` varchar(255) DEFAULT NULL,
  `soundcloud` varchar(255) DEFAULT NULL,
  `pouet` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `EligibilityTime` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artistgroups`
--

DROP TABLE IF EXISTS `artistgroups`;
CREATE TABLE `artistgroups` (
  `id` int(11) NOT NULL,
  `artist` int(11) NOT NULL,
  `groups` int(11) NOT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
CREATE TABLE `artists` (
  `id` int(11) NOT NULL,
  `artist` varchar(250) NOT NULL,
  `lastplayed` varchar(30) NOT NULL,
  `totalplays` int(11) NOT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `EligibilityTime` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artiststitles`
--

DROP TABLE IF EXISTS `artiststitles`;
CREATE TABLE `artiststitles` (
  `id` int(11) NOT NULL,
  `artist` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `changelog`
--

DROP TABLE IF EXISTS `changelog`;
CREATE TABLE `changelog` (
  `id` int(11) NOT NULL,
  `trackid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `timestamphr` varchar(120) NOT NULL,
  `artist` varchar(300) NOT NULL,
  `title` varchar(300) NOT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `logtext` text DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `lastplayed` int(11) DEFAULT NULL,
  `totalplays` int(11) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ips`
--

DROP TABLE IF EXISTS `ips`;
CREATE TABLE `ips` (
  `id` int(11) NOT NULL,
  `IP` varchar(255) DEFAULT NULL,
  `Country` varchar(40) DEFAULT NULL,
  `Regionname` varchar(512) DEFAULT NULL,
  `Isp` varchar(512) DEFAULT NULL,
  `City` varchar(512) DEFAULT NULL,
  `Zip` varchar(120) DEFAULT NULL,
  `Timestamp` int(11) NOT NULL,
  `Timestamphr` varchar(100) DEFAULT NULL,
  `Hits` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nowplaying`
--

DROP TABLE IF EXISTS `nowplaying`;
CREATE TABLE `nowplaying` (
  `fullartist` varchar(250) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `lastplayed` int(11) NOT NULL,
  `trackid` int(11) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PlayedRequests`
--

DROP TABLE IF EXISTS `PlayedRequests`;
CREATE TABLE `PlayedRequests` (
  `id` int(11) NOT NULL,
  `TrackID` int(11) NOT NULL,
  `StationID` int(11) NOT NULL COMMENT 'Not used today, but reserved for future expansion',
  `title` varchar(255) NOT NULL,
  `fullartist` varchar(250) NOT NULL,
  `Guid` varchar(255) NOT NULL,
  `lastplayed` int(11) NOT NULL COMMENT 'This is when the track was last played on the station. Not when it was played as a request.',
  `Duration` decimal(40,20) NOT NULL,
  `lastplayedasrequest` int(11) NOT NULL,
  `lastplayedasrequesthr` varchar(60) NOT NULL,
  `greeting` text DEFAULT NULL,
  `nameofrequester` varchar(120) DEFAULT NULL,
  `Path` varchar(450) DEFAULT NULL,
  `ip` varchar(130) DEFAULT NULL,
  `Source` varchar(20) DEFAULT NULL,
  `CueIn` decimal(40,20) DEFAULT 0.00000000000000000000,
  `CueOut` decimal(40,20) DEFAULT 0.00000000000000000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playoutlog`
--

DROP TABLE IF EXISTS `playoutlog`;
CREATE TABLE `playoutlog` (
  `id` int(11) NOT NULL,
  `trackid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `timestamphr` varchar(120) NOT NULL,
  `artist` varchar(300) NOT NULL,
  `title` varchar(300) NOT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `QueuedRequests`
--

DROP TABLE IF EXISTS `QueuedRequests`;
CREATE TABLE `QueuedRequests` (
  `id` int(11) NOT NULL,
  `TrackID` int(11) NOT NULL COMMENT 'Unique!',
  `StationID` int(11) NOT NULL COMMENT 'Not used today, but reserved for future expansion',
  `title` varchar(255) NOT NULL,
  `fullartist` varchar(250) NOT NULL,
  `Guid` varchar(255) NOT NULL,
  `lastplayed` int(11) NOT NULL COMMENT 'This is when the track was last played on the station. Not when it was played as a request.',
  `Duration` decimal(40,20) NOT NULL,
  `greeting` text DEFAULT NULL,
  `nameofrequester` varchar(120) DEFAULT NULL,
  `Path` varchar(450) NOT NULL,
  `ip` varchar(120) DEFAULT NULL,
  `Source` varchar(20) DEFAULT NULL,
  `CueIn` decimal(40,20) DEFAULT 0.00000000000000000000,
  `CueOut` decimal(40,20) DEFAULT 0.00000000000000000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requestlog`
--

DROP TABLE IF EXISTS `requestlog`;
CREATE TABLE `requestlog` (
  `id` int(11) NOT NULL,
  `timestamp` varchar(30) NOT NULL,
  `timestamphr` varchar(120) NOT NULL,
  `browserhash` varchar(120) NOT NULL,
  `ip` varchar(140) NOT NULL,
  `trackid` int(11) NOT NULL,
  `nameofrequester` varchar(120) DEFAULT NULL,
  `RealName` varchar(400) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `greeting` text DEFAULT NULL,
  `Path` varchar(450) DEFAULT NULL,
  `Source` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `starlog`
--

DROP TABLE IF EXISTS `starlog`;
CREATE TABLE `starlog` (
  `id` int(11) NOT NULL,
  `timestamp` varchar(30) NOT NULL,
  `timestamphr` varchar(120) NOT NULL,
  `browserhash` varchar(120) NOT NULL,
  `ip` varchar(140) NOT NULL,
  `trackid` int(11) NOT NULL,
  `stars` int(11) NOT NULL,
  `RealName` varchar(400) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `totalartistplays` int(11) NOT NULL DEFAULT 0,
  `totaltrackplays` int(11) NOT NULL DEFAULT 0,
  `totalgroupplays` int(11) NOT NULL DEFAULT 0,
  `StationID` int(11) DEFAULT NULL,
  `totaltracks` int(11) DEFAULT 0,
  `TotalLength` decimal(5,1) DEFAULT 0.0,
  `LastBuild` varchar(222) DEFAULT NULL COMMENT 'Date of the latest podcast addition'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streamevents`
--

DROP TABLE IF EXISTS `streamevents`;
CREATE TABLE `streamevents` (
  `id` int(11) NOT NULL,
  `IP` varchar(255) DEFAULT NULL,
  `Event` varchar(512) NOT NULL,
  `Playtime` int(11) DEFAULT NULL,
  `Country` varchar(40) DEFAULT NULL,
  `Regionname` varchar(512) DEFAULT NULL,
  `Isp` varchar(512) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Zip` varchar(255) DEFAULT NULL,
  `Eventtype` varchar(30) DEFAULT NULL,
  `Timestamp` int(11) NOT NULL,
  `Timestamphr` varchar(100) DEFAULT NULL,
  `Agent` varchar(255) DEFAULT NULL,
  `PlayingAtLogoff` varchar(512) DEFAULT NULL,
  `Identifier` varchar(255) DEFAULT NULL,
  `StreamID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

DROP TABLE IF EXISTS `titles`;
CREATE TABLE `titles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `lastplayed` varchar(30) NOT NULL,
  `totalplays` int(11) NOT NULL,
  `fullartist` varchar(250) NOT NULL,
  `artist` int(11) DEFAULT NULL,
  `lastplayedhr` varchar(120) DEFAULT NULL,
  `trackid` int(11) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Album` text DEFAULT NULL,
  `Genre` varchar(255) DEFAULT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `Year` varchar(30) DEFAULT NULL,
  `Duration` decimal(40,20) DEFAULT NULL,
  `OutCue` decimal(40,20) DEFAULT NULL,
  `Tags` varchar(1024) DEFAULT NULL,
  `Disabled` varchar(10) DEFAULT NULL,
  `Type` varchar(250) DEFAULT NULL,
  `Intro` varchar(250) DEFAULT NULL,
  `CueIn` decimal(40,20) DEFAULT NULL,
  `CueOut` decimal(40,20) DEFAULT NULL,
  `Added` varchar(250) DEFAULT NULL,
  `Sweeper` varchar(250) DEFAULT NULL,
  `NoFade` varchar(250) DEFAULT NULL,
  `ValidFrom` varchar(250) DEFAULT NULL,
  `Expires` varchar(250) DEFAULT NULL,
  `Path` varchar(800) DEFAULT NULL,
  `Segue` decimal(40,20) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `EligibilityTime` int(11) DEFAULT NULL COMMENT 'Last time it was played OR requested.',
  `ArtistEligibilityTime` int(11) DEFAULT NULL,
  `CreationDate` int(11) DEFAULT NULL,
  `CreationDateHr` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trackdata`
--

DROP TABLE IF EXISTS `trackdata`;
CREATE TABLE `trackdata` (
  `id` int(11) NOT NULL,
  `trackid` int(11) DEFAULT NULL,
  `artist` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `compositerating` decimal(2,1) DEFAULT 0.0 COMMENT 'CalculatedStars in order to speed up lookups',
  `voters` int(11) DEFAULT 0,
  `about` text DEFAULT NULL,
  `trackertype` text DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `timestamphr` varchar(120) NOT NULL,
  `Guid` varchar(255) DEFAULT NULL,
  `StationID` int(11) DEFAULT NULL,
  `podcast` varchar(355) DEFAULT NULL,
  `image` varchar(355) DEFAULT NULL,
  `BroadcastDate` varchar(50) DEFAULT NULL,
  `Equipment` text DEFAULT NULL,
  `PlayList` text DEFAULT NULL,
  `ProductionNotes` text DEFAULT NULL,
  `EpisodeNumber` int(11) DEFAULT NULL,
  `IsPodcast` int(11) DEFAULT 0,
  `IsNews` int(11) DEFAULT 0,
  `PodcastURL` varchar(455) DEFAULT NULL,
  `PodcastFileLength` int(11) DEFAULT NULL,
  `Footer` text DEFAULT NULL,
  `News` longtext DEFAULT NULL,
  `explicit` varchar(100) DEFAULT NULL COMMENT 'Valid values are yes, no and clean. no and clean seems to mean the same to Apple :)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TrackerTypes`
--

DROP TABLE IF EXISTS `TrackerTypes`;
CREATE TABLE `TrackerTypes` (
  `TrackerType` varchar(200) NOT NULL,
  `Extension` varchar(10) NOT NULL,
  `Plays` int(11) NOT NULL DEFAULT 0,
  `Comment` text DEFAULT NULL,
  `StationID` int(11) NOT NULL,
  `Percent` decimal(3,1) DEFAULT 0.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artistdata`
--
ALTER TABLE `artistdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `artistid` (`artistid`);

--
-- Indexes for table `artistgroups`
--
ALTER TABLE `artistgroups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `artiststitles`
--
ALTER TABLE `artiststitles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `title` (`title`);

--
-- Indexes for table `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamphr` (`timestamphr`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ips`
--
ALTER TABLE `ips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nowplaying`
--
ALTER TABLE `nowplaying`
  ADD UNIQUE KEY `StationID` (`StationID`),
  ADD KEY `trackid` (`trackid`);

--
-- Indexes for table `PlayedRequests`
--
ALTER TABLE `PlayedRequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lastplayedasrequest` (`lastplayedasrequest`),
  ADD KEY `TrackID` (`TrackID`) USING BTREE;

--
-- Indexes for table `playoutlog`
--
ALTER TABLE `playoutlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `StationID` (`StationID`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `QueuedRequests`
--
ALTER TABLE `QueuedRequests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `TrackID` (`TrackID`);

--
-- Indexes for table `requestlog`
--
ALTER TABLE `requestlog`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `starlog`
--
ALTER TABLE `starlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `browserhash` (`browserhash`,`trackid`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `streamevents`
--
ALTER TABLE `streamevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `search` (`title`,`fullartist`,`StationID`) USING BTREE,
  ADD KEY `fullartist` (`fullartist`,`StationID`),
  ADD KEY `lastplayed` (`lastplayed`);

--
-- Indexes for table `trackdata`
--
ALTER TABLE `trackdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trackid` (`trackid`);

--
-- Indexes for table `TrackerTypes`
--
ALTER TABLE `TrackerTypes`
  ADD UNIQUE KEY `TrackerType` (`TrackerType`,`StationID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artistdata`
--
ALTER TABLE `artistdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `artistgroups`
--
ALTER TABLE `artistgroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `artiststitles`
--
ALTER TABLE `artiststitles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `changelog`
--
ALTER TABLE `changelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ips`
--
ALTER TABLE `ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `PlayedRequests`
--
ALTER TABLE `PlayedRequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `playoutlog`
--
ALTER TABLE `playoutlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `QueuedRequests`
--
ALTER TABLE `QueuedRequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requestlog`
--
ALTER TABLE `requestlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `starlog`
--
ALTER TABLE `starlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stats`
--
ALTER TABLE `stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `streamevents`
--
ALTER TABLE `streamevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `titles`
--
ALTER TABLE `titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trackdata`
--
ALTER TABLE `trackdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
