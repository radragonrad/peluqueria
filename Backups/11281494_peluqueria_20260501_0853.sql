-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: PMYSQL196.dns-servicio.com:3306
-- Tiempo de generación: 01-05-2026 a las 08:53:22
-- Versión del servidor: 8.4.8
-- Versión de PHP: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `11281494_peluqueria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int NOT NULL DEFAULT '1',
  `horario_lunes_a_viernes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '9:30 - 20:00',
  `horario_sabado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '9:30 - 14:00',
  `horario_domingo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Cerrado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones_usuario`
--

CREATE TABLE `cupones_usuario` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `promocion_id` int DEFAULT NULL,
  `cupones_actuales` int DEFAULT '0',
  `total_historico` int DEFAULT '0',
  `premios_canjeados` int DEFAULT '0',
  `ultima_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#3498db',
  `creado_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_dia` int NOT NULL,
  `dia_semana` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abierto` tinyint(1) DEFAULT '1',
  `h_apertura_1` time DEFAULT '09:30:00',
  `h_cierre_1` time DEFAULT '14:00:00',
  `h_apertura_2` time DEFAULT '16:00:00',
  `h_cierre_2` time DEFAULT '20:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_dia`, `dia_semana`, `abierto`, `h_apertura_1`, `h_cierre_1`, `h_apertura_2`, `h_cierre_2`) VALUES
