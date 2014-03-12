SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `rockwelltest`
--
CREATE DATABASE IF NOT EXISTS `rockwelltest` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `rockwelltest`;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `id` varchar(40) NOT NULL,
  `userId` varchar(40) NOT NULL,
  `text` varchar(140) COLLATE utf8_bin NOT NULL,
  `source` varchar(90) COLLATE utf8_bin NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(40) NOT NULL,
  `name` varchar(90) COLLATE utf8_bin NOT NULL,
  `handle` varchar(20) COLLATE utf8_bin NOT NULL,
  `followersCount` smallint(6) NOT NULL,
  `friendsCount` smallint(6) NOT NULL,
  `statusesCount` smallint(6) NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `status`
--
ALTER TABLE `status`
  ADD CONSTRAINT `fk_status_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);