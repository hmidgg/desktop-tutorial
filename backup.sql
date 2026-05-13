-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: examen
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Horaire`
--

DROP TABLE IF EXISTS `Horaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Horaire` (
  `ID_Horaire` int NOT NULL AUTO_INCREMENT,
  `Jour` varchar(20) NOT NULL,
  `Heure` int NOT NULL,
  `ID_Med` varchar(50) NOT NULL,
  `Est_Disponible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID_Horaire`),
  KEY `idx_horaire_med` (`ID_Med`),
  CONSTRAINT `Horaire_ibfk_1` FOREIGN KEY (`ID_Med`) REFERENCES `Medecin` (`ID_Med`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Horaire`
--

LOCK TABLES `Horaire` WRITE;
/*!40000 ALTER TABLE `Horaire` DISABLE KEYS */;
/*!40000 ALTER TABLE `Horaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Medecin`
--

DROP TABLE IF EXISTS `Medecin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Medecin` (
  `ID_Med` varchar(50) NOT NULL,
  `Addresse` varchar(255) DEFAULT NULL,
  `Specialite` varchar(100) DEFAULT NULL,
  `ID_Spe` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`ID_Med`),
  KEY `Email` (`Email`),
  KEY `idx_medecin_spe` (`ID_Spe`),
  CONSTRAINT `Medecin_ibfk_1` FOREIGN KEY (`ID_Spe`) REFERENCES `Specialite` (`ID_Spe`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `Medecin_ibfk_2` FOREIGN KEY (`Email`) REFERENCES `Personne` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Medecin`
--

LOCK TABLES `Medecin` WRITE;
/*!40000 ALTER TABLE `Medecin` DISABLE KEYS */;
INSERT INTO `Medecin` VALUES ('0123',NULL,NULL,'MED_GEN','hmidguezguez2@gmail.com','$2y$10$ewJBXopLUcW.0lIo.l7hd..eaiiU1bomw.ETaw4FYWAtVjYEaCD.i'),('123',NULL,NULL,'MED_GEN','hmidguezguez@gmail.com','$2y$10$FnB6P6moQuINhnkusFd3hOq47KvJA0R6ErxAkJ4mpdUoi0GTovxv2');
/*!40000 ALTER TABLE `Medecin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Medecin_Salle`
--

DROP TABLE IF EXISTS `Medecin_Salle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Medecin_Salle` (
  `ID_Med` varchar(50) NOT NULL,
  `ID_Salle` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_Med`,`ID_Salle`),
  KEY `ID_Salle` (`ID_Salle`),
  CONSTRAINT `Medecin_Salle_ibfk_1` FOREIGN KEY (`ID_Med`) REFERENCES `Medecin` (`ID_Med`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Medecin_Salle_ibfk_2` FOREIGN KEY (`ID_Salle`) REFERENCES `Salle` (`ID_Salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Medecin_Salle`
--

LOCK TABLES `Medecin_Salle` WRITE;
/*!40000 ALTER TABLE `Medecin_Salle` DISABLE KEYS */;
/*!40000 ALTER TABLE `Medecin_Salle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Patient`
--

DROP TABLE IF EXISTS `Patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Patient` (
  `Matricule` int NOT NULL,
  `Email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`Matricule`),
  KEY `Email` (`Email`),
  CONSTRAINT `Patient_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `Personne` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Patient`
--

LOCK TABLES `Patient` WRITE;
/*!40000 ALTER TABLE `Patient` DISABLE KEYS */;
INSERT INTO `Patient` VALUES (123,'hmidguezguez1@gmail.com','$2y$10$oocIyjT/GXM8VzBKCRppM.yoizKNSxQY86bHtnhwa.q9m5tE7Jdn2'),(12345,'mounacherif@gmail.com','$2y$10$EVfOPnl.sd647dMEYQRAG.K.nqFL07/Uk4gFmERcxTawOM04bSJgm'),(123456,'Ahmedguezguez@gmail.com','$2y$10$dXcnB81Ly29I.Uprwvw8PuiGtoqlnWH4GKFqe2cx8EcnijFjI/.2C');
/*!40000 ALTER TABLE `Patient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Personne`
--

DROP TABLE IF EXISTS `Personne`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Personne` (
  `Email` varchar(100) NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Prenom` varchar(100) NOT NULL,
  `Telephone` int DEFAULT NULL,
  PRIMARY KEY (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Personne`
--

LOCK TABLES `Personne` WRITE;
/*!40000 ALTER TABLE `Personne` DISABLE KEYS */;
INSERT INTO `Personne` VALUES ('admin@gmail.com','Admin','Admin',12345678),('Ahmedguezguez@gmail.com','Ahmed','guezguez',NULL),('hmidguezguez@gmail.com','hmid','guezguez',NULL),('hmidguezguez1@gmail.com','ahmed','guezguez',NULL),('hmidguezguez2@gmail.com','ahmed','guezguez',NULL),('mounacherif@gmail.com','cherif','mounacherif',NULL);
/*!40000 ALTER TABLE `Personne` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Rendez_vous`
--

DROP TABLE IF EXISTS `Rendez_vous`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rendez_vous` (
  `ID_RDV` varchar(50) NOT NULL,
  `DateHeure` datetime NOT NULL,
  `Matricule` int NOT NULL,
  `login` varchar(50) DEFAULT NULL,
  `ID_Med` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_RDV`),
  KEY `idx_rdv_patient` (`Matricule`),
  KEY `idx_rdv_medecin` (`ID_Med`),
  KEY `idx_rdv_user` (`login`),
  CONSTRAINT `Rendez_vous_ibfk_1` FOREIGN KEY (`Matricule`) REFERENCES `Patient` (`Matricule`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `Rendez_vous_ibfk_2` FOREIGN KEY (`login`) REFERENCES `User` (`login`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Rendez_vous_ibfk_3` FOREIGN KEY (`ID_Med`) REFERENCES `Medecin` (`ID_Med`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rendez_vous`
--

LOCK TABLES `Rendez_vous` WRITE;
/*!40000 ALTER TABLE `Rendez_vous` DISABLE KEYS */;
INSERT INTO `Rendez_vous` VALUES ('1','2029-07-07 17:07:00',123456,NULL,'123'),('RDV000001','2058-07-15 22:22:00',123,NULL,'0123');
/*!40000 ALTER TABLE `Rendez_vous` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`grouped`@`localhost`*/ /*!50003 TRIGGER `trg_rdv_generate_id` BEFORE INSERT ON `Rendez_vous` FOR EACH ROW BEGIN
    DECLARE next_num INT DEFAULT 1;

    IF NEW.ID_RDV IS NULL OR NEW.ID_RDV = '' THEN
        SELECT COALESCE(
            MAX(CAST(SUBSTRING(ID_RDV, 4) AS UNSIGNED)),
            0
        ) + 1
        INTO next_num
        FROM Rendez_vous
        WHERE ID_RDV REGEXP '^RDV[0-9]+$';

        SET NEW.ID_RDV = CONCAT('RDV', LPAD(next_num, 6, '0'));
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Rendez_vous_backup`
--

DROP TABLE IF EXISTS `Rendez_vous_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rendez_vous_backup` (
  `ID_RDV` varchar(50) NOT NULL,
  `DateHeure` datetime NOT NULL,
  `Matricule` int NOT NULL,
  `login` varchar(50) NOT NULL,
  `ID_Med` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rendez_vous_backup`
--

LOCK TABLES `Rendez_vous_backup` WRITE;
/*!40000 ALTER TABLE `Rendez_vous_backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `Rendez_vous_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Rendez_vous_suivi`
--

DROP TABLE IF EXISTS `Rendez_vous_suivi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rendez_vous_suivi` (
  `ID_RDV` varchar(50) NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'attente',
  `ID_Salle` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_RDV`),
  KEY `fk_suivi_salle` (`ID_Salle`),
  CONSTRAINT `fk_suivi_rdv` FOREIGN KEY (`ID_RDV`) REFERENCES `Rendez_vous` (`ID_RDV`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_suivi_salle` FOREIGN KEY (`ID_Salle`) REFERENCES `Salle` (`ID_Salle`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rendez_vous_suivi`
--

LOCK TABLES `Rendez_vous_suivi` WRITE;
/*!40000 ALTER TABLE `Rendez_vous_suivi` DISABLE KEYS */;
INSERT INTO `Rendez_vous_suivi` VALUES ('1','valide','S102','2026-05-11 16:40:27');
/*!40000 ALTER TABLE `Rendez_vous_suivi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Salle`
--

DROP TABLE IF EXISTS `Salle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Salle` (
  `ID_Salle` varchar(50) NOT NULL,
  `Equipement` varchar(255) DEFAULT NULL,
  `Est_Disponible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID_Salle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Salle`
--

LOCK TABLES `Salle` WRITE;
/*!40000 ALTER TABLE `Salle` DISABLE KEYS */;
INSERT INTO `Salle` VALUES ('S101','chirugien',1),('S102',NULL,1);
/*!40000 ALTER TABLE `Salle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Specialite`
--

DROP TABLE IF EXISTS `Specialite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Specialite` (
  `ID_Spe` varchar(50) NOT NULL,
  `Nom` varchar(100) NOT NULL,
  PRIMARY KEY (`ID_Spe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Specialite`
--

LOCK TABLES `Specialite` WRITE;
/*!40000 ALTER TABLE `Specialite` DISABLE KEYS */;
INSERT INTO `Specialite` VALUES ('CARDIO','Cardiologie'),('CHIR','Chirurgie générale'),('CHIR_MAIN','Chirurgie de la main, des brûlés et réparatrice'),('DERMA','Dermatologie'),('DIAB','Diabétologie et endocrinologie'),('DIET','Diététique'),('GASTRO','Gastro-entérologie'),('GERIA','Gériatrie'),('GYN','Gynécologie-obstétrique'),('MED_GEN','Médecine générale'),('MED_INT','Médecine interne'),('NEPH','Néphrologie'),('NEURO','Neurologie'),('OPHT','Ophtalmologie'),('ORL','Oto-rhino-laryngologie (ORL)'),('ORTHO','Orthopédie'),('PNEUM','Pneumologie'),('PYCH','Psychiatrie'),('RHUM','Rhumatologie'),('UROL','Urologie');
/*!40000 ALTER TABLE `Specialite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User` (
  `login` varchar(50) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  PRIMARY KEY (`login`),
  KEY `Email` (`Email`),
  CONSTRAINT `User_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `Personne` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES ('admin','123','admin@gmail.com');
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-13 11:11:00
