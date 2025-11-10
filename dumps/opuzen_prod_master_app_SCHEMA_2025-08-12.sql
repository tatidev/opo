# ************************************************************
# Sequel Ace SQL dump
# Version 20080
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: opuzen-aurora-mysql8-cluster.cluster-c7886s6kkcmk.us-west-1.rds.amazonaws.com (MySQL 8.0.39)
# Database: opuzen_prod_master_app
# Generation Time: 2025-08-12 15:29:54 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ACT_DB
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ACT_DB`;

CREATE TABLE `ACT_DB` (
  `Contact_Type` text NOT NULL,
  `Private` text NOT NULL,
  `Company` text NOT NULL,
  `Contact` text NOT NULL,
  `Phone` text NOT NULL,
  `Phone_Ext-` text NOT NULL,
  `Title` text NOT NULL,
  `Address_1` text NOT NULL,
  `Address_2` text NOT NULL,
  `City` text NOT NULL,
  `State` text NOT NULL,
  `Zip` text NOT NULL,
  `E-mail` text NOT NULL,
  `2nd_Last_Reach` text NOT NULL,
  `3rd_Last_Reach` text NOT NULL,
  `Access_Level` text NOT NULL,
  `Address_3` text NOT NULL,
  `Alt_Phone` text NOT NULL,
  `Alt_Phone_Ext-` text NOT NULL,
  `Birth_Date` text NOT NULL,
  `Country` text NOT NULL,
  `Create_Date` text NOT NULL,
  `Department` text NOT NULL,
  `E-Mail_2_E-mail` text NOT NULL,
  `E-Mail_3_E-mail` text NOT NULL,
  `E-Mail_4_E-mail` text NOT NULL,
  `Edit_Date` text NOT NULL,
  `Fax` text NOT NULL,
  `Fax_Ext-` text NOT NULL,
  `First_Name` text NOT NULL,
  `formerly_ID_Status` text NOT NULL,
  `Home_Address_1` text NOT NULL,
  `Home_Address_2` text NOT NULL,
  `Home_Address_3` text NOT NULL,
  `Home_City` text NOT NULL,
  `Home_Country` text NOT NULL,
  `Home_Extension` text NOT NULL,
  `Home_Phone` text NOT NULL,
  `Home_State` text NOT NULL,
  `Home_Zip` text NOT NULL,
  `IDStatus` text NOT NULL,
  `Import_Date` text NOT NULL,
  `Is_Imported` text NOT NULL,
  `Last_Attempt` text NOT NULL,
  `Last_E-mail` text NOT NULL,
  `Last_Edited_By` text NOT NULL,
  `Last_Meeting` text NOT NULL,
  `Last_Name` text NOT NULL,
  `Last_Reach` text NOT NULL,
  `Last_Results` text NOT NULL,
  `Letter_Date` text NOT NULL,
  `Messenger_ID` text NOT NULL,
  `Middle_Name` text NOT NULL,
  `Mobile_Extension` text NOT NULL,
  `Mobile_Phone` text NOT NULL,
  `Name_Prefix` text NOT NULL,
  `Name_Suffix` text NOT NULL,
  `Owner` text NOT NULL,
  `Pager` text NOT NULL,
  `Pager_Extension` text NOT NULL,
  `Personal_E-mail` text NOT NULL,
  `Record_Creator` text NOT NULL,
  `Record_Manager` text NOT NULL,
  `Referred_By` text NOT NULL,
  `Salutation` text NOT NULL,
  `Showroom_Affiliation` text NOT NULL,
  `Spouse` text NOT NULL,
  `Ticker_Symbol` text NOT NULL,
  `Trivia` text NOT NULL,
  `User_1` text NOT NULL,
  `User_10` text NOT NULL,
  `User_11` text NOT NULL,
  `User_12` text NOT NULL,
  `User_13` text NOT NULL,
  `User_14` text NOT NULL,
  `User_15` text NOT NULL,
  `User_2` text NOT NULL,
  `User_3` text NOT NULL,
  `User_4` text NOT NULL,
  `User_5` text NOT NULL,
  `User_6` text NOT NULL,
  `User_7` text NOT NULL,
  `User_8` text NOT NULL,
  `User_9` text NOT NULL,
  `Web_Site` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;



# Dump of table BK_RESTOCK_ORDER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `BK_RESTOCK_ORDER`;

CREATE TABLE `BK_RESTOCK_ORDER` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `size` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `restock_status_id` int NOT NULL DEFAULT '1',
  `priority` enum('1','0') NOT NULL DEFAULT '0',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`,`destination_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table BK_RESTOCK_ORDER_COMPLETED
# ------------------------------------------------------------

DROP TABLE IF EXISTS `BK_RESTOCK_ORDER_COMPLETED`;

CREATE TABLE `BK_RESTOCK_ORDER_COMPLETED` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `size` int NOT NULL,
  `quantity` int NOT NULL,
  `restock_status_id` int NOT NULL,
  `priority` enum('0','1') NOT NULL DEFAULT '0',
  `date_requested` timestamp NULL DEFAULT NULL,
  `user_id_requested` int NOT NULL,
  `date_completed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id_completed` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table BK_RESTOCK_SHIP
# ------------------------------------------------------------

DROP TABLE IF EXISTS `BK_RESTOCK_SHIP`;

CREATE TABLE `BK_RESTOCK_SHIP` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `quantity` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table BK_RESTOCK_SHIP_COMPLETED
# ------------------------------------------------------------

DROP TABLE IF EXISTS `BK_RESTOCK_SHIP_COMPLETED`;

CREATE TABLE `BK_RESTOCK_SHIP_COMPLETED` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `quantity` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table MB_RECENT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `MB_RECENT`;

CREATE TABLE `MB_RECENT` (
  `item_id` int DEFAULT NULL,
  `product_name` varchar(17) DEFAULT NULL,
  `color` varchar(12) DEFAULT NULL,
  `done_by` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;



# Dump of table MB_STATUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `MB_STATUS`;

CREATE TABLE `MB_STATUS` (
  `item_id` int DEFAULT NULL,
  `code` varchar(9) DEFAULT NULL,
  `product_name` varchar(20) DEFAULT NULL,
  `color` varchar(16) DEFAULT NULL,
  `color_status` varchar(3) DEFAULT NULL,
  `notes` varchar(47) DEFAULT NULL,
  `done_by` varchar(10) DEFAULT NULL,
  `date_modif` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;



# Dump of table PORTFOLIO_PICTURE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PORTFOLIO_PICTURE`;

