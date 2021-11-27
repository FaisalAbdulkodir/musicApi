-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2021 at 07:44 AM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `music`
--

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

CREATE TABLE `album` (
  `id_album` int(11) NOT NULL,
  `album_name` varchar(50) NOT NULL,
  `realease_date` date NOT NULL,
  `id_genre` int(11) NOT NULL,
  `id_label` int(11) NOT NULL,
  `id_chart` int(11) NOT NULL,
  `id_producer` int(11) NOT NULL,
  `id_publisher` int(11) NOT NULL,
  `id_licensing` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  `id_studio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `album`
--

INSERT INTO `album` (`id_album`, `album_name`, `realease_date`, `id_genre`, `id_label`, `id_chart`, `id_producer`, `id_publisher`, `id_licensing`, `id_artist`, `id_studio`) VALUES
(1, 'All Distiortion Are Intentional', '2020-07-24', 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'Nevermind', '1991-09-24', 2, 2, 2, 2, 2, 2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

CREATE TABLE `artist` (
  `id_artist` int(11) NOT NULL,
  `artist_name` varchar(50) NOT NULL,
  `origin` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `artist`
--

INSERT INTO `artist` (`id_artist`, `artist_name`, `origin`) VALUES
(1, 'Neck Deep', 'Wales'),
(2, 'Nirvana', 'Aberden'),
(3, 'Angle', 'USA');

-- --------------------------------------------------------

--
-- Table structure for table `chart`
--

CREATE TABLE `chart` (
  `id_chart` int(11) NOT NULL,
  `chart_name` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `chart`
--

INSERT INTO `chart` (`id_chart`, `chart_name`, `year`) VALUES
(1, 'UK Albums ', 2020),
(2, 'Canada Top Albums', 1991),
(3, 'Breed Album ', 2020);

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `id_genre` int(11) NOT NULL,
  `genre_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id_genre`, `genre_name`) VALUES
(1, 'Pop Punk'),
(2, 'Grunge'),
(3, 'blues');

-- --------------------------------------------------------

--
-- Table structure for table `label`
--

CREATE TABLE `label` (
  `id_label` int(11) NOT NULL,
  `label_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `label`
--

INSERT INTO `label` (`id_label`, `label_name`) VALUES
(1, 'Hopeless Record'),
(2, 'Sub Pop'),
(3, 'Heavy Record');

-- --------------------------------------------------------

--
-- Table structure for table `licensing`
--

CREATE TABLE `licensing` (
  `id_licensing` int(11) NOT NULL,
  `license_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `licensing`
--

INSERT INTO `licensing` (`id_licensing`, `license_name`) VALUES
(1, 'Bradcast Music Inc'),
(2, 'Kobalt Music Publishing'),
(3, 'ASCAP');

-- --------------------------------------------------------

--
-- Table structure for table `music_publisher`
--

CREATE TABLE `music_publisher` (
  `id_publisher` int(11) NOT NULL,
  `publisher_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `music_publisher`
--

INSERT INTO `music_publisher` (`id_publisher`, `publisher_name`) VALUES
(1, 'May Music Limited'),
(2, 'DGC Records'),
(3, 'desnara');

-- --------------------------------------------------------

--
-- Table structure for table `producer`
--

CREATE TABLE `producer` (
  `id_producer` int(11) NOT NULL,
  `producer_namer` varchar(30) NOT NULL,
  `award` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `producer`
--

INSERT INTO `producer` (`id_producer`, `producer_namer`, `award`) VALUES
(1, 'Matt Squire', 'Boston Music Award 2007'),
(2, 'Butch Vig', 'Grammy Award Winner'),
(3, 'Faisal Abdullah', 'Banjaran music award 2021');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id_song` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `plays` int(11) NOT NULL,
  `id_genre` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  `id_label` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id_song`, `title`, `plays`, `id_genre`, `id_album`, `id_artist`, `id_label`) VALUES
(1, 'Telling Stories', 10321, 1, 1, 1, 1),
(2, 'Smell Like Teen Spirit', 1001403, 2, 2, 2, 2),
(5, 'Empty House', 65432, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `studio`
--

CREATE TABLE `studio` (
  `id_studio` int(11) NOT NULL,
  `studio_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `studio`
--

INSERT INTO `studio` (`id_studio`, `studio_name`) VALUES
(1, 'Monnow Valley Studio'),
(2, 'Sound City'),
(3, '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `IdUser` int(11) NOT NULL,
  `Username` varchar(255) DEFAULT NULL,
  `NamaLengkap` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`IdUser`, `Username`, `NamaLengkap`, `Email`, `Password`) VALUES
(1, 'user', 'User', 'user@gmail.com', '123'),
(2, 'user', 'User', 'user@gmail.com', '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`id_album`),
  ADD KEY `id_genre_fk` (`id_genre`),
  ADD KEY `id_label_fk` (`id_label`),
  ADD KEY `id_chart_fk` (`id_chart`),
  ADD KEY `id_producer_fk` (`id_producer`),
  ADD KEY `id_publisher_fk` (`id_publisher`),
  ADD KEY `id_licensing_fk` (`id_licensing`),
  ADD KEY `id_artist_fk` (`id_artist`),
  ADD KEY `id_studio_fk` (`id_studio`);

--
-- Indexes for table `artist`
--
ALTER TABLE `artist`
  ADD PRIMARY KEY (`id_artist`);

--
-- Indexes for table `chart`
--
ALTER TABLE `chart`
  ADD PRIMARY KEY (`id_chart`);

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`id_genre`);

--
-- Indexes for table `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`id_label`);

--
-- Indexes for table `licensing`
--
ALTER TABLE `licensing`
  ADD PRIMARY KEY (`id_licensing`);

--
-- Indexes for table `music_publisher`
--
ALTER TABLE `music_publisher`
  ADD PRIMARY KEY (`id_publisher`);

--
-- Indexes for table `producer`
--
ALTER TABLE `producer`
  ADD PRIMARY KEY (`id_producer`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id_song`),
  ADD KEY `id_genre_fk2` (`id_genre`),
  ADD KEY `id_album_fk2` (`id_album`),
  ADD KEY `id_artist_fk2` (`id_artist`),
  ADD KEY `id_label_fk2` (`id_label`);

--
-- Indexes for table `studio`
--
ALTER TABLE `studio`
  ADD PRIMARY KEY (`id_studio`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`IdUser`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `album`
--
ALTER TABLE `album`
  MODIFY `id_album` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `artist`
--
ALTER TABLE `artist`
  MODIFY `id_artist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chart`
--
ALTER TABLE `chart`
  MODIFY `id_chart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `id_genre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `label`
--
ALTER TABLE `label`
  MODIFY `id_label` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `licensing`
--
ALTER TABLE `licensing`
  MODIFY `id_licensing` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `music_publisher`
--
ALTER TABLE `music_publisher`
  MODIFY `id_publisher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `producer`
--
ALTER TABLE `producer`
  MODIFY `id_producer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id_song` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `studio`
--
ALTER TABLE `studio`
  MODIFY `id_studio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `IdUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `album`
--
ALTER TABLE `album`
  ADD CONSTRAINT `id_artist_fk` FOREIGN KEY (`id_artist`) REFERENCES `artist` (`id_artist`),
  ADD CONSTRAINT `id_chart_fk` FOREIGN KEY (`id_chart`) REFERENCES `chart` (`id_chart`),
  ADD CONSTRAINT `id_genre_fk` FOREIGN KEY (`id_genre`) REFERENCES `genre` (`id_genre`),
  ADD CONSTRAINT `id_label_fk` FOREIGN KEY (`id_label`) REFERENCES `label` (`id_label`),
  ADD CONSTRAINT `id_licensing_fk` FOREIGN KEY (`id_licensing`) REFERENCES `licensing` (`id_licensing`),
  ADD CONSTRAINT `id_producer_fk` FOREIGN KEY (`id_producer`) REFERENCES `producer` (`id_producer`),
  ADD CONSTRAINT `id_publisher_fk` FOREIGN KEY (`id_publisher`) REFERENCES `music_publisher` (`id_publisher`),
  ADD CONSTRAINT `id_studio_fk` FOREIGN KEY (`id_studio`) REFERENCES `studio` (`id_studio`);

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `id_album_fk2` FOREIGN KEY (`id_album`) REFERENCES `album` (`id_album`),
  ADD CONSTRAINT `id_artist_fk2` FOREIGN KEY (`id_artist`) REFERENCES `artist` (`id_artist`),
  ADD CONSTRAINT `id_genre_fk2` FOREIGN KEY (`id_genre`) REFERENCES `genre` (`id_genre`),
  ADD CONSTRAINT `id_label_fk2` FOREIGN KEY (`id_label`) REFERENCES `label` (`id_label`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
