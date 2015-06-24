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
-- Table structure for table `Departments`
--

DROP TABLE IF EXISTS `Departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Departments` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) DEFAULT NULL,
  `Chief` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Id` (`Id`),
  KEY `Chief` (`Chief`),
  CONSTRAINT `Departments_ibfk_1` FOREIGN KEY (`Chief`) REFERENCES `Users` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Departments`
--

LOCK TABLES `Departments` WRITE;
/*!40000 ALTER TABLE `Departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `Departments` ENABLE KEYS */;
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
  UNIQUE KEY `Id` (`Id`),
  KEY `Department` (`Department`),
  CONSTRAINT `Users_ibfk_1` FOREIGN KEY (`Department`) REFERENCES `Departments` (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'Cthulhu','$2y$10$KNUZTbNDUW4zkqJ186cbheUQrsxjQSNGYuZoyMxlTsUhRaYYXtY/G','cthulhu@fhtagn.rlyeh',NULL,'uploads/phpJoo1mh80862657','Cthulhu','The Great Old One','Admin',NULL),(2,'User','$2y$10$tLlgJ8fHWz625yX2A4HjeOxO9m65fMMis8ZT0YQvuqvMqrOfAGkwO','martino.pilia@gmail.com',NULL,'images/default_avatar.svg','John','Doe','User',NULL),(3,'Pippo','$2y$10$B0t0KXXKNDpqrYt/qY03/O7ztGma0XMOqmT11jTZBNB5LA8jIzLLK','a@a.a',NULL,'uploads/phpx5A8SA41465683','First Pippo','Last Pippo','User',NULL),(4,'Mario','$2y$10$bsdTUF4P4ThKqjvkonWdT.WuUOfmdjeZpYj0VEzBPVaB6DCyR03si','a@a.a',NULL,'images/default_avatar.svg','Mario','Rossi','User',NULL),(5,'Paolo','$2y$10$FWUTB2a1TjZgLYw/QUfNm.4Gp9BvtAzpnnpESk4AyAEPCNet8SWTW','a@a.a',NULL,'images/default_avatar.svg','Paolo','Bianchi','User',NULL),(6,'Test','$2y$10$QHvLx8mHftjTx1v8v0.JMei6Z7ludSKc0Tvryik2mmYH2JEQKwL/W','a@a.a',NULL,'images/default_avatar.svg','Test','Test','User',NULL),(7,'Test2','$2y$10$NYvJwmKrXvcL/hp7caObwuQuc.pV9CI17hXT0WfWu75gsewFyULe6','a@a.a',NULL,'images/default_avatar.svg','Test','Test','User',NULL),(8,'Text','$2y$10$UMZk9NuIi8qARsZZrFVppuqedboffYalpJFUUyZRikN7bWeVmecSq','a@a.a',NULL,'images/default_avatar.svg','Text','Text','User',NULL),(9,'Text2','$2y$10$Nv76LkG.zzthUg5AXqLWpuU.M998dTKkLsFwazcf1QH1HtxJ/SsM.','a@a.a',NULL,'images/default_avatar.svg','Text','Text','User',NULL),(10,'Text3','$2y$10$oGUNdbHw4TxEgCrS1KcJ8O8J3lLTo2lmnR0zfF/MAjUdmZjKfIlT6','a@a.a',NULL,'images/default_avatar.svg','Te','Te','User',NULL),(11,'Text4','bla','a@a.a',NULL,'images/default_avatar.svg','Te','Te','User',NULL);
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

-- Dump completed on 2015-06-23 12:55:37