(1, 'Lunes', 1, '10:30:00', '14:00:00', '16:30:00', '21:00:00'),
(2, 'Martes', 1, '10:30:00', '14:00:00', '16:30:00', '21:00:00'),
(3, 'Miércoles', 1, '10:30:00', '14:00:00', '16:30:00', '21:00:00'),
(4, 'Jueves', 1, '10:30:00', '14:00:00', '16:30:00', '21:00:00'),
(5, 'Viernes', 1, '10:30:00', '14:00:00', '16:30:00', '21:00:00'),
(6, 'Sábado', 1, '10:30:00', '14:30:00', NULL, NULL),
(7, 'Domingo', 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario_excepciones`
--

CREATE TABLE `horario_excepciones` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cerrado` tinyint(1) DEFAULT '1',
  `solo_tramo` tinyint(1) DEFAULT '0',
  `h_inicio` time DEFAULT NULL,
  `h_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horario_excepciones`
--

INSERT INTO `horario_excepciones` (`id`, `fecha`, `descripcion`, `cerrado`, `solo_tramo`, `h_inicio`, `h_fin`) VALUES
(17, '2026-05-01', 'FESTIVO', 1, 0, NULL, NULL),
(18, '2026-05-02', 'FESTIVO', 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_promociones`
--

CREATE TABLE `logs_promociones` (
  `id` int NOT NULL,
  `reserva_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `promocion_id` int NOT NULL,
  `tipo_movimiento` enum('SUMA','REVERSION') NOT NULL,
  `tipo_promo` varchar(50) DEFAULT NULL,
  `detalles` text,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `archivo` varchar(150) DEFAULT NULL,
  `detalle` text,
  `email_enviado` tinyint(1) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `logs_sistema`
--

INSERT INTO `logs_sistema` (`id`, `usuario_id`, `accion`, `archivo`, `detalle`, `email_enviado`, `ip_address`, `fecha_registro`) VALUES
(1, 83, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"107\",\"cliente_nombre\":\"Nuria gil \",\"cliente_email\":\"cuestacng@gmail.com\",\"servicio\":\"Corte + Barba\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"duracion_min\":45,\"google_event_id\":\"hevbngu6fe1321iu643j8pp3nc\"}', 1, '185.13.202.154', '2026-04-25 19:50:19'),
(2, 83, 'CANCELACION_RESERVA', 'cancelar_reserva.php', '{\"reserva_id\":107,\"cliente_nombre\":\"Nuria gil \",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"google_event_id\":\"hevbngu6fe1321iu643j8pp3nc\",\"google_borrado\":true}', 0, '185.13.202.154', '2026-04-25 19:53:41'),
(3, 83, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"108\",\"cliente_nombre\":\"Nuria gil \",\"cliente_email\":\"cuestacng@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"duracion_min\":30,\"google_event_id\":\"vjpjle9onuddm7r64sajjglhoo\"}', 1, '185.13.202.154', '2026-04-25 19:54:14'),
(4, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"84\",\"nombre_cliente\":\"Ivan Gutiérrez\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@ivan.gutiérrez\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.100.43', '2026-04-25 20:31:45'),
(5, 43, 'ALTA_CLIENTE_ERROR', 'registrar_cliente_admin.php', '{\"nombre_intentado\":\"Ivan Gutiérrez \",\"correo_intentado\":\"\",\"error_mensaje\":\"SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'sinemail@ivan.gutiérrez\' for key \'usuarios.email\'\"}', 0, '79.117.100.43', '2026-04-25 20:33:07'),
(6, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"86\",\"nombre_cliente\":\"Ivan Gutiérrez ibañez\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@ivan.gutiérrez.ibañez\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.100.43', '2026-04-25 20:33:19'),
(7, 83, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"112\",\"cliente_nombre\":\"Nuria gil \",\"cliente_email\":\"cuestacng@gmail.com\",\"servicio\":\"Arreglo de Barba\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-27\",\"hora\":\"11:30:00\",\"duracion_min\":15,\"google_event_id\":\"qsc9vojutokhnp9ol6sk4v25mo\"}', 1, '86.106.2.183', '2026-04-25 23:46:42'),
(8, 83, 'CANCELACION_RESERVA', 'cancelar_reserva.php', '{\"reserva_id\":108,\"cliente_nombre\":\"Nuria gil \",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"google_event_id\":\"vjpjle9onuddm7r64sajjglhoo\",\"google_borrado\":true}', 0, '86.106.2.183', '2026-04-25 23:47:04'),
(9, 83, 'CANCELACION_RESERVA', 'cancelar_reserva.php', '{\"reserva_id\":112,\"cliente_nombre\":\"Nuria gil \",\"servicio\":\"Arreglo de Barba\",\"fecha\":\"2026-04-27\",\"hora\":\"11:30:00\",\"google_event_id\":\"qsc9vojutokhnp9ol6sk4v25mo\",\"google_borrado\":true}', 0, '86.106.2.183', '2026-04-25 23:47:14'),
(10, 87, 'REGISTRO', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"nombre\":\"Rad Usuario\",\"telefono\":\"677377938\",\"email_enviado\":\"sí\"}', 1, '213.194.150.225', '2026-04-26 10:11:12'),
(11, 88, 'REGISTRO', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"nombre\":\"Rad Usuario\",\"telefono\":\"677377938\",\"email_enviado\":\"sí\"}', 1, '213.194.150.225', '2026-04-26 10:13:05'),
(12, 88, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"213.194.150.225\"}', 0, '213.194.150.225', '2026-04-26 10:13:23'),
(13, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.100.43', '2026-04-26 11:19:45'),
(14, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"89\",\"nombre_cliente\":\"Tomás entrénate\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@tomás.entrénate\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.100.43', '2026-04-26 11:21:20'),
(15, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"90\",\"nombre_cliente\":\"Aleman\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@aleman\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.100.43', '2026-04-26 11:22:07'),
(16, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"91\",\"nombre_cliente\":\"Tristancho\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@tristancho\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.100.43', '2026-04-26 11:23:08'),
(17, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"92\",\"nombre_cliente\":\"Juan Diego\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@juan.diego\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.100.43', '2026-04-26 11:24:07'),
(18, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"93\",\"nombre_cliente\":\"Esther Samuel baloncesto\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@esther.samuel.baloncesto\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.100.43', '2026-04-26 11:25:06'),
(19, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"94\",\"nombre_cliente\":\"Queso\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@queso\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.100.43', '2026-04-26 11:29:02'),
(20, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.100.43', '2026-04-26 11:56:44'),
(21, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.225', '2026-04-26 11:57:42'),
(22, 56, 'LOGIN', 'auth.php', '{\"email\":\"Jperezfernandez981@gmail.com\",\"usuario\":\"jperezfernandez981\",\"rol\":\"usuario\"}', 0, '31.4.220.43', '2026-04-26 13:10:25'),
(23, 56, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"126\",\"cliente_nombre\":\"Joaquin Perez Fernandez \",\"cliente_email\":\"Jperezfernandez981@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-04\",\"hora\":\"19:00:00\",\"duracion_min\":30,\"google_event_id\":\"c6oil1ssn1675jjrvec34nd9h4\"}', 1, '31.4.220.43', '2026-04-26 13:28:35'),
(24, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"aarongarfillol@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.181.59', '2026-04-26 14:15:51'),
(25, 95, 'REGISTRO', 'auth.php', '{\"email\":\"aarongarfillol@gmail.com\",\"nombre\":\"Aaron Garcia Fillol\",\"telefono\":\"610905272\",\"email_enviado\":\"sí\"}', 1, '46.6.181.59', '2026-04-26 14:16:19'),
(26, 95, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"46.6.181.59\"}', 0, '46.6.181.59', '2026-04-26 14:16:37'),
(27, 95, 'LOGIN', 'auth.php', '{\"email\":\"aarongarfillol@gmail.com\",\"usuario\":\"aarongarfillol\",\"rol\":\"usuario\"}', 0, '46.6.181.59', '2026-04-26 14:16:50'),
(28, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivnagutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:35:49'),
(29, 96, 'REGISTRO', 'auth.php', '{\"email\":\"ivnagutierrezfga24@gmail.com\",\"nombre\":\"Ivan Gutierrez\",\"telefono\":\"645325730\",\"email_enviado\":\"sí\"}', 1, '79.116.246.218', '2026-04-26 14:36:22'),
(30, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:48'),
(31, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:50'),
(32, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:50'),
(33, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:50'),
(34, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:51'),
(35, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:51'),
(36, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:51'),
(37, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:51'),
(38, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:51'),
(39, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:52'),
(40, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:52'),
(41, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:52'),
(42, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:52'),
(43, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:52'),
(44, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:53'),
(45, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:53'),
(46, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:53'),
(47, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:53'),
(48, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:53'),
(49, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:54'),
(50, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:54'),
(51, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:54'),
(52, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:55'),
(53, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:56'),
(54, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:36:57'),
(55, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:37:08'),
(56, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:37:16'),
(57, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:37:17'),
(58, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:37:17'),
(59, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.246.218', '2026-04-26 14:37:17'),
(60, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 15:10:55'),
(61, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 16:17:19'),
(62, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ulisesholgado59@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '194.220.58.43', '2026-04-26 16:17:43'),
(63, 97, 'REGISTRO', 'auth.php', '{\"email\":\"ulisesholgado20@gmail.com\",\"nombre\":\"Ulises Holgado Rodriguez \",\"telefono\":\"615611783\",\"email_enviado\":\"sí\"}', 1, '194.220.58.43', '2026-04-26 16:18:39'),
(64, 98, 'REGISTRO', 'auth.php', '{\"email\":\"holgadito20@gmail.com\",\"nombre\":\"ulises holgado rodriguez \",\"telefono\":\"615611783\",\"email_enviado\":\"sí\"}', 1, '194.220.58.43', '2026-04-26 16:19:45'),
(65, 98, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"194.220.58.43\"}', 0, '194.220.58.43', '2026-04-26 16:20:01'),
(66, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"658a6d02...\"}', 0, '142.250.32.8', '2026-04-26 16:20:02'),
(67, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"658a6d02...\"}', 0, '74.125.210.173', '2026-04-26 16:20:02'),
(68, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"658a6d02...\"}', 0, '74.125.210.171', '2026-04-26 16:20:02'),
(69, 98, 'LOGIN', 'auth.php', '{\"email\":\"holgadito20@gmail.com\",\"usuario\":\"holgadito20\",\"rol\":\"usuario\"}', 0, '194.220.58.43', '2026-04-26 16:20:15'),
(70, 99, 'REGISTRO', 'auth.php', '{\"email\":\"malalemadrid@gmail.com\",\"nombre\":\"Manuel Ruiz\",\"telefono\":\"623018914\",\"email_enviado\":\"sí\"}', 1, '37.26.251.83', '2026-04-26 17:31:31'),
(71, 99, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"37.26.251.83\"}', 0, '37.26.251.83', '2026-04-26 17:31:58'),
(72, 99, 'LOGIN', 'auth.php', '{\"email\":\"malalemadrid@gmail.com\",\"usuario\":\"malalemadrid\",\"rol\":\"usuario\"}', 0, '37.26.251.83', '2026-04-26 17:32:09'),
(73, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 17:38:12'),
(74, 100, 'REGISTRO', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"nombre\":\"Jose Antonio Tristancho Castillo \",\"telefono\":\"600011218\",\"email_enviado\":\"sí\"}', 1, '47.63.248.70', '2026-04-26 19:33:42'),
(75, 100, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"47.63.248.70\"}', 0, '47.63.248.70', '2026-04-26 19:33:53'),
(76, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '47.63.248.70', '2026-04-26 19:34:24'),
(77, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '47.63.248.70', '2026-04-26 19:34:32'),
(78, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '47.63.248.70', '2026-04-26 19:34:36'),
(79, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '47.63.248.70', '2026-04-26 19:34:43'),
(80, 100, 'LOGIN', 'auth.php', '{\"email\":\"joseantoniotriscas@gmail.com\",\"usuario\":\"joseantoniotriscas\",\"rol\":\"usuario\"}', 0, '47.63.248.70', '2026-04-26 19:34:49'),
(81, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 20:34:37'),
(82, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '85.52.163.241', '2026-04-26 20:38:21'),
(83, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '85.52.163.241', '2026-04-26 20:38:42'),
(84, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '85.52.163.241', '2026-04-26 20:38:43'),
(85, 101, 'REGISTRO', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"nombre\":\"Andrés Zamorano \",\"telefono\":\"678418789\",\"email_enviado\":\"sí\"}', 1, '85.52.163.241', '2026-04-26 20:39:30'),
(86, 101, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '85.52.163.241', '2026-04-26 20:40:05'),
(87, 101, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"85.52.163.241\"}', 0, '85.52.163.241', '2026-04-26 20:40:23'),
(88, 102, 'REGISTRO', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"nombre\":\"Fernando Galan Gelo\",\"telefono\":\"640118796\",\"email_enviado\":\"sí\"}', 1, '86.127.226.30', '2026-04-26 20:40:24'),
(89, 101, 'LOGIN', 'auth.php', '{\"email\":\"andreszamoranomartin@gmail.com\",\"usuario\":\"andreszamoranomartin\",\"rol\":\"usuario\"}', 0, '85.52.163.241', '2026-04-26 20:40:33'),
(90, 102, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '86.127.226.30', '2026-04-26 20:40:36'),
(91, 102, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '86.127.226.30', '2026-04-26 20:40:47'),
(92, 102, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '86.127.226.30', '2026-04-26 20:40:56'),
(93, 102, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"86.127.226.30\"}', 0, '86.127.226.30', '2026-04-26 20:41:05'),
(94, 102, 'LOGIN', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"usuario\":\"fernandogalangelo\",\"rol\":\"usuario\"}', 0, '86.127.226.30', '2026-04-26 20:41:13'),
(95, 101, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"127\",\"cliente_nombre\":\"Andrés Zamorano \",\"cliente_email\":\"andreszamoranomartin@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-30\",\"hora\":\"19:45:00\",\"duracion_min\":30,\"google_event_id\":\"ckkmmkfgkp3tadjoai73m8p6jk\"}', 1, '85.52.163.241', '2026-04-26 20:42:50'),
(96, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.225', '2026-04-26 20:50:28'),
(97, 102, 'LOGIN', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"usuario\":\"fernandogalangelo\",\"rol\":\"usuario\"}', 0, '79.117.67.203', '2026-04-26 20:50:39'),
(98, 102, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"128\",\"cliente_nombre\":\"Fernando Galan Gelo\",\"cliente_email\":\"fernandogalangelo@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-27\",\"hora\":\"12:00:00\",\"duracion_min\":30,\"google_event_id\":\"dicuniqcbn4e9dhdcdrg4o514s\"}', 1, '79.117.67.203', '2026-04-26 20:51:37'),
(99, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 20:52:56'),
(100, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.225', '2026-04-26 21:04:42'),
(101, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.225', '2026-04-26 21:04:54'),
(102, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.225', '2026-04-26 21:24:50'),
(103, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 22:02:15'),
(104, 99, 'LOGIN', 'auth.php', '{\"email\":\"malalemadrid@gmail.com\",\"usuario\":\"malalemadrid\",\"rol\":\"usuario\"}', 0, '37.26.251.83', '2026-04-26 22:12:51'),
(105, 58, 'RECUPERAR_PASSWORD', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"email_enviado\":\"sí\"}', 1, '31.4.221.49', '2026-04-26 22:14:57'),
(106, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.221.49', '2026-04-26 22:16:18'),
(107, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-26 22:50:48'),
(108, 103, 'REGISTRO', 'auth.php', '{\"email\":\"daviddelgadofernandezsev2001@gmail.com\",\"nombre\":\"David Delgado Fernández\",\"telefono\":\"633750230\",\"email_enviado\":\"sí\"}', 1, '95.18.50.22', '2026-04-26 23:31:21'),
(109, 103, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"95.18.50.22\"}', 0, '95.18.50.22', '2026-04-26 23:31:36'),
(110, 103, 'LOGIN', 'auth.php', '{\"email\":\"daviddelgadofernandezsev2001@gmail.com\",\"usuario\":\"daviddelgadofernandezsev2001\",\"rol\":\"usuario\"}', 0, '95.18.50.22', '2026-04-26 23:31:51'),
(111, 103, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"129\",\"cliente_nombre\":\"David Delgado Fernández\",\"cliente_email\":\"daviddelgadofernandezsev2001@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-08\",\"hora\":\"16:30:00\",\"duracion_min\":30,\"google_event_id\":\"r5a4dabct9ijvnjtdnlp45h70s\"}', 1, '95.18.50.22', '2026-04-26 23:32:36'),
(112, 104, 'REGISTRO', 'auth.php', '{\"email\":\"alvarocaro93@gmail.com\",\"nombre\":\"Álvaro caro pineda\",\"telefono\":\"691606974\",\"email_enviado\":\"sí\"}', 1, '46.6.206.253', '2026-04-27 07:34:01'),
(113, 104, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alvarocaro93@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '46.6.206.253', '2026-04-27 07:34:12'),
(114, 104, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alvarocaro93@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '46.6.206.253', '2026-04-27 07:34:28'),
(115, 104, 'REGISTRO', 'auth.php', '{\"email\":\"alvarocaro93@gmail.com\",\"nombre\":\"Alvaro caro\",\"telefono\":\"691606974\",\"email_enviado\":\"sí\"}', 1, '46.6.206.253', '2026-04-27 08:35:04'),
(116, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"62968e52...\"}', 0, '46.6.206.253', '2026-04-27 08:35:43'),
(117, 104, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"46.6.206.253\"}', 0, '46.6.206.253', '2026-04-27 08:36:10'),
(118, 104, 'LOGIN', 'auth.php', '{\"email\":\"alvarocaro93@gmail.com\",\"usuario\":\"alvarocaro93\",\"rol\":\"usuario\"}', 0, '46.6.206.253', '2026-04-27 08:36:24'),
(119, 63, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"130\",\"cliente_nombre\":\"Adrián Perejón Torres \",\"cliente_email\":\"adrianpere05@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-30\",\"hora\":\"19:15:00\",\"duracion_min\":30,\"google_event_id\":\"jpu14igbdffbnp60stj6a9sjh8\"}', 1, '79.117.92.252', '2026-04-27 09:11:45'),
(120, 56, 'LOGIN', 'auth.php', '{\"email\":\"Jperezfernandez981@gmail.com\",\"usuario\":\"jperezfernandez981\",\"rol\":\"usuario\"}', 0, '31.4.220.43', '2026-04-27 10:10:57'),
(121, 56, 'LOGIN', 'auth.php', '{\"email\":\"Jperezfernandez981@gmail.com\",\"usuario\":\"jperezfernandez981\",\"rol\":\"usuario\"}', 0, '31.4.220.43', '2026-04-27 10:11:39'),
(122, 58, 'RECUPERAR_PASSWORD', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"email_enviado\":\"sí\"}', 1, '79.117.227.229', '2026-04-27 10:28:24'),
(123, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.227.229', '2026-04-27 10:28:53'),
(124, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 10:29:13'),
(125, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":116,\"nuevo_estado\":\"ANULADA LOCAL\",\"cliente\":\"Tristancho\",\"cliente_email\":\"sinemail@tristancho\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"18:00:00\",\"metodo_pago\":null,\"motivo\":\"a\",\"google_borrado\":\"sí\"}', 0, '79.117.227.229', '2026-04-27 10:30:56'),
(126, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 10:33:16'),
(127, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 10:47:23'),
(128, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":113,\"nuevo_estado\":\"ANULADA LOCAL\",\"cliente\":\"CORREA\",\"cliente_email\":\"sinemail@correa\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"metodo_pago\":null,\"motivo\":\"-\",\"google_borrado\":\"sí\"}', 0, '79.117.227.229', '2026-04-27 11:05:37'),
(129, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":113,\"nuevo_estado\":\"ANULADA LOCAL\",\"cliente\":\"CORREA\",\"cliente_email\":\"sinemail@correa\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"10:30:00\",\"metodo_pago\":null,\"motivo\":\"-\",\"google_borrado\":\"sí\"}', 0, '79.117.227.229', '2026-04-27 11:05:38'),
(130, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":132,\"nuevo_estado\":\"ANULADA LOCAL\",\"cliente\":\"Jose Antonio Tristancho Castillo \",\"cliente_email\":\"joseantoniotriscas@gmail.com\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-28\",\"hora\":\"19:45:00\",\"metodo_pago\":null,\"motivo\":\"mas tarde\",\"google_borrado\":\"sí\"}', 0, '79.117.227.229', '2026-04-27 11:19:36'),
(131, 102, 'LOGIN', 'auth.php', '{\"email\":\"fernandogalangelo@gmail.com\",\"usuario\":\"fernandogalangelo\",\"rol\":\"usuario\"}', 0, '79.117.67.203', '2026-04-27 12:26:22'),
(132, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 13:01:02'),
(133, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":114,\"cliente\":\"Tomás entrénate \",\"antes_fecha\":\"2026-04-27\",\"antes_hora\":\"13:30:00\",\"antes_servicio\":\"Corte\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-27\",\"nueva_hora\":\"17:30\",\"nuevo_servicio\":\"Corte\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.229', '2026-04-27 13:01:21'),
(134, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":128,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Fernando Galan Gelo\",\"cliente_email\":\"fernandogalangelo@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"12:00:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-27 13:12:52'),
(135, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":115,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Aleman\",\"cliente_email\":\"sinemail@aleman\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"12:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-27 13:13:03'),
(136, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 13:46:27'),
(137, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.197', '2026-04-27 13:46:41'),
(138, 105, 'REGISTRO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"nombre\":\"Francisco Javier Garcia\",\"telefono\":\"605259591\",\"email_enviado\":\"sí\"}', 1, '37.26.251.60', '2026-04-27 14:03:07'),
(139, 105, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '37.26.251.60', '2026-04-27 14:03:17'),
(140, 88, 'RECUPERAR_PASSWORD', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"email_enviado\":\"sí\"}', 1, '213.194.150.197', '2026-04-27 14:03:24'),
(141, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.227.253', '2026-04-27 14:12:14'),
(142, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"62968e52...\"}', 0, '46.6.206.253', '2026-04-27 14:14:31'),
(143, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.227.253', '2026-04-27 14:14:46'),
(144, 105, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '37.26.251.60', '2026-04-27 14:19:27'),
(145, 105, 'REGISTRO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"nombre\":\"Javier García \",\"telefono\":\"605259591\",\"email_enviado\":\"sí\"}', 1, '37.26.251.60', '2026-04-27 14:21:05'),
(146, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"9f40c82f...\"}', 0, '37.26.251.60', '2026-04-27 14:25:55'),
(147, 105, 'REGISTRO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"nombre\":\"Javier Garcia\",\"telefono\":\"605259591\",\"email_enviado\":\"sí\"}', 1, '37.26.251.60', '2026-04-27 14:26:39'),
(148, 56, 'LOGIN', 'auth.php', '{\"email\":\"Jperezfernandez981@gmail.com\",\"usuario\":\"jperezfernandez981\",\"rol\":\"usuario\"}', 0, '31.4.220.43', '2026-04-27 14:27:25'),
(149, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"6aae4043...\"}', 0, '37.26.251.60', '2026-04-27 14:29:24'),
(150, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"6aae4043...\"}', 0, '37.26.251.60', '2026-04-27 14:29:30'),
(151, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"9f40c82f...\"}', 0, '37.26.251.60', '2026-04-27 14:29:34'),
(152, 105, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '37.26.251.60', '2026-04-27 14:29:49'),
(153, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"6aae4043...\"}', 0, '37.26.251.60', '2026-04-27 14:30:13'),
(154, 105, 'REGISTRO', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"nombre\":\"Javier Garcia\",\"telefono\":\"605259591\",\"email_enviado\":\"sí\"}', 1, '37.26.251.60', '2026-04-27 14:30:48'),
(155, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"2948f503...\"}', 0, '37.26.251.60', '2026-04-27 14:35:32'),
(156, 105, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"37.26.251.60\"}', 0, '37.26.251.60', '2026-04-27 14:35:44'),
(157, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-27 14:35:49'),
(158, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-27 14:35:55'),
(159, 105, 'LOGIN', 'auth.php', '{\"email\":\"javier6p@gmail.com\",\"usuario\":\"javier6p\",\"rol\":\"usuario\"}', 0, '37.26.251.60', '2026-04-27 14:35:56'),
(160, 105, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"133\",\"cliente_nombre\":\"Francisco Javier Garcia\",\"cliente_email\":\"javier6p@gmail.com\",\"servicio\":\"Arreglo de Barba\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-30\",\"hora\":\"16:45:00\",\"duracion_min\":15,\"google_event_id\":\"r3vpnmutov1cmmrqeo58u18vh4\"}', 1, '90.167.166.30', '2026-04-27 14:41:04'),
(161, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.197', '2026-04-27 15:06:35'),
(162, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.197', '2026-04-27 15:06:44'),
(163, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.197', '2026-04-27 15:06:53'),
(164, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.197', '2026-04-27 15:08:41'),
(165, 86, 'LOGIN', 'auth.php', '{\"email\":\"ivangutierrezfga24@gmail.com\",\"usuario\":\"ivan.gutiérrez.ibañez\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 15:11:29'),
(166, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 16:09:22'),
(167, 88, 'RECUPERAR_PASSWORD', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"email_enviado\":\"sí\"}', 1, '213.194.150.197', '2026-04-27 16:29:31'),
(168, 88, 'RESET_PASSWORD', 'auth.php', '{\"motivo\":\"Contraseña restablecida con éxito\"}', 0, '213.194.150.197', '2026-04-27 16:30:05'),
(169, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 16:30:14'),
(170, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 16:31:43'),
(171, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 16:34:25'),
(172, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '213.194.150.197', '2026-04-27 16:39:27'),
(173, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 16:39:34'),
(174, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '80.27.23.56', '2026-04-27 17:18:32'),
(175, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '80.27.23.56', '2026-04-27 17:18:39'),
(176, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '80.27.23.56', '2026-04-27 17:18:41'),
(177, 106, 'REGISTRO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"nombre\":\"Ivan Macías Alonso \",\"telefono\":\"624823603\",\"email_enviado\":\"sí\"}', 1, '31.4.223.35', '2026-04-27 17:23:04'),
(178, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.223.35', '2026-04-27 17:23:18'),
(179, 106, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '31.4.223.35', '2026-04-27 17:23:26'),
(180, 106, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '31.4.223.35', '2026-04-27 17:23:39'),
(181, 106, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"31.4.223.35\"}', 0, '31.4.223.35', '2026-04-27 17:23:55'),
(182, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"3f28c3b6...\"}', 0, '142.250.32.7', '2026-04-27 17:23:57'),
(183, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"3f28c3b6...\"}', 0, '74.125.210.173', '2026-04-27 17:23:58'),
(184, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"3f28c3b6...\"}', 0, '66.249.88.228', '2026-04-27 17:23:59'),
(185, 106, 'LOGIN', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"usuario\":\"ivanmafutbol2012\",\"rol\":\"usuario\"}', 0, '31.4.223.35', '2026-04-27 17:24:10'),
(186, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 17:54:33'),
(187, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '80.27.23.56', '2026-04-27 17:57:55'),
(188, 106, 'LOGIN', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"usuario\":\"ivanmafutbol2012\",\"rol\":\"usuario\"}', 0, '80.27.23.56', '2026-04-27 17:58:07'),
(189, 106, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"134\",\"cliente_nombre\":\"Ivan Macías Alonso \",\"cliente_email\":\"ivanmafutbol2012@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-28\",\"hora\":\"17:00:00\",\"duracion_min\":30,\"google_event_id\":\"3hvkgstqge1971843f2itg7fo8\"}', 1, '80.27.23.56', '2026-04-27 17:59:22'),
(190, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":114,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Tomás entrénate \",\"cliente_email\":\"sinemail@tomás.entrénate\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"17:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-27 18:27:11'),
(191, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.197', '2026-04-27 18:34:12'),
(192, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '213.194.150.197', '2026-04-27 18:38:32'),
(193, 107, 'REGISTRO', 'auth.php', '{\"email\":\"josemanuelvicentev4@gmail.com\",\"nombre\":\"Jose manuel vicente\",\"telefono\":\"695843543\",\"email_enviado\":\"sí\"}', 1, '90.165.36.185', '2026-04-27 19:30:24'),
(194, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:30:55'),
(195, 107, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"90.165.36.185\"}', 0, '90.165.36.185', '2026-04-27 19:31:13'),
(196, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:31:41'),
(197, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:31:59'),
(198, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:32:16'),
(199, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:32:21'),
(200, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:32:24'),
(201, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"josemanuelvicentevelasco4@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.165.36.185', '2026-04-27 19:32:33'),
(202, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"7a391e6a...\"}', 0, '84.78.245.152', '2026-04-27 19:36:31'),
(203, 108, 'REGISTRO', 'auth.php', '{\"email\":\"rociodelarosacalderon@gmail.com\",\"nombre\":\"Rocio rosa\",\"telefono\":\"695843543\",\"email_enviado\":\"sí\"}', 1, '90.165.36.185', '2026-04-27 19:37:27'),
(204, 108, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"90.165.36.185\"}', 0, '90.165.36.185', '2026-04-27 19:37:43'),
(205, 108, 'LOGIN', 'auth.php', '{\"email\":\"rociodelarosacalderon@gmail.com\",\"usuario\":\"rociodelarosacalderon\",\"rol\":\"usuario\"}', 0, '90.165.36.185', '2026-04-27 19:38:04'),
(206, 108, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"135\",\"cliente_nombre\":\"Rocio rosa\",\"cliente_email\":\"rociodelarosacalderon@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-30\",\"hora\":\"20:15:00\",\"duracion_min\":30,\"google_event_id\":\"120cps48nsfklqphvgjeaf7n8k\"}', 1, '90.165.36.185', '2026-04-27 19:38:52'),
(207, 109, 'REGISTRO', 'auth.php', '{\"email\":\"pablitord16@gmail.com\",\"nombre\":\"pablo Rodríguez diaz\",\"telefono\":\"656430306\",\"email_enviado\":\"sí\"}', 1, '86.127.226.141', '2026-04-27 20:20:22'),
(208, 109, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"pablitord16@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '86.127.226.141', '2026-04-27 20:20:36'),
(209, 109, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"86.127.226.141\"}', 0, '86.127.226.141', '2026-04-27 20:20:49'),
(210, 109, 'LOGIN', 'auth.php', '{\"email\":\"pablitord16@gmail.com\",\"usuario\":\"pablitord16\",\"rol\":\"usuario\"}', 0, '86.127.226.141', '2026-04-27 20:21:02'),
(211, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 20:22:45'),
(212, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":117,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Juan Diego \",\"cliente_email\":\"sinemail@juan.diego\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"19:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-27 20:23:14'),
(213, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":118,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Esther Samuel baloncesto \",\"cliente_email\":\"sinemail@esther.samuel.baloncesto\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-27\",\"hora\":\"20:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-27 20:23:18'),
(214, 58, 'RECUPERAR_PASSWORD', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"email_enviado\":\"sí\"}', 1, '31.4.221.219', '2026-04-27 20:24:46'),
(215, NULL, 'RESET_PASSWORD_FALLIDO', 'auth.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"e22f21c5...\"}', 0, '31.4.221.219', '2026-04-27 20:25:08'),
(216, NULL, 'RESET_PASSWORD_FALLIDO', 'auth.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"e22f21c5...\"}', 0, '31.4.221.219', '2026-04-27 20:25:10'),
(217, 58, 'RESET_PASSWORD', 'auth.php', '{\"motivo\":\"Contraseña restablecida con éxito\"}', 0, '31.4.221.219', '2026-04-27 20:25:26'),
(218, 58, 'LOGIN', 'auth.php', '{\"email\":\"miguelestevez039@gmail.com\",\"usuario\":\"miguelestevez039\",\"rol\":\"usuario\"}', 0, '31.4.221.219', '2026-04-27 20:26:03'),
(219, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":133,\"cliente\":\"Francisco Javier Garcia\",\"antes_fecha\":\"2026-04-30\",\"antes_hora\":\"16:45:00\",\"antes_servicio\":\"Arreglo de Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-30\",\"nueva_hora\":\"17:00\",\"nuevo_servicio\":\"Arreglo de Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.229', '2026-04-27 20:28:35'),
(220, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 20:31:18'),
(221, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":121,\"cliente\":\"Carlos Parreño\",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"18:15:00\",\"antes_servicio\":\"Corte + Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-28\",\"nueva_hora\":\"19:45\",\"nuevo_servicio\":\"Corte + Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.229', '2026-04-27 20:33:05'),
(222, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 20:35:35'),
(223, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 20:37:25'),
(224, 106, 'LOGIN', 'auth.php', '{\"email\":\"ivanmafutbol2012@gmail.com\",\"usuario\":\"ivanmafutbol2012\",\"rol\":\"usuario\"}', 0, '47.63.45.117', '2026-04-27 22:39:09'),
(225, 106, 'CANCELACION_RESERVA', 'cancelar_reserva.php', '{\"reserva_id\":134,\"cliente_nombre\":\"Ivan Macías Alonso \",\"servicio\":\"Corte\",\"fecha\":\"2026-04-28\",\"hora\":\"17:00:00\",\"google_event_id\":\"3hvkgstqge1971843f2itg7fo8\",\"google_borrado\":true}', 0, '47.63.45.117', '2026-04-27 22:39:23'),
(226, 106, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"136\",\"cliente_nombre\":\"Ivan Macías Alonso \",\"cliente_email\":\"ivanmafutbol2012@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-28\",\"hora\":\"18:00:00\",\"duracion_min\":30,\"google_event_id\":\"qbc2crphvqni9l7d4b32mlbeb0\"}', 1, '47.63.45.117', '2026-04-27 22:40:55'),
(227, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-27 23:33:15'),
(228, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"110\",\"nombre_cliente\":\"Sergio Fernánde\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@sergio.fernánde\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.253', '2026-04-27 23:33:47'),
(229, 88, 'LOGIN', 'auth.php', '{\"email\":\"hadesinfer@gmail.com\",\"usuario\":\"hadesinfer\",\"rol\":\"usuario\"}', 0, '213.194.150.197', '2026-04-27 23:37:54'),
(230, 111, 'REGISTRO', 'auth.php', '{\"email\":\"sergiofdezsj2001@gmail.com\",\"nombre\":\"Sergio Fdez\",\"telefono\":\"627710725\",\"email_enviado\":\"sí\"}', 1, '2.140.192.48', '2026-04-28 06:56:47'),
(231, 111, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"2.140.192.48\"}', 0, '2.140.192.48', '2026-04-28 06:56:59'),
(232, 111, 'LOGIN', 'auth.php', '{\"email\":\"sergiofdezsj2001@gmail.com\",\"usuario\":\"sergiofdezsj2001\",\"rol\":\"usuario\"}', 0, '2.140.192.48', '2026-04-28 06:57:12'),
(233, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 10:51:05'),
(234, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javimerida497@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '178.139.170.64', '2026-04-28 12:03:49'),
(235, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '46.6.165.96', '2026-04-28 12:40:05'),
(236, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 12:47:47'),
(237, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"112\",\"nombre_cliente\":\"lobera\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@lobera\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.227.229', '2026-04-28 12:48:03'),
(238, 113, 'REGISTRO', 'auth.php', '{\"email\":\"jorgelf10@icloud.com\",\"nombre\":\"Jorge Lobera Fernandez\",\"telefono\":\"608224708\",\"email_enviado\":\"sí\"}', 1, '31.4.192.93', '2026-04-28 12:52:58'),
(239, 113, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"jorgelf10@icloud.com\",\"motivo\":\"Cuenta no activada\"}', 0, '31.4.192.93', '2026-04-28 12:53:23'),
(240, 113, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"104.28.88.142\"}', 0, '104.28.88.142', '2026-04-28 12:54:52'),
(241, 113, 'LOGIN', 'auth.php', '{\"email\":\"jorgelf10@icloud.com\",\"usuario\":\"jorgelf10\",\"rol\":\"usuario\"}', 0, '104.28.88.142', '2026-04-28 12:55:09'),
(242, 113, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"138\",\"cliente_nombre\":\"Jorge Lobera Fernandez\",\"cliente_email\":\"jorgelf10@icloud.com\",\"servicio\":\"Corte + Barba\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-06\",\"hora\":\"18:00:00\",\"duracion_min\":45,\"google_event_id\":\"3incm3ttqr9uff4l6b7sqjnq7o\"}', 1, '104.28.88.142', '2026-04-28 12:55:27'),
(243, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ricardo.delacasalimon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.219.62', '2026-04-28 13:00:15'),
(244, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ricardo.delacasalimon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.219.62', '2026-04-28 13:00:24'),
(245, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ricardo.delacasalimon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.219.62', '2026-04-28 13:00:25'),
(246, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ricardo.delacasalimon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '31.4.219.62', '2026-04-28 13:00:49'),
(247, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 13:05:36');
INSERT INTO `logs_sistema` (`id`, `usuario_id`, `accion`, `archivo`, `detalle`, `email_enviado`, `ip_address`, `fecha_registro`) VALUES
(248, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"114\",\"nombre_cliente\":\"primo jesus\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@primo.jesus\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.229', '2026-04-28 13:05:52'),
(249, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"115\",\"nombre_cliente\":\"javi amigode queso\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@javi.amigode.queso\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.229', '2026-04-28 13:10:27'),
(250, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 14:06:24'),
(251, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"116\",\"nombre_cliente\":\"Ricardo castilla\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@ricardo.castilla\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.229', '2026-04-28 14:06:39'),
(252, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-28 15:09:37'),
(253, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"117\",\"nombre_cliente\":\"Agustín Perejon\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@agustín.perejon\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.253', '2026-04-28 15:10:07'),
(254, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-28 15:13:06'),
(255, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":141,\"cliente\":\"Ricardo castilla\",\"antes_fecha\":\"2026-04-30\",\"antes_hora\":\"12:45:00\",\"antes_servicio\":\"Arreglo de Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-30\",\"nueva_hora\":\"14:00\",\"nuevo_servicio\":\"Arreglo de Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.253', '2026-04-28 15:13:22'),
(256, 118, 'REGISTRO', 'auth.php', '{\"email\":\"pepecapmany9@gmail.com\",\"nombre\":\"Pepe Capmany mayorga\",\"telefono\":\"645192579\",\"email_enviado\":\"sí\"}', 1, '90.165.36.58', '2026-04-28 15:54:19'),
(257, 118, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"pepecapmany9@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '90.165.36.58', '2026-04-28 15:54:33'),
(258, 118, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"90.165.36.58\"}', 0, '90.165.36.58', '2026-04-28 15:54:43'),
(259, 118, 'LOGIN', 'auth.php', '{\"email\":\"pepecapmany9@gmail.com\",\"usuario\":\"pepecapmany9\",\"rol\":\"usuario\"}', 0, '90.165.36.58', '2026-04-28 15:54:54'),
(260, 118, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"143\",\"cliente_nombre\":\"Pepe Capmany mayorga\",\"cliente_email\":\"pepecapmany9@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-28\",\"hora\":\"16:30:00\",\"duracion_min\":30,\"google_event_id\":\"r2hqbao9pm8v9halkoq7l0v1uc\"}', 1, '90.165.36.58', '2026-04-28 15:55:43'),
(261, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 18:51:05'),
(262, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":143,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Pepe Capmany mayorga\",\"cliente_email\":\"pepecapmany9@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-28\",\"hora\":\"16:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-28 18:51:17'),
(263, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":136,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Ivan Macías Alonso \",\"cliente_email\":\"ivanmafutbol2012@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-28\",\"hora\":\"18:00:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-28 18:51:23'),
(264, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":142,\"cliente\":\"Agustín Perejon \",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"19:45:00\",\"antes_servicio\":\"Corte + Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-30\",\"nueva_hora\":\"15:45\",\"nuevo_servicio\":\"Corte + Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.229', '2026-04-28 18:55:59'),
(265, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"119\",\"nombre_cliente\":\"jose antonio\",\"telefono\":\"663318155\",\"correo_usado\":\"sinemail@jose.antonio\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.229', '2026-04-28 19:34:25'),
(266, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-28 21:23:49'),
(267, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"120\",\"nombre_cliente\":\"fernando carranco\",\"telefono\":\"647252496\",\"correo_usado\":\"sinemail@fernando.carranco\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.229', '2026-04-28 21:24:38'),
(268, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"121\",\"nombre_cliente\":\"miguel\",\"telefono\":\"660625240\",\"correo_usado\":\"sinemail@miguel\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.227.229', '2026-04-28 21:25:43'),
(269, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":121,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Carlos Parreño\",\"cliente_email\":\"carlosparrenoferrete@gmail.com\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-28\",\"hora\":\"19:45:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-28 21:26:41'),
(270, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":144,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Jose Antonio Tristancho Castillo \",\"cliente_email\":\"joseantoniotriscas@gmail.com\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-28\",\"hora\":\"21:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.229', '2026-04-28 21:26:46'),
(271, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-28 22:32:05'),
(272, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:33:58'),
(273, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:08'),
(274, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:10'),
(275, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:12'),
(276, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:19'),
(277, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:20'),
(278, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:27'),
(279, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javisanchezmoreno@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:28'),
(280, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:58'),
(281, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:34:59'),
(282, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:35:13'),
(283, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:35:50'),
(284, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:35:51'),
(285, 122, 'REGISTRO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"nombre\":\"Francisco Javier Sanchez Moreno\",\"telefono\":\"622678812\",\"email_enviado\":\"sí\"}', 1, '77.230.18.8', '2026-04-28 22:37:26'),
(286, 122, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '77.230.18.8', '2026-04-28 22:37:37'),
(287, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:37:47'),
(288, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:37:53'),
(289, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"javielpro2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:38:11'),
(290, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:38:37'),
(291, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '77.230.18.8', '2026-04-28 22:38:38'),
(292, 123, 'REGISTRO', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"nombre\":\"Francisco Javier\",\"telefono\":\"622678812\",\"email_enviado\":\"sí\"}', 1, '77.230.18.8', '2026-04-28 22:39:18'),
(293, 123, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"142.250.32.9\"}', 0, '142.250.32.9', '2026-04-28 22:39:39'),
(294, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"608ffbb5...\"}', 0, '77.230.18.8', '2026-04-28 22:39:39'),
(295, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"608ffbb5...\"}', 0, '74.125.210.173', '2026-04-28 22:39:39'),
(296, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"608ffbb5...\"}', 0, '192.178.15.9', '2026-04-28 22:39:39'),
(297, 123, 'LOGIN', 'auth.php', '{\"email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"usuario\":\"franciscojaviersanchezmoreno\",\"rol\":\"usuario\"}', 0, '77.230.18.8', '2026-04-28 22:40:35'),
(298, 123, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"148\",\"cliente_nombre\":\"Francisco Javier\",\"cliente_email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-04-29\",\"hora\":\"20:30:00\",\"duracion_min\":30,\"google_event_id\":\"94e22khnb53npr3rla4gutslh0\"}', 1, '77.230.18.8', '2026-04-28 22:41:38'),
(299, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '95.18.67.30', '2026-04-28 22:56:19'),
(300, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '95.18.67.30', '2026-04-28 22:56:32'),
(301, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '95.18.67.30', '2026-04-28 22:56:33'),
(302, 124, 'REGISTRO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"nombre\":\"Marcos pedrosa garcia\",\"telefono\":\"632108753\",\"email_enviado\":\"sí\"}', 1, '95.18.67.30', '2026-04-28 22:57:14'),
(303, 124, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Cuenta no activada\"}', 0, '95.18.67.30', '2026-04-28 22:57:38'),
(304, 124, 'REGISTRO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"nombre\":\"Marcos pedrosa garcia\",\"telefono\":\"632108753\",\"email_enviado\":\"sí\"}', 1, '95.18.67.30', '2026-04-28 22:58:33'),
(305, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"7bc8ab04...\"}', 0, '95.18.67.30', '2026-04-28 22:58:51'),
(306, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"5MD1ke02...\"}', 0, '212.89.24.6', '2026-04-28 22:58:56'),
(307, 124, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Cuenta no activada\"}', 0, '95.18.67.30', '2026-04-28 22:59:05'),
(308, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"7bc8ab04...\"}', 0, '95.18.67.30', '2026-04-28 23:01:10'),
(309, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"7bc8ab04...\"}', 0, '95.18.67.30', '2026-04-28 23:01:19'),
(310, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '95.18.67.30', '2026-04-28 23:02:01'),
(311, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"marcospedrosagarcia@iesaliscar.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '95.18.67.30', '2026-04-28 23:02:21'),
(312, 40, 'LOGIN', 'auth.php', '{\"email\":\"radragon.rad@gmail.com\",\"usuario\":\"radragon.rad\",\"rol\":\"admin\"}', 0, '46.6.181.83', '2026-04-28 23:03:29'),
(313, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"samuelaragon52@icloud.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.180.145', '2026-04-28 23:05:58'),
(314, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.180.145', '2026-04-28 23:06:15'),
(315, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.180.145', '2026-04-28 23:06:16'),
(316, 125, 'REGISTRO', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"nombre\":\"Samuel Aragon ponce \",\"telefono\":\"600480255\",\"email_enviado\":\"sí\"}', 1, '46.6.180.145', '2026-04-28 23:07:11'),
(317, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.180.145', '2026-04-28 23:08:09'),
(318, 125, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"46.6.180.145\"}', 0, '46.6.180.145', '2026-04-28 23:09:13'),
(319, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '46.6.180.145', '2026-04-28 23:09:37'),
(320, 125, 'LOGIN', 'auth.php', '{\"email\":\"samuelaragonponce@gmail.com\",\"usuario\":\"samuelaragonponce\",\"rol\":\"usuario\"}', 0, '46.6.180.145', '2026-04-28 23:09:44'),
(321, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 10:46:22'),
(322, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":139,\"cliente\":\"primo jesus\",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"11:00:00\",\"antes_servicio\":\"Corte\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-29\",\"nueva_hora\":\"12:15\",\"nuevo_servicio\":\"Corte\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.249', '2026-04-29 10:46:46'),
(323, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"7bc8ab04...\"}', 0, '95.18.67.30', '2026-04-29 11:02:31'),
(324, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 11:08:00'),
(325, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":146,\"cliente\":\"fernando carranco\",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"12:00:00\",\"antes_servicio\":\"Arreglo de Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-29\",\"nueva_hora\":\"17:45\",\"nuevo_servicio\":\"Arreglo de Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.249', '2026-04-29 11:09:24'),
(326, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":146,\"cliente\":\"fernando carranco\",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"17:45:00\",\"antes_servicio\":\"Arreglo de Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-29\",\"nueva_hora\":\"18:45\",\"nuevo_servicio\":\"Arreglo de Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.249', '2026-04-29 11:09:39'),
(327, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":146,\"cliente\":\"fernando carranco\",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"18:45:00\",\"antes_servicio\":\"Arreglo de Barba\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-29\",\"nueva_hora\":\"18:30\",\"nuevo_servicio\":\"Arreglo de Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.249', '2026-04-29 11:09:53'),
(328, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 11:54:37'),
(329, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"manuelhomeramos@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '87.218.208.95', '2026-04-29 14:04:08'),
(330, 126, 'REGISTRO', 'auth.php', '{\"email\":\"manuelhomeramos@gmail.com\",\"nombre\":\"Manuel Suárez Reina\",\"telefono\":\"633402208\",\"email_enviado\":\"sí\"}', 1, '87.218.208.95', '2026-04-29 14:05:06'),
(331, 126, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"manuelhomeramos@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '87.218.208.95', '2026-04-29 14:05:19'),
(332, 126, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"manuelhomeramos@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '87.218.208.95', '2026-04-29 14:05:26'),
(333, 126, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"87.218.208.95\"}', 0, '87.218.208.95', '2026-04-29 14:05:48'),
(334, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"912e2e1e...\"}', 0, '142.250.32.9', '2026-04-29 14:05:50'),
(335, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"912e2e1e...\"}', 0, '142.250.32.9', '2026-04-29 14:05:50'),
(336, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"912e2e1e...\"}', 0, '74.125.210.172', '2026-04-29 14:05:51'),
(337, 126, 'LOGIN', 'auth.php', '{\"email\":\"manuelhomeramos@gmail.com\",\"usuario\":\"manuelhomeramos\",\"rol\":\"usuario\"}', 0, '87.218.208.95', '2026-04-29 14:06:06'),
(338, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-29 14:20:54'),
(339, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.253', '2026-04-29 14:21:00'),
(340, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.163.233.43', '2026-04-29 15:55:25'),
(341, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.163.233.43', '2026-04-29 15:55:36'),
(342, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '90.163.233.43', '2026-04-29 15:55:38'),
(343, 127, 'REGISTRO', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"nombre\":\"Aaron Ortiz\",\"telefono\":\"608292684\",\"email_enviado\":\"sí\"}', 1, '90.163.233.43', '2026-04-29 15:56:25'),
(344, 127, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '90.163.233.43', '2026-04-29 15:56:50'),
(345, 127, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"90.163.233.43\"}', 0, '90.163.233.43', '2026-04-29 15:56:59'),
(346, 127, 'LOGIN', 'auth.php', '{\"email\":\"ortizgalan2008@gmail.com\",\"usuario\":\"ortizgalan2008\",\"rol\":\"usuario\"}', 0, '90.163.233.43', '2026-04-29 15:59:03'),
(347, 127, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"151\",\"cliente_nombre\":\"Aaron Ortiz\",\"cliente_email\":\"ortizgalan2008@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-08\",\"hora\":\"18:30:00\",\"duracion_min\":30,\"google_event_id\":\"gio0v3br7fgvifhs7hg27p37sk\"}', 1, '90.163.233.43', '2026-04-29 16:04:39'),
(348, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 16:31:17'),
(349, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":139,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"primo jesus\",\"cliente_email\":\"sinemail@primo.jesus\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-29\",\"hora\":\"12:15:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 16:32:16'),
(350, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 18:52:25'),
(351, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":119,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Marco lobera \",\"cliente_email\":\"fernandezlobera10@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-29\",\"hora\":\"16:30:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 18:54:49'),
(352, 43, 'MODIFICACION_RESERVA', 'guardar_reserva_admin.php', '{\"reserva_id\":120,\"cliente\":\"Marco lobera \",\"antes_fecha\":\"2026-04-29\",\"antes_hora\":\"17:00:00\",\"antes_servicio\":\"Corte\",\"antes_peluquero\":\"Ruben gutierrez ibañez\",\"nueva_fecha\":\"2026-04-29\",\"nueva_hora\":\"17:00\",\"nuevo_servicio\":\"Corte + Barba\",\"nuevo_peluquero\":\"Ruben gutierrez ibañez\"}', 0, '79.117.227.249', '2026-04-29 18:54:57'),
(353, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":120,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Marco lobera \",\"cliente_email\":\"fernandezlobera10@gmail.com\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-29\",\"hora\":\"17:00:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 18:55:01'),
(354, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":147,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"miguel \",\"cliente_email\":\"sinemail@miguel\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-29\",\"hora\":\"18:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 18:55:07'),
(355, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":146,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"fernando carranco\",\"cliente_email\":\"sinemail@fernando.carranco\",\"servicio\":\"Arreglo de Barba\",\"fecha\":\"2026-04-29\",\"hora\":\"18:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 18:55:18'),
(356, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-29 20:15:39'),
(357, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":145,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"NU jose antonio \",\"cliente_email\":\"sinemail@jose.antonio\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-29\",\"hora\":\"19:30:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-29 20:15:47'),
(358, 128, 'REGISTRO', 'auth.php', '{\"email\":\"mateo_1905@hotmail.es\",\"nombre\":\"Mateo Suarez\",\"telefono\":\"664708333\",\"email_enviado\":\"sí\"}', 1, '83.47.180.92', '2026-04-30 02:11:17'),
(359, 128, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"mateo_1905@hotmail.es\",\"motivo\":\"Cuenta no activada\"}', 0, '83.47.180.92', '2026-04-30 02:11:28'),
(360, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"mateo_1905@hotmail.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '83.47.180.92', '2026-04-30 02:11:32'),
(361, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"mateo_1905@hotmail.es\",\"motivo\":\"Credenciales incorrectas\"}', 0, '83.47.180.92', '2026-04-30 02:11:42'),
(362, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.116.116.101', '2026-04-30 08:27:30'),
(363, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"129\",\"nombre_cliente\":\"Onio\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@onio\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.116.116.101', '2026-04-30 08:27:41'),
(364, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 10:08:06'),
(365, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"130\",\"nombre_cliente\":\"paco\",\"telefono\":\"66891806\",\"correo_usado\":\"sinemail@paco\",\"fecha_nac\":\"1900-01-01\"}', 0, '79.117.227.249', '2026-04-30 10:08:40'),
(366, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":148,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Francisco Javier\",\"cliente_email\":\"franciscojaviersanchezmoreno@iesaliscar.es\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-29\",\"hora\":\"20:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 10:11:41'),
(367, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 11:26:50'),
(368, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":158,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"paco\",\"cliente_email\":\"sinemail@paco\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"10:30:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 11:27:01'),
(369, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.124.24', '2026-04-30 11:31:47'),
(370, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.116.124.24', '2026-04-30 11:34:17'),
(371, 131, 'REGISTRO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"nombre\":\"alfonso font \",\"telefono\":\"656825808\",\"email_enviado\":\"sí\"}', 1, '79.116.124.24', '2026-04-30 11:35:10'),
(372, 131, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '79.116.124.24', '2026-04-30 11:35:24'),
(373, 131, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '79.116.124.24', '2026-04-30 11:35:35'),
(374, 131, 'REGISTRO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"nombre\":\"alfonso font\",\"telefono\":\"656825808\",\"email_enviado\":\"sí\"}', 1, '79.116.124.24', '2026-04-30 11:36:13'),
(375, 131, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '79.116.124.24', '2026-04-30 11:37:11'),
(376, 131, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"alfonsofont2008@gmail.com\",\"motivo\":\"Cuenta no activada\"}', 0, '79.116.124.24', '2026-04-30 11:38:22'),
(377, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":149,\"nuevo_estado\":\"ANULADA LOCAL\",\"cliente\":\"Marcos pedrosa garcia\",\"cliente_email\":\"marcospedrosagarcia@iesaliscar.es\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"11:30:00\",\"metodo_pago\":null,\"motivo\":\".\",\"google_borrado\":\"sí\"}', 0, '79.117.227.249', '2026-04-30 11:56:14'),
(378, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 12:46:11'),
(379, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":157,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Onio\",\"cliente_email\":\"sinemail@onio\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"12:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 12:46:18'),
(380, 132, 'REGISTRO', 'auth.php', '{\"email\":\"jdominguezbegines@gmail.com\",\"nombre\":\"jose dominguez\",\"telefono\":\"610085822\",\"email_enviado\":\"sí\"}', 1, '79.117.64.239', '2026-04-30 12:53:04'),
(381, 132, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"79.117.64.239\"}', 0, '79.117.64.239', '2026-04-30 12:53:22'),
(382, 132, 'LOGIN', 'auth.php', '{\"email\":\"jdominguezbegines@gmail.com\",\"usuario\":\"jdominguezbegines\",\"rol\":\"usuario\"}', 0, '79.117.64.239', '2026-04-30 12:54:03'),
(383, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"pedrocalero080@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.106.247', '2026-04-30 13:39:22'),
(384, 133, 'REGISTRO', 'auth.php', '{\"email\":\"pedrocalero080@gmail.com\",\"nombre\":\"Pedro Calero\",\"telefono\":\"635843520\",\"email_enviado\":\"sí\"}', 1, '79.117.106.247', '2026-04-30 13:40:32'),
(385, 133, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"79.117.106.247\"}', 0, '79.117.106.247', '2026-04-30 13:40:47'),
(386, NULL, 'ACTIVACION_FALLIDA', 'activar.php', '{\"motivo\":\"Token inválido o expirado\",\"token\":\"r83N2002...\"}', 0, '212.142.160.100', '2026-04-30 13:40:50'),
(387, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"pedrocalero080@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.106.247', '2026-04-30 13:40:56'),
(388, 133, 'LOGIN', 'auth.php', '{\"email\":\"pedrocalero080@gmail.com\",\"usuario\":\"pedrocalero080\",\"rol\":\"usuario\"}', 0, '79.117.106.247', '2026-04-30 13:41:08'),
(389, 133, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"161\",\"cliente_nombre\":\"Pedro Calero\",\"cliente_email\":\"pedrocalero080@gmail.com\",\"servicio\":\"Corte\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-08\",\"hora\":\"20:00:00\",\"duracion_min\":30,\"google_event_id\":\"08htb098q12f2m4heqemu4ocpo\"}', 1, '79.117.106.247', '2026-04-30 13:43:52'),
(390, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 14:42:49'),
(391, 134, 'REGISTRO', 'auth.php', '{\"email\":\"jlcuevas96@gmail.com\",\"nombre\":\"José Luis Cuevas Romero\",\"telefono\":\"635082543\",\"email_enviado\":\"sí\"}', 1, '79.117.100.88', '2026-04-30 14:53:31'),
(392, 134, 'ACTIVACION_CUENTA', 'activar.php', '{\"motivo\":\"Cuenta activada mediante enlace de correo\",\"ip\":\"79.117.100.88\"}', 0, '79.117.100.88', '2026-04-30 14:55:06'),
(393, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"jlcuevas96@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.100.88', '2026-04-30 14:55:35'),
(394, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"jlcuevas96@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.100.88', '2026-04-30 14:55:54'),
(395, 134, 'LOGIN', 'auth.php', '{\"email\":\"jlcuevas96@gmail.com\",\"usuario\":\"jlcuevas96\",\"rol\":\"usuario\"}', 0, '79.117.100.88', '2026-04-30 14:55:59'),
(396, 133, 'LOGIN', 'auth.php', '{\"email\":\"pedrocalero080@gmail.com\",\"usuario\":\"pedrocalero080\",\"rol\":\"usuario\"}', 0, '79.117.227.236', '2026-04-30 15:03:38'),
(397, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 15:44:07'),
(398, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":125,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Alejandro perejon torres\",\"cliente_email\":\"alex.perejon52@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"13:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 15:44:30'),
(399, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":131,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Miguel Estévez galan\",\"cliente_email\":\"miguelestevez039@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"13:30:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 15:44:35'),
(400, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":141,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Ricardo castilla\",\"cliente_email\":\"ricardo.delacasalimon@gmail.com\",\"servicio\":\"Arreglo de Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"14:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 15:44:39'),
(401, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":160,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Mateo Suarez\",\"cliente_email\":\"mateo_1905@hotmail.es\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"14:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 15:44:46'),
(402, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":142,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Agustín Perejon \",\"cliente_email\":\"sinemail@agustín.perejon\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"15:45:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 15:44:55'),
(403, 104, 'NUEVA_RESERVA', 'confirmar_reserva.php', '{\"id_reserva\":\"163\",\"cliente_nombre\":\"Álvaro caro pineda\",\"cliente_email\":\"alvarocaro93@gmail.com\",\"servicio\":\"Corte + Barba\",\"peluquero\":\"Ruben gutierrez ibañez\",\"fecha\":\"2026-05-14\",\"hora\":\"18:00:00\",\"duracion_min\":45,\"google_event_id\":\"vdg9cvg1epgsbs90c2g95moiho\"}', 1, '46.6.229.71', '2026-04-30 17:30:57'),
(404, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 17:33:16'),
(405, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"scastillolikon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.65.214', '2026-04-30 21:50:21'),
(406, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"scastillolikon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.65.214', '2026-04-30 21:50:24'),
(407, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"scastillolikon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.65.214', '2026-04-30 21:50:32'),
(408, NULL, 'LOGIN_FALLIDO', 'auth.php', '{\"email\":\"scastillolikon@gmail.com\",\"motivo\":\"Credenciales incorrectas\"}', 0, '79.117.65.214', '2026-04-30 21:50:46'),
(409, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.249', '2026-04-30 21:55:12'),
(410, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":124,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Javier barba naranjo\",\"cliente_email\":\"javibetico2004@gmail.com\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"18:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:57:44'),
(411, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":123,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Santiago Cabeza Alonso\",\"cliente_email\":\"santiagocabezaalonso@iesaliscar.es\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"18:00:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:57:50'),
(412, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":122,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Queso\",\"cliente_email\":\"sinemail@queso\",\"servicio\":\"Corte + Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"17:15:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:01'),
(413, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":133,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Francisco Javier Garcia\",\"cliente_email\":\"javier6p@gmail.com\",\"servicio\":\"Arreglo de Barba\",\"fecha\":\"2026-04-30\",\"hora\":\"17:00:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:11'),
(414, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":137,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Sergio Fernánde\",\"cliente_email\":\"sinemail@sergio.fernánde\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"16:30:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:18'),
(415, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":130,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Adrián Perejón Torres \",\"cliente_email\":\"adrianpere05@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"19:15:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:23'),
(416, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":127,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Andrés Zamorano \",\"cliente_email\":\"andreszamoranomartin@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"19:45:00\",\"metodo_pago\":\"Efectivo\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:28'),
(417, 43, 'CAMBIO_ESTADO_RESERVA', 'gestion_reservas.php', '{\"reserva_id\":135,\"nuevo_estado\":\"COMPLETADA\",\"cliente\":\"Rocio rosa\",\"cliente_email\":\"rociodelarosacalderon@gmail.com\",\"servicio\":\"Corte\",\"fecha\":\"2026-04-30\",\"hora\":\"20:15:00\",\"metodo_pago\":\"Tarjeta\",\"motivo\":null,\"google_borrado\":\"no\"}', 1, '79.117.227.249', '2026-04-30 21:58:46'),
(418, 43, 'ALTA_CLIENTE_ADMIN', 'registrar_cliente_admin.php', '{\"cliente_creado_id\":\"135\",\"nombre_cliente\":\"Santi huevo\",\"telefono\":\"999999999\",\"correo_usado\":\"sinemail@santi.huevo\",\"fecha_nac\":\"1900-01-01\"}', 1, '79.117.227.249', '2026-04-30 21:59:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id` int NOT NULL,
  `tipo` enum('INGRESO','GASTO') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT 'efectivo',
  `categoria` varchar(50) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `creado_por` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peluqueros`
--

CREATE TABLE `peluqueros` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `especialidad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `avatar` varchar(100) CHARACTER SET utf16 COLLATE utf16_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `peluqueros`
--

INSERT INTO `peluqueros` (`id`, `usuario_id`, `especialidad`, `activo`, `avatar`) VALUES
(8, 43, 'Barbería y estilo masculino', 1, 'raul.PNG');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `cupones_necesarios` int DEFAULT '5',
  `valor_descuento` decimal(5,2) DEFAULT NULL,
  `etiqueta_id` int DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `tipo` enum('VISITAS','ETIQUETA','PORCENTAJE','RECOMENDADO') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'VISITAS',
  `activa` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `servicio_id` int NOT NULL,
  `peluquero_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('ANULADA LOCAL','ANULADA WEB','COMPLETADA','PENDIENTE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `metodo_pago` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_cancelacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `google_event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagado_deuda` tinyint(1) DEFAULT '0',
  `fecha_pago_deuda` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `user_id`, `servicio_id`, `peluquero_id`, `fecha`, `hora`, `created_at`, `estado`, `metodo_pago`, `motivo_cancelacion`, `google_event_id`, `pagado_deuda`, `fecha_pago_deuda`) VALUES
(92, 43, 1, 8, '2026-04-27', '10:30:00', '2026-04-23 14:19:19', 'ANULADA WEB', NULL, NULL, '1uqvug2o4uehig72vpu543sums', 0, NULL),
(93, 43, 1, 8, '2026-04-27', '11:00:00', '2026-04-23 14:23:39', 'ANULADA LOCAL', NULL, NULL, 'gfr2uhlmp2iuioo02itvoqq2n4', 0, NULL),
(94, 43, 1, 8, '2026-04-27', '11:30:00', '2026-04-23 14:28:08', 'ANULADA LOCAL', NULL, NULL, '6q5qucgvsbld2ri3vhrf1a1j90', 0, NULL),
(95, 43, 1, 8, '2026-04-28', '10:30:00', '2026-04-23 17:25:12', 'ANULADA LOCAL', NULL, NULL, 'i1m73arep5lg70fptc3b8u17dk', 0, NULL),
(97, 43, 1, 8, '2026-04-27', '11:30:00', '2026-04-24 08:50:47', 'ANULADA WEB', NULL, NULL, 'vqhv1mhkmqr2r0vi301aikunog', 0, NULL),
(98, 43, 1, 8, '2026-04-27', '11:30:00', '2026-04-24 08:52:31', 'ANULADA LOCAL', NULL, 's', 'dj7c5vunp5j2tcctf939nvlmho', 0, NULL),
(99, 43, 1, 8, '2026-04-27', '10:30:00', '2026-04-24 08:53:20', 'ANULADA LOCAL', NULL, 'x', 'getlbu23odhtaqnbmnke3k8qg8', 0, NULL),
(100, 65, 1, 8, '2026-04-27', '10:30:00', '2026-04-24 08:59:35', 'ANULADA LOCAL', NULL, 'A', NULL, 0, NULL),
(101, 43, 1, 8, '2026-04-27', '11:00:00', '2026-04-24 09:06:33', 'ANULADA LOCAL', NULL, 'F', '3qgvftr1n80dh4mmhhnfa0iks8', 0, NULL),
(102, 76, 2, 8, '2026-05-13', '18:00:00', '2026-04-24 17:03:49', 'PENDIENTE', NULL, NULL, 'c23tkpesghsuuc36kkhjpoco64', 0, NULL),
(103, 78, 2, 8, '2026-04-30', '18:45:00', '2026-04-24 19:39:38', 'ANULADA LOCAL', NULL, 'Fallo técnico guapo', '3bre5aq1iitaf4k13ef6acu884', 0, NULL),
(104, 79, 1, 8, '2026-04-30', '18:00:00', '2026-04-24 20:34:56', 'ANULADA LOCAL', NULL, 'Cambio', 'd16610djlbh794ltv1e54qmul0', 0, NULL),
(105, 81, 1, 8, '2026-04-29', '16:30:00', '2026-04-24 21:05:31', 'ANULADA LOCAL', NULL, 'A', NULL, 0, NULL),
(106, 81, 1, 8, '2026-04-29', '17:10:00', '2026-04-24 21:07:09', 'ANULADA LOCAL', NULL, 'A', NULL, 0, NULL),
(107, 83, 2, 8, '2026-04-27', '10:30:00', '2026-04-25 17:50:19', 'ANULADA WEB', NULL, NULL, 'hevbngu6fe1321iu643j8pp3nc', 0, NULL),
(108, 83, 1, 8, '2026-04-27', '10:30:00', '2026-04-25 17:54:14', 'ANULADA WEB', NULL, NULL, 'vjpjle9onuddm7r64sajjglhoo', 0, NULL),
(109, 65, 1, 8, '2026-04-27', '11:00:00', '2026-04-25 18:31:00', 'ANULADA LOCAL', NULL, 'A', '11022j6cq9f1bos6914kd5f24o', 0, NULL),
(110, 65, 2, 8, '2026-04-27', '12:00:00', '2026-04-25 18:32:52', 'ANULADA LOCAL', NULL, 'A', 'to40qmrhpejncvif3ial3otn5o', 0, NULL),
(111, 84, 1, 8, '2026-04-27', '12:45:00', '2026-04-25 21:38:32', 'ANULADA LOCAL', NULL, 'A', '5mlnmief5k84qtnnldd8ul11pg', 0, NULL),
(112, 83, 3, 8, '2026-04-27', '11:30:00', '2026-04-25 21:46:41', 'ANULADA WEB', NULL, NULL, 'qsc9vojutokhnp9ol6sk4v25mo', 0, NULL),
(113, 65, 1, 8, '2026-04-27', '10:30:00', '2026-04-26 09:20:57', 'ANULADA LOCAL', NULL, '-', '3klatn57dgottnbicb06e5sllk', 0, NULL),
(114, 89, 1, 8, '2026-04-27', '17:30:00', '2026-04-26 09:21:40', 'COMPLETADA', 'Efectivo', NULL, 'f2t13d6p4e9s0bbfsbl1kogql0', 0, NULL),
(115, 90, 1, 8, '2026-04-27', '12:30:00', '2026-04-26 09:22:27', 'COMPLETADA', 'Efectivo', NULL, 'a5sodf10olr8nacp45d01t1t3g', 0, NULL),
(116, 91, 1, 8, '2026-04-27', '18:00:00', '2026-04-26 09:23:47', 'ANULADA LOCAL', NULL, 'a', 'a509g1j47eql9mgeamlcdgbnlc', 0, NULL),
(117, 92, 1, 8, '2026-04-27', '19:00:00', '2026-04-26 09:24:34', 'COMPLETADA', 'Efectivo', NULL, '308bnf3k39gk5nv32cee02i030', 0, NULL),
(118, 93, 1, 8, '2026-04-27', '20:00:00', '2026-04-26 09:25:39', 'COMPLETADA', 'Efectivo', NULL, 'ntnv61339fmdg0dt1sn8oa9bjk', 0, NULL),
(119, 81, 1, 8, '2026-04-29', '16:30:00', '2026-04-26 09:26:34', 'COMPLETADA', 'Tarjeta', NULL, 'vhs564g01dhma7g21frtomg5jo', 0, NULL),
(120, 81, 2, 8, '2026-04-29', '17:00:00', '2026-04-26 09:26:56', 'COMPLETADA', 'Tarjeta', NULL, 'b1cqej4vffohn8clfmssrjv9d0', 0, NULL),
(121, 60, 2, 8, '2026-04-28', '19:45:00', '2026-04-26 09:27:37', 'COMPLETADA', 'Efectivo', NULL, 'u5ab8r6m7lv7heg10kh5m5ivvo', 0, NULL),
(122, 94, 2, 8, '2026-04-30', '17:15:00', '2026-04-26 09:29:35', 'COMPLETADA', 'Tarjeta', NULL, 'b4jesejt4ffvv289dqv82jbd4s', 0, NULL),
(123, 79, 1, 8, '2026-04-30', '18:00:00', '2026-04-26 09:30:12', 'COMPLETADA', 'Tarjeta', NULL, 'ldi2qh5doedm346pi7sk58gvg0', 0, NULL),
(124, 78, 2, 8, '2026-04-30', '18:30:00', '2026-04-26 09:30:42', 'COMPLETADA', 'Efectivo', NULL, 'ok6nolc799psku15llsai5rp5k', 0, NULL),
(125, 55, 1, 8, '2026-04-30', '13:00:00', '2026-04-26 09:31:37', 'COMPLETADA', 'Efectivo', NULL, 'vs6ma69kbifmii825l5v4epjd0', 0, NULL),
(126, 56, 1, 8, '2026-05-04', '19:00:00', '2026-04-26 11:28:34', 'PENDIENTE', NULL, NULL, 'c6oil1ssn1675jjrvec34nd9h4', 0, NULL),
(127, 101, 1, 8, '2026-04-30', '19:45:00', '2026-04-26 18:42:49', 'COMPLETADA', 'Efectivo', NULL, 'ckkmmkfgkp3tadjoai73m8p6jk', 0, NULL),
(128, 102, 1, 8, '2026-04-27', '12:00:00', '2026-04-26 18:51:36', 'COMPLETADA', 'Tarjeta', NULL, 'dicuniqcbn4e9dhdcdrg4o514s', 0, NULL),
(129, 103, 1, 8, '2026-05-08', '16:30:00', '2026-04-26 21:32:35', 'PENDIENTE', NULL, NULL, 'r5a4dabct9ijvnjtdnlp45h70s', 0, NULL),
(130, 63, 1, 8, '2026-04-30', '19:15:00', '2026-04-27 07:11:44', 'COMPLETADA', 'Efectivo', NULL, 'jpu14igbdffbnp60stj6a9sjh8', 0, NULL),
(131, 58, 1, 8, '2026-04-30', '13:30:00', '2026-04-27 08:32:01', 'COMPLETADA', 'Tarjeta', NULL, '7k54b27jucjlvrlejrq5jd1mf0', 0, NULL),
(132, 100, 2, 8, '2026-04-28', '19:45:00', '2026-04-27 09:04:18', 'ANULADA LOCAL', NULL, 'mas tarde', 'lgo6e6t9d569gpnnj45q46p5k4', 0, NULL),
(133, 105, 3, 8, '2026-04-30', '17:00:00', '2026-04-27 12:41:02', 'COMPLETADA', 'Efectivo', NULL, 'r3vpnmutov1cmmrqeo58u18vh4', 0, NULL),
(134, 106, 1, 8, '2026-04-28', '17:00:00', '2026-04-27 15:59:21', 'ANULADA WEB', NULL, NULL, '3hvkgstqge1971843f2itg7fo8', 0, NULL),
(135, 108, 1, 8, '2026-04-30', '20:15:00', '2026-04-27 17:38:51', 'COMPLETADA', 'Tarjeta', NULL, '120cps48nsfklqphvgjeaf7n8k', 0, NULL),
(136, 106, 1, 8, '2026-04-28', '18:00:00', '2026-04-27 20:40:54', 'COMPLETADA', 'Tarjeta', NULL, 'qbc2crphvqni9l7d4b32mlbeb0', 0, NULL),
(137, 110, 1, 8, '2026-04-30', '16:30:00', '2026-04-27 21:34:04', 'COMPLETADA', 'Efectivo', NULL, '0tqpkpu7u8m445u7qr80b33cfs', 0, NULL),
(138, 113, 2, 8, '2026-05-06', '18:00:00', '2026-04-28 10:55:26', 'PENDIENTE', NULL, NULL, '3incm3ttqr9uff4l6b7sqjnq7o', 0, NULL),
(139, 114, 1, 8, '2026-04-29', '12:15:00', '2026-04-28 11:06:17', 'COMPLETADA', 'Efectivo', NULL, 'qb2j89iokpkis9e98shjsvvu70', 0, NULL),
(140, 115, 2, 8, '2026-05-06', '18:45:00', '2026-04-28 11:11:46', 'PENDIENTE', NULL, NULL, 'l0kiohhn684de18fvdk33980kc', 0, NULL),
(141, 116, 3, 8, '2026-04-30', '14:00:00', '2026-04-28 12:07:49', 'COMPLETADA', 'Efectivo', NULL, 'ft36o9ee64q67v6e6f8o283f9c', 0, NULL),
(142, 117, 2, 8, '2026-04-30', '15:45:00', '2026-04-28 13:12:34', 'COMPLETADA', 'Efectivo', NULL, 'k0p81kc76ckamiepqkmk4li94c', 0, NULL),
(143, 118, 1, 8, '2026-04-28', '16:30:00', '2026-04-28 13:55:42', 'COMPLETADA', 'Efectivo', NULL, 'r2hqbao9pm8v9halkoq7l0v1uc', 0, NULL),
(144, 100, 2, 8, '2026-04-28', '21:00:00', '2026-04-28 16:53:40', 'COMPLETADA', 'Efectivo', NULL, 'adoq2ov07a7snojvsbtpitsibk', 0, NULL),
(145, 119, 1, 8, '2026-04-29', '19:30:00', '2026-04-28 17:34:53', 'COMPLETADA', 'Tarjeta', NULL, '4267cg5cqooes9eda14i5msj3s', 0, NULL),
(146, 120, 3, 8, '2026-04-29', '18:30:00', '2026-04-28 19:24:55', 'COMPLETADA', 'Efectivo', NULL, '2964rrl9docs5qpo0pfdsvuo24', 0, NULL),
(147, 121, 1, 8, '2026-04-29', '18:00:00', '2026-04-28 19:26:08', 'COMPLETADA', 'Efectivo', NULL, 'uko3oubs7b508opntppg0k7qd0', 0, NULL),
(148, 123, 1, 8, '2026-04-29', '20:30:00', '2026-04-28 20:41:37', 'COMPLETADA', 'Efectivo', NULL, '94e22khnb53npr3rla4gutslh0', 0, NULL),
(149, 124, 1, 8, '2026-04-30', '11:30:00', '2026-04-28 21:07:23', 'ANULADA LOCAL', NULL, '.', 'tgab69p3nj23m2a3ims3el61v8', 0, NULL),
(150, 126, 2, 8, '2026-05-04', '16:30:00', '2026-04-29 12:21:40', 'PENDIENTE', NULL, NULL, '2or8bul91sd35ld9m8kv2cb6ug', 0, NULL),
(151, 127, 1, 8, '2026-05-08', '18:30:00', '2026-04-29 14:04:38', 'PENDIENTE', NULL, NULL, 'gio0v3br7fgvifhs7hg27p37sk', 0, NULL),
(152, 60, 2, 8, '2026-05-06', '19:30:00', '2026-04-29 18:18:03', 'PENDIENTE', NULL, NULL, 'vpc2pcijgrgva2gavmm6ps3qhc', 0, NULL),
(153, 116, 2, 8, '2026-05-08', '17:45:00', '2026-04-29 18:20:18', 'PENDIENTE', NULL, NULL, 'a52tmn3augh47o3sin87cvfvqk', 0, NULL),
(154, 111, 1, 8, '2026-05-08', '19:00:00', '2026-04-29 18:21:18', 'PENDIENTE', NULL, NULL, 'ehfb6050jli77mo2ujsgm5l48g', 0, NULL),
(155, 78, 2, 8, '2026-05-07', '18:45:00', '2026-04-29 18:22:13', 'PENDIENTE', NULL, NULL, '9p7or8913629td6153gos0daoc', 0, NULL),
(156, 55, 1, 8, '2026-05-07', '12:30:00', '2026-04-29 18:22:54', 'PENDIENTE', NULL, NULL, 'n3b5mv4361tdv9rgsouuc3ss40', 0, NULL),
(157, 129, 2, 8, '2026-04-30', '12:00:00', '2026-04-30 06:28:03', 'COMPLETADA', 'Efectivo', NULL, 'v4n24hpn7h8qbnctv6kn9boa38', 0, NULL),
(158, 130, 2, 8, '2026-04-30', '10:30:00', '2026-04-30 08:08:58', 'COMPLETADA', 'Tarjeta', NULL, '4k7ac25tt1ora8kmneveglck1s', 0, NULL),
(159, 75, 1, 8, '2026-05-08', '17:00:00', '2026-04-30 08:13:09', 'PENDIENTE', NULL, NULL, 'l0pigmdmb05brsffq99ttqlidk', 0, NULL),
(160, 128, 1, 8, '2026-04-30', '14:30:00', '2026-04-30 09:31:18', 'COMPLETADA', 'Efectivo', NULL, 'n2nrq190dlpoup2nvdi3bsrqu8', 0, NULL),
(161, 133, 1, 8, '2026-05-08', '20:00:00', '2026-04-30 11:43:51', 'PENDIENTE', NULL, NULL, '08htb098q12f2m4heqemu4ocpo', 0, NULL),
(162, 128, 1, 8, '2026-05-05', '10:30:00', '2026-04-30 12:43:25', 'PENDIENTE', NULL, NULL, 'adapkrql69euma7pe1bec1cjog', 0, NULL),
(163, 104, 2, 8, '2026-05-14', '18:00:00', '2026-04-30 15:30:56', 'PENDIENTE', NULL, NULL, 'vdg9cvg1epgsbs90c2g95moiho', 0, NULL),
(164, 135, 2, 8, '2026-05-04', '12:00:00', '2026-04-30 20:01:19', 'PENDIENTE', NULL, NULL, '0pkj6dhd52ds99t5r07gvruacg', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icono` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `duracion_min` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `icono`, `precio`, `duracion_min`, `activo`) VALUES
(1, 'Corte', 'Corte limpio y preciso para hombre o niño. Ideal para el día a día: rápido, ordenado y adaptado a tu estilo.', 'corte_clasico.svg', 10.00, 30, 1),
(2, 'Corte + Barba', 'Corte de pelo + perfilado de barba con cuchilla. Todo en una sola cita: pelo impecable y barba definida, con acabado profesional.', 'corte_barba.svg', 14.00, 45, 1),
(3, 'Arreglo de Barba', 'Perfilado completo de barba con cuchilla. Definimos contornos.', 'arreglo_barba.svg', 6.00, 15, 1),
(5, 'Decoloración', 'Aclarado suave del vello facial o corporal. Ideal para cejas, patillas o vellos finos. Resultado natural y sin irritación.', 'decoloracion.svg', 70.00, 90, 0),
(6, 'Mechas', 'Mechas naturales o de contraste. Iluminamos tu look con reflejos personalizados: californianas, babylights o mechas marcadas.', 'mechas.svg', 40.00, 200, 1),
(7, 'Servicio VIP', 'Reserva este servicio y disfruta de una experiencia premium: corte o color personalizado, masaje capilar relajante, tratamiento hidratante y atención 100% dedicada. Ideal para regalarte un rato de relax o prepararte para una ocasión especial.', 'servicio_vip.svg', 35.00, 50, 0),
(11, 'corte niño', 'Corte para menores de 10 años', 'arreglo_barba.svg', 10.00, 30, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '0',
  `token_activacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_activacion` datetime DEFAULT NULL,
  `rol` enum('usuario','empleado','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  `telefono` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `generado` enum('local','web') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `nombre`, `email`, `password_hash`, `ip_address`, `created_at`, `activo`, `token_activacion`, `fecha_activacion`, `rol`, `telefono`, `fecha_nacimiento`, `generado`) VALUES
(40, 'radragon.rad', 'rafa', 'radragon.rad@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$ZjdrLjdqYkVVaWxUQjZyUw$05sxw24FNYXmW7+jqSh5QGW7uiB/FSRfOKIvxvRmKS8', '213.194.148.52', '2026-02-25 10:35:14', 1, NULL, NULL, 'admin', '677377938', '1977-11-14', 'web'),
(43, 'rubeng123455', 'Ruben gutierrez ibañez', 'rubeng123455@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$L09SU3pLWVFzd1JQWHVpVA$a6JJ3RH1JeaDAWQibkKGpbEiHK/Aa7x92MatX84N0JM', '79.116.150.166', '2026-02-26 17:06:59', 1, NULL, NULL, 'admin', '657553377', '1999-02-21', 'web'),
(55, 'alex.perejon52', 'Alejandro perejon torres', 'alex.perejon52@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bER5cHBUTVBRWmNDVm0wLw$NLalGJoRCiHrfrVS8bMmgMtecAoD8DaIPDIitgORjiE', '46.6.203.19', '2026-04-05 18:38:29', 1, NULL, '2026-04-05 20:39:30', 'usuario', '603191178', '1999-01-06', 'web'),
(56, 'jperezfernandez981', 'Joaquin Perez Fernandez ', 'Jperezfernandez981@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$SUt4NXlmZTRMUndqL0huWA$mE91tVZQjfHTERkufTIpObhHtULJzRZfcvfVv34F9SI', '31.4.223.160', '2026-04-05 18:43:05', 1, NULL, '2026-04-05 20:43:42', 'usuario', '666125383', '1999-01-17', 'web'),
(58, 'miguelestevez039', 'Miguel Estévez galan', 'miguelestevez039@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RnFFZDVRUldqcmpVMlplYw$RAtYy0o/RXQK25b8hGu1JxiT8iGpvfjRt22OsjE1T4w', '31.4.199.37', '2026-04-06 09:51:49', 1, NULL, NULL, 'usuario', '692322164', '2005-12-03', 'web'),
(59, 'mcoskar2018', 'Oscar gregorio carmona ponce', 'mcoskar2018@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$eXRvdWNWVk1xMUNZMnlSZg$uChhdfwFFWhv7orP7UdY0EadNE+aO50iVY5x3juQfMY', '79.117.227.238', '2026-04-06 12:21:53', 1, NULL, '2026-04-06 14:22:09', 'usuario', '625353530', '2005-11-26', 'web'),
(60, 'carlosparrenoferrete', 'Carlos Parreño', 'carlosparrenoferrete@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$UzZoSlovOEo5Nlp0RGFZaQ$ac4Wf2mk04ZYfuEr7a5jjRoELx494WII/owMUmQpvVA', '213.143.60.229', '2026-04-09 15:59:33', 1, NULL, '2026-04-09 18:09:48', 'usuario', '615221923', '1997-07-29', 'web'),
(61, 'isaac_acevedo06', 'Isaac Acevedo ', 'isaac_acevedo06@yahoo.com', '$argon2id$v=19$m=65536,t=4,p=1$VFlpMFlORWpROU9hSFhxRw$EMV7OzVjrHozJ5r3pQJqd05dzrjUkpq1zJ2c6IjOqnU', '79.116.164.146', '2026-04-09 18:30:37', 0, 'ab30081646d8e2a19299ac111998da49a2472a4bb155a68dcff89c7edcccf812', NULL, 'usuario', '656359665', '2000-02-07', 'web'),
(62, 'riejos', 'Emilio Ruiz', 'riejos@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RFZaSHJrS1EuTkJsVVVRVg$7hlAfFSndXrL6l9MeXbqbiz1bRwJtrrN76lHrI4EWL4', '83.47.181.86', '2026-04-11 17:02:28', 1, NULL, '2026-04-11 19:04:33', 'usuario', '600950589', '1982-09-11', 'web'),
(63, 'adrianpere05', 'Adrián Perejón Torres ', 'adrianpere05@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$S2ZMdWtUQlJ2MDZBNC9zdw$3xOq064GSDmmp0QFrIu4jZezFoIaVfjr3EMP625JgqE', '81.36.198.164', '2026-04-12 10:28:32', 1, NULL, '2026-04-12 12:29:13', 'usuario', '611461981', '2005-02-18', 'web'),
(65, 'correa', 'CORREA', 'sinemail@correa', '$argon2id$v=19$m=65536,t=4,p=1$eVNvMjhiV1gvN3ZaZ3d6Zg$YoEmgVViRZVLsSGzAGRXHeQl9z47Yw61fYVEwyldCe8', '79.117.227.229', '2026-04-24 08:59:12', 1, NULL, '2026-04-24 10:59:12', 'usuario', '644789261', '2004-02-12', 'local'),
(75, 'alejandromalvar2002', 'Alejandro Malvar Rodríguez', 'alejandromalvar2002@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$SWprcUE0WjE4L0FTc2FsWg$f0h0c/N/DsJgdazPaxGOxxN3LyJGfk7fr3xuZMnEhtg', '79.117.227.241', '2026-04-24 10:37:13', 0, '322efcd789cb8f99d8af32e8c269ccdd736980c12db9f90f6c3f98f6c5b5954e', NULL, 'usuario', '627898291', '2002-10-04', 'web'),
(76, 'ruizdnieto', 'david ruiz', 'ruizdnieto@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$dWE5ZS5xeVBLU0VFUGxqQw$rfJANa2YzbkPBdqm4NhtRdD1kdNAnh2zr6NgfMJWR3M', '2.140.194.231', '2026-04-24 16:49:51', 1, NULL, '2026-04-24 19:01:50', 'usuario', '669360757', '1999-05-21', 'web'),
(78, 'javibetico2004', 'Javier barba naranjo', 'javibetico2004@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$WDh2dWMyWmFTTkdsN2Myaw$jPkcS147YtPCSLrxVCgYUwYqIG36OlJChJ9ntdxwFAE', '83.47.179.229', '2026-04-24 18:04:59', 1, NULL, '2026-04-24 21:29:58', 'usuario', '621034351', '2004-02-29', 'web'),
(79, 'santiagocabezaalonso', 'Santiago Cabeza Alonso', 'santiagocabezaalonso@iesaliscar.es', '$argon2id$v=19$m=65536,t=4,p=1$MmtJMXFMY2c0T01HRy83UQ$fkbMlDER+VKDF1pKUHgW5CAWpuK1MC/kP0IQQ2X+cyo', '85.62.149.128', '2026-04-24 20:31:30', 1, NULL, '2026-04-24 22:33:02', 'usuario', '635933210', '2011-08-24', 'web'),
(81, 'marco.lobera', 'Marco lobera ', 'fernandezlobera10@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$V2QxM0ZQaVpaWm5HbVppUA$QeX3q1z2FhsuXdFSb+4nA909j3td2STUrj7UVqIqftE', '79.117.227.253', '2026-04-24 21:05:03', 1, NULL, '2026-04-24 23:05:03', 'usuario', '608321875', '2002-12-25', 'local'),
(82, 'r.tororafa030', 'Rafa toro', 'r.tororafa030@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$cjRvdk41a2pFb3pXMUZ1RA$kH02+u+zl7jH37ZTAxq2UXTSsAfqeAXgCqn0efB4qu8', '46.6.192.112', '2026-04-25 08:50:32', 1, NULL, '2026-04-25 10:50:47', 'usuario', '633181560', '1995-05-13', 'web'),
(83, 'cuestacng', 'Nuria gil ', 'cuestacng@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$V1czVldJZWpXcHZmZHpZYg$1YNs08f0uadWAsZhaaXxYluP/Oa2kgFAOwHIOpHkXJ0', '185.13.202.154', '2026-04-25 17:49:02', 1, NULL, '2026-04-25 19:49:20', 'usuario', '664830039', '1999-01-24', 'web'),
(84, 'ivan.gutiérrez', 'Ivan Gutiérrez ', 'sinemail@ivan.gutiérrez', '$argon2id$v=19$m=65536,t=4,p=1$cVE2WUhCRUhnNndNU3FaWg$wiQp51sJOD3/OMMhAzth1bsK8mEKX52Gj5uX4cDnsJc', '79.117.100.43', '2026-04-25 18:31:45', 1, NULL, '2026-04-25 20:31:45', 'usuario', '999999999', '1900-01-01', 'local'),
(86, 'ivan.gutiérrez.ibañez', 'Ivan Gutiérrez ibañez', 'ivangutierrezfga24@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$TmxobnI5cmV5eDAzZ0ZYVA$PZUe44kZ4c5r8bkXfi2zIJdHorWCBGrXnf7ZUstETwI', '79.117.100.43', '2026-04-25 18:33:19', 1, NULL, '2026-04-25 20:33:19', 'usuario', '999999999', '1900-01-01', 'local'),
(88, 'hadesinfer', 'Rad Usuario', 'hadesinfer@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$c1lIeVJyNlpmeC9MUnJKaQ$1EXt0ZrfWUyZbW54MROJSBpPfYp1O8pbc0fSJHJuCJ4', '213.194.150.225', '2026-04-26 08:13:05', 1, NULL, '2026-04-26 10:13:23', 'usuario', '677377938', '1977-11-14', 'web'),
(89, 'tomás.entrénate', 'Tomás entrénate ', 'sinemail@tomás.entrénate', '$argon2id$v=19$m=65536,t=4,p=1$TzdMUFJUdkdhQ0x6MlZwVQ$MQEx8IZa8MhNMU/nQwDHriAMnSy89/Dk+b9y53RF2eM', '79.117.100.43', '2026-04-26 09:21:19', 1, NULL, '2026-04-26 11:21:19', 'usuario', '999999999', '1900-01-01', 'local'),
(90, 'aleman', 'Aleman', 'sinemail@aleman', '$argon2id$v=19$m=65536,t=4,p=1$cC9Na2tLdkR1ZE05OHJ5VQ$siANJzw+93MxMVAnrSPvW4WcYSwExlPqB+3NchcRM4k', '79.117.100.43', '2026-04-26 09:22:07', 1, NULL, '2026-04-26 11:22:07', 'usuario', '999999999', '1900-01-01', 'local'),
(91, 'tristancho', 'Tristancho', 'sinemail@tristancho', '$argon2id$v=19$m=65536,t=4,p=1$akRnQ0xaQkc1L0wyNEx2TA$bF5NwDWZjjploOOZP7iDMc77eA6fu1FjjkA/1Bc88Eg', '79.117.100.43', '2026-04-26 09:23:08', 1, NULL, '2026-04-26 11:23:08', 'usuario', '999999999', '1900-01-01', 'local'),
(92, 'juan.diego', 'Juan Diego ', 'sinemail@juan.diego', '$argon2id$v=19$m=65536,t=4,p=1$MEg2ZXBMRHJxQnhnV2Z6Wg$XYg/UcfiVhO+OeBHG2VL38Y4mflG3Fad7v6W5AqTQkA', '79.117.100.43', '2026-04-26 09:24:06', 1, NULL, '2026-04-26 11:24:06', 'usuario', '999999999', '1900-01-01', 'local'),
(93, 'esther.samuel.baloncesto', 'Esther Samuel baloncesto ', 'sinemail@esther.samuel.baloncesto', '$argon2id$v=19$m=65536,t=4,p=1$L0pGdjlOL0tKbmhzL3h1Nw$XgeGS1pb9ezl60mO8NUHHDZPJYdM6LNBQBnRI6MxOKg', '79.117.100.43', '2026-04-26 09:25:06', 1, NULL, '2026-04-26 11:25:06', 'usuario', '999999999', '1900-01-01', 'local'),
(94, 'queso', 'Queso', 'sinemail@queso', '$argon2id$v=19$m=65536,t=4,p=1$TFEweXpCNHM3cWFUV2ZUSA$R2nLEbKmvwmo8HKJTc0CuRowsrTuoZNQtHb8oaoIblg', '79.117.100.43', '2026-04-26 09:29:02', 1, NULL, '2026-04-26 11:29:02', 'usuario', '999999999', '1900-01-01', 'local'),
(95, 'aarongarfillol', 'Aaron Garcia Fillol', 'aarongarfillol@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$OThFdE9hNU9HQ05XZHpteg$3HMSbwwKsupRQaIvh29DP96JxlgRCkjRs5UDwfBqh+M', '46.6.181.59', '2026-04-26 12:16:18', 1, NULL, '2026-04-26 14:16:37', 'usuario', '610905272', '2005-08-25', 'web'),
(96, 'ivnagutierrezfga24', 'Ivan Gutierrez', 'ivnagutierrezfga24@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$Qjlpa3Q4OXAxaWRHaVl2UA$7zdjornTR66pID5RZrz2J1GC8Tu7Q43FA7lRuNvxS5g', '79.116.246.218', '2026-04-26 12:36:22', 0, '52bd9cc7c4978aa64a0f24f360b5ba5da19e2c16fcf3cef5be364d43c72f98e8', NULL, 'usuario', '645325730', '2004-05-04', 'web'),
(97, 'ulisesholgado20', 'Ulises Holgado Rodriguez ', 'ulisesholgado20@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$M2VKcE81T0RSb0pCOGo4eA$xmeORgF2WxChjZ7xl6MGS/Xx52fwxLeyRFW89m7E5bg', '194.220.58.43', '2026-04-26 14:18:39', 0, '3413e1bdaa062a7ee9e10770fc9cf03ad0611936bbf0955d922ff79f40da1986', NULL, 'usuario', '615611783', '2001-06-20', 'web'),
(98, 'holgadito20', 'ulises holgado rodriguez ', 'holgadito20@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bnpySWtQRzlCTlhSVG1vSg$EtNeNFxORTBZkS8mD05JEcMJhM3uRmKDfP5ftZhONOo', '194.220.58.43', '2026-04-26 14:19:45', 1, NULL, '2026-04-26 16:20:01', 'usuario', '615611783', '2001-06-20', 'web'),
(99, 'malalemadrid', 'Manuel Ruiz', 'malalemadrid@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$YnhQckx3MWswRlNwLk9oWQ$wyIecYPhWgWJ3GcQ3z081th5QOzw64UuFvMtQEIRkvY', '37.26.251.83', '2026-04-26 15:31:31', 1, NULL, '2026-04-26 17:31:58', 'usuario', '623018914', '2010-10-27', 'web'),
(100, 'joseantoniotriscas', 'Jose Antonio Tristancho Castillo ', 'joseantoniotriscas@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$d1NrRVF6dlJ4L0RsR2VQMA$/npPDwbGr33SB336uUBMUesW4DXc2byNCqBf52XbmmI', '47.63.248.70', '2026-04-26 17:33:42', 1, NULL, '2026-04-26 19:33:53', 'usuario', '600011218', '2000-12-23', 'web'),
(101, 'andreszamoranomartin', 'Andrés Zamorano ', 'andreszamoranomartin@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RmlpTWZpY3N0TmkwLm4uSg$8Kg6As7L6fQy5M0cCHuzm1y16mD3lYTfpoIR+7JtNuY', '85.52.163.241', '2026-04-26 18:39:30', 1, NULL, '2026-04-26 20:40:23', 'usuario', '678418789', '2005-08-05', 'web'),
(102, 'fernandogalangelo', 'Fernando Galan Gelo', 'fernandogalangelo@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$ODMwRXdGM1hpRWNpclBXZA$i5q1DT3qGQJSs5rEAtAKCBSHF9xjmgYHI8x3sj/VqJM', '86.127.226.30', '2026-04-26 18:40:24', 1, NULL, '2026-04-26 20:41:05', 'usuario', '640118796', '2001-03-22', 'web'),
(103, 'daviddelgadofernandezsev2001', 'David Delgado Fernández', 'daviddelgadofernandezsev2001@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$U2g1by9pOEdKejVJalB4SQ$hwCsSp548+pDx+2N20dOLSu0yTHex1WSQF27LRryXXE', '95.18.50.22', '2026-04-26 21:31:21', 1, NULL, '2026-04-26 23:31:36', 'usuario', '633750230', '2001-02-08', 'web'),
(104, 'alvarocaro93', 'Álvaro caro pineda', 'alvarocaro93@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$U2RyaVM4b1RzNGxsRUVxVA$emcQ7OUWkEBiXCAvJdqBC1UMZkJGAadGW1bJyfdXU8A', '46.6.206.253', '2026-04-27 05:34:01', 1, NULL, '2026-04-27 08:36:10', 'usuario', '691606974', '2026-04-09', 'web'),
(105, 'javier6p', 'Francisco Javier Garcia', 'javier6p@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RDhiNjVIR1A1NXNLMi5JOQ$fmtJFBiEdpqRfX+6IZyJ3z3c1NzAB4Rm3oJA2KCuPNc', '37.26.251.60', '2026-04-27 12:03:07', 1, NULL, '2026-04-27 14:35:44', 'usuario', '605259591', '1989-08-09', 'web'),
(106, 'ivanmafutbol2012', 'Ivan Macías Alonso ', 'ivanmafutbol2012@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$Z2tNZWxnMmYwOXVKbmc4Qw$UsztxKhqVlAyKdC7l6tnu3w1Fw/R1v1ALnOJ6EBQpPc', '31.4.223.35', '2026-04-27 15:23:03', 1, NULL, '2026-04-27 17:23:55', 'usuario', '624823603', '2012-11-23', 'web'),
(107, 'josemanuelvicentev4', 'Jose manuel vicente', 'josemanuelvicentev4@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$eW9TSEx6ZlQwSFl0bURKNA$o0+T3BAjxf3VSolcP7/mebVpQZlKmVDLj2waf0FP5/U', '90.165.36.185', '2026-04-27 17:30:23', 1, NULL, '2026-04-27 19:31:13', 'usuario', '695843543', '1991-06-04', 'web'),
(108, 'rociodelarosacalderon', 'Rocio rosa', 'rociodelarosacalderon@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$dkI0MTlXQ2lySk5wVFZ0eQ$d08f9Z/j/Myfee8c1f/aAsyI5IjbQsbqa7cOBEDjyec', '90.165.36.185', '2026-04-27 17:37:27', 1, NULL, '2026-04-27 19:37:43', 'usuario', '695843543', '2005-04-27', 'web'),
(109, 'pablitord16', 'pablo Rodríguez diaz', 'pablitord16@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$UlBYaGg3TzRiNTZFSTVoRA$oeAFnbVSqY8MdpAOOz9zpuWiXa9OIB7smqgvakpm+34', '86.127.226.141', '2026-04-27 18:20:22', 1, NULL, '2026-04-27 20:20:49', 'usuario', '656430306', '1999-05-11', 'web'),
(110, 'sergio.fernánde', 'Sergio Fernánde', 'sinemail@sergio.fernánde', '$argon2id$v=19$m=65536,t=4,p=1$T1YwZS4zT3RPT0dRS25IYg$T9XxlvbjwqG7+TKx0hEX4S+kYD3C2bc5mnUgGEljVgM', '79.117.227.253', '2026-04-27 21:33:46', 1, NULL, '2026-04-27 23:33:46', 'usuario', '999999999', '1900-01-01', 'local'),
(111, 'sergiofdezsj2001', 'Sergio Fdez', 'sergiofdezsj2001@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$aHdPVXZYa1ZqUWZmLld2bQ$TCgbofR/aYmp+evQx+ubuZGgLGbl6nDyqn7Ze4N6Vz4', '2.140.192.48', '2026-04-28 04:56:47', 1, NULL, '2026-04-28 06:56:59', 'usuario', '627710725', '2001-10-29', 'web'),
(112, 'lobera', 'lobera', 'sinemail@lobera', '$argon2id$v=19$m=65536,t=4,p=1$RnRTc2FmN3hRZTRna084RQ$QIV3c5k9r+QpHEkGh8u87eT01gqsMbf3+GtepO/oc64', '79.117.227.229', '2026-04-28 10:48:03', 1, NULL, '2026-04-28 12:48:03', 'usuario', '999999999', '1900-01-01', 'local'),
(113, 'jorgelf10', 'Jorge Lobera Fernandez', 'jorgelf10@icloud.com', '$argon2id$v=19$m=65536,t=4,p=1$UjhGNTJONWVZMDRtcHBqdg$4MKsqVlBcBpQyHNxQWJYRbDhp9jvKI/30TieGAJWArQ', '31.4.192.93', '2026-04-28 10:52:57', 1, NULL, '2026-04-28 12:54:52', 'usuario', '608224708', '2000-09-01', 'web'),
(114, 'primo.jesus', 'primo jesus', 'sinemail@primo.jesus', '$argon2id$v=19$m=65536,t=4,p=1$YkQudDR5UjQvbzgwL1VqNw$q6vV1vFP780Tw9muG5jMV+8A6k4DjqnE3/HnNENr9h8', '79.117.227.229', '2026-04-28 11:05:52', 1, NULL, '2026-04-28 13:05:52', 'usuario', '999999999', '1900-01-01', 'local'),
(115, 'javi.amigode.queso', 'javi amigo de queso', 'sinemail@javi.amigode.queso', '$argon2id$v=19$m=65536,t=4,p=1$MnhLc20zSklwS0dYUC5aMQ$h3x8J4ndGS6Qoff9UrMz/x9ngAG/xykXzl5FZ1+sVak', '79.117.227.229', '2026-04-28 11:10:26', 1, NULL, '2026-04-28 13:10:26', 'usuario', '999999999', '1900-01-01', 'local'),
(116, 'ricardo.castilla', 'Ricardo castilla', 'ricardo.delacasalimon@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$cDdWaW1MaGZWdTZtd3BSSw$P+859uFwOmEiMAbwpbOaw4DjAPF15p7ko9hGwcq31xw', '79.117.227.229', '2026-04-28 12:06:38', 1, NULL, '2026-04-28 14:06:38', 'usuario', '999999999', '1900-01-01', 'local'),
(117, 'agustín.perejon', 'Agustín Perejon ', 'sinemail@agustín.perejon', '$argon2id$v=19$m=65536,t=4,p=1$aFBpSEQ3MkJ5NzdwU2VLeg$paZvVsm2AATrdxRxOcDKvmDcMCoMTzZcjNGgHNRbHI4', '79.117.227.253', '2026-04-28 13:10:07', 1, NULL, '2026-04-28 15:10:07', 'usuario', '999999999', '1900-01-01', 'local'),
(118, 'pepecapmany9', 'Pepe Capmany mayorga', 'pepecapmany9@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$STBaUTdYTVoydWp5YVQ5Zg$DSFrXHSVOR2YuPVxXHjtRtLilALbB2feyL/f+za5WcQ', '90.165.36.58', '2026-04-28 13:54:19', 1, NULL, '2026-04-28 15:54:43', 'usuario', '645192579', '2003-12-15', 'web'),
(119, 'jose.antonio', 'NU jose antonio ', 'sinemail@jose.antonio', '$argon2id$v=19$m=65536,t=4,p=1$U0pnclIyYm1KMFRJSEQ5Lg$l/WIWp96/FMrzWSN5ldKLrVh+7CoXZZ9dklu5J3qkaE', '79.117.227.229', '2026-04-28 17:34:25', 1, NULL, '2026-04-28 19:34:25', 'usuario', '663318155', '1900-01-01', 'local'),
(120, 'fernando.carranco', 'fernando carranco', 'sinemail@fernando.carranco', '$argon2id$v=19$m=65536,t=4,p=1$ajhpZ1RVbVRsY0g5ck51Mg$v7E1Ti1+pNKaOCQHIeB+ZMLwrkbBwiAgSYfEVPtRZeo', '79.117.227.229', '2026-04-28 19:24:37', 1, NULL, '2026-04-28 21:24:37', 'usuario', '647252496', '1900-01-01', 'local'),
(121, 'miguel', 'miguel ', 'sinemail@miguel', '$argon2id$v=19$m=65536,t=4,p=1$Tm5wR2RoMWwwdk1jYk53SQ$lKEUZAZxTTxbSkzx8wOBWrETG2ALx/XQw6bzlGw+Rrg', '79.117.227.229', '2026-04-28 19:25:43', 1, NULL, '2026-04-28 21:25:43', 'usuario', '660625240', '1900-01-01', 'local'),
(122, 'javielpro2008', 'Francisco Javier Sanchez Moreno', 'javielpro2008@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$QWJuMk1hZENmWVUydENuUw$m7OTlFl5eNeGIz5pykSR0sAqRTm2cqVNekO2zLVfcsY', '77.230.18.8', '2026-04-28 20:37:26', 0, 'd05b2aaeda8df70a0efe79049ddbcc0eeb87bf996b34ab8cbbc5f22c0d76bbad', NULL, 'usuario', '622678812', '2008-11-03', 'web'),
(123, 'franciscojaviersanchezmoreno', 'Francisco Javier', 'franciscojaviersanchezmoreno@iesaliscar.es', '$argon2id$v=19$m=65536,t=4,p=1$U0dPZHFkYk9XckpuN0syTA$VYuTfOFyN5RFdVjskKgcqOK7juRQHJ1hogjN3ODzJIA', '77.230.18.8', '2026-04-28 20:39:18', 1, NULL, '2026-04-28 22:39:39', 'usuario', '622678812', '2008-11-03', 'web'),
(124, 'marcospedrosagarcia', 'Marcos pedrosa garcia', 'marcospedrosagarcia@iesaliscar.es', '$argon2id$v=19$m=65536,t=4,p=1$YUdELmlVTHhaWURtV0pYSw$ENCMSiOifa1yhpI3ZndJbXg8dlM7oF1ZWzWVoDVRbDc', '95.18.67.30', '2026-04-28 20:57:14', 1, NULL, '2026-04-29 07:41:19', 'usuario', '632108753', '2009-09-28', 'web'),
(125, 'samuelaragonponce', 'Samuel Aragon ponce ', 'samuelaragonponce@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$TXA2VlVPc05LWk9ZaGk0eg$fsghMenW/Gt6zgxxZnOHumNQedCXpzbJ0b1zLadBZxw', '46.6.180.145', '2026-04-28 21:07:11', 1, NULL, '2026-04-28 23:09:13', 'usuario', '600480255', '2008-08-27', 'web'),
(126, 'manuelhomeramos', 'Manuel Suárez Reina', 'manuelhomeramos@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$QVcyS3REZy5BcjM0Nno3cA$hem78Id0bs8FdkTqFNBbIlaxgIO0p2p8ErEjnN46iQY', '87.218.208.95', '2026-04-29 12:05:06', 1, NULL, '2026-04-29 14:05:48', 'usuario', '633402208', '2005-05-04', 'web'),
(127, 'ortizgalan2008', 'Aaron Ortiz', 'ortizgalan2008@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$cWRZeDRsV0NMd1dLZVBsSg$8FWuJE5xc0yeEAJo6YF7VssF8jAniOdzUukWYjWxz8A', '90.163.233.43', '2026-04-29 13:56:25', 1, NULL, '2026-04-29 15:56:59', 'usuario', '608292684', '2008-12-20', 'web'),
(128, 'mateo_1905', 'Mateo Suarez', 'mateo_1905@hotmail.es', '$argon2id$v=19$m=65536,t=4,p=1$M3JqZy9KUUh1T2xMZTM2VA$b1drO/PCRGCMlhWEAnsL1O6+K5pmTrwuUmrdDEUBNrA', '83.47.180.92', '2026-04-30 00:11:17', 1, NULL, '2026-04-30 15:22:06', 'usuario', '664708333', '1994-04-05', 'web'),
(129, 'onio', 'Onio', 'sinemail@onio', '$argon2id$v=19$m=65536,t=4,p=1$MzdHcUl6VnhaRFNRRS90eg$m1khM01mD4KUOP2nmGKjm6aaSjUV8evHX1f04yTN++U', '79.116.116.101', '2026-04-30 06:27:41', 1, NULL, '2026-04-30 08:27:41', 'usuario', '999999999', '1900-01-01', 'local'),
(130, 'paco', 'paco hombre vva', 'sinemail@paco', '$argon2id$v=19$m=65536,t=4,p=1$ZG5oc0pxVjJ2VUtScUcueQ$EfCkARmzd8dq55HaYE4b4ZiOAIcQwutLMJAb9/R28OE', '79.117.227.249', '2026-04-30 08:08:40', 1, NULL, '2026-04-30 10:08:40', 'usuario', '66891806', '1900-01-01', 'local'),
(131, 'alfonsofont2008', 'alfonso font ', 'alfonsofont2008@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NnY1UUVzbUJzMEpTOGgudg$VhcASTnQp5gXYdIxieDjpa2EPqpYJaq8xue+xNNYHDg', '79.116.124.24', '2026-04-30 09:35:10', 1, NULL, '2026-04-30 15:22:02', 'usuario', '656825808', '2008-01-21', 'web'),
(132, 'jdominguezbegines', 'jose dominguez', 'jdominguezbegines@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$VFcubkEwVE1PRmh6NUo5TQ$6vBGoOqK4YhymzP3ad4LJWfTKO2SfRW7t38tcx6XYa0', '79.117.64.239', '2026-04-30 10:53:03', 1, NULL, '2026-04-30 12:53:23', 'usuario', '610085822', '2005-11-01', 'web'),
(133, 'pedrocalero080', 'Pedro Calero', 'pedrocalero080@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$UWtDY3pxeFNuUG53SWNqag$lwN585aAtX9paPT7bwMIXBTmtggf2p8AY2FIvT4h44s', '79.117.106.247', '2026-04-30 11:40:32', 1, NULL, '2026-04-30 13:40:47', 'usuario', '635843520', '2004-01-29', 'web'),
(134, 'jlcuevas96', 'José Luis Cuevas Romero', 'jlcuevas96@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$a1lESVlibE9YN1ZkbTRlQg$PausOq1zzQ5e8KvisGe1t73YlQMR0YB42aUJeD8ekiI', '79.117.100.88', '2026-04-30 12:53:31', 1, NULL, '2026-04-30 14:55:06', 'usuario', '635082543', '1996-10-03', 'web'),
(135, 'santi.huevo', 'Santi huevo', 'sinemail@santi.huevo', '$argon2id$v=19$m=65536,t=4,p=1$Nm5mTWR4Vm81YnhnVlRBTQ$QzU+5frU6WJ0O3TsC0d7TrF3h+6aZaPVMyEkiE6O49U', '79.117.227.249', '2026-04-30 19:59:56', 1, NULL, '2026-04-30 21:59:56', 'usuario', '999999999', '1900-01-01', 'local');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_etiquetas`
--

CREATE TABLE `usuarios_etiquetas` (
  `usuario_id` int NOT NULL,
  `etiqueta_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cupones_usuario`
--
ALTER TABLE `cupones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_promo` (`usuario_id`,`promocion_id`),
  ADD KEY `promocion_id` (`promocion_id`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_dia`);

--
-- Indices de la tabla `horario_excepciones`
--
ALTER TABLE `horario_excepciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `logs_promociones`
--
ALTER TABLE `logs_promociones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reserva_id` (`reserva_id`);

--
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `peluqueros`
--
ALTER TABLE `peluqueros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `usuario_id_2` (`usuario_id`),
  ADD KEY `activo` (`activo`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_promo_etiqueta` (`etiqueta_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_id` (`servicio_id`),
  ADD KEY `idx_fecha_hora` (`fecha`,`hora`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `peluquero_id` (`peluquero_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`usuario`),
  ADD KEY `activo` (`activo`);

--
-- Indices de la tabla `usuarios_etiquetas`
--
ALTER TABLE `usuarios_etiquetas`
  ADD PRIMARY KEY (`usuario_id`,`etiqueta_id`),
  ADD KEY `etiqueta_id` (`etiqueta_id`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cupones_usuario`
--
ALTER TABLE `cupones_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `horario_excepciones`
--
ALTER TABLE `horario_excepciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `logs_promociones`
--
ALTER TABLE `logs_promociones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=419;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `peluqueros`
--
ALTER TABLE `peluqueros`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `logs_promociones`
--
ALTER TABLE `logs_promociones`
  ADD CONSTRAINT `logs_promociones_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `peluqueros`
--
ALTER TABLE `peluqueros`
  ADD CONSTRAINT `peluqueros_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD CONSTRAINT `fk_promo_etiqueta` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios_etiquetas`
--
ALTER TABLE `usuarios_etiquetas`
  ADD CONSTRAINT `usuarios_etiquetas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_etiquetas_ibfk_2` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
