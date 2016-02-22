-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5.4

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
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai` (
  `NIP` char(5) COLLATE latin1_general_ci NOT NULL,
  `nama` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `telepon` varchar(25) COLLATE latin1_general_ci NOT NULL,
  `umur` int(2) NOT NULL,
  `alamat` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `foto` varchar(100) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`NIP`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES ('22058','August Mathis','(501) 790-2023',27,'Ap #332-8054 Massa. Rd.','foto/f2.jpg'),('92504','Perry Chang','(593) 551-5076',27,'P.O. Box 754, 6090 Dictum Rd.','foto/f10.jpg'),('50896','Jared Curry','(444) 948-7637',23,'P.O. Box 505, 3416 Volutpat. Street',''),('91536','Ray Booker','(331) 227-6510',40,'8860 Turpis Rd.','foto/f6.jpg'),('12610','Shad Harmon','(513) 676-3576',39,'P.O. Box 719, 4260 Nunc Ave',''),('68460','Alden Kelley','(927) 538-3735',24,'3471 Amet, Street','foto/f2.jpg'),('85974','Oleg Waters','(216) 962-0679',38,'358-8284 Neque. Rd.','foto/f10.jpg'),('06316','Nero Vega','(954) 938-6208',39,'P.O. Box 126, 1487 Adipiscing Rd.',''),('99044','Dexter Rios','(334) 650-3409',38,'6232 Ac, Street','foto/f11.jpg'),('57457','Tucker Pratt','(807) 702-1394',35,'P.O. Box 491, 3917 Lacus. St.','foto/f11.jpg'),('64353','Kirk Dunn','(110) 567-3454',25,'579-8643 Felis Rd.',''),('39579','Prescott Dale','(446) 790-2104',26,'8919 Penatibus St.','foto/f3.jpg'),('55379','Ronan Burnett','(565) 422-4057',30,'297-7525 Purus. Rd.','foto/f11.jpg'),('52277','Ezra Ortiz','(792) 162-8765',27,'Ap #573-8076 Eu Ave','foto/f4.jpg'),('03227','Kenyon Hull','(507) 785-0429',36,'798 Sit Rd.','foto/f6.jpg'),('46568','Fritz Durham','(313) 645-4009',24,'3076 Feugiat. Rd.',''),('73571','Peter Hanson','(288) 845-6779',40,'696-307 Ipsum Rd.','foto/f7.jpg'),('36944','Leo Hardin','(737) 309-0418',28,'5928 Leo. Street','foto/f3.jpg'),('05506','Carl Horton','(318) 913-4762',25,'P.O. Box 484, 5315 Sagittis Rd.','foto/f4.jpg'),('75494','Nasim Rodriquez','(173) 689-7329',25,'767-9158 Auctor St.','foto/f10.jpg'),('19064','Dean Bauer','(995) 490-0718',24,'P.O. Box 583, 313 Congue. Ave',''),('35166','Kyle Hester','(673) 637-9478',40,'P.O. Box 257, 6415 Proin Av.','foto/f2.jpg'),('10797','Russell Hooper','(729) 986-5322',35,'810-699 Integer Rd.','foto/f10.jpg'),('26035','Bevis Knowles','(225) 912-7609',33,'P.O. Box 570, 1953 Enim Av.',''),('47834','Emerson Carlson','(558) 683-6359',39,'P.O. Box 345, 7300 Eu, St.','foto/f4.jpg'),('32321','Julian Sawyer','(306) 230-5550',29,'Ap #799-3627 Pellentesque. Avenue','foto/f10.jpg'),('37288','Jonah Blevins','(683) 814-4974',33,'Ap #541-3485 Nec, Av.',''),('15625','Hall Bird','(877) 256-7615',40,'510-6400 At, Ave','foto/f8.jpg'),('28217','Salvador Norton','(102) 737-1368',26,'5070 Duis Road','foto/f5.jpg'),('09090','Noble Hays','(536) 458-0441',30,'4694 Urna. Av.',''),('01093','Tanner Gregory','(133) 143-0175',32,'P.O. Box 280, 9450 Nulla Av.','foto/f9.jpg'),('56093','Victor Osborne','(898) 991-8222',35,'P.O. Box 445, 8095 Donec Rd.','foto/f7.jpg'),('89327','Guy Huffman','(791) 885-7537',30,'Ap #231-9280 Eget, Rd.',''),('73610','Maxwell Kent','(854) 271-5294',29,'4553 Nunc Road','foto/f5.jpg'),('92125','Reuben Sexton','(776) 137-1029',33,'838-6445 Sapien. St.','foto/f11.jpg'),('67194','Hyatt Suarez','(931) 167-8446',25,'7079 Dictum Avenue',''),('00657','Nero Wagner','(535) 345-7946',28,'P.O. Box 381, 5793 Id Ave','foto/f5.jpg'),('58163','Carl Joyner','(115) 772-4688',27,'Ap #865-2492 Donec Street','foto/f10.jpg'),('14571','Keegan Anderson','(708) 962-1132',36,'P.O. Box 556, 7486 Facilisis St.','foto/f4.jpg'),('83906','Neville Stephens','(883) 455-9520',30,'Ap #382-9414 Mauris Ave',''),('96696','Paki Kent','(468) 464-1529',39,'689-1070 Orci Rd.','foto/f6.jpg'),('67484','Bevis Clayton','(407) 204-8585',29,'Ap #421-7395 Diam St.','foto/f4.jpg'),('93668','Ali Fry','(970) 455-5795',32,'9064 Quam, Rd.','foto/f1.jpg'),('18441','Lee Crane','(532) 898-6015',35,'748-5375 Elit. Avenue',''),('06156','Nissim Luna','(684) 135-6509',36,'Ap #481-9791 Erat Avenue','foto/f9.jpg'),('79699','Yoshio Patrick','(503) 650-3162',32,'P.O. Box 340, 584 Quis, Rd.','foto/f5.jpg'),('02782','Galvin Farley','(212) 812-8878',26,'P.O. Box 762, 5741 Neque. Rd.','foto/f8.jpg'),('89054','Cadman Robbins','(520) 302-6348',34,'1809 Eros Avenue',''),('86000','James Edwards','(701) 716-1346',32,'Ap #709-6015 Adipiscing Avenue','foto/f2.jpg'),('90453','Gannon Hodge','(610) 620-1482',34,'659-7023 Molestie Road','foto/f6.jpg');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-06-23  9:37:35
