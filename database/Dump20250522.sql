CREATE DATABASE  IF NOT EXISTS `cinema_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cinema_db`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: cinema_db
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actors`
--

DROP TABLE IF EXISTS `actors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actors`
--

LOCK TABLES `actors` WRITE;
/*!40000 ALTER TABLE `actors` DISABLE KEYS */;
INSERT INTO `actors` VALUES (1,'Леонардо Ді Капріо','1974-11-11'),(2,'Кіану Рівз','1964-09-02'),(3,'Джозеф Гордон-Левітт','1981-02-17'),(4,'Елліот Пейдж','1987-02-21'),(5,'Том Гарді','1977-09-15'),(6,'Кілліан Мерфі','1976-05-25'),(7,'Маріон Котіяр','1975-09-30'),(8,'Майкл Кейн','1933-03-14'),(9,'Крістіан Бейл','1974-01-30'),(10,'Гіт Леджер','1979-04-04'),(11,'Меттью Макконахі','1969-11-04'),(12,'Енн Гетевей','1982-11-12'),(13,'Керрі-Енн Мосс','1967-08-21'),(14,'Том Круз','1962-07-03'),(15,'Хейлі Атвелл','1982-04-05'),(16,'Вінг Реймс','1959-05-12'),(17,'Саймон Пегг','1970-02-13'),(18,'Есай Моралес','1962-10-01'),(19,'Пом Клементьєфф','1986-05-03'),(20,'Генрі Черні','1959-02-08'),(21,'Крістофер Нолан','0001-01-01'),(22,'Сестри Вачовскі','0001-01-01'),(23,'Крістофер МакКуаррі','0001-01-01');
/*!40000 ALTER TABLE `actors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genres`
--

LOCK TABLES `genres` WRITE;
/*!40000 ALTER TABLE `genres` DISABLE KEYS */;
INSERT INTO `genres` VALUES (3,'Комедія'),(4,'Трилер'),(8,'Екшн'),(10,'Кримінал'),(11,'Фантастика'),(14,'Драма'),(15,'Пригоди'),(16,'qwerqwer');
/*!40000 ALTER TABLE `genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `halls`
--

DROP TABLE IF EXISTS `halls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `halls` (
  `id` int unsigned NOT NULL,
  `seat_map_template` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `halls`
--

LOCK TABLES `halls` WRITE;
/*!40000 ALTER TABLE `halls` DISABLE KEYS */;
INSERT INTO `halls` VALUES (1,'__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bb__\n_.bbb_bbb_bbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa'),(2,'_bb__bb__bb_\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\n_aaaaaaaaaa_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa'),(3,'\n.bb_bb_bb_\naaaaaaaaa\naaaaaaaaaa\n---bb_bbb_bb_\n__aaaaaaaaa_\n_aaaaaaaaaa\naaaaaaaaaa\naaaaaaaaa\n');
/*!40000 ALTER TABLE `halls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movie_genres`
--

DROP TABLE IF EXISTS `movie_genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movie_genres` (
  `movie_id` int unsigned NOT NULL,
  `genre_id` int unsigned NOT NULL,
  PRIMARY KEY (`movie_id`,`genre_id`),
  KEY `genre_genre_fk` (`genre_id`),
  CONSTRAINT `genre_genre_fk` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movie_movie_fk` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movie_genres`
--

LOCK TABLES `movie_genres` WRITE;
/*!40000 ALTER TABLE `movie_genres` DISABLE KEYS */;
INSERT INTO `movie_genres` VALUES (10,4),(11,8),(14,8),(15,8),(11,10),(15,10),(10,11),(13,11),(14,11),(11,14),(13,14),(15,14),(13,15);
/*!40000 ALTER TABLE `movie_genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `description` text,
  `director_id` int unsigned DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `year` int DEFAULT '2025',
  `rating` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `director_id_idx` (`director_id`),
  CONSTRAINT `director_id` FOREIGN KEY (`director_id`) REFERENCES `actors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (10,'Початок','Ми звикли, що в нашому розумінні злодій - це людина здатна вкрасти якісь цінності або гроші. Сюжет фантастичного бойовика «Початок» розповідає про злодіїв здатних вкрасти ідею прямо у людини з підсвідомості. Одним з таких є головний герой фільму Домінік Кобб. Після того як його дружина померла, він змушений ховатися, і не може навіть повернутися в країну, щоб побачити дітей. Якось раз Кобб отримує дуже неординарне замовлення: йому потрібно не вкрасти, а навпаки впровадити нову ідею в підсвідомість людини. Домініку не надто хотітися братися за цю справу, але замовник в обмін пропонує можливість повернутися додому. Заручившись підтримкою професіоналів цієї справи, Кобб починає розробляти план як все провернути. Все потрібно дуже добре продумати, адже злодіям доведеться відтворити багатошарову реальність в підсвідомості об\'єкта, в результаті чого межі можуть почати стиратися.',21,148,2010,8.8),(11,'Темний лицар','Бетмен протистоїть Джокеру, кримінальному генію, який хоче занурити Готем у хаос.',21,152,2025,9),(13,'Інтерстеллар','Команда дослідників подорожує через кротовину в космосі, щоб забезпечити виживання людства',21,168,2013,8.6),(14,'Матриця','Хакер дізнається про справжню природу своєї реальності та свою роль у війні проти її контролерів.',22,136,1999,8.7),(15,'Місія неможлива: Фінальна розплата','Агент секретної служби, яка спеціалізується на неможливих місіях, Ітан Гант (Том Круз) готується до нового завдання. Впливовий зловмисник планує заволодіти доступом до штучного інтелекту, який здатен змінити весь порядок у світі. Перешкодити його планам неможливо. Однак саме для таких ситуацій і існує команда Ітана Ганта, з якою він вирушає у чергову місію. ',23,170,2025,8.8);
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movies_actors`
--

