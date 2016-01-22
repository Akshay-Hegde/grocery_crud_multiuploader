-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: multi_uploader
-- ------------------------------------------------------
-- Server version	5.1.73

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
-- Table structure for table `multi_uploader_gallery`
--

DROP TABLE IF EXISTS `multi_uploader_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multi_uploader_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `my_pictures` text,
  `my_files` text,
  `my_mail_attachments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multi_uploader_gallery`
--

LOCK TABLES `multi_uploader_gallery` WRITE;
/*!40000 ALTER TABLE `multi_uploader_gallery` DISABLE KEYS */;
INSERT INTO `multi_uploader_gallery` VALUES (1,'test file upload','a:3:{i:0;s:65:\"10858515_376256832544074_5836979769187221254_n_20141221204057.jpg\";i:1;s:47:\"Goa-Beach-Tour-HD-Wallpaper_20141221204327.jpeg\";i:2;s:54:\"1780131_679628865422197_963985172_o_20141221205146.jpg\";}','a:2:{i:0;s:53:\"grocerycrud_API_and_Functions_list_20141221204352.pdf\";i:1;s:24:\"stock_20141221204418.pdf\";}','a:2:{i:0;s:25:\"output_20141221204206.txt\";i:1;s:24:\"input_20141221204215.txt\";}'),(2,NULL,'a:1:{i:0;s:31:\"S_S_ship__1__20141222055037.jpg\";}','a:0:{}','a:0:{}');
/*!40000 ALTER TABLE `multi_uploader_gallery` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-22 12:11:51
