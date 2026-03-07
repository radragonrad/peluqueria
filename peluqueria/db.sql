/*
Navicat MySQL Data Transfer

Source Server         : Wikitic
Source Server Version : 50733
Source Host           : localhost:3306
Source Database       : peluqueria

Target Server Type    : MYSQL
Target Server Version : 50733
File Encoding         : 65001

Date: 2026-02-16 07:30:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for configuracion
-- ----------------------------
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL DEFAULT '1',
  `horario_lunes_a_viernes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '9:30 - 20:00',
  `horario_sabado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '9:30 - 14:00',
  `horario_domingo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Cerrado',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of configuracion
-- ----------------------------

-- ----------------------------
-- Table structure for horario
-- ----------------------------
DROP TABLE IF EXISTS `horario`;
CREATE TABLE `horario` (
  `id_dia` int(1) NOT NULL,
  `dia_nombre` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abierto` tinyint(1) DEFAULT '1',
  `h_apertura_1` time DEFAULT '09:30:00',
  `h_cierre_1` time DEFAULT '14:00:00',
  `h_apertura_2` time DEFAULT '16:00:00',
  `h_cierre_2` time DEFAULT '20:00:00',
  PRIMARY KEY (`id_dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of horario
-- ----------------------------
INSERT INTO `horario` VALUES ('1', 'Lunes', '1', '09:30:00', '14:00:00', '16:00:00', '20:00:00');
INSERT INTO `horario` VALUES ('2', 'Martes', '1', '09:30:00', '20:00:00', null, null);
INSERT INTO `horario` VALUES ('3', 'Miércoles', '1', '09:30:00', '20:00:00', null, null);
INSERT INTO `horario` VALUES ('4', 'Jueves', '1', '09:30:00', '20:00:00', null, null);
INSERT INTO `horario` VALUES ('5', 'Viernes', '1', '09:30:00', '20:00:00', null, null);
INSERT INTO `horario` VALUES ('6', 'Sábado', '1', '09:30:00', '14:00:00', null, null);
INSERT INTO `horario` VALUES ('7', 'Domingo', '0', null, null, null, null);

-- ----------------------------
-- Table structure for horario_excepciones
-- ----------------------------
DROP TABLE IF EXISTS `horario_excepciones`;
CREATE TABLE `horario_excepciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `cerrado` tinyint(1) DEFAULT '1',
  `solo_tramo` tinyint(1) DEFAULT '0',
  `h_inicio` time DEFAULT NULL,
  `h_fin` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of horario_excepciones
-- ----------------------------
INSERT INTO `horario_excepciones` VALUES ('1', '2026-02-19', 'Dia libre', '1', '0', null, null);
INSERT INTO `horario_excepciones` VALUES ('2', '2026-02-20', 'Cerrado por la tarde', '1', '1', '15:00:00', '20:00:00');

-- ----------------------------
-- Table structure for peluqueros
-- ----------------------------
DROP TABLE IF EXISTS `peluqueros`;
CREATE TABLE `peluqueros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `avatar` varchar(100) CHARACTER SET utf16 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of peluqueros
-- ----------------------------
INSERT INTO `peluqueros` VALUES ('1', 'Rúben Gutiérrez', 'Barbería y estilo masculino', '1', 'raul.png');
INSERT INTO `peluqueros` VALUES ('2', 'María José', 'Coloración y tratamientos capilares', '1', 'default-avatar.png');
INSERT INTO `peluqueros` VALUES ('3', 'Carlos Pérez', 'Cortes modernos y tendencias', '1', 'default.png');

-- ----------------------------
-- Table structure for reservas
-- ----------------------------
DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `peluquero_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `servicio_id` (`servicio_id`),
  KEY `idx_fecha_hora` (`fecha`,`hora`),
  KEY `idx_user_id` (`user_id`),
  KEY `peluquero_id` (`peluquero_id`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`peluquero_id`) REFERENCES `peluqueros` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of reservas
-- ----------------------------
INSERT INTO `reservas` VALUES ('3', '10', '3', '2', '2026-02-14', '10:30:00', '2026-02-14 09:35:33');
INSERT INTO `reservas` VALUES ('4', '10', '7', '1', '2026-02-14', '10:20:00', '2026-02-14 10:21:22');
INSERT INTO `reservas` VALUES ('7', '10', '2', '1', '2026-02-20', '09:30:00', '2026-02-14 13:48:15');
INSERT INTO `reservas` VALUES ('8', '10', '1', '1', '2026-02-20', '10:15:00', '2026-02-14 20:12:59');
INSERT INTO `reservas` VALUES ('9', '10', '7', '1', '2026-02-20', '11:45:00', '2026-02-14 20:14:04');
INSERT INTO `reservas` VALUES ('10', '10', '6', '1', '2026-02-20', '11:00:00', '2026-02-14 20:14:14');
INSERT INTO `reservas` VALUES ('11', '10', '3', '3', '2026-02-27', '11:15:00', '2026-02-14 22:36:26');

-- ----------------------------
-- Table structure for servicios
-- ----------------------------
DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `icono` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `duracion_min` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of servicios
-- ----------------------------
INSERT INTO `servicios` VALUES ('1', 'Corte Clásico', 'Corte limpio y preciso para hombre o niño. Ideal para el día a día: rápido, ordenado y adaptado a tu estilo.', 'corte_clasico.svg', '12.00', '30', '1');
INSERT INTO `servicios` VALUES ('2', 'Corte + Barba', 'Corte de pelo + perfilado de barba con cuchilla. Todo en una sola cita: pelo impecable y barba definida, con acabado profesional.', 'corte_barba.svg', '18.00', '45', '1');
INSERT INTO `servicios` VALUES ('3', 'Arreglo de Barba', 'Perfilado completo de barba con cuchilla. Definimos contornos.', 'arreglo_barba.svg', '10.00', '15', '1');
INSERT INTO `servicios` VALUES ('5', 'Decoloración', 'Aclarado suave del vello facial o corporal. Ideal para cejas, patillas o vellos finos. Resultado natural y sin irritación.', 'decoloracion.svg', '15.00', '45', '1');
INSERT INTO `servicios` VALUES ('6', 'Mechas', 'Mechas naturales o de contraste. Iluminamos tu look con reflejos personalizados: californianas, babylights o mechas marcadas.', 'mechas.svg', '25.00', '45', '1');
INSERT INTO `servicios` VALUES ('7', 'Servicio VIP', 'Reserva este servicio y disfruta de una experiencia premium: corte o color personalizado, masaje capilar relajante, tratamiento hidratante y atención 100% dedicada. Ideal para regalarte un rato de relax o prepararte para una ocasión especial.', 'servicio_vip.svg', '35.00', '50', '1');
INSERT INTO `servicios` VALUES ('11', 'corte niño', 'Corte para menores de 10 años', 'arreglo_barba.svg', '15.00', '20', '1');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '0',
  `token_activacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_activacion` datetime DEFAULT NULL,
  `rol` enum('usuario','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('9', 'radragon_rad', 'radragon.rad@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$aXN4M3BMYXI5Szhsa1VycQ$Q+52YlpwcBXS7EUY1JG2GT40htehWFEu+oML8qRpq0Y', '::1', '2026-02-08 09:59:25', '1', null, '2026-02-08 09:59:33', 'admin');
INSERT INTO `users` VALUES ('10', 'hadesinfer', 'hadesinfer@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RENwV2x2WlVVYll6ZHdDYw$+OVAVE4bFedMHdjxd4XwKagEREeYEZQHKyQyrkgNdDo', '::1', '2026-02-08 12:45:22', '1', null, '2026-02-08 12:45:55', 'usuario');