DROP TABLE IF EXISTS `movies_actors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies_actors` (
  `movie_id` int unsigned NOT NULL,
  `actor_id` int unsigned NOT NULL,
  `char_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`movie_id`,`actor_id`),
  KEY `actor_id_idx` (`actor_id`),
  CONSTRAINT `actor_id` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movie_id` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies_actors`
--

LOCK TABLES `movies_actors` WRITE;
/*!40000 ALTER TABLE `movies_actors` DISABLE KEYS */;
INSERT INTO `movies_actors` VALUES (10,1,'Дом Кобб'),(10,3,'Артур'),(10,4,'Аріадн'),(10,5,'Імз'),(10,6,'Роберт Фішер'),(10,7,'Мел'),(10,8,'Майлз'),(11,9,'Брюс Уейн / Бетмен'),(11,10,'Джокер'),(13,11,'Купер'),(13,12,'Бренд'),(14,2,'Нео'),(14,13,'Трініті'),(15,14,'Ітан Гант'),(15,15,'Ґрейс'),(15,16,'Лютер Стікел'),(15,17,'Бенджамін (Бенджі) Данн'),(15,18,'Габріель'),(15,19,'Париж'),(15,20,'Юджин Кіттрідж');
/*!40000 ALTER TABLE `movies_actors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (3,'admin'),(4,'default');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `movie_id` int unsigned DEFAULT NULL,
  `hall_id` int unsigned DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `seat_map` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `hall_id_idx` (`hall_id`),
  KEY `movie_id_idx` (`movie_id`),
  CONSTRAINT `hall_id` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`),
  CONSTRAINT `movie_session_id` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (8,10,1,'2025-06-23 11:00:00','__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaaaxxxxxaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bx__\n_.bbb_bbb_bbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa'),(9,11,1,'2025-06-23 15:00:00','__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bb__\n_.bbb_bbb_bbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa'),(10,11,3,'2025-05-21 23:00:00','\n.bb_bb_bb_\naaaaaaaaa\naaaaaaaaaa\n---bb_bbb_bb_\n__aaaaaaaaa_\n_aaaaaaaaaa\naaaaaaaaaa\naaaaaaaaa\n'),(11,14,3,'2025-05-21 18:00:00','\n.bb_bb_bb_\naaaaaaaaa\naaaaaaaaaa\n---bb_bbb_bb_\n__aaaaaaaaa_\n_aaaaaaaaaa\naaaaaaaaaa\naaaaaaaaa\n'),(12,13,1,'2025-05-21 17:00:00','__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bb__\n_.bbb_bbb_bbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa'),(13,13,2,'2025-05-22 17:30:00','_bb__bb__bb_\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\n_aaaaaaaaaa_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa'),(14,14,2,'2025-05-22 14:45:00','_bb__bb__bb_\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\n_aaaaaaaaaa_\naaaaaaaaaaaa\naaaaaaaaaaaa\n.bbb_bbb_bbb_\naaaaaaaaaaaa\naaaaaaaaaaaa'),(15,15,1,'2025-05-21 21:00:00','__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bb__\n_.bbb_bbb_bbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa'),(16,10,3,'2025-05-22 19:00:00','\n.bb_bb_bb_\naaaaaaaaa\naaaaaaaaaa\n---bb_bbb_bb_\n__aaaaaaaaa_\n_aaaaaaaaaa\naaaaaaaaaa\naaaaaaaaa\n'),(17,10,1,'2025-05-22 15:16:00','__bb__bb__bb__\n.aaaaaaaaaaaaa.\naaaaaxxxxaaaaa\naaaaaaaaaaaaaa\n_aaaaaaaaaaaa_\n__aaaaaaaaaa__\n__.aaaaaaaaa.__\n__bb__bb__bx__\n_.bbb_bbb_xbb__\naaaaaaaaaaaaaa\naaaaaaaaaaaaaa');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `session_id` int unsigned NOT NULL,
  `row` int unsigned NOT NULL,
  `col` int unsigned NOT NULL,
  `buy_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `price` int NOT NULL,
  `user_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idtickets_UNIQUE` (`id`),
  KEY `session_id_idx` (`session_id`),
  KEY `user_id_idx` (`user_id`),
  CONSTRAINT `session_id` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`),
  CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (13,8,2,11,'2025-05-21 03:14:36',100,2),(14,8,2,12,'2025-05-21 03:14:36',100,2),(15,8,2,10,'2025-05-21 03:14:36',100,2),(16,8,2,9,'2025-05-21 03:14:36',100,2),(17,8,2,8,'2025-05-21 03:14:36',100,2),(18,8,7,6,'2025-05-21 03:14:36',150,2),(19,17,2,6,'2025-05-21 19:01:37',100,2),(20,17,2,7,'2025-05-21 19:01:37',100,2),(21,17,2,8,'2025-05-21 19:01:37',100,2),(22,17,2,9,'2025-05-21 19:01:37',100,2),(23,17,7,6,'2025-05-21 19:01:37',150,2),(24,17,8,7,'2025-05-21 19:01:37',150,2);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `role_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id_idx` (`role_id`),
  CONSTRAINT `role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'percev.yevgen@gmail.com','admin123',3),(2,'Єгвен','mail@mail.com','pass222',4);
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

-- Dump completed on 2025-05-22 19:19:11
