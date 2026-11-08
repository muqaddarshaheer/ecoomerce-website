-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: paperlane_shop
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `paperlane_shop`
--

/*!40000 DROP DATABASE IF EXISTS `paperlane_shop`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `paperlane_shop` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `paperlane_shop`;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `design` varchar(20) NOT NULL DEFAULT 'classic',
  `color_name` varchar(20) NOT NULL DEFAULT 'Ivory',
  `custom_name` varchar(60) NOT NULL DEFAULT '',
  `gift_message` varchar(180) NOT NULL DEFAULT '',
  `custom_note` varchar(180) NOT NULL DEFAULT '',
  `line_token` char(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_line` (`cart_id`,`product_id`,`line_token`),
  KEY `idx_cart_items_product` (`product_id`),
  KEY `idx_cart_items_cart` (`cart_id`),
  CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (1,1,12,1,'classic','Ivory','','','','3a1a6565226245fa'),(2,2,12,1,'classic','Ivory','','','','3a1a6565226245fa'),(3,2,1,9,'classic','Ivory','','','','3a1a6565226245fa'),(6,1,7,1,'classic','Ivory','','','','3a1a6565226245fa'),(13,6,107,2,'classic','Ivory','','','','3a1a6565226245fa'),(14,6,267,2,'classic','Ivory','','','','3a1a6565226245fa'),(15,6,240,1,'classic','Ivory','','','','3a1a6565226245fa'),(16,6,240,1,'classic','Lilac','maria','from khazeena , best person in the world','want silver chane','dcfd43f0ec564f88'),(17,6,102,1,'classic','Ivory','','','','3a1a6565226245fa');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_carts_user` (`user_id`),
  CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,4,'2026-10-30 15:56:11'),(2,5,'2026-10-29 19:21:40'),(6,3,'2026-11-03 03:23:34'),(17,7,'2026-11-03 01:53:56');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `code_prefix` char(2) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  UNIQUE KEY `uq_categories_prefix` (`code_prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Greeting Cards & Gifts','greeting-cards-gifts','01','Cards, wraps and small gift boxes for birthdays, weddings and everyday notes.','cards.jpg','active','2026-10-29 02:00:50'),(2,'Dolls, Files & Stationery','dolls-files-stationery','02','Dolls, files, notebooks and pens for school, work and gifting.','stationery.jpg','active','2026-10-29 02:00:50'),(3,'Handbags & Wallets','handbags-wallets','03','Shoulder bags and wallets chosen for daily use.','bags.jpg','active','2026-10-29 02:00:50'),(4,'Beauty & Accessories','beauty-accessories','04','Necklaces, earrings, jhumkas, bracelets, watches, rings, perfume, makeup, shawls, ties, wallets, handbags and gift baskets.','beauty.jpg','active','2026-10-29 02:00:50'),(5,'Resin Art','resin-art','05','Earrings, necklaces, bracelets and keepsakes set in resin.','cat-resin.jpg','active','2026-11-03 00:53:10'),(6,'Trending Bracelets','trending-bracelets','06','Alphabet, name, pearl, beaded and charm bracelets.','cat-bracelets.jpg','active','2026-11-03 00:53:10'),(7,'Gift Bouquets','gift-bouquets','07','Flower, chocolate, candy, makeup, toy and stationery bouquets.','cat-bouquets.jpg','active','2026-11-03 00:53:10'),(8,'Gift Baskets','gift-baskets','08','Snack, baby, birthday and chocolate baskets.','cat-baskets.jpg','active','2026-11-03 00:53:10'),(9,'Gift Jars','gift-jars','09','Chocolate, candy, jewellery and personalised jars.','cat-jars.jpg','active','2026-11-03 00:53:10');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(80) NOT NULL,
  `state` varchar(80) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_addresses_customer` (`customer_id`),
  CONSTRAINT `fk_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
INSERT INTO `customer_addresses` VALUES (1,1,'Neha Shah','03009876543','18 Lakeview Road','Karachi','Sindh','75500',1),(2,4,'Sana Test','03001112233','12 Test Lane','Karachi','Sindh','75500',1);
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_user` (`user_id`),
  CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,3,'2026-10-29 02:00:50'),(2,4,'2026-10-29 02:03:47'),(3,5,'2026-10-29 18:46:00'),(4,7,'2026-11-03 01:53:11');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dispatch_records`
--

DROP TABLE IF EXISTS `dispatch_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispatch_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `actor_user_id` int(10) unsigned NOT NULL,
  `action` enum('dispatched','delivered','delivery_failed') NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `action_date` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dispatch_order` (`order_id`),
  KEY `idx_dispatch_actor` (`actor_user_id`),
  KEY `fk_dispatch_employee` (`employee_id`),
  CONSTRAINT `fk_dispatch_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_dispatch_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `fk_dispatch_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dispatch_records`
--

LOCK TABLES `dispatch_records` WRITE;
/*!40000 ALTER TABLE `dispatch_records` DISABLE KEYS */;
INSERT INTO `dispatch_records` VALUES (1,2,1,2,'dispatched','Parcel handed to delivery.','2026-10-30 16:14:01','2026-10-30 16:14:01');
/*!40000 ALTER TABLE `dispatch_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `designation` varchar(80) NOT NULL DEFAULT 'Dispatch executive',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employees_user` (`user_id`),
  UNIQUE KEY `uq_employees_code` (`employee_code`),
  CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,2,'EMP0001','Dispatch executive','2026-10-29 02:00:50'),(2,6,'EMP0002','Packing assistant','2026-10-29 19:00:38');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'Do I need an account to look at products?','No. Anyone can browse the shop, search and open a product page. An account is required only when you place an order, open your profile or view your own orders.',1,'active'),(2,'How is the order number formed?','Every order number has 16 digits. The first digit is the payment type (1 credit card, 2 cheque, 3 cash on delivery). The next 7 digits are the product ID of the lowest product code in that order. The last 8 digits are the shop sequence for that order.',2,'active'),(3,'Which payment methods can I use?','Credit card, cheque, and VPP (cash on delivery). The card page is a clearly labelled simulation for this project. It does not charge a real bank and it never stores the full card number or CVV. Cheque orders wait until a staff member verifies the cheque and marks it cleared. Cash is collected when a VPP order is delivered.',3,'active'),(4,'When will my order be dispatched?','Credit-card and cheque orders stay in the shop until payment is cleared. VPP orders can be dispatched before the cash is collected. You can follow each step from your order page.',4,'active'),(5,'Can I cancel an order?','Yes, but only before the order is dispatched. Open the order and use Cancel order. If the payment was already cleared, a refund is created for an administrator to issue. Stock is returned to the shelf when the cancellation succeeds.',5,'active'),(6,'How do returns and replacements work?','After delivery you have 7 days to request a return or a replacement. An administrator approves or rejects the request. A refund is created only after a return is approved and only if the payment had been cleared. The refund is marked issued by an administrator; it is not sent to a real bank in this project.',6,'active'),(7,'How much is delivery?','Delivery inside Karachi is Rs 180, in line with a small same-city parcel. It is free when the goods total is Rs 2,500 or more. The total is calculated on the server from the current database prices.',7,'active');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `status` enum('new','reviewed') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_feedback_status` (`status`),
  KEY `fk_feedback_user` (`user_id`),
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` VALUES (1,NULL,'MARIA','muqaddarapptach@gmail.com','The website','i had a best experience thank you',5,'reviewed','2026-11-03 05:00:11'),(2,NULL,'Neelam Tahir','muqaddarapptach@gmail.com','A product','zzzzzzzzzzzz',5,'reviewed','2026-11-03 15:34:30'),(3,NULL,'Neelam Tahir','muqaddarapptach@gmail.com','A product','---------0pppppppppp',5,'reviewed','2026-11-03 15:40:13'),(4,NULL,'Monthly','muqaddarapptach@gmail.com','A product','bbbbbbbbbb',5,'reviewed','2026-11-03 19:39:58');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_counters`
--

DROP TABLE IF EXISTS `order_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_counters` (
  `id` tinyint(3) unsigned NOT NULL,
  `last_seq` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_counters`
--

LOCK TABLES `order_counters` WRITE;
/*!40000 ALTER TABLE `order_counters` DISABLE KEYS */;
INSERT INTO `order_counters` VALUES (1,5);
/*!40000 ALTER TABLE `order_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_code` char(7) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `design` varchar(20) NOT NULL DEFAULT '',
  `color_name` varchar(20) NOT NULL DEFAULT '',
  `custom_name` varchar(60) NOT NULL DEFAULT '',
  `gift_message` varchar(180) NOT NULL DEFAULT '',
  `custom_note` varchar(180) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,40,'0400010','Pearl Bracelet Set',2100.00,3,6300.00,'','','','',''),(2,2,39,'0400009','Moon Necklace',1560.00,7,10920.00,'','','','',''),(3,3,15,'0100006','Black and Gold Gift Boxes',880.00,1,880.00,'','','','',''),(4,4,242,'0500006','Resin Keychains',900.00,1,900.00,'luxury','Gold','Hina','For your new desk',''),(5,5,141,'0200011','Dollar Ballpoint Pen',40.00,1,40.00,'classic','Ivory','','','');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` char(16) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `delivery_type` char(1) NOT NULL,
  `primary_product_code` char(7) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `ship_phone` varchar(20) NOT NULL,
  `ship_address` varchar(255) NOT NULL,
  `ship_city` varchar(80) NOT NULL,
  `ship_state` varchar(80) NOT NULL,
  `ship_postal` varchar(20) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','verified','cleared','failed','refunded') NOT NULL DEFAULT 'pending',
  `dispatch_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_status` (`order_status`),
  KEY `idx_orders_created` (`created_at`),
  KEY `idx_orders_delivery` (`delivery_type`),
  CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'1040001000000001',1,'1','0400010','Neha Shah','03009876543','18 Lakeview Road','Karachi','Sindh','75500',6300.00,0.00,6300.00,'confirmed','cleared',NULL,NULL,NULL,'2026-10-29 20:30:57'),(2,'3040000900000002',1,'3','0400009','Neha Shah','03009876543','18 Lakeview Road','Karachi','Sindh','75500',10920.00,0.00,10920.00,'dispatched','cleared','2026-10-30 16:14:01',NULL,NULL,'2026-10-30 16:12:04'),(3,'3010000600000003',1,'3','0100006','Neha Shah','03001234567','defence','Karachi','Sindh','75500',880.00,99.00,979.00,'cancelled','failed',NULL,NULL,'Trial gift box removed','2026-10-31 20:36:04'),(4,'3050000600000004',1,'3','0500006','Neha Shah','03009876543','18 Lakeview Road','Karachi','Sindh','75500',900.00,99.00,999.00,'cancelled','failed',NULL,NULL,'Test checkout only','2026-11-03 01:00:04'),(5,'3020001100000005',4,'3','0200011','Sana Test','03001112233','12 Test Lane','Karachi','Sindh','75500',40.00,180.00,220.00,'cancelled','failed',NULL,NULL,'Test order, please ignore','2026-11-03 01:54:35');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `method` enum('credit_card','cheque','vpp') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','verified','cleared','failed','refunded') NOT NULL DEFAULT 'pending',
  `card_last4` char(4) DEFAULT NULL,
  `card_brand` varchar(20) DEFAULT NULL,
  `txn_reference` varchar(40) DEFAULT NULL,
  `cheque_number` varchar(30) DEFAULT NULL,
  `cheque_bank` varchar(100) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_order` (`order_id`),
  KEY `idx_payments_status` (`status`),
  KEY `fk_payments_user` (`verified_by`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'credit_card',6300.00,'cleared','4242','Visa','SIMC13B2E9743',NULL,NULL,NULL,NULL,NULL,'Simulated card payment. The full card number and CVV were not stored.','2026-10-29 20:30:57'),(2,2,'vpp',10920.00,'cleared',NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-30 16:53:51','Cash collected on delivery.','2026-10-30 16:12:04'),(3,3,'vpp',979.00,'failed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Gift box, cash on delivery','2026-10-31 20:36:04'),(4,4,'vpp',999.00,'failed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Cash is collected on delivery.','2026-11-03 01:00:04'),(5,5,'vpp',220.00,'failed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Cash is collected on delivery.','2026-11-03 01:54:35');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_images_product` (`product_id`),
  CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'birthday-card.jpg',1),(2,2,'wedding-card.jpg',1),(3,3,'gift-box.jpg',1),(4,4,'doll.jpg',1),(5,5,'files.jpg',1),(6,6,'notebook.jpg',1),(7,7,'pens.jpg',1),(8,8,'handbag.jpg',1),(9,9,'wallet.jpg',1),(10,10,'lipstick.jpg',1),(11,11,'perfume.jpg',1),(12,12,'scarf.jpg',1),(13,13,'gifts-red.jpg',1),(14,14,'gift-candy.jpg',1),(15,15,'gifts-black.jpg',1),(16,16,'gift-candle.jpg',1),(17,17,'gift-frames.jpg',1),(18,18,'gift-kraft.jpg',1),(19,19,'gifts-mixed.jpg',1),(20,20,'pencils.jpg',1),(21,21,'notebook-spiral.jpg',1),(22,22,'pen-pad.jpg',1),(23,23,'writing-set.jpg',1),(24,24,'alphabet-blocks.jpg',1),(25,25,'notes-book.jpg',1),(26,26,'bag-teal.jpg',1),(27,27,'bag-floral.jpg',1),(28,28,'bag-yellow.jpg',1),(29,29,'bag-suede.jpg',1),(30,30,'bag-stripe.jpg',1),(31,31,'bag-satchel.jpg',1),(32,32,'bag-daypack.jpg',1),(33,33,'bag-messenger.jpg',1),(34,34,'mascara.jpg',1),(35,35,'bottles.jpg',1),(36,36,'pearls.jpg',1),(37,37,'earrings.jpg',1),(38,38,'nails.jpg',1),(39,39,'necklace-moon.jpg',1),(40,40,'bracelet.jpg',1),(41,41,'neck-layered.jpg',1),(42,42,'set-necklace.jpg',1),(43,42,'set-necklace-side.jpg',0),(44,43,'ear-hoop.jpg',1),(45,44,'ear-drop.jpg',1),(46,45,'jhumka-bell.jpg',1),(47,46,'brace-women.jpg',1),(48,47,'brace-charm.jpg',1),(49,48,'brace-men.jpg',1),(50,49,'watch-women.jpg',1),(51,50,'watch-men.jpg',1),(52,51,'ring-halo.jpg',1),(53,52,'ring-pink.jpg',1),(54,52,'ring-pink-side.jpg',0),(55,53,'ring-band.jpg',1),(56,54,'nails-nude.jpg',1),(57,55,'makeup-kit.jpg',1),(58,56,'skin-serum.jpg',1),(59,57,'perfume-men.jpg',1),(60,58,'tie-stripe.jpg',1),(61,59,'tie-knit.jpg',1),(62,60,'wallet-men.jpg',1),(63,61,'bag-chain.jpg',1),(64,62,'bag-tan.jpg',1),(65,63,'basket-fruit.jpg',1),(66,64,'basket-ribbon.jpg',1),(67,65,'basket-flowers.jpg',1),(68,66,'basket-blue.jpg',1),(69,67,'nails-pink.jpg',1),(70,68,'nails-blue.jpg',1),(71,69,'nails-ombre.jpg',1),(72,70,'nails-red.jpg',1),(73,71,'neck-bar.jpg',1),(74,72,'neck-choker.jpg',1),(75,73,'neck-clover.jpg',1),(76,74,'neck-butterfly.jpg',1),(77,75,'neck-fine.jpg',1),(78,76,'set-red.jpg',1),(79,77,'set-heart.jpg',1),(80,78,'set-filigree.jpg',1),(81,79,'set-leaf.jpg',1),(82,80,'set-bridal.jpg',1),(83,81,'set-beaded.jpg',1),(84,82,'jhumka-filigree.jpg',1),(85,83,'jhumka-silver.jpg',1),(86,84,'jhumka-pearl.jpg',1),(87,85,'brace-gold.jpg',1),(88,86,'brace-silver.jpg',1),(89,87,'brace-bangles.jpg',1),(90,88,'ring-green.jpg',1),(91,89,'makeup-eyes.jpg',1),(92,90,'makeup-nude.jpg',1),(93,91,'shawl-embroidered.jpg',1),(94,92,'ear-studs.jpg',1),(95,93,'ear-frame.jpg',1),(96,94,'ear-pearldrop.jpg',1),(97,95,'ear-green.jpg',1),(98,96,'ear-twotone.jpg',1),(99,97,'bag-woven.jpg',1),(100,98,'bag-rainbow.jpg',1),(101,99,'bag-straw.jpg',1),(102,100,'basket-rose.jpg',1),(103,101,'basket-chocolate.jpg',1),(104,102,'makeup-red.jpg',1),(105,103,'wallet-card.jpg',1),(106,104,'perfume-coco.jpg',1),(107,105,'perfume-no5.jpg',1),(108,106,'perfume-bleu.jpg',1),(109,107,'perfume-dg.jpg',1),(110,108,'gift-bday-card.jpg',1),(111,109,'gift-bday-hamper-a.jpg',1),(112,110,'gift-bday-hamper-b.jpg',1),(113,111,'gift-baby-hamper.jpg',1),(114,112,'gift-baby-clothes.jpg',1),(115,113,'gift-baby-shoes.jpg',1),(116,114,'gift-baby-accessories.jpg',1),(117,115,'gift-baby-arrangement.jpg',1),(118,116,'gift-baby-envelope.jpg',1),(119,117,'gift-grad-card.jpg',1),(120,118,'gift-grad-bouquet.jpg',1),(121,119,'gift-grad-box.jpg',1),(122,120,'gift-grad-envelope.jpg',1),(123,121,'gift-mom-card.jpg',1),(124,122,'gift-mom-bouquet.jpg',1),(125,123,'gift-mom-mug.jpg',1),(126,124,'gift-mom-hamper.jpg',1),(127,125,'gift-dad-card.jpg',1),(128,126,'gift-dad-mug.jpg',1),(129,127,'gift-dad-hamper.jpg',1),(130,128,'gift-ann-card.jpg',1),(131,129,'gift-ann-bouquet.jpg',1),(132,130,'gift-ann-hamper.jpg',1),(133,131,'gift-pk-badges.jpg',1),(134,132,'gift-pk-women.jpg',1),(135,133,'gift-pk-men.jpg',1),(136,134,'gift-pk-bangles.jpg',1),(137,135,'gift-pk-locket.jpg',1),(138,136,'gift-pk-hamper.jpg',1),(139,137,'gift-pk-box.jpg',1),(140,138,'gift-pk-card.jpg',1),(141,139,'gift-eid-a.jpg',1),(142,140,'gift-eid-b.jpg',1),(143,141,'p0200011.jpg',1),(144,142,'p0200012.jpg',1),(145,143,'p0200013.jpg',1),(146,144,'p0200014.jpg',1),(147,145,'p0200015.jpg',1),(148,146,'p0200016.jpg',1),(149,147,'p0200017.jpg',1),(150,148,'p0200018.jpg',1),(151,149,'p0200019.jpg',1),(152,150,'p0200020.jpg',1),(153,151,'p0200021.jpg',1),(154,152,'p0200022.jpg',1),(155,153,'p0200023.jpg',1),(156,154,'p0200024.jpg',1),(157,155,'p0200025.jpg',1),(158,156,'p0200026.jpg',1),(159,157,'p0200027.jpg',1),(160,158,'p0200028.jpg',1),(161,159,'p0200029.jpg',1),(162,160,'p0200030.jpg',1),(163,161,'p0200031.jpg',1),(164,162,'p0200032.jpg',1),(165,163,'p0200033.jpg',1),(166,164,'p0200034.jpg',1),(167,165,'p0200035.jpg',1),(168,166,'p0200036.jpg',1),(169,167,'p0200037.jpg',1),(170,168,'p0200038.jpg',1),(171,169,'p0200039.jpg',1),(172,170,'p0200040.jpg',1),(173,171,'p0200041.jpg',1),(174,172,'p0200042.jpg',1),(175,173,'p0200043.jpg',1),(176,174,'p0200044.jpg',1),(177,175,'p0200045.jpg',1),(178,176,'p0200046.jpg',1),(179,177,'p0200047.jpg',1),(180,178,'p0200048.jpg',1),(181,179,'p0200049.jpg',1),(182,180,'p0200050.jpg',1),(183,181,'p0200051.jpg',1),(184,182,'p0200052.jpg',1),(185,183,'p0200053.jpg',1),(186,184,'p0200054.jpg',1),(187,185,'p0200055.jpg',1),(188,186,'p0200056.jpg',1),(189,187,'p0200057.jpg',1),(190,188,'p0200058.jpg',1),(191,189,'p0200059.jpg',1),(192,190,'p0200060.jpg',1),(193,191,'p0200061.jpg',1),(194,192,'p0200062.jpg',1),(195,193,'p0200063.jpg',1),(196,194,'p0200064.jpg',1),(197,195,'p0200065.jpg',1),(198,196,'p0200066.jpg',1),(199,197,'p0200067.jpg',1),(200,198,'p0200068.jpg',1),(201,199,'p0200069.jpg',1),(202,200,'p0200070.jpg',1),(203,201,'p0200071.jpg',1),(204,202,'p0200072.jpg',1),(205,203,'p0200073.jpg',1),(206,204,'p0200074.jpg',1),(207,205,'p0200075.jpg',1),(208,206,'p0200076.jpg',1),(209,207,'p0200077.jpg',1),(210,208,'p0200078.jpg',1),(211,209,'p0200079.jpg',1),(212,210,'p0200080.jpg',1),(213,211,'p0200081.jpg',1),(214,212,'p0200082.jpg',1),(215,213,'p0200083.jpg',1),(216,214,'p0200084.jpg',1),(217,215,'p0200085.jpg',1),(218,216,'p0200086.jpg',1),(219,217,'p0200087.jpg',1),(220,218,'p0200088.jpg',1),(221,219,'p0200089.jpg',1),(222,220,'p0200090.jpg',1),(223,221,'p0200091.jpg',1),(224,222,'p0200092.jpg',1),(225,223,'p0200093.jpg',1),(226,224,'p0200094.jpg',1),(227,225,'p0200095.jpg',1),(228,226,'p0200096.jpg',1),(229,227,'p0200097.jpg',1),(230,228,'p0200098.jpg',1),(231,229,'p0200099.jpg',1),(232,230,'p0200100.jpg',1),(233,231,'p0100044.jpg',1),(234,232,'p0100045.jpg',1),(235,233,'p0100046.jpg',1),(236,234,'p0100047.jpg',1),(237,235,'p0100048.jpg',1),(238,236,'p0100049.jpg',1),(239,237,'p0500001.jpg',1),(240,238,'p0500002.jpg',1),(241,239,'p0500003.jpg',1),(242,240,'p0500004.jpg',1),(243,241,'p0500005.jpg',1),(244,242,'p0500006.jpg',1),(245,243,'p0500007.jpg',1),(246,244,'p0600001.jpg',1),(247,245,'p0600002.jpg',1),(248,246,'p0600003.jpg',1),(249,247,'p0600004.jpg',1),(250,248,'p0600005.jpg',1),(251,249,'p0600006.jpg',1),(252,250,'p0600007.jpg',1),(253,251,'p0600008.jpg',1),(254,252,'p0700001.jpg',1),(255,253,'p0700002.jpg',1),(256,254,'p0700003.jpg',1),(257,255,'p0700004.jpg',1),(258,256,'p0700005.jpg',1),(259,257,'p0700006.jpg',1),(260,258,'p0700007.jpg',1),(261,259,'p0800001.jpg',1),(262,260,'p0800002.jpg',1),(263,261,'p0800003.jpg',1),(264,262,'p0800004.jpg',1),(265,263,'p0800005.jpg',1),(266,264,'p0800006.jpg',1),(267,265,'p0900001.jpg',1),(268,266,'p0900002.jpg',1),(269,267,'p0900003.jpg',1),(270,268,'p0900004.jpg',1),(271,269,'p0900005.jpg',1),(272,270,'p0900006.jpg',1),(273,271,'p0100050.jpg',1),(274,272,'p0100051.jpg',1),(275,273,'p0100052.jpg',1),(276,274,'p0100053.jpg',1),(277,275,'p0100054.jpg',1),(278,276,'p0100055.jpg',1),(279,277,'p0800007.jpg',1),(280,278,'p0800008.jpg',1),(281,279,'p0800009.jpg',1),(282,280,'p0800010.jpg',1),(283,281,'p0800011.jpg',1),(284,282,'p0800012.jpg',1),(285,283,'p0100056.jpg',1),(286,284,'p0100057.jpg',1),(287,285,'p0100058.jpg',1),(288,286,'p0100059.jpg',1);
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_code` char(7) NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `warranty_info` varchar(255) NOT NULL DEFAULT 'No warranty',
  `stock` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_code` (`product_code`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_price` (`price`),
  KEY `idx_products_status` (`status`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=287 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'0100001',1,'Gold Ribbon Gift Box','A pink gift box finished with a gold ribbon and a few gold hearts. The box is sold on its own, ready to fill.',280.00,'No warranty',40,0,'active','2026-10-29 02:00:50',NULL),(2,'0100002',1,'Kraft and Berry Gifts','A group of wrapped boxes in kraft, red and white, tied with cotton string and a small berry sprig.',950.00,'No warranty',18,0,'active','2026-10-29 02:00:50',NULL),(3,'0100003',1,'Blue Ribbon Gift Stack','Three stacked gift boxes in white and blue, tied with a satin ribbon. Suitable for a desk gift or a hamper.',1250.00,'No warranty',15,0,'active','2026-10-29 02:00:50',NULL),(4,'0200001',2,'Honey Plush Bear','A soft honey-coloured bear sold with two small gift boxes. It sits on a desk or goes straight into a hamper.',890.00,'Stitching checked for 30 days',16,0,'inactive','2026-10-29 02:00:50',NULL),(5,'0200002',2,'Campus File Box','A pale blue file box with labelled paper folders inside. It holds a normal stack of A4 sheets.',420.00,'No warranty',50,0,'active','2026-10-29 02:00:50',NULL),(6,'0200003',2,'Linen Notebook','192 ruled pages in a stitched linen cover. Plain enough for class notes or a journal.',640.00,'No warranty',28,0,'active','2026-10-29 02:00:50',NULL),(7,'0200004',2,'Ink Pen Duo','Two smooth ballpoint pens packed in a small gift sleeve.',560.00,'3 months',4,0,'active','2026-10-29 02:00:50',NULL),(8,'0300001',3,'Tan Shoulder Bag','A structured shoulder bag with an inner zip pocket and an adjustable strap.',3450.00,'6 months against manufacturing defects',8,0,'active','2026-10-29 02:00:50',NULL),(9,'0300002',3,'Slim Everyday Wallet','Eight card slots, one note pocket and a snap closure. It fits a small bag.',1480.00,'6 months against manufacturing defects',14,0,'active','2026-10-29 02:00:50',NULL),(10,'0400001',4,'Lip and Blush Set','A pink lip colour and a matching blush compact for daily use. Patch-test first if your skin is sensitive.',760.00,'No warranty. Sealed for hygiene.',22,0,'active','2026-10-29 02:00:50',NULL),(11,'0400002',4,'Amber Dropper Bottle','A small amber glass bottle with a dropper top. Use it for a light fragrance or a facial oil.',2150.00,'No warranty',12,0,'inactive','2026-10-29 02:00:50',NULL),(12,'0400003',4,'Women Check Shawl','A lightweight check shawl in brown, cream and rust. Wear it, or fold it around a gift.',990.00,'No warranty',16,0,'active','2026-10-29 02:00:50',NULL),(13,'0100004',1,'Red Bow Gift Set','Several small boxes wrapped in red, kraft and white, each finished with a bow. Sold as one gift set.',640.00,'No warranty',24,1,'active','2026-10-29 18:57:33',NULL),(14,'0100005',1,'Candy Cane Gift Box','A kraft box printed with candy canes and tied with baker’s twine. The box is empty and ready to fill.',520.00,'No warranty',20,0,'active','2026-10-29 18:57:33',NULL),(15,'0100006',1,'Black and Gold Gift Boxes','Matte black boxes with gold satin bows, in a few sizes. Useful for jewellery or a small accessory.',880.00,'No warranty',16,0,'active','2026-10-29 18:57:33',NULL),(16,'0100007',1,'Glass Gift Candle','A cream candle in a clear glass, packed as a gift. Burn it on a stable surface and never leave it unattended.',740.00,'No warranty',18,1,'active','2026-10-29 18:57:33',NULL),(17,'0100008',1,'Gallery Frame Set','A mixed set of small and medium frames for prints, notes and photographs. Frames only; prints are not included.',1650.00,'No warranty',10,0,'active','2026-10-29 18:57:33',NULL),(18,'0100009',1,'Kraft Box with Pink Ribbon','A plain kraft box with a long pink satin ribbon. Simple enough for a birthday or a thank-you.',360.00,'No warranty',30,0,'active','2026-10-29 18:57:33',NULL),(19,'0100010',1,'Mixed Gift Pile','A small pile of wrapped boxes in red, white and kraft. Sold together as one decorative gift bundle.',1100.00,'No warranty',12,0,'active','2026-10-29 18:57:33',NULL),(20,'0200005',2,'Colour Pencil Row','A row of sharpened colour pencils for school drawing and card making. Keep the points covered in a pencil case.',480.00,'No warranty',36,1,'active','2026-10-29 18:57:33',NULL),(21,'0200006',2,'Spiral Desk Notebook','An open spiral notebook with blank pages and a slim black pen beside it. The pen is included.',390.00,'No warranty',32,0,'active','2026-10-29 18:57:33',NULL),(22,'0200007',2,'Fountain Pen Pad','A lined pad with a silver fountain pen. The pen writes with a smooth nib and is refillable.',720.00,'3 months on the pen',14,0,'active','2026-10-29 18:57:33',NULL),(23,'0200008',2,'Desk Writing Set','A blue pen with loose sheets and a small notebook, photographed as a working desk set. Sheets and pen are included.',450.00,'No warranty',22,0,'active','2026-10-29 18:57:33',NULL),(24,'0200009',2,'Wooden Alphabet Blocks','Painted wooden blocks with letters, for a desk or a child’s shelf. Wipe them with a dry cloth.',980.00,'No warranty',11,1,'inactive','2026-10-29 18:57:33',NULL),(25,'0200010',2,'Ruled Notes Book','A ruled notebook open to a page marked Notes, with a fountain pen resting on it.',540.00,'No warranty',26,0,'active','2026-10-29 18:57:33',NULL),(26,'0300003',3,'Teal Top Handle','A teal leather top-handle bag with a gold clasp. The strap is short, so it is carried in the hand or on the forearm.',4200.00,'6 months against manufacturing defects',7,1,'active','2026-10-29 18:57:33',NULL),(27,'0300004',3,'Floral Chain Bag','A white chain bag printed with blue flowers and finished with small studs. It hangs from a long chain strap.',2890.00,'6 months against manufacturing defects',9,1,'active','2026-10-29 18:57:33',NULL),(28,'0300005',3,'Yellow Mini Backpack','A small yellow backpack with playful patches. Light enough for a day out or a college bag.',1980.00,'6 months against manufacturing defects',10,0,'active','2026-10-29 18:57:33',NULL),(29,'0300006',3,'Suede Top Handle','A warm brown suede bag with two short handles and silver hardware. Keep it dry and brush the nap gently.',3650.00,'6 months against manufacturing defects',6,0,'active','2026-10-29 18:57:33',NULL),(30,'0300007',3,'Stripe Chain Bag','A pale pink chain bag with yellow and white stripes across the flap. The chain is detachable.',2450.00,'6 months against manufacturing defects',8,0,'active','2026-10-29 18:57:33',NULL),(31,'0300008',3,'Black Satchel','A compact black satchel with two buckle straps and a long shoulder strap. It holds a phone, a wallet and a small notebook.',3100.00,'6 months against manufacturing defects',9,0,'active','2026-10-29 18:57:33',NULL),(32,'0300009',3,'Grey Daypack','A plain grey backpack with one front pocket. Suitable for files, a bottle and a light jacket.',2200.00,'6 months against manufacturing defects',13,0,'active','2026-10-29 18:57:33',NULL),(33,'0300010',3,'Tan Messenger','A tan leather messenger with dark straps and brass rings. The flap closes over an A4 folder.',3980.00,'6 months against manufacturing defects',5,0,'active','2026-10-29 18:57:33',NULL),(34,'0400004',4,'Blue Mascara','A single blue mascara tube for everyday use. Replace it within three months of opening.',540.00,'No warranty. Sealed for hygiene.',28,0,'active','2026-10-29 18:57:33',NULL),(35,'0400005',4,'Amber Bottle Trio','Three amber glass bottles: a pump, a spray and a dropper. Bottles are empty and ready to fill.',860.00,'No warranty',15,0,'inactive','2026-10-29 18:57:33','2026-10-30 17:35:15'),(36,'0400006',4,'Pearl Necklace Box','A strand of pearls presented in a red gift box. Store it flat so the strand does not kink.',4500.00,'6 months against clasp defects',6,1,'active','2026-10-29 18:57:33',NULL),(37,'0400007',4,'Blue Stone Earrings','A pair of drop earrings with a blue centre stone, shown on a green leaf. The posts are for pierced ears.',1890.00,'6 months against stone loss',12,1,'active','2026-10-29 18:57:33',NULL),(38,'0400008',4,'Gloss Nail Set','A glossy black and tortoiseshell nail look. The set is a pair of polish shades for home use.',620.00,'No warranty. Sealed for hygiene.',20,0,'active','2026-10-29 18:57:33',NULL),(39,'0400009',4,'Moon and Blue Stone Necklace','Two fine chains: a moon pendant and a small blue stone. Light chains, not a heavy set.',1560.00,'6 months against clasp defects',7,0,'active','2026-10-29 18:57:33','2026-10-30 17:36:40'),(40,'0400010',4,'Women Pearl Bracelet','A pearl bracelet worn with a matching necklace. The bracelet closes with a small clasp.',2100.00,'6 months against clasp defects',6,0,'active','2026-10-29 18:57:33',NULL),(41,'0400011',4,'Blue Stone and Moon Necklace','Two fine chains: a small blue stone and a moon pendant. Not a heavy set.',2200.00,'6 months against clasp defects',12,0,'inactive','2026-10-29 21:06:29','2026-10-30 17:36:40'),(42,'0400012',4,'Women Necklace and Earring Set','A beaded necklace with a flower centre, sold with matching drop earrings. A bracelet is shown with the set.',5200.00,'6 months against clasp defects',8,0,'active','2026-10-29 21:06:29',NULL),(43,'0400013',4,'Women Gold Hoop Earrings','A pair of twisted gold hoop earrings. The hoops close with a hinged latch.',1450.00,'6 months against stone loss',16,0,'active','2026-10-29 21:06:29',NULL),(44,'0400014',4,'Women Drop Earrings','A pair of rose-gold drop earrings with a round stone centre. The hooks are for pierced ears.',1890.00,'6 months against stone loss',14,0,'active','2026-10-29 21:06:29',NULL),(45,'0400015',4,'Women Silver Bell Jhumkas','A silver bell jhumka with a round top and small white drops. Hook fitting for pierced ears.',1800.00,'6 months against stone loss',10,0,'active','2026-10-29 21:06:29','2026-10-30 17:35:15'),(46,'0400016',4,'Women Stone Bracelet','A gold bracelet set with small stones in a repeating curve. It closes with a clasp.',2400.00,'6 months against clasp defects',11,0,'active','2026-10-29 21:06:29',NULL),(47,'0400017',4,'Women Charm Bracelet','Slim bangles and charm bracelets worn together. The charms sit on the band.',1650.00,'6 months against clasp defects',13,0,'active','2026-10-29 21:06:29',NULL),(48,'0400018',4,'Men Chain Bracelet','A gold chain-link bracelet for a man. It closes with a clasp and sits close on the wrist.',1850.00,'6 months against clasp defects',12,0,'active','2026-10-29 21:06:29',NULL),(49,'0400019',4,'Women Rose Watch','A slim rose-gold watch with a pale face and a grey strap. Suitable for everyday wear, not for swimming.',4900.00,'1 year against movement defects',7,0,'active','2026-10-29 21:06:29',NULL),(50,'0400020',4,'Men Steel Watch','A steel chronograph watch with a dark face and a metal bracelet. Keep it away from strong magnets.',6400.00,'1 year against movement defects',6,0,'active','2026-10-29 21:06:29',NULL),(51,'0400021',4,'Women Halo Ring','A ring with a round centre stone and a halo of smaller stones.',3200.00,'6 months against stone loss',9,0,'active','2026-10-29 21:06:29',NULL),(52,'0400022',4,'Women Pink Stone Ring','A gold ring with a pink centre stone and a border of small stones.',2800.00,'6 months against stone loss',9,0,'active','2026-10-29 21:06:29',NULL),(53,'0400023',4,'Men Gold Band','A plain gold band for a man. The inside of the band is smooth.',2100.00,'6 months against manufacturing defects',10,0,'active','2026-10-29 21:06:29',NULL),(54,'0400024',4,'Women Nude Nail Set','A pale nude nail colour for home use. Two shades are included. Sealed for hygiene.',890.00,'No warranty. Sealed for hygiene.',18,0,'active','2026-10-29 21:06:29',NULL),(55,'0400025',4,'Women Makeup Kit','Brushes, a lipstick, a powder compact and a mascara wand for everyday makeup. Patch-test before use.',2450.00,'No warranty. Sealed for hygiene.',10,0,'active','2026-10-29 21:06:29',NULL),(56,'0400026',4,'Women Face Serum','An amber dropper bottle for a face serum. Patch-test on the wrist before use on the face.',1680.00,'No warranty. Sealed for hygiene.',14,0,'inactive','2026-10-29 21:06:29','2026-10-30 17:35:15'),(57,'0400027',4,'Men Cologne','A glass cologne bottle with a silver cap, for external use. Keep it away from heat.',3100.00,'No warranty',11,0,'active','2026-10-29 21:06:29',NULL),(58,'0400028',4,'Men Stripe Tie','A navy tie with pale stripes. It is a standard length and ties in a simple knot.',1100.00,'No warranty',15,0,'active','2026-10-29 21:06:29',NULL),(59,'0400029',4,'Men Knit Tie','A dark knit tie with a small check. It is narrower than a silk tie.',980.00,'No warranty',15,0,'active','2026-10-29 21:06:29',NULL),(60,'0400030',4,'Men Bifold Wallet','A brown leather bifold wallet with card slots. It folds once.',1950.00,'6 months against stitching defects',12,0,'active','2026-10-29 21:06:29',NULL),(61,'0400031',4,'Women Chain Handbag','A pale pink handbag with a chain strap and a coloured stripe across the flap.',4200.00,'6 months against manufacturing defects',7,0,'active','2026-10-29 21:06:29',NULL),(62,'0400032',4,'Women Tan Handbag','A tan leather handbag with dark straps and a long shoulder strap.',3800.00,'6 months against manufacturing defects',8,0,'active','2026-10-29 21:06:29',NULL),(63,'0400033',4,'Fruit Gift Basket','A wicker basket filled with fruit, packed as a gift.',2500.00,'No warranty. Perishable once opened.',8,0,'active','2026-10-29 21:06:29',NULL),(64,'0400034',4,'Ribbon Gift Boxes','A set of small gift boxes tied with ribbon, for giving.',1800.00,'No warranty',10,0,'active','2026-10-29 21:06:29',NULL),(65,'0400035',4,'Flower Gift Bouquet','A bouquet of pale pink flowers, wrapped to give.',2200.00,'No warranty. Flowers are perishable.',8,0,'active','2026-10-29 21:06:29',NULL),(66,'0400036',4,'Blue Gift Stack','A stack of blue and white gift boxes with ribbon, packed beside flowers.',1600.00,'No warranty',9,0,'active','2026-10-29 21:06:29',NULL),(67,'0400037',4,'Women Soft Pink Nails','A real hand with a soft pink manicure and a pale tip. Sealed for hygiene.',950.00,'No warranty. Sealed for hygiene.',16,0,'active','2026-10-30 15:37:46',NULL),(68,'0400038',4,'Women Blue Glitter Nails','A real hand with royal-blue almond nails and a silver glitter tip.',1100.00,'No warranty. Sealed for hygiene.',14,0,'active','2026-10-30 15:37:46',NULL),(69,'0400039',4,'Women Blue Tip Nails','A real hand with a white nail and a blue glitter tip.',1200.00,'No warranty. Sealed for hygiene.',14,0,'active','2026-10-30 15:37:46',NULL),(70,'0400040',4,'Women Red Almond Nails','Real hands with glossy red almond nails.',980.00,'No warranty. Sealed for hygiene.',16,0,'active','2026-10-30 15:37:46',NULL),(71,'0400041',4,'Women Stone Bar Necklace','A gold chain with a rectangular pale stone pendant.',3400.00,'6 months against clasp defects',8,0,'active','2026-10-30 15:37:46',NULL),(72,'0400042',4,'Women Choker Necklace','Layered gold necklaces, a short choker and a small pendant, worn with rings.',2900.00,'6 months against clasp defects',9,0,'active','2026-10-30 15:37:46',NULL),(73,'0400043',4,'Small Clover Necklace','A short gold chain with one small clover pendant. Light enough for every day.',1800.00,'6 months against clasp defects',4,0,'active','2026-10-30 15:37:46','2026-10-30 17:35:15'),(74,'0400044',4,'Butterfly Pendant','A fine chain with a small butterfly pendant. One pendant, no heavy stones.',1450.00,'6 months against clasp defects',5,0,'active','2026-10-30 15:37:46','2026-10-30 17:35:15'),(75,'0400045',4,'Fine Gold Chain','A thin gold chain on its own, with no pendant stacked on it.',990.00,'6 months against clasp defects',7,0,'active','2026-10-30 15:37:46','2026-10-30 17:35:15'),(76,'0400046',4,'Women Red Stone Set','A gold necklace with red stones and matching gold earrings.',6400.00,'6 months against clasp defects',6,0,'active','2026-10-30 15:37:46',NULL),(77,'0400047',4,'Women Heart Necklace Set','A gold heart necklace with matching heart drop earrings.',2800.00,'6 months against clasp defects',8,0,'active','2026-10-30 15:37:46',NULL),(78,'0400048',4,'Women Filigree Necklace Set','A gold filigree necklace with matching jhumka earrings, shown in a display case.',7200.00,'6 months against clasp defects',5,0,'active','2026-10-30 15:37:46',NULL),(79,'0400049',4,'Women Leaf Necklace Set','A fine gold necklace with a flower pendant and leaf earrings.',3100.00,'6 months against clasp defects',7,0,'active','2026-10-30 15:37:46',NULL),(80,'0400050',4,'Women Bridal Pearl Set','A pearl and gold necklace worn with a jhumka, rings and bangles.',12500.00,'6 months against clasp defects',3,0,'active','2026-10-30 15:37:46',NULL),(81,'0400051',4,'Women Beaded Necklace Set','A long pearl and ruby necklace with matching jhumkas, rings and a slim bracelet.',9800.00,'6 months against clasp defects',4,0,'active','2026-10-30 15:37:46',NULL),(82,'0400052',4,'Women Gold Filigree Jhumkas','A pair of gold jhumkas with red enamel and small bell drops. The hooks are for pierced ears.',2400.00,'6 months against stone loss',10,0,'inactive','2026-10-30 15:37:46','2026-10-30 17:35:15'),(83,'0400053',4,'Women Silver Jhumkas','Silver bell jhumkas with a fine chain beside them. The hooks are for pierced ears.',2100.00,'6 months against stone loss',9,0,'active','2026-10-30 15:37:46',NULL),(84,'0400054',4,'Women Pearl Jhumkas','Gold jhumkas edged with pearls, shown with a matching pendant.',3200.00,'6 months against stone loss',8,0,'inactive','2026-10-30 15:37:46','2026-10-30 17:35:15'),(85,'0400055',4,'Women Gold Link Bracelet','A gold bracelet of small linked stones. It closes with a clasp.',2600.00,'6 months against clasp defects',10,0,'active','2026-10-30 15:37:46',NULL),(86,'0400056',4,'Women Silver Stone Bracelet','A silver bracelet set with round clear stones. It closes with a clasp.',2300.00,'6 months against clasp defects',9,0,'active','2026-10-30 15:37:46',NULL),(87,'0400057',4,'Women Red Bangle Stack','A stack of gold bangles with red stones, for a woman.',2800.00,'6 months against manufacturing defects',8,0,'active','2026-10-30 15:37:46',NULL),(88,'0400058',4,'Women Green Stone Ring','A gold ring with a green centre stone and a border of red, green and black stones.',3600.00,'6 months against stone loss',8,0,'active','2026-10-30 15:37:46',NULL),(89,'0400059',4,'Women Eye Colour Palette','A small palette of warm eye colours. Patch-test before use.',1850.00,'No warranty. Sealed for hygiene.',12,0,'active','2026-10-30 15:37:46',NULL),(90,'0400060',4,'Nude Lipstick','A nude-pink lipstick in a gold and black case. The photo is the lipstick, not a face.',720.00,'No warranty. Sealed for hygiene.',10,0,'active','2026-10-30 15:37:46','2026-10-30 17:35:15'),(91,'0400061',4,'Women Embroidered Shawl','A red and blush bridal dupatta with gold embroidery, worn as a shawl.',4500.00,'No warranty',6,0,'active','2026-10-30 15:37:46',NULL),(92,'0400062',4,'Women Silver Stud Set','Small silver studs in blue, black and green, shown as a set on a white surface.',890.00,'6 months against stone loss',16,0,'active','2026-10-30 17:35:15',NULL),(93,'0400063',4,'Women Silver Frame Earrings','Silver hook earrings with a rectangular frame and a pink centre stone.',1650.00,'6 months against stone loss',12,0,'active','2026-10-30 17:35:15',NULL),(94,'0400064',4,'Women Silver Pearl Drops','Silver drops with green and pink stones and a short fall of pearls.',1750.00,'6 months against stone loss',12,0,'active','2026-10-30 17:35:15',NULL),(95,'0400065',4,'Women Silver Green Drops','Silver leaf drops with a green teardrop stone, packed in a small box.',1550.00,'6 months against stone loss',11,0,'active','2026-10-30 17:35:15',NULL),(96,'0400066',4,'Women Two Tone Hoops','Small oval hoops, gold on the inside and silver on the rope edge.',1400.00,'6 months against clasp defects',14,0,'active','2026-10-30 17:35:15',NULL),(97,'0400067',4,'Women White Woven Flap','A small white bag with a woven flap and a dark metal clasp.',3600.00,'6 months against manufacturing defects',8,0,'active','2026-10-30 17:35:15',NULL),(98,'0400068',4,'Women Rainbow Weave Sling','A crescent sling woven in rainbow stripes, with a red zip.',2900.00,'6 months against manufacturing defects',8,0,'active','2026-10-30 17:35:15',NULL),(99,'0400069',4,'Women Straw Stripe Tote','A straw tote with a striped woven panel and short handles.',3200.00,'6 months against manufacturing defects',7,0,'active','2026-10-30 17:35:15',NULL),(100,'0400070',4,'Rose Flower Basket','White roses, pink carnations and baby\'s breath sitting in a round basket.',2400.00,'Fresh flowers. No return once delivered.',8,0,'active','2026-10-30 17:35:15',NULL),(101,'0400071',4,'Chocolate Snack Box','A box of assorted chocolates with red roses in front. The sweets are the gift.',2800.00,'Food gift. No return once delivered.',8,0,'active','2026-10-30 17:35:15',NULL),(102,'0400072',4,'Red Lipstick and Gloss','A red lipstick beside a pink lip gloss. Both are the product, not a model.',980.00,'Sealed for hygiene. No return once opened.',14,0,'active','2026-10-30 17:35:15',NULL),(103,'0400073',4,'Men Tan Card Holder','A tan leather card holder with pale stitching. Slim enough for a shirt pocket.',1450.00,'6 months against stitching defects',12,0,'active','2026-10-30 17:35:15',NULL),(104,'0400074',4,'Coco Noir','A black bottle labelled Coco Noir Chanel. The name on the card is the name on the bottle.',8900.00,'Sealed. No return once opened.',6,0,'active','2026-10-30 17:35:15',NULL),(105,'0400075',4,'Chanel No. 5','A square glass bottle labelled Chanel No. 5 Eau de Parfum.',9200.00,'Sealed. No return once opened.',6,0,'active','2026-10-30 17:35:15',NULL),(106,'0400076',4,'Men Bleu de Chanel','A dark blue bottle labelled Bleu de Chanel Eau de Parfum.',8500.00,'Sealed. No return once opened.',6,0,'active','2026-10-30 17:35:15',NULL),(107,'0400077',4,'Men D&G The One Pour Homme','A small travel vial labelled Dolce & Gabbana The One Pour Homme.',3200.00,'Sealed. No return once opened.',10,0,'active','2026-10-30 17:35:15',NULL),(108,'0100011',1,'Happy Birthday Card','A cream greeting card with Happy Birthday printed in forest green.',450.00,'No warranty',14,0,'active','2024-06-01 10:00:00',NULL),(109,'0100012',1,'Birthday Scent Hamper','An open gift box with a plain perfume bottle, a silk scarf and a small card.',6800.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(110,'0100013',1,'Birthday Basket Hamper','A woven basket with a wrapped box, dried flowers, a glass candle and ribbon.',5400.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(111,'0100014',1,'Aqiqah Baby Hamper','A cream basket filled with baby clothes, tiny shoes, a rattle, a bonnet and a lamb.',7200.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(112,'0100015',1,'Soft Baby Clothes','A folded ivory baby onesie and knit cardigan.',2400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(113,'0100016',1,'Tiny White Baby Shoes','A pair of small white baby shoes.',1600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(114,'0100017',1,'Baby Rattle and Bonnet','A wooden rattle, a cream knit bonnet and a wooden teether.',1450.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(115,'0100018',1,'Soft Baby Gift Bundle','A cream box with a sage ribbon and a small plush lamb.',3200.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(116,'0100019',1,'Aqiqah Money Envelope','An ivory envelope printed with stars, a crescent and the word Aqiqah.',350.00,'No warranty',20,0,'active','2024-06-01 10:00:00',NULL),(117,'0100020',1,'Graduation Greeting Card','A white card printed with Congratulations! and a small graduation cap.',450.00,'No warranty',14,0,'active','2024-06-01 10:00:00',NULL),(118,'0100021',1,'Graduation Flower Bouquet','White hydrangeas and pale yellow roses wrapped in cream paper.',2400.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(119,'0100022',1,'Small Graduation Gift Box','A small cream box with a champagne ribbon and a tiny cap charm.',1800.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(120,'0100023',1,'Graduation Money Envelope','An ivory envelope with a gold border, a cap mark and the word Congratulations.',350.00,'No warranty',20,0,'active','2024-06-01 10:00:00',NULL),(121,'0100024',1,'Happy Mother\'s Day Card','A cream card printed with Happy Mother\'s Day.',480.00,'No warranty',14,0,'active','2024-06-01 10:00:00',NULL),(122,'0100025',1,'Blush Rose Bouquet','A bunch of blush roses in a small cream vase.',2600.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(123,'0100026',1,'Best Mom Mug','A white mug with Best Mom in gold script.',890.00,'No warranty',16,0,'active','2024-06-01 10:00:00',NULL),(124,'0100027',1,'Mother\'s Day Gift Hamper','An open cream hamper with a blush scarf, a plain perfume bottle and a rose.',6400.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(125,'0100028',1,'Happy Father\'s Day Card','A cream card printed with Happy Father\'s Day.',480.00,'No warranty',14,0,'active','2024-06-01 10:00:00',NULL),(126,'0100029',1,'Best Father Mug','A charcoal mug with Best Father in cream letters.',890.00,'No warranty',16,0,'active','2024-06-01 10:00:00',NULL),(127,'0100030',1,'Father\'s Day Gift Hamper','An open box with a navy tie, a plain cologne bottle and a tan wallet.',7500.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(128,'0100031',1,'Happy Anniversary Card','A cream card with a gold border and the words Happy Anniversary.',520.00,'No warranty',14,0,'active','2024-06-01 10:00:00',NULL),(129,'0100032',1,'Anniversary Rose Bouquet','White and blush roses with eucalyptus, wrapped in cream paper.',2800.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(130,'0100033',1,'Anniversary Gift Hamper','An open cream hamper with a plain perfume bottle, a silk ribbon and a jewelry pouch.',8200.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(131,'0100034',1,'Green and White Star Badges','Round green and white enamel pins, each with a star.',750.00,'No warranty',18,0,'active','2024-06-01 10:00:00',NULL),(132,'0100035',1,'Green and White Women\'s Outfit','A folded green and white cotton outfit.',4800.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(133,'0100036',1,'Green and White Men\'s Shirt','A folded men\'s shirt in deep green and white.',2900.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(134,'0100037',1,'Green and White Bangles','A stack of green and white glass bangles with gold lines.',1800.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(135,'0100038',1,'Gold Star Locket','A round gold locket on a fine chain, engraved with a star.',2400.00,'No warranty',8,0,'active','2024-06-01 10:00:00',NULL),(136,'0100039',1,'Green and White Gift Hamper','An open hamper with green tissue, a green scarf and a small white box.',5600.00,'No warranty',6,0,'active','2024-06-01 10:00:00',NULL),(137,'0100040',1,'Green and White Gift Box','A white gift box tied with a deep green satin ribbon.',1600.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(138,'0100041',1,'Independence Day Greeting Card','A cream card with a green border, a gold star and the words Happy Independence Day.',450.00,'No warranty',16,0,'active','2024-06-01 10:00:00',NULL),(139,'0100042',1,'Eid-ul-Fitr Gold Envelope','An ivory money envelope with a gold geometric pattern and the words Eid-ul-Fitr.',400.00,'No warranty',24,0,'active','2024-06-01 10:00:00',NULL),(140,'0100043',1,'Eid-ul-Fitr Sage Envelope','A sage money envelope with a cream arch pattern, a crescent and the words Eid-ul-Fitr.',450.00,'No warranty',24,0,'active','2024-06-01 10:00:00',NULL),(141,'0200011',2,'Dollar Ballpoint Pen','A blue capped ball pen for everyday writing.',40.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(142,'0200012',2,'Dollar Gel Pen','A black gel pen with a clear barrel.',85.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(143,'0200013',2,'Dollar Permanent Marker','A black chisel-tip marker for paper and card.',70.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(144,'0200014',2,'Dollar Highlighter','A yellow highlighter for notes and textbooks.',60.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(145,'0200015',2,'Dollar HB Pencils','Ten wooden HB pencils, tied as a set.',180.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(146,'0200016',2,'Dollar Blue Ink','A bottle of blue ink for fountain pens.',150.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(147,'0200017',2,'Piano HB Pencil','One hexagonal lead pencil.',25.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(148,'0200018',2,'Piano Pencil Dozen','Twelve lead pencils for school work.',220.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(149,'0200019',2,'Piano Eraser-Top Pencil','A lead pencil with an eraser on the end.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(150,'0200020',2,'Piano Colour Pencils','A set of twelve colour pencils.',280.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(151,'0200021',2,'Piano Ballpoint Pen','A navy capped ball pen.',40.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(152,'0200022',2,'Piano Sharpener','A single-hole metal sharpener.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(153,'0200023',2,'Deer Colour Pencils','Twelve colour pencils in a row.',290.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(154,'0200024',2,'Deer Ballpoint Pen','A retractable green ball pen.',45.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(155,'0200025',2,'Deer Correction Pen','A white pen for covering small marks.',95.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(156,'0200026',2,'Deer Eraser','A white dust-free eraser.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(157,'0200027',2,'Deer Oil Pastels','Twenty-four oil pastels.',480.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(158,'0200028',2,'Deer Whiteboard Marker','A black marker for whiteboards.',75.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(159,'0200029',2,'Dux Lead Pencil','A hexagonal lead pencil.',25.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(160,'0200030',2,'Dux Fountain Pen','A black fountain pen with a steel nib.',220.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(161,'0200031',2,'Dux Stapler','A small desktop stapler.',520.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(162,'0200032',2,'Dux Staples','A box of staples for the stapler.',90.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(163,'0200033',2,'Dux Sharpener','A single-hole plastic sharpener.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(164,'0200034',2,'Dux Colour Pencils','Twelve colour pencils tied as a set.',340.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(165,'0200035',2,'Goldfish HB Pencils','Six yellow HB pencils.',190.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(166,'0200036',2,'Goldfish Colour Pencils','Twelve colour pencils in a tin.',260.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(167,'0200037',2,'Goldfish Geometry Box','A geometry box with compass, divider, protractor and set squares.',420.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(168,'0200038',2,'Goldfish Sharpener','A double-hole metal sharpener.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(169,'0200039',2,'Goldfish Eraser','A round white eraser.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(170,'0200040',2,'Goldfish Glue Stick','A glue stick for paper.',80.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(171,'0200041',2,'Kidco Crayons 12','Twelve wax crayons.',180.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(172,'0200042',2,'Kidco Crayons 24','Twenty-four wax crayons.',320.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(173,'0200043',2,'Kidco Colour Pencils','Twelve short colour pencils.',240.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(174,'0200044',2,'Kidco Sketch Book','A plain sketch book.',160.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(175,'0200045',2,'Kidco Poster Colours','Six jars of poster colour.',280.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(176,'0200046',2,'Kidco Pencil Box','A metal pencil box.',220.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(177,'0200047',2,'Sayyed HB Pencil','One black lead pencil.',25.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(178,'0200048',2,'Sayyed Eraser Pencil','A lead pencil with an eraser tip.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(179,'0200049',2,'Sayyed Triangular Pencil','A thick triangular pencil.',40.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(180,'0200050',2,'Sayyed Colour Pencils','Twelve full-length colour pencils.',300.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(181,'0200051',2,'Sayyed Half Colour Pencils','Twelve short colour pencils.',180.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(182,'0200052',2,'Sayyed Pencil Dozen','Twelve lead pencils wrapped as a dozen.',210.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(183,'0200053',2,'Naulakha Register','A hardbound school register.',180.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(184,'0200054',2,'Naulakha Narrow Copy','A slim narrow-line exercise copy.',90.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(185,'0200055',2,'Naulakha Broad Notebook','A broad-line notebook.',120.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(186,'0200056',2,'Naulakha Practical Notebook','A practical notebook.',160.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(187,'0200057',2,'Naulakha Drawing Copy','A drawing copy with blank pages.',140.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(188,'0200058',2,'Naulakha Spiral Pad','A small spiral note pad.',150.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(189,'0200059',2,'UHU Stic','A small glue stick for paper.',90.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(190,'0200060',2,'UHU Stic Large','A large glue stick for paper.',160.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(191,'0200061',2,'UHU All Purpose Glue','A bottle of white all-purpose glue.',180.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(192,'0200062',2,'UHU Twist Glue','A slim twist-up glue pen.',200.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(193,'0200063',2,'UHU Paper Glue','A bottle of clear paper glue.',120.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(194,'0200064',2,'UHU Hobby Glue','A tube of clear hobby glue.',220.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(195,'0200065',2,'Myco Blue Pen','A transparent blue ball pen.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(196,'0200066',2,'Myco Black Pen','A matte black ball pen.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(197,'0200067',2,'Myco Red Pen','A transparent red ball pen.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(198,'0200068',2,'Myco Retractable Pen','A grey click pen.',55.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(199,'0200069',2,'Myco Fine Pen','A slim fine-point ball pen.',45.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(200,'0200070',2,'Myco Gel Pen','A black gel pen with a grip.',70.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(201,'0200071',2,'Bahadur HB Pencil','One orange lead pencil.',20.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(202,'0200072',2,'Bahadur Eraser','A beige eraser.',25.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(203,'0200073',2,'Bahadur Sharpener','A single-hole plastic sharpener.',25.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(204,'0200074',2,'Bahadur Geometry Box','A closed geometry tin.',350.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(205,'0200075',2,'Bahadur Ballpoint Pen','A brown capped ball pen.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(206,'0200076',2,'Bahadur Colour Pencils','Six colour pencils.',240.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(207,'0200077',2,'Winner Blue Pen','A blue stick ball pen.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(208,'0200078',2,'Winner Black Pen','A black stick ball pen.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(209,'0200079',2,'Winner Red Pen','A red stick ball pen.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(210,'0200080',2,'Winner Click Pen','A silver retractable pen.',50.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(211,'0200081',2,'Winner Fine Pen','A teal fine-point pen.',40.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(212,'0200082',2,'Winner Gel Pen','A burgundy gel pen.',65.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(213,'0200083',2,'Picasso Glue Stick','A glue stick for paper.',90.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(214,'0200084',2,'Picasso Highlighter','An orange highlighter.',70.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(215,'0200085',2,'Picasso Permanent Marker','A blue permanent marker.',75.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(216,'0200086',2,'Picasso Whiteboard Marker','A red whiteboard marker.',75.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(217,'0200087',2,'Picasso Gel Pen','A purple gel pen.',80.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(218,'0200088',2,'Picasso Ballpoint Pen','A green capped ball pen.',40.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(219,'0200089',2,'Mercury HB Pencils','Three lead pencils with erasers.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(220,'0200090',2,'Mercury Fountain Pen','A burgundy fountain pen with a steel nib.',240.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(221,'0200091',2,'Mercury Colour Pencils','Twelve colour pencils in a wooden case.',310.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(222,'0200092',2,'Mercury Whiteboard Marker','A green whiteboard marker.',80.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(223,'0200093',2,'Mercury Gel Pen','A navy gel pen.',75.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(224,'0200094',2,'Mercury Ballpoint Pen','An ivory capped ball pen.',45.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(225,'0200095',2,'Camel Eraser','A large white eraser.',30.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(226,'0200096',2,'Camel Sharpener','A wedge pencil sharpener.',35.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(227,'0200097',2,'Camel Poster Colours','Twelve pots of poster colour.',360.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(228,'0200098',2,'Camel Watercolours','A tin of watercolour pans with a brush.',420.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(229,'0200099',2,'Camel Glue Stick','A glue stick for paper.',85.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(230,'0200100',2,'Camel HB Pencils','Four wooden HB pencils.',120.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(231,'0100044',1,'Ribboned Gift Box, Square Black','A square black gift box with a gold ribbon.',1800.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(232,'0100045',1,'Ribboned Gift Box, Square Ivory','A square ivory gift box with a sage ribbon.',1800.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(233,'0100046',1,'Ribboned Gift Box, Rectangle Sage','A rectangular sage gift box with a champagne ribbon.',2200.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(234,'0100047',1,'Ribboned Gift Box, Round Blush','A round blush gift box with a gold ribbon.',2400.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(235,'0100048',1,'Ribboned Gift Box, Tall Forest','A tall forest-green gift box with an ivory ribbon.',2600.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(236,'0100049',1,'Ribboned Gift Box, Set of Three','Three nested gift boxes in black, ivory and sage.',4200.00,'No warranty',12,0,'active','2024-06-01 10:00:00',NULL),(237,'0500001',5,'Resin Art Jhumkas','A pair of resin jhumkas with pressed flowers and gold hooks.',1800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(238,'0500002',5,'Resin Floral Earrings','Small resin studs with dried flowers inside.',1500.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(239,'0500003',5,'Resin Art Necklaces','A resin pendant necklace with dried flowers and a fine chain.',2400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(240,'0500004',5,'Resin Pendant Sets','A matching resin necklace and earring set.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(241,'0500005',5,'Resin Art Bracelets','A resin bangle with a pressed leaf and gold flakes.',1600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(242,'0500006',5,'Resin Keychains','A round resin keychain with a pressed flower.',900.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(243,'0500007',5,'Personalised Resin Gifts','A clear resin block for a flower, a name or a short message.',2800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(244,'0600001',6,'Alphabet Bracelets A–Z','A beaded bracelet. Add the letters you want in the name field.',1200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(245,'0600002',6,'Personalised Name Bracelets','A bracelet made with the name you write at checkout.',1500.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(246,'0600003',6,'Couple Bracelets','Two matching bracelets. Add both names in the note.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(247,'0600004',6,'Beaded Bracelets','A glass-bead bracelet in sage, cream, gold and blush.',1400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(248,'0600005',6,'Pearl Bracelets','A small pearl bracelet with a gold clasp.',2600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(249,'0600006',6,'Charm Bracelets','A fine chain with a heart, a star and a flower charm.',1800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(250,'0600007',6,'Pastel Bracelets','A soft pink, lilac and mint beaded bracelet.',1600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(251,'0600008',6,'Trending Bracelet Stacks','Four thin bracelets worn together.',3200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(252,'0700001',7,'Fresh Flower Bouquets','White roses and olive leaves tied with a sage ribbon.',3500.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(253,'0700002',7,'Chocolate Bouquets','Wrapped chocolates arranged like a bouquet.',2800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(254,'0700003',7,'Candy Bouquets','Wrapped sweets arranged in a paper cone.',1800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(255,'0700004',7,'Makeup Bouquets','Lipsticks, lip gloss and mascara arranged as a bouquet.',4500.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(256,'0700005',7,'Toy and Teddy Bouquets','A small teddy, a plush bunny and a heart gathered in paper.',3800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(257,'0700006',7,'Stationery Bouquets','Pens, a notebook and pencils arranged as a bouquet.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(258,'0700007',7,'Mixed Gift Bouquets','Flowers, a chocolate and a small candle in one bouquet.',4200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(259,'0800001',8,'Snacks Gift Baskets','A basket of chocolates, biscuits and a small jar.',3200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(260,'0800002',8,'Baby Essentials Baskets','A blanket, a teddy, a rattle and folded baby clothes.',4800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(261,'0800003',8,'Baby Care Hampers','Lotion, a soft towel, a comb and a small plush.',5200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(262,'0800004',8,'Birthday Gift Baskets','Flowers, chocolates and a candle in a birthday basket.',3600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(263,'0800005',8,'Chocolate Gift Baskets','A basket filled with assorted chocolates.',3000.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(264,'0800006',8,'Customised Gift Baskets','Tell us the colours and the note, and the basket is packed for you.',5500.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(265,'0900001',9,'Chocolate Jars','A glass jar of chocolate pieces with a gold ribbon.',1400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(266,'0900002',9,'Candy Jars','A glass jar of colourful candies with a blush ribbon.',1200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(267,'0900003',9,'Jewellery Jars','A small jar holding rings and pearl earrings.',2400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(268,'0900004',9,'Personalised Gift Jars','A ribboned jar with a blank tag for the name and message you add.',1800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(269,'0900005',9,'Cookie Jars','A glass jar of assorted cookies, tied and ready to give.',1600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(270,'0900006',9,'Dry Fruit Jars','Almonds, cashews and raisins packed in a glass jar.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(271,'0100050',1,'Birthday Garden Card','A folded greeting card painted with garden flowers, a small cake, and soft balloons.',180.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(272,'0100051',1,'Thank You Bouquet Card','An illustrated bouquet of garden flowers across a cream card, for a simple thank-you.',160.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(273,'0100052',1,'Congratulations Ribbon Card','Painted ribbons and small stars on a warm gold and cream card for good news.',190.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(274,'0100053',1,'Wildflower Note Card','Loose wildflowers and a soft pattern, for any ordinary day worth marking.',150.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(275,'0100054',1,'Botanical Love Card','Leaves and blossoms on a blush card, for someone you care about.',170.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(276,'0100055',1,'Confetti Celebration Card','Illustrated confetti and ribbons in coral, gold, and sage, for a party or a small win.',200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(277,'0800007',8,'Cream Tea Hamper','Tea, biscuits, and a small honey jar arranged in a cream hamper with a sage ribbon.',2400.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(278,'0800008',8,'Blush Flower Hamper','Roses, a candle, and wrapped sweets arranged in a blush hamper.',3200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(279,'0800009',8,'Breakfast Hamper','Honey, jam, and a small tin arranged in a natural hamper with a linen cloth.',2600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(280,'0800010',8,'Chocolate Rose Hamper','Chocolates and a few roses in a deep cream hamper with a narrow gold ribbon.',3600.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(281,'0800011',8,'Soft Welcome Hamper','A pale hamper with a folded cloth, a candle, dried flowers, and little treats.',2900.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(282,'0800012',8,'Sweet and Paper Hamper','Notebooks, coloured pencils, and wrapped sweets in a light sage hamper.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(283,'0100056',1,'Men Ivory Kurta','A soft ivory kurta with a narrow sage stitch at the collar, for Eid morning.',3200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(284,'0100057',1,'Women Blush Lawn Suit','A blush lawn kameez and trousers with a matching printed dupatta.',4800.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(285,'0100058',1,'Men Sage Waistcoat','A sage waistcoat with a thin champagne edge, worn over a plain kurta.',2200.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL),(286,'0100059',1,'Eid-ul-Fitr Greeting Card','An illustrated card with a crescent, lanterns and sage leaves. No printed greeting.',180.00,'No warranty',10,0,'active','2024-06-01 10:00:00',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` int(10) unsigned DEFAULT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','issued') NOT NULL DEFAULT 'pending',
  `reason` varchar(255) NOT NULL,
  `issued_by` int(10) unsigned DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_refunds_order` (`order_id`),
  KEY `idx_refunds_status` (`status`),
  KEY `fk_refunds_return` (`return_id`),
  KEY `fk_refunds_user` (`issued_by`),
  CONSTRAINT `fk_refunds_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `fk_refunds_return` FOREIGN KEY (`return_id`) REFERENCES `return_requests` (`id`),
  CONSTRAINT `fk_refunds_user` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_requests`
--

DROP TABLE IF EXISTS `return_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `request_type` enum('return','replacement') NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `admin_notes` varchar(500) DEFAULT NULL,
  `processed_by` int(10) unsigned DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_returns_order` (`order_id`),
  KEY `idx_returns_status` (`status`),
  KEY `fk_returns_customer` (`customer_id`),
  KEY `fk_returns_admin` (`processed_by`),
  CONSTRAINT `fk_returns_admin` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_returns_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_requests`
--

LOCK TABLES `return_requests` WRITE;
/*!40000 ALTER TABLE `return_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `change_qty` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `reason` enum('opening','adjustment','order','cancel','return','replacement') NOT NULL,
  `reference_type` varchar(30) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stock_product` (`product_id`),
  KEY `idx_stock_created` (`created_at`),
  KEY `fk_stock_user` (`created_by`),
  CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stock_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,40,40,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(2,2,18,18,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(3,3,15,15,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(4,4,16,16,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(5,5,50,50,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(6,6,28,28,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(7,7,4,4,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(8,8,8,8,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(9,9,14,14,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(10,10,22,22,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(11,11,12,12,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(12,12,16,16,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 02:00:50'),(13,13,24,24,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(14,14,20,20,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(15,15,16,16,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(16,16,18,18,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(17,17,10,10,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(18,18,30,30,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(19,19,12,12,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(20,20,36,36,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(21,21,32,32,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(22,22,14,14,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(23,23,22,22,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(24,24,11,11,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(25,25,26,26,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(26,26,7,7,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(27,27,9,9,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(28,28,10,10,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(29,29,6,6,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(30,30,8,8,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(31,31,9,9,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(32,32,13,13,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(33,33,5,5,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(34,34,28,28,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(35,35,15,15,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(36,36,6,6,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(37,37,12,12,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(38,38,20,20,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(39,39,14,14,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(40,40,9,9,'opening',NULL,NULL,'Sample opening stock',NULL,'2026-10-29 18:57:33'),(44,40,-3,6,'order','order',1,'Order 1040001000000001',3,'2026-10-29 20:30:57'),(45,41,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(46,42,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(47,43,16,16,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(48,44,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(49,45,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(50,46,11,11,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(51,47,13,13,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(52,48,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(53,49,7,7,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(54,50,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(55,51,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(56,52,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(57,53,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(58,54,18,18,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(59,55,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(60,56,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(61,57,11,11,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(62,58,15,15,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(63,59,15,15,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(64,60,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(65,61,7,7,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(66,62,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(67,63,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(68,64,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(69,65,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(70,66,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-29 21:06:29'),(71,67,16,16,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(72,68,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(73,69,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(74,70,16,16,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(75,71,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(76,72,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(77,73,4,4,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(78,74,5,5,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(79,75,7,7,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(80,76,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(81,77,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(82,78,5,5,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(83,79,7,7,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(84,80,3,3,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(85,81,4,4,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(86,82,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(87,83,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(88,84,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(89,85,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(90,86,9,9,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(91,87,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(92,88,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(93,89,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(94,90,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(95,91,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 15:37:46'),(96,39,-7,7,'order','order',2,'Order 3040000900000002',3,'2026-10-30 16:12:04'),(97,92,16,16,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(98,93,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(99,94,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(100,95,11,11,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(101,96,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(102,97,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(103,98,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(104,99,7,7,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(105,100,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(106,101,8,8,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(107,102,14,14,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(108,103,12,12,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(109,104,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(110,105,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(111,106,6,6,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(112,107,10,10,'opening',NULL,NULL,'Opening stock',NULL,'2026-10-30 17:35:15'),(113,15,-1,15,'order','order',3,'Gift box 3010000600000003',3,'2026-10-31 20:36:04'),(114,15,1,16,'cancel','order',3,'Cancelled before dispatch',NULL,'2026-10-31 20:40:21'),(115,108,14,14,'opening','product',108,'Opening stock',NULL,'2026-11-02 01:25:22'),(116,109,8,8,'opening','product',109,'Opening stock',NULL,'2026-11-02 01:25:22'),(117,110,8,8,'opening','product',110,'Opening stock',NULL,'2026-11-02 01:25:22'),(118,111,6,6,'opening','product',111,'Opening stock',NULL,'2026-11-02 01:25:22'),(119,112,10,10,'opening','product',112,'Opening stock',NULL,'2026-11-02 01:25:22'),(120,113,10,10,'opening','product',113,'Opening stock',NULL,'2026-11-02 01:25:22'),(121,114,10,10,'opening','product',114,'Opening stock',NULL,'2026-11-02 01:25:22'),(122,115,8,8,'opening','product',115,'Opening stock',NULL,'2026-11-02 01:25:22'),(123,116,20,20,'opening','product',116,'Opening stock',NULL,'2026-11-02 01:25:22'),(124,117,14,14,'opening','product',117,'Opening stock',NULL,'2026-11-02 01:25:22'),(125,118,8,8,'opening','product',118,'Opening stock',NULL,'2026-11-02 01:25:22'),(126,119,12,12,'opening','product',119,'Opening stock',NULL,'2026-11-02 01:25:22'),(127,120,20,20,'opening','product',120,'Opening stock',NULL,'2026-11-02 01:25:22'),(128,121,14,14,'opening','product',121,'Opening stock',NULL,'2026-11-02 01:25:22'),(129,122,8,8,'opening','product',122,'Opening stock',NULL,'2026-11-02 01:25:22'),(130,123,16,16,'opening','product',123,'Opening stock',NULL,'2026-11-02 01:25:22'),(131,124,6,6,'opening','product',124,'Opening stock',NULL,'2026-11-02 01:25:22'),(132,125,14,14,'opening','product',125,'Opening stock',NULL,'2026-11-02 01:25:22'),(133,126,16,16,'opening','product',126,'Opening stock',NULL,'2026-11-02 01:25:22'),(134,127,6,6,'opening','product',127,'Opening stock',NULL,'2026-11-02 01:25:22'),(135,128,14,14,'opening','product',128,'Opening stock',NULL,'2026-11-02 01:25:22'),(136,129,8,8,'opening','product',129,'Opening stock',NULL,'2026-11-02 01:25:22'),(137,130,6,6,'opening','product',130,'Opening stock',NULL,'2026-11-02 01:25:22'),(138,131,18,18,'opening','product',131,'Opening stock',NULL,'2026-11-02 01:25:22'),(139,132,6,6,'opening','product',132,'Opening stock',NULL,'2026-11-02 01:25:22'),(140,133,8,8,'opening','product',133,'Opening stock',NULL,'2026-11-02 01:25:22'),(141,134,12,12,'opening','product',134,'Opening stock',NULL,'2026-11-02 01:25:22'),(142,135,8,8,'opening','product',135,'Opening stock',NULL,'2026-11-02 01:25:22'),(143,136,6,6,'opening','product',136,'Opening stock',NULL,'2026-11-02 01:25:22'),(144,137,12,12,'opening','product',137,'Opening stock',NULL,'2026-11-02 01:25:22'),(145,138,16,16,'opening','product',138,'Opening stock',NULL,'2026-11-02 01:25:22'),(146,139,24,24,'opening','product',139,'Opening stock',NULL,'2026-11-02 01:25:22'),(147,140,24,24,'opening','product',140,'Opening stock',NULL,'2026-11-02 01:25:22'),(148,141,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(149,142,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(150,143,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(151,144,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(152,145,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(153,146,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(154,147,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(155,148,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(156,149,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(157,150,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(158,151,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(159,152,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(160,153,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(161,154,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(162,155,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(163,156,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(164,157,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(165,158,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(166,159,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(167,160,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(168,161,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(169,162,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(170,163,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(171,164,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(172,165,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(173,166,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(174,167,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(175,168,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(176,169,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(177,170,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(178,171,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(179,172,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(180,173,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(181,174,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(182,175,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(183,176,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(184,177,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(185,178,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(186,179,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(187,180,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(188,181,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(189,182,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(190,183,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(191,184,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(192,185,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(193,186,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(194,187,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(195,188,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(196,189,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(197,190,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(198,191,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(199,192,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(200,193,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(201,194,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(202,195,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(203,196,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(204,197,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(205,198,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(206,199,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(207,200,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(208,201,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(209,202,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(210,203,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(211,204,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(212,205,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(213,206,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(214,207,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(215,208,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(216,209,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(217,210,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(218,211,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(219,212,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(220,213,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(221,214,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(222,215,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(223,216,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(224,217,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(225,218,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(226,219,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(227,220,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(228,221,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(229,222,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(230,223,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(231,224,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(232,225,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(233,226,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(234,227,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(235,228,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(236,229,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(237,230,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(238,231,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(239,232,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(240,233,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(241,234,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(242,235,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(243,236,12,12,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:21:38'),(244,237,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(245,238,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(246,239,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(247,240,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(248,241,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(249,242,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(250,243,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(251,244,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(252,245,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(253,246,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(254,247,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(255,248,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(256,249,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(257,250,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(258,251,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(259,252,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(260,253,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(261,254,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(262,255,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(263,256,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(264,257,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(265,258,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(266,259,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(267,260,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(268,261,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(269,262,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(270,263,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(271,264,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(272,265,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(273,266,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(274,267,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(275,268,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 00:53:10'),(276,242,-1,9,'order','order',4,'Order 3050000600000004',3,'2026-11-03 01:00:04'),(277,242,1,10,'cancel','order',4,'Cancelled before dispatch',NULL,'2026-11-03 01:09:06'),(278,269,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 01:41:27'),(279,270,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 01:41:27'),(280,141,-1,11,'order','order',5,'Order 3020001100000005',7,'2026-11-03 01:54:35'),(281,141,1,12,'cancel','order',5,'Cancelled before dispatch',NULL,'2026-11-03 01:55:10'),(282,271,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(283,272,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(284,273,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(285,274,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(286,275,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(287,276,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(288,277,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(289,278,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(290,279,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(291,280,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(292,281,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(293,282,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 02:22:54'),(294,283,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 03:01:53'),(295,284,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 03:01:53'),(296,285,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 03:01:53'),(297,286,10,10,'opening',NULL,NULL,'opening',NULL,'2026-11-03 03:01:53');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('admin','employee','customer') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Paperlane Admin','admin@paperlane.local','$2y$10$rJm6ulQ3LyRyjkzbyvdljuRduRMUkvbTyb/EWQtuEcnFZJCPCi57m','03000000000','active','2026-10-29 02:00:50'),(2,'employee','Ayesha Khan','employee@paperlane.local','$2y$10$RCiFlPSMFdNBdlY9seM/MeLIzYfCeBRNZfqobTQ0Mu29u1lBC1hzG','03001234567','active','2026-10-29 02:00:50'),(3,'customer','Neha Shah','customer@paperlane.local','$2y$10$yJQMzRg6C1MMZXkm4OshRO9DPWhu6HevTOXa5v46zQYensh/gtf7W','03009876543','active','2026-10-29 02:00:50'),(4,'customer','Muqaddar Aptech','muqaddarapptach@gmail.com','$2y$10$teoFvDRgwQYxgR7Vn3DZIe154jaOoeIptclQB1Bl1h2Jte3Z3Vr5e','03220660961','active','2026-10-29 02:03:47'),(5,'customer','muhammad hashir','abc@gmail.com','$2y$10$PKhGYuwkGoaf.TIkbzL2aeIAXvu4I8Vu3AFNDRJuvVREP/2z/QgsS','03001234567','active','2026-10-29 18:45:59'),(6,'employee','Hina Qureshi','hina.qureshi@paperlane.local','$2y$10$kjkPmAmQ/.dfUOJHkyMoQOfZbr9LhG.pps9QNxLGgF2.gEC.DajKm','03005551234','active','2026-10-29 19:00:38'),(7,'customer','Sana Test','sana.test.paperlane@example.com','$2y$10$orjfC4IEZtvC5IxjHEVy6uKx2cg9TdIuY6QnKg1NrI3d9uJgqZFjq','03001112233','active','2026-11-03 01:53:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-08 12:03:49
