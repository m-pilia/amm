-- MySQL dump 10.15  Distrib 10.0.19-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: amm15_piliaMartino
-- ------------------------------------------------------
-- Server version	10.0.19-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Events`
--

DROP TABLE IF EXISTS `Events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Events` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Owner_id` bigint(20) unsigned DEFAULT NULL,
  `Resource_id` bigint(20) unsigned DEFAULT NULL,
  `Day` tinyint(4) DEFAULT NULL,
  `Month` tinyint(4) DEFAULT NULL,
  `Year` smallint(6) DEFAULT NULL,
  `Start` tinyint(4) DEFAULT NULL,
  `End` tinyint(4) DEFAULT NULL,
  `Notes` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Id` (`Id`),
  KEY `Owner_id` (`Owner_id`),
  KEY `Resource_id` (`Resource_id`),
  CONSTRAINT `Events_ibfk_1` FOREIGN KEY (`Owner_id`) REFERENCES `Users` (`Id`) ON UPDATE CASCADE,
  CONSTRAINT `Events_ibfk_2` FOREIGN KEY (`Resource_id`) REFERENCES `Resources` (`Id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Events`
--

LOCK TABLES `Events` WRITE;
/*!40000 ALTER TABLE `Events` DISABLE KEYS */;
INSERT INTO `Events` VALUES (1,1,1,26,6,2015,20,24,'This is a note'),(2,1,3,26,6,2015,27,32,'This is another note'),(3,1,6,26,6,2015,23,32,'This is the first event created with the web interface.'),(4,1,3,28,6,2015,44,45,''),(5,1,6,28,6,2015,14,15,''),(6,1,1,28,6,2015,21,46,''),(7,1,2,27,6,2015,27,32,''),(8,1,4,28,6,2015,30,34,''),(9,1,2,28,6,2015,18,19,''),(10,1,3,28,6,2015,37,38,''),(11,1,5,28,6,2015,42,43,''),(12,1,1,29,6,2015,19,25,'This is the first event edited with the web interface.'),(13,1,6,29,6,2015,18,22,''),(15,1,2,30,6,2015,36,38,'Some notes.'),(17,1,2,29,6,2015,38,40,NULL),(23,1,1,2,7,2015,24,28,NULL),(24,1,2,2,7,2015,16,18,NULL),(25,1,5,2,7,2015,32,40,NULL),(26,1,4,4,7,2015,19,20,NULL),(27,1,4,4,7,2015,16,17,NULL),(28,1,1,4,7,2015,26,30,NULL),(29,1,8,4,7,2015,32,37,NULL),(30,1,3,5,7,2015,16,18,NULL),(31,1,3,5,7,2015,26,30,NULL),(32,1,6,5,7,2015,20,24,NULL),(33,1,5,5,7,2015,32,36,NULL),(34,1,1,16,7,2015,22,26,NULL),(35,17,2,1,7,2015,16,26,NULL),(36,17,8,1,7,2015,36,38,NULL),(37,17,2,2,7,2015,30,36,NULL),(38,17,4,3,7,2015,20,24,NULL),(39,17,1,4,7,2015,32,44,NULL);
/*!40000 ALTER TABLE `Events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Resources`
--

DROP TABLE IF EXISTS `Resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Resources` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Id` (`Id`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Resources`
--

LOCK TABLES `Resources` WRITE;
/*!40000 ALTER TABLE `Resources` DISABLE KEYS */;
INSERT INTO `Resources` VALUES (3,'Beamer'),(1,'Conference hall'),(2,'Meeting room #1'),(4,'Meeting room #2'),(5,'Meeting room #3'),(8,'Meeting room #4'),(6,'Meeting room with a very long name');
/*!40000 ALTER TABLE `Resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(128) DEFAULT NULL,
  `Password_hash` varchar(255) DEFAULT NULL,
  `Email` varchar(320) DEFAULT NULL,
  `Department` bigint(20) unsigned DEFAULT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `First` varchar(128) DEFAULT NULL,
  `Last` varchar(128) DEFAULT NULL,
  `Role` varchar(20) DEFAULT NULL,
  `ResetToken` varchar(32) DEFAULT NULL,
  `Created_events` bigint(20) unsigned DEFAULT '0',
  UNIQUE KEY `Id` (`Id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'Cthulhu','$2y$10$f7iQeOfJe1TUSBaII29pyuoBx7UqQ8i3.unLV.JyrfwK1zCqxI5Wm','cthulhu@fhtagn.rlyeh',NULL,'uploads/phpJoo1mh80862657','Cthulhu','The Great Old One','Admin',NULL,29),(17,'John','$2y$10$gO7Ga4/0CnbuWYtFKhxzoez9gNn/yIQM3PwvYdNptXhu92gifo.Ca','martino.pilia@gmail.com',NULL,'uploads/phpnihNUk15296665','John','Doe','User',NULL,5);
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-06-30 23:26:21
