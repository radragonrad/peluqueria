-- Horario individual por peluquero.
-- Cada peluquero tiene sus propias 7 filas (una por día). Si `abierto` = 0
-- ese día se considera cerrado para ese peluquero, independientemente del
-- horario general de la tienda (tabla `horarios`).
CREATE TABLE IF NOT EXISTS `horarios_peluquero` (
  `id` int NOT NULL AUTO_INCREMENT,
  `peluquero_id` int NOT NULL,
  `id_dia` int NOT NULL,
  `dia_semana` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abierto` tinyint(1) NOT NULL DEFAULT '0',
  `h_apertura_1` time DEFAULT NULL,
  `h_cierre_1` time DEFAULT NULL,
  `h_apertura_2` time DEFAULT NULL,
  `h_cierre_2` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_peluquero_dia` (`peluquero_id`,`id_dia`),
  CONSTRAINT `fk_horpel_peluquero` FOREIGN KEY (`peluquero_id`) REFERENCES `peluqueros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
