-- Digital Mall: canli kurulum veritabani (sema + ornek magazalar + demo hesaplar). phpMyAdmin > Import ile BIR KEZ yukle.


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
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `categories` VALUES (24,'album','tool.com'),(26,'guitar','donmadanmüzik.net'),(27,'pick','donmadanmüzik.net'),(28,'bass','donmadanmüzik.net'),(29,'processor','donmadanmüzik.net'),(30,'pencil case','kırtasiyeci'),(31,'pencil','kırtasiyeci'),(32,'notebook','kırtasiyeci'),(33,'eraser','kırtasiyeci'),(34,'bag','kırtasiyeci'),(35,'sketchbook','kırtasiyeci'),(36,'tshirt','shirtHane'),(37,'hoodie','shirtHane'),(38,'hat','shirtHane'),(42,'case','donmadanmüzik.net');
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_email` varchar(255) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `items` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `order_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `orders` VALUES (12,'customer@demo.com','kırtasiyeci','kalem1 (x3), defter1 (x2)',86.00,'Approved','25.05.2026'),(14,'customer@demo.com','kırtasiyeci','kalem1 (x5), defter1 (x7)',235.00,'Approved','26.05.2026'),(16,'customer@demo.com','kırtasiyeci','kalem1 (x5), defter1 (x5)',185.00,'Approved','26.05.2026'),(17,'customer@demo.com','tool.com','laterlaus (x1), tool (x1)',198.00,'Approved','26.05.2026'),(19,'customer@demo.com','shirtHane','thsirt (x3), cool hat (x1), jacket (x1), hoodie (x1), tee (x1)',199.00,'Approved','26.05.2026'),(20,'customer@demo.com','donmadanmüzik.net','jazz bass (x1), guitar hero (x1), guitar case (x1)',276.00,'Rejected','26.05.2026'),(21,'customer@demo.com','tool.com','adam  (x2)',198.00,'Approved','26.05.2026'),(22,'customer@demo.com','kırtasiyeci','pencil case (x1), minions bag (x1)',36.00,'Approved','26.05.2026'),(27,'customer@demo.com','donmadanmüzik.net','jazz bass (x1), guitar hero (x1), electronic guitar (x1), bass (x1), pena (x12), guitar case (x1)',658.00,'Approved','30.05.2026'),(28,'customer@demo.com','shirtHane','cool hat (x1), thsirt (x1), jacket (x1), hoodie (x1), tee (x1)',173.00,'Approved','30.05.2026'),(29,'customer@demo.com','kırtasiyeci','pencil (x1), notebook (x1), pencil case (x1), mono eraser (x1), posca  (x1), minions bag (x1), sketchbook (x1)',79.00,'Approved','30.05.2026'),(30,'customer@demo.com','tool.com','laterlaus (x1), tool (x1), adam  (x1)',297.00,'Pending','30.05.2026'),(31,'customer@demo.com','donmadanmüzik.net','highend gitar (x1), pena (x1), jazz bass (x1), guitar hero (x1), electronic guitar (x1), bass (x1), processor (x1), 6 guitar case (x1), guitar case (x1)',1277.00,'Approved','30.05.2026'),(32,'customer@demo.com','kırtasiyeci','notebook (x1), pencil (x3), posca  (x1), minions bag (x1)',56.00,'Approved','30.05.2026'),(33,'customer@demo.com','kırtasiyeci','notebook (x9), pencil case (x1), mono eraser (x1), posca  (x1), minions bag (x1)',183.00,'Approved','30.05.2026'),(34,'customer@demo.com','kırtasiyeci','pencil (x1), notebook (x1), pencil case (x1), mono eraser (x1), posca  (x1), minions bag (x1), sketchbook (x1)',79.00,'Approved','30.05.2026'),(35,'customer@demo.com','tool.com','laterlaus (x1), tool (x1), adam  (x1)',297.00,'Pending','30.05.2026'),(36,'customer@demo.com','donmadanmüzik.net','highend gitar (x1), pena (x1), jazz bass (x1), guitar hero (x1), electronic guitar (x1)',691.00,'Approved','30.05.2026'),(37,'customer@demo.com','shirtHane','thsirt (x1), cool hat (x1), jacket (x1), hoodie (x1), tee (x1)',173.00,'Approved','30.05.2026'),(38,'customer@demo.com','kırtasiyeci','pencil (x1), notebook (x1), pencil case (x1), sketchbook (x1)',43.00,'Approved','31.05.2026'),(39,'customer@demo.com','tool.com','tool (x1), adam  (x1)',198.00,'Pending','31.05.2026'),(40,'customer@demo.com','donmadanmüzik.net','pena (x1), jazz bass (x1), guitar hero (x1), processor (x1), guitar case (x1)',357.00,'Approved','31.05.2026'),(41,'customer@demo.com','shirtHane','thsirt (x1), cool hat (x2), jacket (x1), hoodie (x1), tee (x1)',240.00,'Approved','31.05.2026');
DROP TABLE IF EXISTS `pictures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pictures_product` (`product_id`),
  CONSTRAINT `fk_pictures_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `pictures` VALUES (56,31,'./upload/kalem1.jpg'),(57,31,'./upload/kalem2.jpg'),(58,31,'./upload/kalem3.jpg'),(59,32,'./upload/defter1.jpg'),(60,32,'./upload/defter2.jpg'),(61,32,'./upload/defter3.jpg'),(62,33,'./upload/later1.jpg'),(63,33,'./upload/later2.jpg'),(64,33,'./upload/later3.jpg'),(65,34,'./upload/tool1.jpg'),(66,34,'./upload/tool2.jpg'),(67,34,'./upload/tool3.jpg'),(68,35,'./upload/gitar1.jpg'),(69,35,'./upload/gitar2.png'),(70,35,'./upload/gitar3.jpg'),(71,36,'./upload/pena1.jpg'),(72,36,'./upload/pena2.jpg'),(73,36,'./upload/pena3.jpg'),(75,38,'./upload/jazzbass1.jpeg'),(76,38,'./upload/jazzbass2.jpeg'),(77,38,'./upload/jazzbass3.jpeg'),(78,39,'./upload/ghero3.jpeg'),(79,39,'./upload/ghero2.jpeg'),(80,39,'./upload/ghero1.jpeg'),(81,40,'./upload/egitar1.jpeg'),(82,40,'./upload/egitar3.jpeg'),(83,40,'./upload/egitr2.jpeg'),(84,41,'./upload/bass1.jpeg'),(85,41,'./upload/bass2.jpeg'),(86,41,'./upload/bass3.jpeg'),(87,42,'./upload/pro3.jpeg'),(88,42,'./upload/pro2.jpeg'),(89,42,'./upload/pro1.jpeg'),(90,43,'./upload/6g2.jpeg'),(91,43,'./upload/6g1.jpeg'),(92,43,'./upload/6g3.jpeg'),(93,44,'./upload/case3.jpeg'),(94,44,'./upload/case2.jpeg'),(95,44,'./upload/case1.jpeg'),(96,45,'./upload/pcase2.jpeg'),(97,45,'./upload/pcase1.jpeg'),(98,45,'./upload/pcase3.jpeg'),(99,46,'./upload/eraser3.jpeg'),(100,46,'./upload/eraser2.jpeg'),(101,46,'./upload/eraser1.jpeg'),(102,47,'./upload/posca3.jpeg'),(103,47,'./upload/posca2.jpeg'),(104,47,'./upload/posca1.jpeg'),(105,48,'./upload/canta3.png'),(106,48,'./upload/canta2.png'),(107,48,'./upload/canta1.png'),(108,49,'./upload/sketchbook2.jpg'),(109,49,'./upload/sketchbook1.jpg'),(110,50,'./upload/t2.jpeg'),(111,50,'./upload/t3.jpeg'),(112,50,'./upload/t1.jpeg'),(113,51,'./upload/tshirt2.jpeg'),(114,51,'./upload/tshirt3.jpeg'),(115,51,'./upload/tshirt1.jpeg'),(116,52,'./upload/hat3.jpeg'),(117,52,'./upload/hat2.jpeg'),(118,52,'./upload/hat1.jpeg'),(119,53,'./upload/jacket1.jpeg'),(120,53,'./upload/jacket2.jpeg'),(121,53,'./upload/jacket3.jpeg'),(122,54,'./upload/hoodie1.jpeg'),(123,54,'./upload/hoodie2.jpeg'),(124,55,'./upload/tee1.jpeg'),(125,55,'./upload/tee2.jpeg'),(126,55,'./upload/tee3.jpeg');
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `products` VALUES (31,'Pencil',3.00,'pencil','Smooth HB pencil for everyday writing.','kırtasiyeci'),(32,'Notebook',15.00,'notebook','A4 squared notebook, 3 sections, 120 pages.','kırtasiyeci'),(33,'Lateralus (Vinyl)',99.00,'album','Second-hand vinyl in mint condition.','tool.com'),(34,'Tool Album (Vinyl)',99.00,'album','Second-hand vinyl, no scratches.','tool.com'),(35,'High-End Guitar',300.00,'guitar','Premium electric guitar with a rich, warm tone.','donmadanmüzik.net'),(36,'Guitar Pick',1.00,'pick','Durable medium-gauge pick.','donmadanmüzik.net'),(38,'Jazz Bass',150.00,'bass','4-string bass with a classic jazz sound.','donmadanmüzik.net'),(39,'Guitar Hero Controller',50.00,'guitar','Guitar controller for rhythm games.','donmadanmüzik.net'),(40,'Electric Guitar',190.00,'guitar','Solid-body electric guitar for beginners and pros.','donmadanmüzik.net'),(41,'Bass Guitar',180.00,'bass','4-string electric bass.','donmadanmüzik.net'),(42,'Effects Processor',80.00,'processor','Multi-effects pedal for guitar and bass.','donmadanmüzik.net'),(43,'6-Guitar Case',250.00,'case','Padded case that holds six guitars.','donmadanmüzik.net'),(44,'Guitar Case',76.00,'case','Padded gig bag with shoulder straps.','donmadanmüzik.net'),(45,'Pencil Case',12.00,'pencil case','Zippered pencil case with two pockets.','kırtasiyeci'),(46,'Mono Eraser',4.00,'eraser','Clean-erasing eraser that leaves little dust.','kırtasiyeci'),(47,'Posca Marker',8.00,'pencil','Water-based paint marker for any surface.','kırtasiyeci'),(48,'Minions Bag',24.00,'bag','Kids school bag with a Minions print.','kırtasiyeci'),(49,'Sketchbook',13.00,'sketchbook','A5 sketchbook with thick drawing paper.','kırtasiyeci'),(50,'Album (Vinyl)',99.00,'album','Second-hand vinyl in good condition.','tool.com'),(51,'T-Shirt',13.00,'tshirt','Cotton t-shirt with a printed design.','shirtHane'),(52,'Cool Hat',67.00,'hat','Adjustable cap, one size.','shirtHane'),(53,'Jacket',56.00,'tshirt','Light jacket for spring and autumn.','shirtHane'),(54,'Hoodie',23.00,'hoodie','Warm cotton hoodie with front pocket.','shirtHane'),(55,'Tee',14.00,'tshirt','Basic tee, regular fit.','shirtHane');
DROP TABLE IF EXISTS `shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `users` VALUES (16,'Admin','admin@digitalmall.local','$2y$10$lNUAkL1DWrh.pYyj4/q.j.fmGxOhdKTHjGX6tv6KGMPx2H8FEPwKW','admin','admin',1),(24,'kırtasiye','stationery@digitalmall.local','$2y$10$fFgHGvU7j2UwxlbbksOEcOqe/f1RcunVUL9wJfzRfHutf8TwcY.GC','manager','kırtasiyeci',1),(25,'tool','tool@digitalmall.local','$2y$10$xjf42Bj8JMobiSKEffP0yO3WR0KnLHuaQt/I9Ji7qXrAat/dMhM72','manager','tool.com',1),(28,'Demo Customer','customer@demo.com','$2y$10$n47qm9vx3Y6JAz5Fe9v6huovtek.FottUPZ.yh.dGM7mxwNy/y2m2','customer','Demo Street 1, Famagusta',1),(29,'muzisyen','music@digitalmall.local','$2y$10$ItKlXYtEa8l70nOGLPPbt.I94TJivWcOk1gbqfzae44HDQBOATfNK','manager','donmadanmüzik.net',1),(30,'Demo Manager','manager@demo.com','$2y$10$n47qm9vx3Y6JAz5Fe9v6huovtek.FottUPZ.yh.dGM7mxwNy/y2m2','manager','shirtHane',1);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

