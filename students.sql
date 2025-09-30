/*
SQLyog Community v13.3.0 (64 bit)
MySQL - 12.0.2-MariaDB : Database - students
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`students` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci */;

USE `students`;

/*Table structure for table `id_perfil` */

DROP TABLE IF EXISTS `id_perfil`;

CREATE TABLE `id_perfil` (
  `id_perfil` int(11) NOT NULL,
  `perfil` varchar(50) NOT NULL,
  PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

/*Data for the table `id_perfil` */

insert  into `id_perfil`(`id_perfil`,`perfil`) values (1,'admin'),(2,'manager'),(3,'junior');

/*Table structure for table `id_usuario` */

DROP TABLE IF EXISTS `id_usuario`;

CREATE TABLE `id_usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contrasena` varchar(12) NOT NULL,
  `id_perfil` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_perfil` (`id_perfil`),
  CONSTRAINT `id_usuario_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `id_perfil` (`id_perfil`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

/*Data for the table `id_usuario` */

insert  into `id_usuario`(`id_usuario`,`nombre_usuario`,`contrasena`,`id_perfil`) values (1000,'entrante','0000',3),(10101,'encargado','1111',2),(101010,'jefe','1234',1);

/*Table structure for table `paises` */

DROP TABLE IF EXISTS `paises`;

CREATE TABLE `paises` (
  `id_paises` int(11) NOT NULL AUTO_INCREMENT,
  `pais` char(50) DEFAULT NULL,
  PRIMARY KEY (`id_paises`),
  KEY `id_paises` (`id_paises`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

/*Data for the table `paises` */

insert  into `paises`(`id_paises`,`pais`) values (1,'Alemania'),(2,'Brazil'),(3,'Canadá'),(4,'China'),(5,'Estados Unidos'),(6,'India'),(7,'Indonesia'),(8,'Japón'),(9,'México'),(10,'Rusia');

/*Table structure for table `sexo` */

DROP TABLE IF EXISTS `sexo`;

CREATE TABLE `sexo` (
  `id_sexo` int(11) NOT NULL,
  `descripcion` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_sexo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

/*Data for the table `sexo` */

insert  into `sexo`(`id_sexo`,`descripcion`) values (1,'Masculino'),(2,'Femenino'),(3,'Otro');

/*Table structure for table `student` */

DROP TABLE IF EXISTS `student`;

CREATE TABLE `student` (
  `id_students` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `id_sexo` int(11) DEFAULT NULL,
  `especifique` varchar(255) DEFAULT '',
  `edad` int(3) NOT NULL,
  `nacimiento` date NOT NULL,
  `id_paises` int(11) DEFAULT NULL,
  `telefono` varchar(14) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `domicilio` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `lista` varchar(255) NOT NULL,
  `excel` varchar(255) NOT NULL,
  `fecha_acceso` timestamp NULL DEFAULT current_timestamp(),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_registro` int(11) DEFAULT NULL,
  `fecha_edicion` timestamp NULL DEFAULT NULL,
  `id_usuario_editor` int(11) DEFAULT NULL,
  `visible` int(11) NOT NULL DEFAULT 1,
  `fecha_borrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_borrador` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_students`),
  KEY `id_paises` (`id_paises`),
  KEY `id_sexo` (`id_sexo`),
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`id_paises`) REFERENCES `paises` (`id_paises`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_ibfk_2` FOREIGN KEY (`id_sexo`) REFERENCES `sexo` (`id_sexo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

/*Data for the table `student` */

insert  into `student`(`id_students`,`nombre`,`id_sexo`,`especifique`,`edad`,`nacimiento`,`id_paises`,`telefono`,`correo`,`domicilio`,`foto`,`lista`,`excel`,`fecha_acceso`,`fecha_registro`,`id_usuario_registro`,`fecha_edicion`,`id_usuario_editor`,`visible`,`fecha_borrado`,`id_usuario_borrador`) values (1,'Willie Houston',1,'',18,'2007-04-06',8,'81-6071555766','batz23@yahoo.com','5869 Isabel Travessa, Ponte de Sor, PA 8955','templates/images.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2018-04-11 08:10:57',NULL,'2025-09-19 13:28:11',101010,1,'2025-09-22 11:19:14',101010),(2,'Doris Powell',2,'',23,'2000-03-19',3,'1-5489028700','major.runte@yahoo.ca','Apt. 683 487 Jerome Port, Bernhardfort, WY 59789-4131','templates/ap2d.webp','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2022-11-22 15:05:12',NULL,NULL,NULL,1,'2025-09-22 11:29:29',101010),(3,'Milo Barrett',1,'',34,'1991-10-25',1,'49-1531045430','anton_rittweg63@gmail.com','Zimmer 620 Sonderburger Str. 1, Malikberg, BW 17608','templates/5560191.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2024-11-18 19:50:24',NULL,NULL,NULL,1,'2025-02-13 16:55:08',NULL),(4,'Benjamin Hurst',1,'',23,'2002-08-24',5,'1-3052556891','keegan.oreilly27@yahoo.com','Suite 917 6808 Annie Forest, Lake Shelachester, GA 56230','templates/red-fox.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2025-01-07 23:09:08',NULL,NULL,NULL,1,'2025-07-17 11:16:48',NULL),(5,'Caiden Chen',3,'she/they',17,'2008-04-15',2,'55-3593786163','sara.albuquerque@yahoo.com','Puerta 831 Salida Laura, 98, Paterna, Bal 19389','templates/nrucodkclyva.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2020-06-30 10:25:03',NULL,NULL,NULL,1,'2024-01-28 04:48:41',NULL),(6,'Caitlyn Holden',2,'',21,'2004-12-22',10,'7-9355293637','yurii63@hotmail.com','Apt. 590 al. Łukasik 970, Mława, PM 65-288','templates/red-fox.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2021-02-17 12:36:41',NULL,NULL,NULL,1,'2022-11-02 09:22:16',NULL),(7,'Kelvin Gardner',1,'',20,'2005-03-07',6,'91-7533871307','rym_aarf24@hotmail.com','480 Ferne Shores, Auermouth, KS 81250','templates/nrucodkclyva.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2019-07-20 20:15:24',NULL,NULL,NULL,1,'2020-07-10 06:08:55',NULL),(8,'Enya Nash',3,'they/them',17,'2008-07-09',7,'62-8764351921','darman.wastuti49@yahoo.com','Jl. Hayamwuruk No. 77, Intan Jaya, JW 41660','templates/ap2d.webp','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2023-03-01 14:06:09',NULL,NULL,NULL,1,'2024-06-06 12:56:00',NULL),(9,'Tina Chandler',2,'',31,'1994-01-10',4,'86-17405689715','yenrh_62@hotmail.com','62630 Peggie Port, Bartonberg, CO 43458-6975','templates/cute.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2021-10-18 05:00:55',NULL,NULL,NULL,1,'2023-03-13 05:16:43',NULL),(10,'Haroon Hudson',1,'',45,'1979-11-14',9,'52-4953941761','abraham60@nearbpo.com','Parque César 85 Esc. 828, Benalmádena, Ext 60878','templates/red-fox.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2020-05-29 11:49:00',NULL,NULL,NULL,1,'2022-10-09 07:36:49',NULL),(11,'Faizan Boone',2,'',20,'2005-05-23',9,'52-16294146737','mariana_gil90@gmail.com','3 OG Sandstr. 45c, Jeremiegrün, HE 50723','templates/cute.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2021-04-04 08:33:06',NULL,NULL,NULL,1,'2023-08-30 21:57:19',NULL),(12,'Eleni Wilkinson',3,'she/he',14,'2011-07-01',5,'1-3771516979','mae.von87@hotmail.com','Jl. Rasuna Said No. 65, Jakarta Barat, YO 72880','templates/5560191.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2017-12-27 18:12:21',NULL,NULL,NULL,1,'2018-03-09 10:40:52',NULL),(13,'Haseeb Richmond',1,'',18,'2007-08-14',6,'91-7477473348','shymaa_slah@gmail.com','Lote 43 2434 Carvalho Viela, Mangualde, MA 3117','templates/images.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2019-01-21 19:29:00',NULL,NULL,NULL,1,'2024-01-01 01:47:08',NULL),(14,'Penny Benjamin',2,'',16,'2009-03-06',10,'7-9926911701','mikhail.evdokimov@yandex.ru','al. Golec 173, Bytom Odrzański, PK 28-156','templates/ap2d.webp','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2025-05-20 15:59:17',NULL,NULL,NULL,1,'2025-09-05 14:32:42',NULL),(15,'Omari Lowe',3,'he/they',37,'1988-02-18',8,'81-8074983691','hassie52@hotmail.com','19056 Silas Drive, Rockyfurt, MD 41345','templates/nrucodkclyva.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2021-06-14 22:30:45',NULL,NULL,NULL,1,'2022-11-20 15:47:14',NULL),(16,'Nicholas Daniel',1,'',34,'1991-03-31',4,'86-18580687830','portjtg.17@gmail.com\r','al. Skalski 664, Zambrów, LS 95-860','templates/5560191.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2023-08-09 07:26:53',NULL,NULL,NULL,1,'2023-12-20 16:25:50',NULL),(17,'Kyley Parker',2,'',54,'1971-12-18',7,'62-8370296292','gaiman56@gmail.co.id','Jl. Gatot Soebroto No. 83, Cilacap, SA 63944','templates/cute.jpg','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2019-09-16 11:05:52',NULL,NULL,NULL,1,'2021-07-08 19:45:01',NULL),(18,'Jake Vasquez',1,'',21,'2004-06-22',3,'1-990989867','kiel49@gmail.com','2825 Wiza Port, Hilpertchester, CT 02715-8367','templates/red-fox.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2020-05-09 02:57:16',NULL,NULL,NULL,1,'2021-04-27 10:29:44',NULL),(19,'Lenny Holmes',3,'they/them',23,'2002-04-26',2,'55-5471014777','liz79@hotmail.com','Edificio Elisa 29 Esc. 358, Cuenta, Man 63032','templates/ap2d.webp','templates/lista.txt','templates/lista.csv','0000-00-00 00:00:00','2024-01-20 05:26:08',NULL,NULL,NULL,1,'2025-06-09 20:30:27',NULL),(20,'Victor Hayden',1,'',19,'2006-01-12',1,'49-13023754','talea33@hotmail.com','Graf-Galen-Platz 73, Bad Ninohagen, BB 46183','templates/5560191.jpg','templates/compras.txt','templates/lista.xlsx','0000-00-00 00:00:00','2022-10-19 16:36:11',NULL,'2025-09-18 10:48:18',101010,1,'2023-03-18 15:44:12',NULL),(57,'test',1,'',1,'2025-09-11',9,'52-1111111111','example@gmail.com','test','./uploads/foto_1757629969.jpg','uploads/lista_1757954405.txt','uploads/excel_1757629969.csv','2025-09-11 16:32:49','2025-09-11 16:32:49',101010,'2025-09-11 16:34:46',101010,0,'2025-09-22 13:23:02',101010),(58,'example',1,'',1,'2025-09-15',1,'49-798645312','example@hotmail.com','example','./uploads/foto_1758213595.jpg','uploads/lista_1757954405.txt','uploads/excel_1758213855.xlsx','2025-09-15 10:40:05','2025-09-15 10:40:05',101010,'2025-09-18 10:48:27',101010,1,'2025-09-22 13:21:20',101010),(59,'1',2,'',99,'2025-09-22',9,'52-5647865478','yufreghyufe@gmail.com','bfdvshjfsd','./uploads/foto_1758569034.jpg','./uploads/lista_1758569034.txt','./uploads/excel_1758569034.csv','2025-09-22 13:23:54','2025-09-22 13:23:54',101010,NULL,NULL,1,'2025-09-22 13:23:54',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