CREATE TABLE `PORTFOLIO_PICTURE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `url` varchar(256) NOT NULL,
  `notes` varchar(256) DEFAULT NULL,
  `date_add` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` int NOT NULL DEFAULT '1',
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `ON_DELETE_PORTFOLIO_PICTURES` BEFORE DELETE ON `PORTFOLIO_PICTURE` FOR EACH ROW INSERT INTO S_HISTORY_PORTFOLIO_PICTURES
(picture_id, project_id, url, notes, date_add, user_id, active, date_modif, user_id_modif)
VALUES
(OLD.id, OLD.project_id, OLD.url, OLD.notes, OLD.date_add, OLD.user_id, OLD.active, OLD.date_modif, OLD.user_id_modif) */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table PORTFOLIO_PRODUCT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PORTFOLIO_PRODUCT`;

CREATE TABLE `PORTFOLIO_PRODUCT` (
  `picture_id` int NOT NULL,
  `item_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  KEY `picture_id` (`picture_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table PORTFOLIO_PROJECT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PORTFOLIO_PROJECT`;

CREATE TABLE `PORTFOLIO_PROJECT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `notes` varchar(300) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `ON_PROJECT_DELETE` BEFORE DELETE ON `PORTFOLIO_PROJECT` FOR EACH ROW INSERT INTO S_HISTORY_PORTFOLIO_PROJECT
(project_id, name, notes, active, date_add, user_id, date_modif, user_id_modif)
VALUES
(OLD.id, OLD.name, OLD.notes, OLD.active, OLD.date_add, OLD.user_id, OLD.date_modif, OLD.user_id_modif) */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table PROC_ITEM_STATUS_CHANGE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PROC_ITEM_STATUS_CHANGE`;

CREATE TABLE `PROC_ITEM_STATUS_CHANGE` (
  `product_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `product_name` text,
  `code` varchar(9) DEFAULT NULL,
  `color` text,
  `status` varchar(20) NOT NULL DEFAULT '',
  `status_id` int NOT NULL DEFAULT '0',
  `old_status` varchar(20) DEFAULT NULL,
  `old_status_id` int DEFAULT NULL,
  `change_date` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table PROC_MASTER_PRICE_LIST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PROC_MASTER_PRICE_LIST`;

CREATE TABLE `PROC_MASTER_PRICE_LIST` (
  `product_id` int NOT NULL DEFAULT '0',
  `product_type` varchar(2) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `status` text,
  `stock_status` text,
  `width` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_front` text,
  `outdoor` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `p_res_cut` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `p_hosp_roll` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `p_dig_res` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `p_dig_hosp` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;





# Dump of table P_ABRASION_LIMIT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_ABRASION_LIMIT`;

CREATE TABLE `P_ABRASION_LIMIT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` varchar(100) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stopped, did it broke, mill not tested?';



# Dump of table P_ABRASION_TEST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_ABRASION_TEST`;

CREATE TABLE `P_ABRASION_TEST` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CATEGORY_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CATEGORY_FILES`;

CREATE TABLE `P_CATEGORY_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `descr` varchar(100) DEFAULT NULL,
  `directory` varchar(60) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CATEGORY_LISTS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CATEGORY_LISTS`;

CREATE TABLE `P_CATEGORY_LISTS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `descr` varchar(100) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CLEANING
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CLEANING`;

CREATE TABLE `P_CLEANING` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CLEANING_INSTRUCTIONS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CLEANING_INSTRUCTIONS`;

CREATE TABLE `P_CLEANING_INSTRUCTIONS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CLEANING_INSTRUCTIONS_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CLEANING_INSTRUCTIONS_FILES`;

CREATE TABLE `P_CLEANING_INSTRUCTIONS_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_COLOR`;

CREATE TABLE `P_COLOR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_CONTENT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_CONTENT`;

CREATE TABLE `P_CONTENT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_FINISH
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_FINISH`;

CREATE TABLE `P_FINISH` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_FIRECODE_TEST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_FIRECODE_TEST`;

CREATE TABLE `P_FIRECODE_TEST` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_FQS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_FQS`;

CREATE TABLE `P_FQS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_FQS_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_FQS_FILES`;

CREATE TABLE `P_FQS_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_GENERAL_CLEANING_INSTRUCTIONS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_GENERAL_CLEANING_INSTRUCTIONS`;

CREATE TABLE `P_GENERAL_CLEANING_INSTRUCTIONS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_GENERAL_CLEANING_INSTRUCTIONS_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_GENERAL_CLEANING_INSTRUCTIONS_FILES`;

CREATE TABLE `P_GENERAL_CLEANING_INSTRUCTIONS_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_GENERAL_WARRANTY
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_GENERAL_WARRANTY`;

CREATE TABLE `P_GENERAL_WARRANTY` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_GENERAL_WARRANTY_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_GENERAL_WARRANTY_FILES`;

CREATE TABLE `P_GENERAL_WARRANTY_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_ORIGIN
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_ORIGIN`;

CREATE TABLE `P_ORIGIN` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_PRICE_PROGRAM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_PRICE_PROGRAM`;

CREATE TABLE `P_PRICE_PROGRAM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_PRICE_TYPE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_PRICE_TYPE`;

CREATE TABLE `P_PRICE_TYPE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_PRODUCT_STATUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_PRODUCT_STATUS`;

CREATE TABLE `P_PRODUCT_STATUS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `web_vis` tinyint(1) DEFAULT NULL COMMENT 'Website visible',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_PRODUCT_TASK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_PRODUCT_TASK`;

CREATE TABLE `P_PRODUCT_TASK` (
  `id` int NOT NULL AUTO_INCREMENT,
  `n_order` int NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `descr` varchar(256) NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_RESTOCK_DESTINATION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_RESTOCK_DESTINATION`;

CREATE TABLE `P_RESTOCK_DESTINATION` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_RESTOCK_STATUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_RESTOCK_STATUS`;

CREATE TABLE `P_RESTOCK_STATUS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_SAMPLING_LOCATIONS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_SAMPLING_LOCATIONS`;

CREATE TABLE `P_SAMPLING_LOCATIONS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_SHELF
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_SHELF`;

CREATE TABLE `P_SHELF` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_STOCK_STATUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_STOCK_STATUS`;

CREATE TABLE `P_STOCK_STATUS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `web_vis` tinyint(1) DEFAULT NULL COMMENT 'website visible',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_TERMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_TERMS`;

CREATE TABLE `P_TERMS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_TERMS_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_TERMS_FILES`;

CREATE TABLE `P_TERMS_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_USE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_USE`;

CREATE TABLE `P_USE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_WARRANTY
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_WARRANTY`;

CREATE TABLE `P_WARRANTY` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `descr` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_WARRANTY_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_WARRANTY_FILES`;

CREATE TABLE `P_WARRANTY_FILES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `related_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_WEAVE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_WEAVE`;

CREATE TABLE `P_WEAVE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table P_WEIGHT_UNIT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `P_WEIGHT_UNIT`;

CREATE TABLE `P_WEIGHT_UNIT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Q_LIST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Q_LIST`;

CREATE TABLE `Q_LIST` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `abrev` varchar(15) DEFAULT NULL,
  `initial_discount` decimal(3,2) NOT NULL DEFAULT '1.00',
  `notes` varchar(40) DEFAULT NULL,
  `o_name` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Q_LIST_CATEGORY
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Q_LIST_CATEGORY`;

CREATE TABLE `Q_LIST_CATEGORY` (
  `list_id` int NOT NULL,
  `category_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `list_id` (`list_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Q_LIST_ITEMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Q_LIST_ITEMS`;

CREATE TABLE `Q_LIST_ITEMS` (
  `list_id` int NOT NULL,
  `item_id` int NOT NULL,
  `n_order` int NOT NULL DEFAULT '0',
  `p_hosp_cut` decimal(6,2) DEFAULT NULL,
  `p_hosp_roll` decimal(6,2) DEFAULT NULL,
  `p_res_cut` decimal(6,2) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1' COMMENT '0 no / 1 yes',
  `big_piece` int NOT NULL DEFAULT '0' COMMENT '0 false / 1 true',
  `user_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated thru code',
  PRIMARY KEY (`list_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Q_LIST_SHOWROOMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Q_LIST_SHOWROOMS`;

CREATE TABLE `Q_LIST_SHOWROOMS` (
  `list_id` int NOT NULL,
  `showroom_id` int NOT NULL,
  KEY `list_id` (`list_id`,`showroom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table RESTOCK_ORDER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `RESTOCK_ORDER`;

CREATE TABLE `RESTOCK_ORDER` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `size` varchar(3) DEFAULT NULL,
  `restock_status_id` int NOT NULL DEFAULT '1',
  `quantity_total` int NOT NULL,
  `quantity_priority` int NOT NULL,
  `quantity_ringsets` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`,`destination_id`,`size`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `log_update` BEFORE UPDATE ON `RESTOCK_ORDER` FOR EACH ROW BEGIN
    INSERT INTO S_HISTORY_RESTOCK_ORDER 
    (order_id, item_id, destination_id, size, restock_status_id, quantity_total, quantity_priority, quantity_ringsets, date_add, user_id, date_modif, user_id_modif)
    VALUES
    (OLD.id, OLD.item_id, OLD.destination_id, OLD.size, OLD.restock_status_id, OLD.quantity_total, OLD.quantity_priority, OLD.quantity_ringsets, OLD.date_add, OLD.user_id, OLD.date_modif, OLD.user_id_modif);
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table RESTOCK_ORDER_COMPLETED
# ------------------------------------------------------------

DROP TABLE IF EXISTS `RESTOCK_ORDER_COMPLETED`;

CREATE TABLE `RESTOCK_ORDER_COMPLETED` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `size` int NOT NULL,
  `quantity_total` int NOT NULL,
  `quantity_priority` int NOT NULL DEFAULT '0',
  `quantity_ringsets` int NOT NULL DEFAULT '0',
  `restock_status_id` int NOT NULL,
  `date_requested` timestamp NULL DEFAULT NULL,
  `user_id_requested` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  `date_completed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id_completed` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table RESTOCK_SHIP
# ------------------------------------------------------------

DROP TABLE IF EXISTS `RESTOCK_SHIP`;

CREATE TABLE `RESTOCK_SHIP` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `quantity` int NOT NULL,
  `quantity_ringsets` int NOT NULL DEFAULT '0',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table RESTOCK_SHIP_COMPLETED
# ------------------------------------------------------------

DROP TABLE IF EXISTS `RESTOCK_SHIP_COMPLETED`;

CREATE TABLE `RESTOCK_SHIP_COMPLETED` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `quantity` int NOT NULL,
  `quantity_ringsets` int NOT NULL DEFAULT '0',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;









# Dump of table SHOWCASE_DIGITAL_STYLE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_DIGITAL_STYLE`;

CREATE TABLE `SHOWCASE_DIGITAL_STYLE` (
  `style_id` int NOT NULL,
  `url_title` varchar(100) NOT NULL,
  `visible` char(1) NOT NULL,
  `pic_big` char(1) DEFAULT NULL,
  `pic_big_url` varchar(150) DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_DIGITAL_STYLE_ITEMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_DIGITAL_STYLE_ITEMS`;

CREATE TABLE `SHOWCASE_DIGITAL_STYLE_ITEMS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `style_id` int NOT NULL,
  `url_title` varchar(100) NOT NULL,
  `visible` char(1) NOT NULL,
  `n_order` int NOT NULL,
  `pic_big` char(1) DEFAULT NULL,
  `pic_big_url` varchar(150) DEFAULT NULL,
  `archived` char(1) NOT NULL DEFAULT 'N',
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SHOWCASE_DIGITAL_STYLE_ITEMS_style_id_index` (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_DIGITAL_STYLE_ITEMS_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_DIGITAL_STYLE_ITEMS_COLOR`;

CREATE TABLE `SHOWCASE_DIGITAL_STYLE_ITEMS_COLOR` (
  `item_id` int NOT NULL,
  `color_id` int NOT NULL,
  `n_order` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`,`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_DIGITAL_STYLE_ITEMS_COORD_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_DIGITAL_STYLE_ITEMS_COORD_COLOR`;

CREATE TABLE `SHOWCASE_DIGITAL_STYLE_ITEMS_COORD_COLOR` (
  `item_id` int NOT NULL DEFAULT '0',
  `coord_color_id` int NOT NULL DEFAULT '0',
  KEY `C_ITEM_ID` (`item_id`),
  KEY `C_COORD_COLOR_ID` (`coord_color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Not in use';



# Dump of table SHOWCASE_DIGITAL_STYLE_PATTERNS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_DIGITAL_STYLE_PATTERNS`;

CREATE TABLE `SHOWCASE_DIGITAL_STYLE_PATTERNS` (
  `style_id` int NOT NULL DEFAULT '0',
  `pattern_id` int NOT NULL DEFAULT '0',
  KEY `ID_PATTERN` (`style_id`,`pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_ITEM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_ITEM`;

CREATE TABLE `SHOWCASE_ITEM` (
  `item_id` int NOT NULL,
  `url_title` varchar(100) NOT NULL,
  `visible` char(1) NOT NULL,
  `n_order` int NOT NULL,
  `pic_big` char(1) DEFAULT NULL,
  `pic_big_url` varchar(150) DEFAULT NULL,
  `pic_hd` char(1) DEFAULT NULL,
  `pic_hd_url` varchar(150) DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_ITEM_COORD_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_ITEM_COORD_COLOR`;

CREATE TABLE `SHOWCASE_ITEM_COORD_COLOR` (
  `item_id` int NOT NULL DEFAULT '0',
  `coord_color_id` int NOT NULL DEFAULT '0',
  KEY `C_ITEM_ID` (`item_id`),
  KEY `C_COORD_COLOR_ID` (`coord_color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_PRESS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_PRESS`;

CREATE TABLE `SHOWCASE_PRESS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `date_upload` date DEFAULT NULL,
  `pic_big` varchar(1) DEFAULT NULL,
  `pic_thumb` varchar(1) NOT NULL DEFAULT 'N',
  `visible` char(1) NOT NULL DEFAULT 'N',
  `n_order` int NOT NULL DEFAULT '0',
  `date_modif` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_PRODUCT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_PRODUCT`;

CREATE TABLE `SHOWCASE_PRODUCT` (
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL COMMENT 'Not in use. All are R for now',
  `url_title` varchar(100) NOT NULL,
  `descr` text,
  `visible` char(1) NOT NULL,
  `pic_big` char(1) DEFAULT NULL,
  `pic_big_url` varchar(150) DEFAULT NULL,
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_PRODUCT_COLLECTION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_PRODUCT_COLLECTION`;

CREATE TABLE `SHOWCASE_PRODUCT_COLLECTION` (
  `product_id` int NOT NULL DEFAULT '0',
  `collection_id` int NOT NULL DEFAULT '0',
  KEY `C_COLLECTION_ID` (`collection_id`,`product_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_PRODUCT_CONTENTS_WEB
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_PRODUCT_CONTENTS_WEB`;

CREATE TABLE `SHOWCASE_PRODUCT_CONTENTS_WEB` (
  `product_id` bigint NOT NULL DEFAULT '0',
  `content_web_id` int NOT NULL DEFAULT '0',
  KEY `SHOWCASE_PRODUCT_CONTENTS_WEB_product_id_content_web_id_index` (`product_id`,`content_web_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_PRODUCT_PATTERNS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_PRODUCT_PATTERNS`;

CREATE TABLE `SHOWCASE_PRODUCT_PATTERNS` (
  `product_id` int NOT NULL DEFAULT '0',
  `pattern_id` int NOT NULL DEFAULT '0',
  KEY `ID_PATTERN` (`product_id`,`pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_P_COLLECTION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_P_COLLECTION`;

CREATE TABLE `SHOWCASE_P_COLLECTION` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_P_CONTENTS_WEB
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_P_CONTENTS_WEB`;

CREATE TABLE `SHOWCASE_P_CONTENTS_WEB` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ID_WEB` int DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SHOWCASE_P_CONTENTS_WEB_ID_WEB_index` (`ID_WEB`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_P_COORD_COLORS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_P_COORD_COLORS`;

CREATE TABLE `SHOWCASE_P_COORD_COLORS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `showImage` char(1) NOT NULL DEFAULT 'N' COMMENT 'N:NO, Y:YES',
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_P_PATTERNS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_P_PATTERNS`;

CREATE TABLE `SHOWCASE_P_PATTERNS` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `date_add` timestamp NULL DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table SHOWCASE_SCREENPRINT_STYLE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SHOWCASE_SCREENPRINT_STYLE`;

CREATE TABLE `SHOWCASE_SCREENPRINT_STYLE` (
  `style_id` int NOT NULL,
  `descr` text NOT NULL,
  `visible` char(1) NOT NULL,
  `pic_thumb` char(1) NOT NULL,
  `pic_full_repeat` char(1) NOT NULL,
  `pic_actual_scale` char(1) NOT NULL,
  `pic_add1` char(1) NOT NULL,
  `pic_add2` char(1) NOT NULL,
  `pic_pdf` char(1) NOT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_ITEM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_ITEM`;

CREATE TABLE `S_HISTORY_ITEM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL,
  `in_ringset` int NOT NULL DEFAULT '0' COMMENT '0 no / 1 yes',
  `code` varchar(9) NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  `stock_status_id` int NOT NULL DEFAULT '1',
  `vendor_color` varchar(20) DEFAULT NULL,
  `vendor_code` varchar(20) DEFAULT NULL,
  `roll_location_id` varchar(11) DEFAULT NULL,
  `roll_yardage` decimal(11,2) DEFAULT NULL,
  `bin_location_id` varchar(11) DEFAULT NULL,
  `bin_quantity` int DEFAULT NULL,
  `min_order_qty` varchar(15) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archived` char(11) NOT NULL DEFAULT 'N',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_ITEM_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_ITEM_COLOR`;

CREATE TABLE `S_HISTORY_ITEM_COLOR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `color_id` int NOT NULL,
  `n_order` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_ITEM_IN_MASTER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_ITEM_IN_MASTER`;

CREATE TABLE `S_HISTORY_ITEM_IN_MASTER` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `in_master` tinytext NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_ITEM_SHELF
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_ITEM_SHELF`;

CREATE TABLE `S_HISTORY_ITEM_SHELF` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `shelf_id` int NOT NULL,
  `date_add` timestamp NULL DEFAULT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_ITEM_STATUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_ITEM_STATUS`;

CREATE TABLE `S_HISTORY_ITEM_STATUS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `status_id` int NOT NULL,
  `stock_status_id` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PORTFOLIO_PICTURES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PORTFOLIO_PICTURES`;

CREATE TABLE `S_HISTORY_PORTFOLIO_PICTURES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `picture_id` int NOT NULL,
  `project_id` int NOT NULL,
  `url` varchar(256) NOT NULL,
  `notes` varchar(256) DEFAULT NULL,
  `date_add` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` int NOT NULL DEFAULT '1',
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PORTFOLIO_PRODUCT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PORTFOLIO_PRODUCT`;

CREATE TABLE `S_HISTORY_PORTFOLIO_PRODUCT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `picture_id` int NOT NULL,
  `item_id` int NOT NULL,
  `date_add` timestamp NULL DEFAULT NULL,
  `user_id` int NOT NULL,
  `date_archive` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id_archive` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PORTFOLIO_PROJECT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PORTFOLIO_PROJECT`;

CREATE TABLE `S_HISTORY_PORTFOLIO_PROJECT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `notes` varchar(300) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT`;

CREATE TABLE `S_HISTORY_PRODUCT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `width` decimal(11,2) NOT NULL,
  `vrepeat` decimal(5,2) NOT NULL,
  `hrepeat` decimal(5,2) NOT NULL,
  `lightfastness` varchar(128) DEFAULT NULL,
  `outdoor` char(1) NOT NULL DEFAULT 'N',
  `dig_product_name` varchar(50) DEFAULT NULL,
  `dig_width` decimal(11,2) DEFAULT NULL,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `log_vers_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_CONTENT_BACK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_CONTENT_BACK`;

CREATE TABLE `S_HISTORY_PRODUCT_CONTENT_BACK` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `perc` decimal(5,2) NOT NULL,
  `content_id` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_CONTENT_FRONT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_CONTENT_FRONT`;

CREATE TABLE `S_HISTORY_PRODUCT_CONTENT_FRONT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `perc` decimal(5,2) NOT NULL,
  `content_id` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_IN_MASTER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_IN_MASTER`;

CREATE TABLE `S_HISTORY_PRODUCT_IN_MASTER` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `log_vers_id` int NOT NULL,
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_PRICE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_PRICE`;

CREATE TABLE `S_HISTORY_PRODUCT_PRICE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL COMMENT 'Regular/Digital/ScreenPrint',
  `p_hosp_cut` decimal(6,2) DEFAULT NULL,
  `p_hosp_roll` decimal(6,2) DEFAULT NULL,
  `p_res_cut` decimal(6,2) DEFAULT NULL,
  `p_dig_hosp` decimal(6,2) DEFAULT NULL,
  `p_dig_res` decimal(6,2) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_type` (`product_id`,`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_PRICE_COST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_PRICE_COST`;

CREATE TABLE `S_HISTORY_PRODUCT_PRICE_COST` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `fob` varchar(50) NOT NULL DEFAULT '',
  `cost_cut_type_id` int DEFAULT NULL,
  `cost_cut` decimal(6,2) DEFAULT NULL,
  `cost_half_roll_type_id` int DEFAULT NULL,
  `cost_half_roll` decimal(6,2) DEFAULT NULL,
  `cost_roll_type_id` int DEFAULT NULL,
  `cost_roll` decimal(6,2) DEFAULT NULL,
  `cost_roll_landed_type_id` int DEFAULT NULL,
  `cost_roll_landed` decimal(6,2) DEFAULT NULL,
  `cost_roll_ex_mill_type_id` int DEFAULT NULL,
  `cost_roll_ex_mill` decimal(6,2) DEFAULT NULL,
  `cost_roll_ex_mill_text` text,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_TASK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_TASK`;

CREATE TABLE `S_HISTORY_PRODUCT_TASK` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `task_id` int NOT NULL,
  `completed` enum('Y','N') NOT NULL DEFAULT 'N',
  `notes` varchar(512) DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_VARIOUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_VARIOUS`;

CREATE TABLE `S_HISTORY_PRODUCT_VARIOUS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `vendor_product_name` varchar(50) NOT NULL,
  `yards_per_roll` varchar(15) NOT NULL,
  `lead_time` varchar(10) NOT NULL,
  `min_order_qty` varchar(15) NOT NULL,
  `tariff_code` varchar(35) NOT NULL,
  `tariff_surcharge` decimal(5,2) DEFAULT NULL,
  `railroaded` char(1) NOT NULL DEFAULT 'N',
  `prop_65` char(1) DEFAULT NULL,
  `weight_n` decimal(5,2) DEFAULT NULL,
  `weight_unit_id` int DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_VENDOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_VENDOR`;

CREATE TABLE `S_HISTORY_PRODUCT_VENDOR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_WEAVE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_WEAVE`;

CREATE TABLE `S_HISTORY_PRODUCT_WEAVE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `weave_id` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_X_DIGITAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_X_DIGITAL`;

CREATE TABLE `S_HISTORY_PRODUCT_X_DIGITAL` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `item_id` int NOT NULL,
  `reverse_ground` char(1) NOT NULL,
  `style_id` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `log_vers_id` int NOT NULL DEFAULT '1',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_3` (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_PRODUCT_X_SCREENPRINT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_PRODUCT_X_SCREENPRINT`;

CREATE TABLE `S_HISTORY_PRODUCT_X_SCREENPRINT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `item_id` int NOT NULL,
  `style_id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `log_vers_id` int NOT NULL DEFAULT '1',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_3` (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_Q_LIST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_Q_LIST`;

CREATE TABLE `S_HISTORY_Q_LIST` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `abrev` varchar(15) DEFAULT NULL,
  `initial_discount` decimal(3,2) NOT NULL DEFAULT '1.00',
  `notes` varchar(40) DEFAULT NULL,
  `o_name` text,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_Q_LIST_CATEGORY
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_Q_LIST_CATEGORY`;

CREATE TABLE `S_HISTORY_Q_LIST_CATEGORY` (
  `list_id` int NOT NULL,
  `category_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `list_id` (`list_id`,`category_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_Q_LIST_ITEMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_Q_LIST_ITEMS`;

CREATE TABLE `S_HISTORY_Q_LIST_ITEMS` (
  `list_id` int NOT NULL,
  `item_id` int NOT NULL,
  `n_order` int NOT NULL DEFAULT '0',
  `p_hosp_cut` decimal(6,2) DEFAULT NULL,
  `p_hosp_roll` decimal(6,2) DEFAULT NULL,
  `p_res_cut` decimal(6,2) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1' COMMENT '0 no / 1 yes',
  `big_piece` int NOT NULL DEFAULT '0' COMMENT '0 false / 1 true',
  `user_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated thru code',
  PRIMARY KEY (`list_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_Q_LIST_SHOWROOMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_Q_LIST_SHOWROOMS`;

CREATE TABLE `S_HISTORY_Q_LIST_SHOWROOMS` (
  `list_id` int NOT NULL,
  `showroom_id` int NOT NULL,
  KEY `list_id` (`list_id`,`showroom_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table S_HISTORY_RESTOCK_ORDER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `S_HISTORY_RESTOCK_ORDER`;

CREATE TABLE `S_HISTORY_RESTOCK_ORDER` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `size` varchar(3) DEFAULT NULL,
  `restock_status_id` int NOT NULL DEFAULT '1',
  `quantity_total` int NOT NULL,
  `quantity_priority` int NOT NULL DEFAULT '0',
  `quantity_ringsets` int NOT NULL DEFAULT '0',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT NULL,
  `user_id_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table TABLE127
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TABLE127`;

CREATE TABLE `TABLE127` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(24) DEFAULT NULL,
  `code` varchar(9) DEFAULT NULL,
  `color` varchar(17) DEFAULT NULL,
  `yards` decimal(3,2) DEFAULT NULL,
  `location` varchar(4) DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;



# Dump of table TEMP_30UNDER_ITEM_IDS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TEMP_30UNDER_ITEM_IDS`;

CREATE TABLE `TEMP_30UNDER_ITEM_IDS` (
  `item_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table TEMP_30UNDER_PRODUCT_IDS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TEMP_30UNDER_PRODUCT_IDS`;

CREATE TABLE `TEMP_30UNDER_PRODUCT_IDS` (
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table TEMP_ROADKIT_IDS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TEMP_ROADKIT_IDS`;

CREATE TABLE `TEMP_ROADKIT_IDS` (
  `id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table TEMP_ROADKIT_ITEMS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TEMP_ROADKIT_ITEMS`;

CREATE TABLE `TEMP_ROADKIT_ITEMS` (
  `item_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table TEMP_ROADKIT_PRODUCTS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `TEMP_ROADKIT_PRODUCTS`;

CREATE TABLE `TEMP_ROADKIT_PRODUCTS` (
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_ITEM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM`;

CREATE TABLE `T_ITEM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL,
  `in_ringset` int NOT NULL DEFAULT '0' COMMENT '0 no / 1 yes',
  `code` varchar(9) DEFAULT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  `stock_status_id` int NOT NULL DEFAULT '1',
  `vendor_color` varchar(50) DEFAULT NULL,
  `vendor_code` varchar(50) DEFAULT NULL,
  `roll_location_id` varchar(11) DEFAULT NULL,
  `roll_yardage` decimal(11,2) DEFAULT NULL,
  `bin_location_id` varchar(11) DEFAULT NULL,
  `bin_quantity` int DEFAULT NULL,
  `min_order_qty` varchar(20) DEFAULT NULL,
  `reselections_ids` varchar(1024) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `archived` char(11) NOT NULL DEFAULT 'N',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_product_id` (`product_id`),
  KEY `product_id` (`product_id`,`id`,`product_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `log_item` AFTER UPDATE ON `T_ITEM` FOR EACH ROW BEGIN

		IF (OLD.in_master <> NEW.in_master) THEN
        	INSERT INTO S_HISTORY_ITEM_IN_MASTER (item_id, in_master, date_modif, user_id)
            VALUES (OLD.id, OLD.in_master, OLD.date_modif, OLD.user_id);
        END IF;

		IF (OLD.status_id <> NEW.status_id OR OLD.stock_status_id <> NEW.stock_status_id) THEN
			INSERT INTO S_HISTORY_ITEM_STATUS (item_id, status_id, stock_status_id, date_modif, user_id)
			VALUES (OLD.id, OLD.status_id, OLD.stock_status_id, OLD.date_modif, OLD.user_id);
		END IF;
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table T_ITEM_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM_COLOR`;

CREATE TABLE `T_ITEM_COLOR` (
  `item_id` int NOT NULL,
  `color_id` int NOT NULL,
  `n_order` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`,`color_id`),
  KEY `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_ITEM_MESSAGES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM_MESSAGES`;

CREATE TABLE `T_ITEM_MESSAGES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `message` text NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_ITEM_RESELECTION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM_RESELECTION`;

CREATE TABLE `T_ITEM_RESELECTION` (
  `item_id_0` int NOT NULL,
  `item_id_1` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`item_id_0`,`item_id_1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_ITEM_SHELF
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM_SHELF`;

CREATE TABLE `T_ITEM_SHELF` (
  `item_id` int NOT NULL,
  `shelf_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  KEY `T_ITEM_SHELF_item_id_shelf_id_index` (`item_id`,`shelf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `log_item_shelf` AFTER DELETE ON `T_ITEM_SHELF` FOR EACH ROW INSERT INTO S_HISTORY_ITEM_SHELF (item_id, shelf_id, date_add, user_id)
VALUES (old.item_id, old.shelf_id, old.date_add, old.user_id) */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table T_ITEM_STOCK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_ITEM_STOCK`;

CREATE TABLE `T_ITEM_STOCK` (
  `item_id` int NOT NULL,
  `stock_id` int NOT NULL,
  `yardsInStock` decimal(11,2) NOT NULL DEFAULT '0.00',
  `yardsOnHold` decimal(11,2) NOT NULL DEFAULT '0.00',
  `yardsAvailable` decimal(11,2) NOT NULL DEFAULT '0.00',
  `yardsOnOrder` decimal(11,2) NOT NULL DEFAULT '0.00',
  `yardsBackorder` decimal(11,2) NOT NULL DEFAULT '0.00',
  `date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`,`stock_id`),
  UNIQUE KEY `item_id` (`item_id`,`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT`;

CREATE TABLE `T_PRODUCT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` char(1) NOT NULL DEFAULT 'R',
  `width` decimal(11,2) DEFAULT NULL,
  `vrepeat` decimal(5,2) DEFAULT NULL,
  `hrepeat` decimal(5,2) DEFAULT NULL,
  `lightfastness` varchar(128) DEFAULT NULL,
  `seam_slippage` varchar(128) NOT NULL,
  `outdoor` char(1) NOT NULL DEFAULT 'N',
  `dig_product_name` varchar(50) DEFAULT NULL,
  `dig_width` decimal(11,2) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `archived` char(1) NOT NULL DEFAULT 'N',
  `log_vers_id` int NOT NULL DEFAULT '1',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`pklopuzen`@`%` */ /*!50003 TRIGGER `log_product` BEFORE UPDATE ON `T_PRODUCT` FOR EACH ROW BEGIN

		IF (OLD.in_master <> NEW.in_master) THEN
        	INSERT INTO S_HISTORY_PRODUCT_IN_MASTER (product_id, in_master, log_vers_id, date_modif, user_id)
            VALUES (OLD.id, OLD.in_master, OLD.log_vers_id, OLD.date_modif, OLD.user_id);
        END IF;
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table T_PRODUCT_ABRASION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_ABRASION`;

CREATE TABLE `T_PRODUCT_ABRASION` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `n_rubs` int NOT NULL,
  `abrasion_test_id` int NOT NULL,
  `abrasion_limit_id` int NOT NULL,
  `visible` char(1) NOT NULL DEFAULT 'Y',
  `data_in_vendor_specsheet` char(1) NOT NULL DEFAULT 'N',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `T_PRODUCT_ABRASION_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `T_PRODUCT` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_ABRASION_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_ABRASION_FILES`;

CREATE TABLE `T_PRODUCT_ABRASION_FILES` (
  `abrasion_id` int NOT NULL,
  `url_dir` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  KEY `abrasion_id` (`abrasion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CLEANING
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CLEANING`;

CREATE TABLE `T_PRODUCT_CLEANING` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `cleaning_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CLEANING_INSTRUCTIONS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CLEANING_INSTRUCTIONS`;

CREATE TABLE `T_PRODUCT_CLEANING_INSTRUCTIONS` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `cleaning_instructions_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CLEANING_SPECIAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CLEANING_SPECIAL`;

CREATE TABLE `T_PRODUCT_CLEANING_SPECIAL` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `special_instruction` varchar(256) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CONTENT_BACK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CONTENT_BACK`;

CREATE TABLE `T_PRODUCT_CONTENT_BACK` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `perc` decimal(5,2) NOT NULL,
  `content_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`perc`,`content_id`),
  UNIQUE KEY `id` (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CONTENT_BACK_DESCR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CONTENT_BACK_DESCR`;

CREATE TABLE `T_PRODUCT_CONTENT_BACK_DESCR` (
  `product_id` int NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CONTENT_FRONT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CONTENT_FRONT`;

CREATE TABLE `T_PRODUCT_CONTENT_FRONT` (
  `product_id` int NOT NULL,
  `perc` decimal(5,2) NOT NULL,
  `content_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`perc`,`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_CONTENT_FRONT_DESCR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_CONTENT_FRONT_DESCR`;

CREATE TABLE `T_PRODUCT_CONTENT_FRONT_DESCR` (
  `product_id` int NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_FILES`;

CREATE TABLE `T_PRODUCT_FILES` (
  `product_id` int NOT NULL,
  `product_type` char(1) DEFAULT NULL,
  `category_id` int NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `url_dir` varchar(100) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  KEY `product_id` (`product_id`,`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_FINISH
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_FINISH`;

CREATE TABLE `T_PRODUCT_FINISH` (
  `product_id` int NOT NULL,
  `finish_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`finish_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_FINISH_SPECIAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_FINISH_SPECIAL`;

CREATE TABLE `T_PRODUCT_FINISH_SPECIAL` (
  `product_id` int NOT NULL,
  `special_instruction` varchar(150) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_FIRECODE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_FIRECODE`;

CREATE TABLE `T_PRODUCT_FIRECODE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `firecode_test_id` int NOT NULL,
  `visible` char(1) NOT NULL DEFAULT 'Y',
  `data_in_vendor_specsheet` char(1) NOT NULL DEFAULT 'N',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_FIRECODE_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_FIRECODE_FILES`;

CREATE TABLE `T_PRODUCT_FIRECODE_FILES` (
  `firecode_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  KEY `firecode_id` (`firecode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_MESSAGES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_MESSAGES`;

CREATE TABLE `T_PRODUCT_MESSAGES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `product_type` varchar(2) NOT NULL,
  `message` text NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`,`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_ORIGIN
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_ORIGIN`;

CREATE TABLE `T_PRODUCT_ORIGIN` (
  `product_id` int NOT NULL,
  `origin_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`origin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_PRICE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_PRICE`;

CREATE TABLE `T_PRODUCT_PRICE` (
  `product_id` int NOT NULL,
  `product_type` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Regular/Digital/ScreenPrint',
  `p_hosp_cut` decimal(6,2) DEFAULT NULL,
  `p_hosp_roll` decimal(6,2) DEFAULT NULL,
  `p_res_cut` decimal(6,2) DEFAULT NULL,
  `p_dig_res` decimal(6,2) DEFAULT NULL,
  `p_dig_hosp` decimal(6,2) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_PRICE_COST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_PRICE_COST`;

CREATE TABLE `T_PRODUCT_PRICE_COST` (
  `product_id` int NOT NULL,
  `fob` varchar(50) NOT NULL DEFAULT '',
  `cost_cut_type_id` int DEFAULT NULL,
  `cost_cut` decimal(6,2) DEFAULT NULL,
  `cost_half_roll_type_id` int DEFAULT NULL,
  `cost_half_roll` decimal(6,2) DEFAULT NULL,
  `cost_roll_type_id` int DEFAULT NULL,
  `cost_roll` decimal(6,2) DEFAULT NULL,
  `cost_roll_landed_type_id` int DEFAULT NULL,
  `cost_roll_landed` decimal(6,2) DEFAULT NULL,
  `cost_roll_ex_mill_type_id` int DEFAULT NULL,
  `cost_roll_ex_mill` decimal(6,2) DEFAULT NULL,
  `cost_roll_ex_mill_text` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_PRICE_PROGRAM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_PRICE_PROGRAM`;

CREATE TABLE `T_PRODUCT_PRICE_PROGRAM` (
  `product_id` int NOT NULL,
  `price_program_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`price_program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_SHELF-DELETE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_SHELF-DELETE`;

CREATE TABLE `T_PRODUCT_SHELF-DELETE` (
  `product_id` int NOT NULL,
  `product_type` char(1) NOT NULL,
  `shelf_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  UNIQUE KEY `product_id` (`product_id`,`product_type`,`shelf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_TASK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_TASK`;

CREATE TABLE `T_PRODUCT_TASK` (
  `product_id` int NOT NULL,
  `product_type` enum('R','D') NOT NULL,
  `task_id` int NOT NULL,
  `task_who` varchar(32) DEFAULT NULL,
  `task_when` timestamp NULL DEFAULT NULL,
  `task_notes` varchar(512) DEFAULT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`product_type`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_USE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_USE`;

CREATE TABLE `T_PRODUCT_USE` (
  `product_id` int NOT NULL,
  `use_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`use_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_VARIOUS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_VARIOUS`;

CREATE TABLE `T_PRODUCT_VARIOUS` (
  `product_id` int NOT NULL,
  `vendor_product_name` varchar(50) NOT NULL,
  `yards_per_roll` varchar(50) NOT NULL,
  `lead_time` varchar(50) NOT NULL,
  `min_order_qty` varchar(50) NOT NULL,
  `tariff_code` varchar(50) NOT NULL,
  `tariff_surcharge` varchar(50) DEFAULT NULL,
  `duty_perc` varchar(50) DEFAULT NULL,
  `freight_surcharge` varchar(64) DEFAULT NULL,
  `vendor_notes` text,
  `railroaded` char(1) NOT NULL DEFAULT 'N',
  `prop_65` char(1) DEFAULT NULL,
  `ab_2998_compliant` char(1) DEFAULT NULL,
  `dyed_options` char(1) DEFAULT NULL,
  `weight_n` decimal(5,2) DEFAULT NULL,
  `weight_unit_id` int DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_VENDOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_VENDOR`;

CREATE TABLE `T_PRODUCT_VENDOR` (
  `product_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_WARRANTY
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_WARRANTY`;

CREATE TABLE `T_PRODUCT_WARRANTY` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `warranty_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_WEAVE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_WEAVE`;

CREATE TABLE `T_PRODUCT_WEAVE` (
  `product_id` int NOT NULL,
  `weave_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`weave_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_X_DIGITAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_X_DIGITAL`;

CREATE TABLE `T_PRODUCT_X_DIGITAL` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `reverse_ground` char(1) NOT NULL DEFAULT 'N',
  `style_id` int NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0',
  `archived` char(1) NOT NULL DEFAULT 'N',
  `log_vers_id` int NOT NULL DEFAULT '1',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_digital_style_id` (`style_id`),
  KEY `idx_digital_item_id` (`item_id`),
  KEY `idx_digital_archived` (`archived`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table T_PRODUCT_X_SCREENPRINT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `T_PRODUCT_X_SCREENPRINT`;

CREATE TABLE `T_PRODUCT_X_SCREENPRINT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `style_id` int NOT NULL,
  `reverse_ground` char(1) NOT NULL DEFAULT 'N',
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `archived` char(1) NOT NULL DEFAULT 'N',
  `log_vers_id` int NOT NULL DEFAULT '1',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table U_DIGITAL_STYLE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `U_DIGITAL_STYLE`;

CREATE TABLE `U_DIGITAL_STYLE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `vrepeat` decimal(5,2) DEFAULT NULL,
  `hrepeat` decimal(5,2) DEFAULT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `archived` char(1) NOT NULL DEFAULT 'N',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table U_DIGITAL_STYLE_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `U_DIGITAL_STYLE_FILES`;

CREATE TABLE `U_DIGITAL_STYLE_FILES` (
  `style_id` int NOT NULL,
  `category_id` int NOT NULL,
  `descr` varchar(50) DEFAULT NULL,
  `url_dir` varchar(100) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  KEY `product_id` (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table U_DIGITAL_STYLE_MESSAGES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `U_DIGITAL_STYLE_MESSAGES`;

CREATE TABLE `U_DIGITAL_STYLE_MESSAGES` (
  `id` int NOT NULL AUTO_INCREMENT,
  `style_id` int NOT NULL,
  `message` text NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `style_id` (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table U_SCREENPRINT_STYLE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `U_SCREENPRINT_STYLE`;

CREATE TABLE `U_SCREENPRINT_STYLE` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `vrepeat` decimal(5,2) NOT NULL,
  `hrepeat` decimal(5,2) NOT NULL,
  `width` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `active` char(1) NOT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `archived` char(1) NOT NULL DEFAULT 'N',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;











































# Dump of table Z_CONTACT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_CONTACT`;

CREATE TABLE `Z_CONTACT` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `company` varchar(128) DEFAULT NULL,
  `position` varchar(64) DEFAULT NULL,
  `address_1` varchar(64) NOT NULL,
  `address_2` varchar(64) DEFAULT NULL,
  `city` varchar(28) NOT NULL,
  `state` varchar(14) NOT NULL,
  `zipcode` varchar(14) NOT NULL,
  `country` varchar(64) NOT NULL,
  `tel_1` varchar(35) DEFAULT NULL,
  `tel_2` varchar(35) DEFAULT NULL,
  `email_1` varchar(35) DEFAULT NULL,
  `email_2` varchar(35) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '1',
  `archived` varchar(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_SHOWROOM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_SHOWROOM`;

CREATE TABLE `Z_SHOWROOM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `abrev` varchar(15) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `archived` varchar(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_SHOWROOM_CONTACT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_SHOWROOM_CONTACT`;

CREATE TABLE `Z_SHOWROOM_CONTACT` (
  `showroom_id` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_SHOWROOM_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_SHOWROOM_FILES`;

CREATE TABLE `Z_SHOWROOM_FILES` (
  `showroom_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `file_category_id` int NOT NULL DEFAULT '0',
  `descr` varchar(100) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  KEY `Z_SHOWROOM_FILES_showroom_id_index` (`showroom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_VENDOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_VENDOR`;

CREATE TABLE `Z_VENDOR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abrev` varchar(15) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '1',
  `active` char(1) NOT NULL DEFAULT 'Y',
  `archived` varchar(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `Z_VENDOR_id_name_index` (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_VENDOR_CONTACT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_VENDOR_CONTACT`;

CREATE TABLE `Z_VENDOR_CONTACT` (
  `vendor_id` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Z_VENDOR_FILES
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Z_VENDOR_FILES`;

CREATE TABLE `Z_VENDOR_FILES` (
  `vendor_id` int NOT NULL,
  `url_dir` varchar(200) NOT NULL,
  `file_category_id` int NOT NULL,
  `descr` varchar(100) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table cached_product_spec_view
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cached_product_spec_view`;

CREATE TABLE `cached_product_spec_view` (
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vrepeat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hrepeat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `product_id` int NOT NULL,
  `outdoor` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `archived` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_master` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abrasions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `count_abrasion_files` int DEFAULT NULL,
  `content_front` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `firecodes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `count_firecode_files` int DEFAULT NULL,
  `uses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uses_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vendor_product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tariff_surcharge` decimal(10,2) DEFAULT NULL,
  `freight_surcharge` decimal(10,2) DEFAULT NULL,
  `p_hosp_cut` decimal(10,2) DEFAULT NULL,
  `p_hosp_roll` decimal(10,2) DEFAULT NULL,
  `p_res_cut` decimal(10,2) DEFAULT NULL,
  `p_dig_res` decimal(10,2) DEFAULT NULL,
  `p_dig_hosp` decimal(10,2) DEFAULT NULL,
  `price_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fob` decimal(10,2) DEFAULT NULL,
  `cost_cut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_half_roll` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll_landed` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll_ex_mill` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendors_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendors_abrev` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_business_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weaves` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `weaves_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `colors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_colors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_uses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_firecodes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_content_front` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_vendors_abrev` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`product_id`,`product_type`),
  KEY `idx_vendors_abrev` (`vendors_abrev`),
  KEY `idx_product_name` (`product_name`),
  FULLTEXT KEY `ft_search` (`product_name`,`vendor_product_name`,`searchable_vendors_abrev`,`searchable_colors`,`searchable_uses`,`searchable_firecodes`,`searchable_content_front`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table cached_product_spec_view_off
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cached_product_spec_view_off`;

CREATE TABLE `cached_product_spec_view_off` (
  `product_name` text,
  `vrepeat` varchar(50) DEFAULT NULL,
  `hrepeat` varchar(50) DEFAULT NULL,
  `width` varchar(50) DEFAULT NULL,
  `product_id` int NOT NULL,
  `outdoor` varchar(5) DEFAULT NULL,
  `product_type` char(1) NOT NULL,
  `archived` char(1) DEFAULT NULL,
  `in_master` char(1) DEFAULT NULL,
  `abrasions` text,
  `count_abrasion_files` int DEFAULT NULL,
  `content_front` text,
  `firecodes` text,
  `count_firecode_files` int DEFAULT NULL,
  `uses` text,
  `uses_id` text,
  `vendor_product_name` varchar(255) DEFAULT NULL,
  `tariff_surcharge` decimal(10,2) DEFAULT NULL,
  `freight_surcharge` decimal(10,2) DEFAULT NULL,
  `p_hosp_cut` decimal(10,2) DEFAULT NULL,
  `p_hosp_roll` decimal(10,2) DEFAULT NULL,
  `p_res_cut` decimal(10,2) DEFAULT NULL,
  `p_dig_res` decimal(10,2) DEFAULT NULL,
  `p_dig_hosp` decimal(10,2) DEFAULT NULL,
  `price_date` varchar(20) DEFAULT NULL,
  `fob` decimal(10,2) DEFAULT NULL,
  `cost_cut` text,
  `cost_half_roll` text,
  `cost_roll` text,
  `cost_roll_landed` text,
  `cost_roll_ex_mill` text,
  `cost_date` varchar(20) DEFAULT NULL,
  `vendors_name` varchar(255) DEFAULT NULL,
  `vendors_abrev` varchar(50) DEFAULT NULL,
  `weaves` text,
  `weaves_id` text,
  `colors` text,
  `color_ids` text,
  `searchable_colors` text,
  `searchable_uses` text,
  `searchable_firecodes` text,
  `searchable_content_front` text,
  PRIMARY KEY (`product_id`,`product_type`),
  FULLTEXT KEY `ft_match` (`product_name`,`vendor_product_name`,`abrasions`,`content_front`,`firecodes`,`uses`,`vendors_name`,`weaves`,`searchable_colors`,`searchable_uses`,`searchable_firecodes`,`searchable_content_front`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



# Dump of table cached_product_spec_view_off_july
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cached_product_spec_view_off_july`;

CREATE TABLE `cached_product_spec_view_off_july` (
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vrepeat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hrepeat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `product_id` int NOT NULL,
  `outdoor` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `archived` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_master` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abrasions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `count_abrasion_files` int DEFAULT NULL,
  `content_front` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `firecodes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `count_firecode_files` int DEFAULT NULL,
  `uses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uses_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vendor_product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tariff_surcharge` decimal(10,2) DEFAULT NULL,
  `freight_surcharge` decimal(10,2) DEFAULT NULL,
  `p_hosp_cut` decimal(10,2) DEFAULT NULL,
  `p_hosp_roll` decimal(10,2) DEFAULT NULL,
  `p_res_cut` decimal(10,2) DEFAULT NULL,
  `p_dig_res` decimal(10,2) DEFAULT NULL,
  `p_dig_hosp` decimal(10,2) DEFAULT NULL,
  `price_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fob` decimal(10,2) DEFAULT NULL,
  `cost_cut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_half_roll` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll_landed` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_roll_ex_mill` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendors_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendors_abrev` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weaves` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `weaves_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `colors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_colors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_uses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_firecodes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_content_front` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `searchable_vendors_abrev` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`product_id`,`product_type`),
  KEY `idx_vendors_abrev` (`vendors_abrev`),
  KEY `idx_product_name` (`product_name`),
  FULLTEXT KEY `ft_search` (`product_name`,`vendor_product_name`,`searchable_vendors_abrev`,`searchable_colors`,`searchable_uses`,`searchable_firecodes`,`searchable_content_front`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of view V_PRODUCT_VENDOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_VENDOR`; DROP VIEW IF EXISTS `V_PRODUCT_VENDOR`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_VENDOR`
AS SELECT
   `TPV`.`product_id` AS `product_id`,
   `TPV`.`vendor_id` AS `vendor_id`,coalesce(`TV`.`abrev`,
   `TV`.`name`) AS `name`
FROM (`T_PRODUCT_VENDOR` `TPV` join `Z_VENDOR` `TV` on((`TPV`.`vendor_id` = `TV`.`id`)));

# Dump of view V_PRODUCT_USE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_USE`; DROP VIEW IF EXISTS `V_PRODUCT_USE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_USE`
AS SELECT
   `TPU`.`product_id` AS `product_id`,
   `TPU`.`use_id` AS `use_id`,
   `PU`.`name` AS `name`
FROM (`T_PRODUCT_USE` `TPU` join `P_USE` `PU` on((`TPU`.`use_id` = `PU`.`id`)));

# Dump of view V_PRODUCT_ABRASION
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_ABRASION`; DROP VIEW IF EXISTS `V_PRODUCT_ABRASION`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_ABRASION`
AS SELECT
   `PA`.`id` AS `id`,
   `PA`.`product_id` AS `product_id`,
   `PA`.`n_rubs` AS `n_rubs`,
   `PA`.`abrasion_test_id` AS `abrasion_test_id`,
   `PA`.`abrasion_limit_id` AS `abrasion_limit_id`,
   `PA`.`visible` AS `visible`,
   `PA`.`data_in_vendor_specsheet` AS `data_in_vendor_specsheet`,
   `PA`.`date_add` AS `date_add`,
   `PA`.`user_id` AS `user_id`,concat(`PA`.`n_rubs`,' ',`Tests`.`name`) AS `descr`
FROM (`T_PRODUCT_ABRASION` `PA` join `P_ABRASION_TEST` `Tests` on((`PA`.`abrasion_test_id` = `Tests`.`id`))) group by `PA`.`id`;

# Dump of view V_ITEM_SHELF
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM_SHELF`; DROP VIEW IF EXISTS `V_ITEM_SHELF`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM_SHELF`
AS SELECT
   `I`.`id` AS `id`,
   `S`.`shelf_id` AS `shelf_id`,
   `PS`.`name` AS `name`
FROM ((`T_ITEM` `I` join `T_ITEM_SHELF` `S` on((`I`.`id` = `S`.`item_id`))) join `P_SHELF` `PS` on((`S`.`shelf_id` = `PS`.`id`)));

# Dump of view R_ITEMS_LAST_STATUS_CHANGE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `R_ITEMS_LAST_STATUS_CHANGE`; DROP VIEW IF EXISTS `R_ITEMS_LAST_STATUS_CHANGE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `R_ITEMS_LAST_STATUS_CHANGE`
AS SELECT
   `I`.`product_id` AS `product_id`,
   `I`.`item_id` AS `item_id`,
   `I`.`product_name` AS `product_name`,coalesce(`I`.`code`,'') AS `code`,
   `I`.`color` AS `color`,
   `I`.`status` AS `status`,
   `I`.`status_id` AS `status_id`,
   `PS`.`name` AS `old_status`,
   `HS`.`status_id` AS `old_status_id`,
   `HS`.`ts` AS `change_date`
FROM ((`V_ITEM` `I` left join `S_HISTORY_ITEM_STATUS` `HS` on((`I`.`item_id` = `HS`.`item_id`))) left join `P_PRODUCT_STATUS` `PS` on((`HS`.`status_id` = `PS`.`id`))) where ((`I`.`status_id` <> `HS`.`status_id`) and (`HS`.`ts` >= (select max(`S_HISTORY_ITEM_STATUS`.`ts`) from `S_HISTORY_ITEM_STATUS` where (`S_HISTORY_ITEM_STATUS`.`item_id` = `I`.`item_id`)))) order by `I`.`product_name`,`I`.`color`;

# Dump of view V_PRODUCT_PORTFOLIO_PICTURE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_PORTFOLIO_PICTURE`; DROP VIEW IF EXISTS `V_PRODUCT_PORTFOLIO_PICTURE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_PORTFOLIO_PICTURE`
AS SELECT
   `PicProd`.`item_id` AS `item_id`,
   `Item`.`product_id` AS `product_id`,
   `Item`.`product_type` AS `product_type`,
   `Pic`.`url` AS `url`
FROM ((`PORTFOLIO_PRODUCT` `PicProd` join `PORTFOLIO_PICTURE` `Pic` on((`PicProd`.`picture_id` = `Pic`.`id`))) join `T_ITEM` `Item` on((`PicProd`.`item_id` = `Item`.`id`))) where (`Pic`.`active` = '1');

# Dump of view R_ITEMS_WHERE_TPRODUCT_INMASTER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `R_ITEMS_WHERE_TPRODUCT_INMASTER`; DROP VIEW IF EXISTS `R_ITEMS_WHERE_TPRODUCT_INMASTER`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `R_ITEMS_WHERE_TPRODUCT_INMASTER`
AS SELECT
   `V`.`name` AS `vendor_name`,
   `I`.`status` AS `status`,
   `I`.`stock_status` AS `stock_status`,
   `P`.`name` AS `product_name`,coalesce(`I`.`code`,'') AS `code`,
   `I`.`color` AS `color`
FROM ((`V_ITEM` `I` join `T_PRODUCT` `P` on(((`I`.`product_id` = `P`.`id`) and (`I`.`product_type` = 'R')))) left join `V_PRODUCT_VENDOR` `V` on((`P`.`id` = `V`.`product_id`))) where ((`P`.`in_master` = 1) and (`I`.`product_type` = 'R')) order by `P`.`name`,`I`.`color`;

# Dump of view V_ITEM
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM`; DROP VIEW IF EXISTS `V_ITEM`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM`
AS SELECT
   if((`T_ITEM`.`code` is null),concat_ws(' ',`Z_VENDOR`.`abrev`,
   `T_PRODUCT`.`name`),`T_PRODUCT`.`name`) AS `product_name`,
   `T_PRODUCT`.`id` AS `product_id`,'R' AS `product_type`,
   `T_PRODUCT`.`outdoor` AS `outdoor`,
   `T_ITEM`.`id` AS `item_id`,
   `T_ITEM`.`code` AS `code`,
   `T_ITEM`.`in_ringset` AS `in_ringset`,group_concat(distinct `P_COLOR`.`name` order by `T_ITEM_COLOR`.`n_order` ASC separator '/') AS `color`,
   `P_STOCK_STATUS`.`name` AS `stock_status`,
   `P_STOCK_STATUS`.`id` AS `stock_status_id`,
   `P_PRODUCT_STATUS`.`name` AS `status`,
   `P_PRODUCT_STATUS`.`id` AS `status_id`,
   `T_ITEM`.`archived` AS `archived`,
   `T_PRODUCT`.`archived` AS `archived_product`,
   `T_ITEM`.`date_add` AS `date_add`,
   `T_ITEM`.`date_modif` AS `date_modif`
FROM ((((((((`T_ITEM` join `T_PRODUCT` on((`T_ITEM`.`product_id` = `T_PRODUCT`.`id`))) join `P_STOCK_STATUS` on((`T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`))) join `P_PRODUCT_STATUS` on((`T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`))) join `T_ITEM_COLOR` on((`T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`))) join `P_COLOR` on((`T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`))) join `T_PRODUCT_VENDOR` on((`T_ITEM`.`product_id` = `T_PRODUCT_VENDOR`.`product_id`))) join `Z_VENDOR` on((`T_PRODUCT_VENDOR`.`vendor_id` = `Z_VENDOR`.`id`))) left join `T_PRODUCT_PRICE` on(((`T_PRODUCT`.`id` = `T_PRODUCT_PRICE`.`product_id`) and (`T_PRODUCT_PRICE`.`product_type` = 'R')))) where (`T_ITEM`.`product_type` = 'R') group by `T_ITEM`.`id` union all select concat(`U_DIGITAL_STYLE`.`name`,' on ',convert((case when (`T_PRODUCT_X_DIGITAL`.`reverse_ground` = 'Y') then 'Reverse ' else '' end) using latin1),coalesce(`T_PRODUCT`.`dig_product_name`,`T_PRODUCT`.`name`),' ',group_concat(distinct `PC`.`name` order by `PC`.`name` ASC separator ' / ')) AS `product_name`,`T_PRODUCT_X_DIGITAL`.`id` AS `product_id`,'D' AS `product_type`,`T_PRODUCT`.`outdoor` AS `outdoor`,`T_ITEM`.`id` AS `item_id`,`T_ITEM`.`code` AS `code`,`T_ITEM`.`in_ringset` AS `in_ringset`,group_concat(distinct `P_COLOR`.`name` order by `T_ITEM_COLOR`.`n_order` ASC separator '/') AS `color`,`P_STOCK_STATUS`.`name` AS `stock_status`,`P_STOCK_STATUS`.`id` AS `stock_status_id`,`P_PRODUCT_STATUS`.`name` AS `status`,`P_PRODUCT_STATUS`.`id` AS `status_id`,`T_ITEM`.`archived` AS `archived`,`T_PRODUCT_X_DIGITAL`.`archived` AS `archived_product`,`T_ITEM`.`date_add` AS `date_add`,`T_ITEM`.`date_modif` AS `date_modif` from (((((((((((`T_ITEM` join `T_PRODUCT_X_DIGITAL` on((`T_ITEM`.`product_id` = `T_PRODUCT_X_DIGITAL`.`id`))) join `T_ITEM` `TT` on((`T_PRODUCT_X_DIGITAL`.`item_id` = `TT`.`id`))) left join `T_ITEM_COLOR` `TC` on((`TT`.`id` = `TC`.`item_id`))) left join `P_COLOR` `PC` on((`TC`.`color_id` = `PC`.`id`))) join `T_PRODUCT` on((`TT`.`product_id` = `T_PRODUCT`.`id`))) join `U_DIGITAL_STYLE` on((`T_PRODUCT_X_DIGITAL`.`style_id` = `U_DIGITAL_STYLE`.`id`))) join `P_STOCK_STATUS` on((`T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`))) join `P_PRODUCT_STATUS` on((`T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`))) join `T_ITEM_COLOR` on((`T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`))) join `P_COLOR` on((`T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`))) left join `T_PRODUCT_PRICE` on(((`T_PRODUCT_X_DIGITAL`.`id` = `T_PRODUCT_PRICE`.`product_id`) and (`T_PRODUCT_PRICE`.`product_type` = 'D')))) where (`T_ITEM`.`product_type` = 'D') group by `T_ITEM`.`id`;

# Dump of view V_PRODUCT_FIRECODE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_FIRECODE`; DROP VIEW IF EXISTS `V_PRODUCT_FIRECODE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_FIRECODE`
AS SELECT
   `TPF`.`product_id` AS `product_id`,
   `TPF`.`firecode_test_id` AS `firecode_test_id`,
   `TPF`.`data_in_vendor_specsheet` AS `data_in_vendor_specsheet`,
   `TPF`.`visible` AS `visible`,
   `PFT`.`name` AS `name`,
   `TPFF`.`url_dir` AS `url_dir`
FROM ((`T_PRODUCT_FIRECODE` `TPF` join `P_FIRECODE_TEST` `PFT` on((`PFT`.`id` = `TPF`.`firecode_test_id`))) join `T_PRODUCT_FIRECODE_FILES` `TPFF` on((`TPF`.`id` = `TPFF`.`firecode_id`)));

# Dump of view V_ITEM_REGULAR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM_REGULAR`; DROP VIEW IF EXISTS `V_ITEM_REGULAR`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM_REGULAR`
AS SELECT
   `TP`.`name` AS `product_name`,
   `TI`.`product_id` AS `product_id`,
   `TI`.`product_type` AS `product_type`,
   `TP`.`dig_product_name` AS `dig_product_name`,
   `TI`.`id` AS `item_id`,
   `TI`.`code` AS `code`,
   `VIC`.`color` AS `color`,
   `TI`.`in_ringset` AS `in_ringset`,
   `TP`.`outdoor` AS `outdoor`,
   `TP`.`width` AS `width`,
   `TP`.`dig_width` AS `dig_width`,
   `PSS`.`name` AS `stock_status`,
   `TI`.`stock_status_id` AS `stock_status_id`,
   `PPS`.`name` AS `status`,
   `TI`.`status_id` AS `status_id`,
   `TI`.`archived` AS `archived`,
   `TP`.`archived` AS `archived_product`
FROM ((((`T_ITEM` `TI` join `V_ITEM_COLOR` `VIC` on((`TI`.`id` = `VIC`.`item_id`))) join `T_PRODUCT` `TP` on((`TI`.`product_id` = `TP`.`id`))) join `P_STOCK_STATUS` `PSS` on((`TI`.`stock_status_id` = `PSS`.`id`))) join `P_PRODUCT_STATUS` `PPS` on((`TI`.`status_id` = `PPS`.`id`))) where (`TI`.`product_type` = 'R') group by `TI`.`id`;

# Dump of view R_ITEMS_DISCONTINUED
# ------------------------------------------------------------

DROP TABLE IF EXISTS `R_ITEMS_DISCONTINUED`; DROP VIEW IF EXISTS `R_ITEMS_DISCONTINUED`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `R_ITEMS_DISCONTINUED`
AS SELECT
   `I`.`item_id` AS `item_id`,
   `I`.`product_name` AS `product_name`,coalesce(`I`.`code`,'') AS `code`,
   `I`.`color` AS `color`,
   `I`.`status` AS `status`,
   `I`.`status_id` AS `status_id`,
   `PS`.`name` AS `old_status`,
   `HS`.`status_id` AS `old_status_id`,
   `HS`.`ts` AS `change_date`
FROM ((`V_ITEM` `I` left join `S_HISTORY_ITEM_STATUS` `HS` on((`I`.`item_id` = `HS`.`item_id`))) left join `P_PRODUCT_STATUS` `PS` on((`HS`.`status_id` = `PS`.`id`))) where ((`I`.`status_id` <> `HS`.`status_id`) and (`I`.`status_id` in (3,20,18,2,5)) and (`HS`.`ts` >= (select max(`S_HISTORY_ITEM_STATUS`.`ts`) from `S_HISTORY_ITEM_STATUS` where (`S_HISTORY_ITEM_STATUS`.`item_id` = `I`.`item_id`)))) order by `I`.`product_name`,`I`.`color`;

# Dump of view V_ITEM_DIGITAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM_DIGITAL`; DROP VIEW IF EXISTS `V_ITEM_DIGITAL`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM_DIGITAL`
AS SELECT
   concat(`UDS`.`name`,' on ',convert((case when (`TPXD`.`reverse_ground` = 'Y') then 'Reverse ' else '' end) using latin1),coalesce(`TP2`.`dig_product_name`,
   `TP2`.`name`),' / ',group_concat(`PC2`.`name` order by `TIC2`.`n_order` ASC separator '/')) AS `product_name`,
   `TI`.`product_id` AS `product_id`,
   `TI`.`product_type` AS `product_type`,NULL AS `dig_product_name`,
   `TI`.`id` AS `item_id`,
   `TI`.`code` AS `code`,group_concat(`PC`.`name` order by `TIC`.`n_order` DESC separator '/') AS `color`,
   `TI`.`in_ringset` AS `in_ringset`,
   `TP2`.`outdoor` AS `outdoor`,
   `TP2`.`dig_width` AS `width`,NULL AS `dig_width`,
   `PSS`.`name` AS `stock_status`,
   `TI`.`stock_status_id` AS `stock_status_id`,
   `PPS`.`name` AS `status`,
   `TI`.`status_id` AS `status_id`,
   `TI`.`archived` AS `archived`,
   `TPXD`.`archived` AS `archived_product`
FROM ((((((((((`T_ITEM` `TI` join `T_ITEM_COLOR` `TIC` on((`TI`.`id` = `TIC`.`item_id`))) join `P_COLOR` `PC` on((`TIC`.`color_id` = `PC`.`id`))) join `T_PRODUCT_X_DIGITAL` `TPXD` on((`TI`.`product_id` = `TPXD`.`id`))) join `T_ITEM` `TI2` on((`TPXD`.`item_id` = `TI2`.`id`))) join `T_ITEM_COLOR` `TIC2` on((`TI2`.`id` = `TIC2`.`item_id`))) join `P_COLOR` `PC2` on((`TIC2`.`color_id` = `PC2`.`id`))) join `T_PRODUCT` `TP2` on((`TI2`.`product_id` = `TP2`.`id`))) join `U_DIGITAL_STYLE` `UDS` on((`TPXD`.`style_id` = `UDS`.`id`))) join `P_STOCK_STATUS` `PSS` on((`TI`.`stock_status_id` = `PSS`.`id`))) join `P_PRODUCT_STATUS` `PPS` on((`TI`.`status_id` = `PPS`.`id`))) where (`TI`.`product_type` = 'D') group by `TI`.`id`;

# Dump of view V_ITEM_COLOR
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM_COLOR`; DROP VIEW IF EXISTS `V_ITEM_COLOR`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM_COLOR`
AS SELECT
   `TI`.`product_id` AS `product_id`,
   `TI`.`product_type` AS `product_type`,
   `TI`.`id` AS `item_id`,group_concat(distinct `TC`.`name` order by `TIC`.`n_order` ASC separator '/') AS `color`
FROM ((`T_ITEM` `TI` join `T_ITEM_COLOR` `TIC` on((`TI`.`id` = `TIC`.`item_id`))) join `P_COLOR` `TC` on((`TIC`.`color_id` = `TC`.`id`))) group by `TI`.`id`;

# Dump of view V_PRODUCT_FINISH
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_FINISH`; DROP VIEW IF EXISTS `V_PRODUCT_FINISH`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_FINISH`
AS SELECT
   `PF`.`product_id` AS `product_id`,
   `PF`.`finish_id` AS `finish_id`,
   `P`.`name` AS `name`
FROM (`T_PRODUCT_FINISH` `PF` join `P_FINISH` `P` on((`PF`.`finish_id` = `P`.`id`)));

# Dump of view PRODUCTS_VISIBLE_NOT_SHOWING_ON_WEBSITE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PRODUCTS_VISIBLE_NOT_SHOWING_ON_WEBSITE`; DROP VIEW IF EXISTS `PRODUCTS_VISIBLE_NOT_SHOWING_ON_WEBSITE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `PRODUCTS_VISIBLE_NOT_SHOWING_ON_WEBSITE`
AS SELECT
   `P`.`id` AS `id`,
   `P`.`name` AS `name`
FROM ((((`T_PRODUCT` `P` left join `T_ITEM` `I` on(((`P`.`id` = `I`.`product_id`) and (`I`.`product_type` = 'R')))) left join `SHOWCASE_PRODUCT` `SP` on((`P`.`id` = `SP`.`product_id`))) left join `SHOWCASE_ITEM` `SI` on((`I`.`id` = `SI`.`item_id`))) left join `opuzen_prod_sales`.`op_products_stock` `Stock` on((`I`.`id` = `Stock`.`master_item_id`))) where ((`SP`.`visible` = 'Y') and (`P`.`archived` = 'N') and (`I`.`archived` = 'N')) group by `P`.`id` having (count(if(((`SI`.`visible` = 'Y') and ((`I`.`status_id` not in (2,3,18)) or (`Stock`.`yardsAvailable` >= 10))),1,NULL)) = 0);

# Dump of view V_PRODUCT_ORIGIN
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_ORIGIN`; DROP VIEW IF EXISTS `V_PRODUCT_ORIGIN`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_ORIGIN`
AS SELECT
   `TPO`.`product_id` AS `product_id`,
   `TPO`.`origin_id` AS `origin_id`,
   `PO`.`name` AS `name`
FROM (`T_PRODUCT_ORIGIN` `TPO` join `P_ORIGIN` `PO` on((`TPO`.`origin_id` = `PO`.`id`)));

# Dump of view V_PRODUCT_CONTENT_FRONT
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_CONTENT_FRONT`; DROP VIEW IF EXISTS `V_PRODUCT_CONTENT_FRONT`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_CONTENT_FRONT`
AS SELECT
   `PCF`.`product_id` AS `product_id`,group_concat(distinct convert(replace(`PCF`.`perc`,'.00','') using latin1),'% ',`PC`.`name` order by `PCF`.`perc` DESC separator ', ') AS `content_front`
FROM (`T_PRODUCT_CONTENT_FRONT` `PCF` left join `P_CONTENT` `PC` on((`PCF`.`content_id` = `PC`.`id`))) group by `PCF`.`product_id`;

# Dump of view V_PORTFOLIO_PICTURE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PORTFOLIO_PICTURE`; DROP VIEW IF EXISTS `V_PORTFOLIO_PICTURE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PORTFOLIO_PICTURE`
AS SELECT
   `Pic`.`id` AS `id`,
   `Pic`.`project_id` AS `project_id`,
   `Pic`.`url` AS `url`,
   `Pic`.`notes` AS `notes`,
   `Pic`.`date_add` AS `date_add`,
   `Pic`.`user_id` AS `user_id`,
   `Pic`.`active` AS `active`,
   `Pic`.`date_modif` AS `date_modif`,
   `Pic`.`user_id_modif` AS `user_id_modif`,group_concat(distinct `I`.`product_name`,',',`I`.`code`,',',`I`.`color` separator '//') AS `products`
FROM ((`PORTFOLIO_PICTURE` `Pic` left join `PORTFOLIO_PRODUCT` `PicProd` on((`Pic`.`id` = `PicProd`.`picture_id`))) left join `V_ITEM` `I` on((`PicProd`.`item_id` = `I`.`item_id`))) group by `Pic`.`id`;

# Dump of view V_PRODUCT_COST
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_COST`; DROP VIEW IF EXISTS `V_PRODUCT_COST`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_COST`
AS SELECT
   `PC`.`product_id` AS `product_id`,
   `PC`.`fob` AS `fob`,if((`PC`.`cost_cut` is null),'-',group_concat(distinct if((`PC`.`cost_cut_type_id` = `PT`.`id`),`PT`.`name`,NULL),' ',`PC`.`cost_cut` separator ',')) AS `cost_cut`,if((`PC`.`cost_half_roll` is null),'-',group_concat(distinct if((`PC`.`cost_half_roll_type_id` = `PT`.`id`),`PT`.`name`,NULL),' ',`PC`.`cost_half_roll` separator ',')) AS `cost_half_roll`,if((`PC`.`cost_roll` is null),'-',group_concat(distinct if((`PC`.`cost_roll_type_id` = `PT`.`id`),`PT`.`name`,NULL),' ',`PC`.`cost_roll` separator ',')) AS `cost_roll`,if((`PC`.`cost_roll_landed` is null),'-',group_concat(distinct if((`PC`.`cost_roll_landed_type_id` = `PT`.`id`),`PT`.`name`,NULL),' ',`PC`.`cost_roll_landed` separator ',')) AS `cost_roll_landed`,if((`PC`.`cost_roll_ex_mill` is null),'-',group_concat(distinct if((`PC`.`cost_roll_ex_mill_type_id` = `PT`.`id`),`PT`.`name`,NULL),' ',`PC`.`cost_roll_ex_mill` separator ',')) AS `cost_roll_ex_mill`,
   `PC`.`date` AS `cost_date`
FROM (`T_PRODUCT_PRICE_COST` `PC` join `P_PRICE_TYPE` `PT`) group by `PC`.`product_id`;

# Dump of view V_PRODUCT_WEAVE
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_WEAVE`; DROP VIEW IF EXISTS `V_PRODUCT_WEAVE`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_WEAVE`
AS SELECT
   `TPW`.`product_id` AS `product_id`,
   `TPW`.`weave_id` AS `weave_id`,
   `W`.`name` AS `name`
FROM (`T_PRODUCT_WEAVE` `TPW` join `P_WEAVE` `W` on((`TPW`.`weave_id` = `W`.`id`)));

# Dump of view V_ITEM_STOCK
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_ITEM_STOCK`; DROP VIEW IF EXISTS `V_ITEM_STOCK`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_ITEM_STOCK`
AS SELECT
   `I`.`product_id` AS `product_id`,
   `I`.`id` AS `item_id`,
   `opuzen_prod_sales`.`Stock`.`yardsInStock` AS `yardsInStock`,
   `opuzen_prod_sales`.`Stock`.`yardsOnHold` AS `yardsOnHold`,
   `opuzen_prod_sales`.`Stock`.`yardsAvailable` AS `yardsAvailable`
FROM (`T_ITEM` `I` join `opuzen_prod_sales`.`v_products_stock` `Stock` on((`I`.`id` = `opuzen_prod_sales`.`Stock`.`master_item_id`)));

# Dump of view V_USER
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_USER`; DROP VIEW IF EXISTS `V_USER`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_USER`
AS SELECT
   `opuzen_prod_users`.`auth_users`.`id` AS `id`,
   `opuzen_prod_users`.`auth_users`.`username` AS `username`,
   `opuzen_prod_users`.`auth_users`.`email` AS `email`
FROM `opuzen_prod_users`.`auth_users`;

# Dump of view V_PRODUCT_CLEANING
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_CLEANING`; DROP VIEW IF EXISTS `V_PRODUCT_CLEANING`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_CLEANING`
AS SELECT
   `PC`.`product_id` AS `product_id`,
   `PC`.`cleaning_id` AS `cleaning_id`,
   `PC`.`date_add` AS `date_add`,
   `PC`.`user_id` AS `user_id`,
   `C`.`name` AS `descr`
FROM (`T_PRODUCT_CLEANING` `PC` join `P_CLEANING` `C` on((`PC`.`cleaning_id` = `C`.`id`))) where (`C`.`id` <> 20) union all select `PC`.`product_id` AS `product_id`,20 AS `cleaning_id`,`PC`.`date_add` AS `date_add`,`PC`.`user_id` AS `user_id`,`PC`.`special_instruction` AS `descr` from `T_PRODUCT_CLEANING_SPECIAL` `PC`;

# Dump of view V_PRODUCT_DIGITAL
# ------------------------------------------------------------

DROP TABLE IF EXISTS `V_PRODUCT_DIGITAL`; DROP VIEW IF EXISTS `V_PRODUCT_DIGITAL`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `V_PRODUCT_DIGITAL`
AS SELECT
   `TPXD`.`id` AS `id`,'D' AS `type`,concat(`UDS`.`name`,' on ',convert((case when (`TPXD`.`reverse_ground` = 'Y') then 'Reverse ' else '' end) using latin1),coalesce(`VIR`.`dig_product_name`,
   `VIR`.`product_name`),' / ',`VIR`.`color`) AS `name`,
   `VIR`.`dig_width` AS `width`,
   `UDS`.`vrepeat` AS `vrepeat`,
   `UDS`.`hrepeat` AS `hrepeat`,
   `VIR`.`outdoor` AS `outdoor`,
   `TPXD`.`archived` AS `archived`
FROM ((`T_PRODUCT_X_DIGITAL` `TPXD` join `V_ITEM_REGULAR` `VIR` on((`TPXD`.`item_id` = `VIR`.`item_id`))) join `U_DIGITAL_STYLE` `UDS` on((`TPXD`.`style_id` = `UDS`.`id`)));


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
