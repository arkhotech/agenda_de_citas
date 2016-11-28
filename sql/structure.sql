-- MySQL dump 10.13  Distrib 5.7.12, for osx10.11 (x86_64)
--
-- Host: localhost    Database: calendars
-- ------------------------------------------------------
-- Server version	5.5.51

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
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applier_id` varchar(20) NOT NULL,
  `applier_name` varchar(150) NOT NULL,
  `applier_email` varchar(80) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `appointment_start_time` datetime NOT NULL,
  `appointment_end_time` datetime NOT NULL,
  `is_reserved` tinyint(4) DEFAULT NULL,
  `reservation_date` datetime NOT NULL,
  `confirmation_date` datetime DEFAULT NULL,
  `user_id_cancel` varchar(20) DEFAULT NULL,
  `user_name_cancel` varchar(150) DEFAULT NULL,
  `is_canceled` tinyint(4) DEFAULT NULL,
  `cancelation_date` datetime DEFAULT NULL,
  `cancelation_cause` text,
  `applier_attended` tinyint(4) DEFAULT NULL,
  `metadata` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_appoinments_calendar_id` (`calendar_id`),
  CONSTRAINT `fk_appoinments_calendar_id` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=367 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps`
--

DROP TABLE IF EXISTS `apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps` (
  `appkey` varchar(15) NOT NULL,
  `domain` varchar(150) NOT NULL,
  `name` varchar(70) NOT NULL,
  `contact_email` varchar(80) NOT NULL,
  `from_email` varchar(80) NOT NULL,
  `from_name` varchar(80) NOT NULL,
  `html_confirmation_email` text NOT NULL,
  `html_modify_email` text NOT NULL,
  `html_cancel_email` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`appkey`,`domain`),
  UNIQUE KEY `app_key_UNIQUE` (`appkey`,`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `block_schedules`
--

DROP TABLE IF EXISTS `block_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) NOT NULL,
  `user_id_block` varchar(20) NOT NULL,
  `user_name_block` varchar(150) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `cause` text NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_block_schedules_calendar_id_idx` (`calendar_id`),
  CONSTRAINT `fk_block_schedules_calendar_id` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=410 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendars`
--

DROP TABLE IF EXISTS `calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `owner_id` varchar(20) NOT NULL,
  `owner_name` varchar(150) NOT NULL,
  `owner_email` varchar(100) DEFAULT NULL,
  `is_group` tinyint(4) DEFAULT NULL,
  `schedule` text NOT NULL,
  `time_attention` int(11) NOT NULL,
  `concurrency` int(11) NOT NULL,
  `ignore_non_working_days` tinyint(4) DEFAULT '0',
  `time_cancel_appointment` tinyint(11) DEFAULT NULL,
  `time_confirm_appointment` tinyint(4) NOT NULL,
  `appkey` varchar(15) NOT NULL,
  `domain` varchar(150) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_calendars_name` (`name`,`appkey`,`domain`),
  KEY `ix_calendars_owner_name` (`owner_name`),
  KEY `fk_calendars_app_key_idx` (`appkey`,`domain`),
  CONSTRAINT `fk_calendars_app_key_domain` FOREIGN KEY (`appkey`, `domain`) REFERENCES `apps` (`appkey`, `domain`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `non_working_days`
--

DROP TABLE IF EXISTS `non_working_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `non_working_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `date_dayoff` date NOT NULL,
  `appkey` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_non_working_days_app_key` (`appkey`),
  KEY `fk_non_working_days_app_key_idx` (`appkey`),
  CONSTRAINT `fk_non_working_days_app_key` FOREIGN KEY (`appkey`) REFERENCES `apps` (`appkey`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-14 16:47:08
