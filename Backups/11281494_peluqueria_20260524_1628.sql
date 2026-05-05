-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: PMYSQL196.dns-servicio.com:3306
-- Tiempo de generación: 27-04-2026 a las 16:28:02
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
(166, 43, 'LOGIN', 'auth.php', '{\"email\":\"rubeng123455@gmail.com\",\"usuario\":\"rubeng123455\",\"rol\":\"admin\"}', 0, '79.117.227.229', '2026-04-27 16:09:22');

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
(114, 89, 1, 8, '2026-04-27', '17:30:00', '2026-04-26 09:21:40', 'PENDIENTE', NULL, NULL, 'f2t13d6p4e9s0bbfsbl1kogql0', 0, NULL),
(115, 90, 1, 8, '2026-04-27', '12:30:00', '2026-04-26 09:22:27', 'COMPLETADA', 'Efectivo', NULL, 'a5sodf10olr8nacp45d01t1t3g', 0, NULL),
(116, 91, 1, 8, '2026-04-27', '18:00:00', '2026-04-26 09:23:47', 'ANULADA LOCAL', NULL, 'a', 'a509g1j47eql9mgeamlcdgbnlc', 0, NULL),
(117, 92, 1, 8, '2026-04-27', '19:00:00', '2026-04-26 09:24:34', 'PENDIENTE', NULL, NULL, '308bnf3k39gk5nv32cee02i030', 0, NULL),
(118, 93, 1, 8, '2026-04-27', '20:00:00', '2026-04-26 09:25:39', 'PENDIENTE', NULL, NULL, 'ntnv61339fmdg0dt1sn8oa9bjk', 0, NULL),
(119, 81, 1, 8, '2026-04-29', '16:30:00', '2026-04-26 09:26:34', 'PENDIENTE', NULL, NULL, 'vhs564g01dhma7g21frtomg5jo', 0, NULL),
(120, 81, 1, 8, '2026-04-29', '17:00:00', '2026-04-26 09:26:56', 'PENDIENTE', NULL, NULL, 'b1cqej4vffohn8clfmssrjv9d0', 0, NULL),
(121, 60, 2, 8, '2026-04-29', '18:15:00', '2026-04-26 09:27:37', 'PENDIENTE', NULL, NULL, 'u5ab8r6m7lv7heg10kh5m5ivvo', 0, NULL),
(122, 94, 2, 8, '2026-04-30', '17:15:00', '2026-04-26 09:29:35', 'PENDIENTE', NULL, NULL, 'b4jesejt4ffvv289dqv82jbd4s', 0, NULL),
(123, 79, 1, 8, '2026-04-30', '18:00:00', '2026-04-26 09:30:12', 'PENDIENTE', NULL, NULL, 'ldi2qh5doedm346pi7sk58gvg0', 0, NULL),
(124, 78, 2, 8, '2026-04-30', '18:30:00', '2026-04-26 09:30:42', 'PENDIENTE', NULL, NULL, 'ok6nolc799psku15llsai5rp5k', 0, NULL),
(125, 55, 1, 8, '2026-04-30', '13:00:00', '2026-04-26 09:31:37', 'PENDIENTE', NULL, NULL, 'vs6ma69kbifmii825l5v4epjd0', 0, NULL),
(126, 56, 1, 8, '2026-05-04', '19:00:00', '2026-04-26 11:28:34', 'PENDIENTE', NULL, NULL, 'c6oil1ssn1675jjrvec34nd9h4', 0, NULL),
(127, 101, 1, 8, '2026-04-30', '19:45:00', '2026-04-26 18:42:49', 'PENDIENTE', NULL, NULL, 'ckkmmkfgkp3tadjoai73m8p6jk', 0, NULL),
(128, 102, 1, 8, '2026-04-27', '12:00:00', '2026-04-26 18:51:36', 'COMPLETADA', 'Tarjeta', NULL, 'dicuniqcbn4e9dhdcdrg4o514s', 0, NULL),
(129, 103, 1, 8, '2026-05-08', '16:30:00', '2026-04-26 21:32:35', 'PENDIENTE', NULL, NULL, 'r5a4dabct9ijvnjtdnlp45h70s', 0, NULL),
(130, 63, 1, 8, '2026-04-30', '19:15:00', '2026-04-27 07:11:44', 'PENDIENTE', NULL, NULL, 'jpu14igbdffbnp60stj6a9sjh8', 0, NULL),
(131, 58, 1, 8, '2026-04-30', '13:30:00', '2026-04-27 08:32:01', 'PENDIENTE', NULL, NULL, '7k54b27jucjlvrlejrq5jd1mf0', 0, NULL),
(132, 100, 2, 8, '2026-04-28', '19:45:00', '2026-04-27 09:04:18', 'ANULADA LOCAL', NULL, 'mas tarde', 'lgo6e6t9d569gpnnj45q46p5k4', 0, NULL),
(133, 105, 3, 8, '2026-04-30', '16:45:00', '2026-04-27 12:41:02', 'PENDIENTE', NULL, NULL, 'r3vpnmutov1cmmrqeo58u18vh4', 0, NULL);

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
(58, 'miguelestevez039', 'Miguel Estévez galan', 'miguelestevez039@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$cEd6YUJYcjQxNWpSd2RPbA$SD09cXz0kRV31Jqfve0QfbvkHADPGVoiFkMQFVcC5M8', '31.4.199.37', '2026-04-06 09:51:49', 1, 'e22f21c56fbb34a11687fc346de7f9d9454b44d222f2c3ea90b194784f359735', NULL, 'usuario', '692322164', '2005-12-03', 'web'),
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
(88, 'hadesinfer', 'Rad Usuario', 'hadesinfer@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$cXBhZ2lidndsM0ZhaW1OWg$6f0UXAetsBcbHMu71OvS+tJ7hL4bAt9pW6zKnEidA9w', '213.194.150.225', '2026-04-26 08:13:05', 1, '83476e99f0b2be554b135e97d2743bcc87fe09b628f992b75b2a6b990a5acfc3', '2026-04-26 10:13:23', 'usuario', '677377938', '1977-11-14', 'web'),
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
(105, 'javier6p', 'Francisco Javier Garcia', 'javier6p@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RDhiNjVIR1A1NXNLMi5JOQ$fmtJFBiEdpqRfX+6IZyJ3z3c1NzAB4Rm3oJA2KCuPNc', '37.26.251.60', '2026-04-27 12:03:07', 1, NULL, '2026-04-27 14:35:44', 'usuario', '605259591', '1989-08-09', 'web');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

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
