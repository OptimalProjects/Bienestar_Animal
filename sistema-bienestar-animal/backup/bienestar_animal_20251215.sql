-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Dec 15, 2025 at 09:09 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bienestar_animal`
--

-- --------------------------------------------------------

--
-- Table structure for table `adopciones`
--

DROP TABLE IF EXISTS `adopciones`;
CREATE TABLE `adopciones` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `animal_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adoptante_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `estado` enum('solicitada','en_evaluacion','aprobada','rechazada','completada','revocada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solicitada',
  `evaluador_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `contrato_firmado` tinyint(1) NOT NULL DEFAULT '0',
  `contrato_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci,
  `motivo_revocacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adopciones`
--

INSERT INTO `adopciones` (`id`, `animal_id`, `adoptante_id`, `fecha_solicitud`, `fecha_aprobacion`, `fecha_entrega`, `estado`, `evaluador_id`, `observaciones`, `contrato_firmado`, `contrato_url`, `motivo_rechazo`, `motivo_revocacion`, `created_at`, `updated_at`) VALUES
('8de779fd-3f11-4272-a720-ad1b23dbd959', '087b52be-fe6e-4094-b057-60b8c35bab78', 'aa040b6f-9dd7-4692-9427-c2a470288e81', '2025-12-04 01:43:21', '2025-12-10 01:43:21', NULL, 'aprobada', NULL, 'Adopción de prueba para testing', 0, NULL, NULL, NULL, '2025-12-11 01:43:22', '2025-12-11 01:43:22');

-- --------------------------------------------------------

--
-- Table structure for table `adoptantes`
--

DROP TABLE IF EXISTS `adoptantes`;
CREATE TABLE `adoptantes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('CC','CE','TI','PA','PEP') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_vivienda` enum('casa','apartamento','finca','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tiene_patio` tinyint(1) NOT NULL DEFAULT '0',
  `experiencia_animales` text COLLATE utf8mb4_unicode_ci,
  `num_personas_hogar` int DEFAULT NULL,
  `estado` enum('activo','inactivo','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adoptantes`
--

INSERT INTO `adoptantes` (`id`, `nombres`, `apellidos`, `tipo_documento`, `numero_documento`, `fecha_nacimiento`, `telefono`, `email`, `direccion`, `tipo_vivienda`, `tiene_patio`, `experiencia_animales`, `num_personas_hogar`, `estado`, `created_at`, `updated_at`) VALUES
('aa040b6f-9dd7-4692-9427-c2a470288e81', 'María', 'García López', 'CC', '1234567890', '1990-05-15', '3001234567', 'maria.garcia@email.com', 'Calle 10 # 20-30, Barrio San Antonio', 'casa', 1, 'He tenido perros toda mi vida', 3, 'activo', '2025-12-11 01:43:05', '2025-12-11 01:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `animals`
--

DROP TABLE IF EXISTS `animals`;
CREATE TABLE `animals` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_unico` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raza` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` enum('macho','hembra','desconocido') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edad_aproximada` int DEFAULT NULL COMMENT 'Edad en meses',
  `peso_actual` decimal(6,2) DEFAULT NULL COMMENT 'Peso en kg',
  `color` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamanio` enum('pequenio','mediano','grande','muy_grande') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senias_particulares` text COLLATE utf8mb4_unicode_ci,
  `foto_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `galeria_fotos` json DEFAULT NULL,
  `fecha_rescate` date DEFAULT NULL,
  `ubicacion_rescate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('en_calle','en_refugio','en_adopcion','adoptado','fallecido','en_tratamiento') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_calle',
  `estado_salud` enum('critico','grave','estable','bueno','excelente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estable',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `animals`
--

INSERT INTO `animals` (`id`, `codigo_unico`, `nombre`, `especie`, `raza`, `sexo`, `edad_aproximada`, `peso_actual`, `color`, `tamanio`, `senias_particulares`, `foto_principal`, `galeria_fotos`, `fecha_rescate`, `ubicacion_rescate`, `estado`, `estado_salud`, `observaciones`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('087b52be-fe6e-4094-b057-60b8c35bab78', 'AN-2025-60117', 'Rocky', 'perro', 'Pitbull', 'macho', 36, 32.00, 'Atigrado', 'grande', NULL, NULL, NULL, '2025-09-24', NULL, 'en_tratamiento', 'estable', 'Rescatado de situacion de maltrato, en recuperacion. Esterilizado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('0902429f-5fec-412e-8665-b6a137e1d1c2', 'AN-2025-75005', 'Garfield', 'gato', 'Mestizo', 'macho', 48, 6.50, 'Naranja', 'mediano', NULL, NULL, NULL, '2025-07-10', NULL, 'adoptado', 'bueno', 'Gato tranquilo y gordito. Esterilizado, vacunado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('0e17e119-88e9-4210-aafc-fcd66ddee738', 'AN-2025-68040', 'Pelusa', 'gato', 'Angora', 'hembra', 18, 3.80, 'Blanco', 'pequenio', NULL, NULL, NULL, '2025-07-11', NULL, 'en_adopcion', 'excelente', 'Gata muy elegante, ideal para departamento. Esterilizada, vacunada y desparasitada.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('339a507a-44c3-428e-bb7d-ad6d992fd6d2', 'AN-2025-89174', 'Michi', 'gato', 'Mestizo', 'macho', 24, 4.50, 'Naranja atigrado', 'pequenio', NULL, NULL, NULL, '2025-07-07', NULL, 'en_adopcion', 'excelente', 'Gato muy carinoso, le gusta dormir en el sol. Esterilizado, vacunado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('36e5d54f-a234-4a0c-ba5c-8eb72be3afb5', 'AN-2025-12499', 'Coco', 'otro', 'Conejo Mini Lop', 'macho', 12, 2.00, 'Blanco y gris', 'pequenio', 'Conejo', NULL, NULL, '2025-08-04', NULL, 'en_adopcion', 'bueno', 'Conejo muy docil, ideal como primera mascota. Esterilizado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('610573c2-1c10-459e-8d59-2f9e5e1463ec', '4564jhhhhh', '4564jhhhhh', 'gato', 'gris', 'macho', 0, NULL, 'blanco', NULL, NULL, NULL, NULL, '2025-12-10', '3.4575368514497837,-76.53354048728944', 'en_calle', 'estable', 'feo', '0e49e9fc-e378-4ed3-9774-930696470497', NULL, '2025-12-10 23:36:44', '2025-12-10 23:36:44', NULL),
('64ea2e7d-42fb-4e7a-92b9-8549047d1661', '4564jhhhhh5', '4564jhhhhh5', 'perro', 'gris', 'macho', 0, NULL, 'blanco', NULL, NULL, NULL, NULL, '2025-12-10', '3.4558829313930506,-76.53408765792848', 'en_refugio', 'estable', 'feo', '0e49e9fc-e378-4ed3-9774-930696470497', NULL, '2025-12-10 23:41:19', '2025-12-10 23:41:19', NULL),
('6a2e62e6-50ef-42bf-8718-d2facaa471b4', 'AN-2025-13987', 'Max', 'perro', 'Golden Retriever', 'macho', 24, 28.50, 'Dorado', 'grande', NULL, NULL, NULL, '2025-08-15', NULL, 'en_adopcion', 'excelente', 'Perro muy carinoso y jugueton, ideal para familias con ninos. Esterilizado, vacunado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('6c574a00-7a29-4fbe-b675-53b534e9c195', 'AN-2025-38012', 'Simba', 'gato', 'Persa', 'macho', 36, 5.00, 'Gris', 'pequenio', NULL, NULL, NULL, '2025-07-31', NULL, 'en_tratamiento', 'estable', 'Gato rescatado con problemas respiratorios, en tratamiento. Esterilizado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('725884be-0305-4c40-b82d-5306bc1050aa', 'AN-2025-94239', 'Bella', 'perro', 'Labrador', 'hembra', 48, 25.00, 'Chocolate', 'grande', NULL, NULL, NULL, '2025-09-18', NULL, 'en_adopcion', 'excelente', 'Perra tranquila, ideal para personas mayores. Esterilizada, vacunada y desparasitada.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('8307e940-aeaf-4f14-a3aa-53e43a7c58dc', 'AN-2025-34873', 'Luna', 'perro', 'Mestizo', 'hembra', 12, 15.00, 'Negro con manchas blancas', 'mediano', NULL, NULL, NULL, '2025-10-27', NULL, 'en_adopcion', 'bueno', 'Cachorra rescatada de la calle, muy sociable. Esterilizada, vacunada y desparasitada.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('a7ae4728-4079-41a9-a750-45d657aea2f8', 'AN-2025-37060', 'Negrita', 'gato', 'Mestizo', 'hembra', 12, 3.20, 'Negro', 'pequenio', NULL, NULL, NULL, '2025-07-06', NULL, 'en_adopcion', 'bueno', 'Gata timida pero muy dulce una vez toma confianza. Esterilizada, vacunada y desparasitada.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('a896a49c-4f0e-4c59-9d95-473e4bcb8d6c', 'AN-2025-13843', 'Thor', 'perro', 'Pastor Aleman', 'macho', 18, 35.00, 'Negro y cafe', 'muy_grande', NULL, NULL, NULL, '2025-07-30', NULL, 'en_refugio', 'bueno', 'Perro guardian, necesita espacio amplio. Vacunado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('c571eb1f-1590-45a1-b3b8-ccff266171c8', 'M123456789', 'M123456789', 'felino', 'gris', 'macho', 21, NULL, 'blanco', 'mediano', NULL, NULL, NULL, '2025-12-09', '3.4541694377228565,-76.53554677963258', 'en_adopcion', 'estable', 'feoooo', '0e49e9fc-e378-4ed3-9774-930696470497', '0e49e9fc-e378-4ed3-9774-930696470497', '2025-12-11 08:32:11', '2025-12-11 09:36:55', NULL),
('ed2828b2-9fd6-430c-98e2-ee1309984a9f', 'AN-2025-43847', 'Nina', 'perro', 'Mestizo pequeno', 'hembra', 6, 5.50, 'Cafe claro', 'pequenio', NULL, NULL, NULL, '2025-10-03', NULL, 'en_refugio', 'estable', 'Cachorra recien llegada en periodo de observacion.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('f45fbb68-1aec-486d-891e-2c043ec69a25', 'AN-2025-59758', 'Toby', 'perro', 'French Poodle', 'macho', 60, 8.00, 'Blanco', 'pequenio', NULL, NULL, NULL, '2025-08-16', NULL, 'en_adopcion', 'bueno', 'Perro senior muy dulce, busca hogar tranquilo. Esterilizado, vacunado y desparasitado.', NULL, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cirugias`
--

DROP TABLE IF EXISTS `cirugias`;
CREATE TABLE `cirugias` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `historial_clinico_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_cirugia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_programada` date NOT NULL,
  `fecha_realizacion` datetime DEFAULT NULL,
  `cirujano_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anestesiologo_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asistentes` json DEFAULT NULL,
  `duracion` int DEFAULT NULL COMMENT 'Duración en minutos',
  `tipo_anestesia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complicaciones` text COLLATE utf8mb4_unicode_ci,
  `resultado` text COLLATE utf8mb4_unicode_ci,
  `postoperatorio` text COLLATE utf8mb4_unicode_ci,
  `seguimiento_requerido` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('programada','realizada','cancelada','postergada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'programada',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cirugias`
--

INSERT INTO `cirugias` (`id`, `historial_clinico_id`, `tipo_cirugia`, `descripcion`, `fecha_programada`, `fecha_realizacion`, `cirujano_id`, `anestesiologo_id`, `asistentes`, `duracion`, `tipo_anestesia`, `complicaciones`, `resultado`, `postoperatorio`, `seguimiento_requerido`, `estado`, `created_at`, `updated_at`) VALUES
('323610f1-b664-4053-8bb7-7f737096f397', '48de479b-6e5a-47f6-90db-ca60c51dbb39', 'esterilizacion', 'Esterilización realizada. obs', '2025-12-11', '2025-12-11 00:00:00', 'c5a20de1-274a-4092-a0cb-e378aeff010f', NULL, NULL, NULL, NULL, NULL, 'exitosa', 'obs', 1, 'realizada', '2025-12-11 09:49:36', '2025-12-11 09:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `consultas`
--

DROP TABLE IF EXISTS `consultas`;
CREATE TABLE `consultas` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `historial_clinico_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `veterinario_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_consulta` datetime NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `sintomas` text COLLATE utf8mb4_unicode_ci,
  `signos_vitales` json DEFAULT NULL,
  `temperatura` decimal(4,2) DEFAULT NULL COMMENT 'Temperatura en °C',
  `frecuencia_cardiaca` int DEFAULT NULL COMMENT 'Latidos por minuto',
  `frecuencia_respiratoria` int DEFAULT NULL COMMENT 'Respiraciones por minuto',
  `peso` decimal(6,2) DEFAULT NULL COMMENT 'Peso en kg',
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `diagnostico_diferencial` text COLLATE utf8mb4_unicode_ci,
  `plan_diagnostico` text COLLATE utf8mb4_unicode_ci,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `recomendaciones` text COLLATE utf8mb4_unicode_ci,
  `proxima_cita` date DEFAULT NULL,
  `tipo_consulta` enum('inicial','control','emergencia','seguimiento') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inicial',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `denunciantes`
--

DROP TABLE IF EXISTS `denunciantes`;
CREATE TABLE `denunciantes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `denuncias`
--

DROP TABLE IF EXISTS `denuncias`;
CREATE TABLE `denuncias` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_ticket` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `denunciante_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_denuncia` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `canal_recepcion` enum('web','telefono','presencial','email','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_denuncia` enum('maltrato','abandono','animal_herido','animal_peligroso','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `evidencias` json DEFAULT NULL,
  `prioridad` enum('baja','media','alta','urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `estado` enum('recibida','en_revision','asignada','en_atencion','resuelta','cerrada','desestimada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibida',
  `responsable_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `observaciones_resolucion` text COLLATE utf8mb4_unicode_ci,
  `es_anonima` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `denuncias`
--

INSERT INTO `denuncias` (`id`, `numero_ticket`, `denunciante_id`, `fecha_denuncia`, `canal_recepcion`, `tipo_denuncia`, `descripcion`, `ubicacion`, `latitud`, `longitud`, `evidencias`, `prioridad`, `estado`, `responsable_id`, `fecha_asignacion`, `fecha_resolucion`, `observaciones_resolucion`, `es_anonima`, `created_at`, `updated_at`) VALUES
('3af27825-0739-4fe7-afb2-9e8310f5e5c4', 'DN-2025-78551', NULL, '2025-12-11 01:04:15', 'web', 'maltrato', 'Observaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionalesObservaciones adicionales', 'Calle 14 #19-78', 3.45584411, -76.58165932, NULL, 'alta', 'recibida', NULL, NULL, NULL, NULL, 1, '2025-12-11 01:04:15', '2025-12-11 01:04:15'),
('ac826666-d612-4e04-82bf-2154c8092be8', 'DN-2025-48592', NULL, '2025-12-11 00:58:23', 'web', 'abandono', 'Animal abandonado en la calle, lleva varios dias sin comida ni agua', 'Calle 15 #23-45, Barrio El Centro', 3.45160000, -76.53190000, NULL, 'media', 'recibida', NULL, NULL, NULL, NULL, 1, '2025-12-11 00:58:23', '2025-12-11 00:58:23');

-- --------------------------------------------------------

--
-- Table structure for table `eventos_auditoria`
--

DROP TABLE IF EXISTS `eventos_auditoria`;
CREATE TABLE `eventos_auditoria` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trace_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `resultado` enum('exitoso','fallido','denegado') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `examenes_laboratorio`
--

DROP TABLE IF EXISTS `examenes_laboratorio`;
CREATE TABLE `examenes_laboratorio` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consulta_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_examen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_resultado` date DEFAULT NULL,
  `laboratorio` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resultados` json DEFAULT NULL,
  `interpretacion` text COLLATE utf8mb4_unicode_ci,
  `archivos_adjuntos` json DEFAULT NULL,
  `estado` enum('solicitado','en_proceso','completado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solicitado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historiales_clinicos`
--

DROP TABLE IF EXISTS `historiales_clinicos`;
CREATE TABLE `historiales_clinicos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `animal_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_apertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','cerrado','archivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `historiales_clinicos`
--

INSERT INTO `historiales_clinicos` (`id`, `animal_id`, `fecha_apertura`, `estado`, `created_at`, `updated_at`) VALUES
('06dfffee-c4d5-42c8-a20d-92d38b0109c4', 'a7ae4728-4079-41a9-a750-45d657aea2f8', '2025-07-06 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('092e3ff0-0da8-4dfd-bbb5-c7d2e9bb94d6', 'ed2828b2-9fd6-430c-98e2-ee1309984a9f', '2025-10-03 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('0afc078e-8dcf-4221-86b5-833ced8bbbe0', '087b52be-fe6e-4094-b057-60b8c35bab78', '2025-09-24 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('11f279a5-d9df-494d-a8fa-fe6826cf72c9', 'a896a49c-4f0e-4c59-9d95-473e4bcb8d6c', '2025-07-30 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('15eb9e64-6e09-4dd6-874e-e19d4a0c36ab', '8307e940-aeaf-4f14-a3aa-53e43a7c58dc', '2025-10-27 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('48de479b-6e5a-47f6-90db-ca60c51dbb39', 'c571eb1f-1590-45a1-b3b8-ccff266171c8', '2025-12-11 08:32:11', 'activo', '2025-12-11 08:32:11', '2025-12-11 08:32:11'),
('50c23c15-718c-4728-b5fd-82d85199b87d', '0e17e119-88e9-4210-aafc-fcd66ddee738', '2025-07-11 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('6c901d59-826e-4d70-927a-34d800759d89', '610573c2-1c10-459e-8d59-2f9e5e1463ec', '2025-12-10 23:36:44', 'activo', '2025-12-10 23:36:44', '2025-12-10 23:36:44'),
('84de3108-4faf-49ea-bb9d-846dfb2b4550', 'f45fbb68-1aec-486d-891e-2c043ec69a25', '2025-08-16 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('a11a8506-d6a1-4b47-bdc7-10b65a93c46b', '6c574a00-7a29-4fbe-b675-53b534e9c195', '2025-07-31 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('a6b1595c-b93d-49d5-af8f-8316028ea47b', '64ea2e7d-42fb-4e7a-92b9-8549047d1661', '2025-12-10 23:41:19', 'activo', '2025-12-10 23:41:19', '2025-12-10 23:41:19'),
('b1f162b8-bd8c-4cb3-a779-4d06563ec577', '6a2e62e6-50ef-42bf-8718-d2facaa471b4', '2025-08-15 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('cf7c6ba6-906a-453e-82e6-1a5cd6a2a060', '725884be-0305-4c40-b82d-5306bc1050aa', '2025-09-18 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('d03d2187-2ddf-44b7-bc17-3a3f476b9d65', '0902429f-5fec-412e-8665-b6a137e1d1c2', '2025-07-10 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('f10b3bb1-b630-4d93-868b-d04a25634c54', '339a507a-44c3-428e-bb7d-ad6d992fd6d2', '2025-07-07 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('fad3d09c-0fe3-4a26-a7b0-47353aa2e441', '36e5d54f-a234-4a0c-ba5c-8eb72be3afb5', '2025-08-04 00:00:00', 'activo', '2025-12-10 21:45:13', '2025-12-10 21:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `indicadores`
--

DROP TABLE IF EXISTS `indicadores`;
CREATE TABLE `indicadores` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formula` text COLLATE utf8mb4_unicode_ci,
  `frecuencia_actualizacion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insumos`
--

DROP TABLE IF EXISTS `insumos`;
CREATE TABLE `insumos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventarios`
--

DROP TABLE IF EXISTS `inventarios`;
CREATE TABLE `inventarios` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_actual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_minima` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `proveedor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicamentos`
--

DROP TABLE IF EXISTS `medicamentos`;
CREATE TABLE `medicamentos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tratamiento_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosis` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `frecuencia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duracion_dias` int NOT NULL,
  `via_administracion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instrucciones_especiales` text COLLATE utf8mb4_unicode_ci,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_22_205530_create_usuarios_table', 1),
(5, '2025_11_22_205640_create_roles_table', 1),
(6, '2025_11_22_205641_create_permisos_table', 1),
(7, '2025_11_22_205642_create_usuario_rol_table', 1),
(8, '2025_11_22_205643_create_rol_permiso_table', 1),
(9, '2025_11_22_205644_create_animals_table', 1),
(10, '2025_11_22_205645_create_historiales_clinicos_table', 1),
(11, '2025_11_22_205646_create_veterinarios_table', 1),
(12, '2025_11_22_205647_create_consultas_table', 1),
(13, '2025_11_22_205648_create_examenes_laboratorio_table', 1),
(14, '2025_11_22_205648_create_tipos_vacunas_table', 1),
(15, '2025_11_22_205649_create_vacunas_table', 1),
(16, '2025_11_22_205650_create_recordatorios_vacunas_table', 1),
(17, '2025_11_22_205651_create_tratamientos_table', 1),
(18, '2025_11_22_205652_create_productos_farmaceuticos_table', 1),
(19, '2025_11_22_205653_create_medicamentos_table', 1),
(20, '2025_11_22_205654_create_procedimientos_table', 1),
(21, '2025_11_22_205655_create_adoptantes_table', 1),
(22, '2025_11_22_205655_create_cirugias_table', 1),
(23, '2025_11_22_205656_create_adopciones_table', 1),
(24, '2025_11_22_205657_create_visitas_domiciliarias_table', 1),
(25, '2025_11_22_205658_create_denunciantes_table', 1),
(26, '2025_11_22_205659_create_denuncias_table', 1),
(27, '2025_11_22_205700_create_rescates_table', 1),
(28, '2025_11_22_205701_create_insumos_table', 1),
(29, '2025_11_22_205701_create_inventarios_table', 1),
(30, '2025_11_22_205703_create_indicadores_table', 1),
(31, '2025_11_22_205704_create_puntos_indicadores_table', 1),
(32, '2025_11_22_205705_create_eventos_auditoria_table', 1),
(33, '2025_11_23_160853_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permisos`
--

INSERT INTO `permisos` (`id`, `recurso`, `accion`, `descripcion`, `created_at`, `updated_at`) VALUES
('05fff966-bb1b-4fb8-815b-14e8a42f4ab0', 'usuarios', 'crear', 'Crear Usuarios', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('10158e4a-b00f-4a61-864e-a363ee2bb74a', 'denuncias', 'resolver', 'Resolver Denuncias', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('159f96d1-50e3-4b3e-ab12-9d8572afa86d', 'adopciones', 'ver', 'Ver Adopciones', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('24adffd6-b91e-4145-a958-e50a41104898', 'auditoria', 'ver', 'Ver Auditoria', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('3a3cbb65-7645-4ab4-aa92-36db16249d3b', 'usuarios', 'ver', 'Ver Usuarios', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('48939245-d642-49db-aed2-b8eea6fe16ef', 'inventario', 'gestionar', 'Gestionar Inventario', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('5477d7ae-6a27-49cc-9c8f-0f844abd4c54', 'consultas', 'ver', 'Ver Consultas', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('5a6a9227-943b-4be5-a5a4-70a6a9aa52de', 'denuncias', 'ver', 'Ver Denuncias', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('5d29f387-db33-45ce-8712-f414964c64bc', 'reportes', 'ver', 'Ver Reportes', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('63fcd37c-fca0-4c2a-b937-5c4ab43ac1eb', 'rescates', 'registrar', 'Registrar Rescates', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('6cd57da5-de74-4c59-88f5-5e138e4928d7', 'adopciones', 'aprobar', 'Aprobar Adopciones', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('707b7922-d5bd-44a9-8b77-1b43f40cfd7e', 'animales', 'ver', 'Ver Animales', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('7f9f1ad7-f376-45e3-915b-4c41c7a72fc1', 'consultas', 'crear', 'Crear Consultas', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('86391d3f-d78b-4ad9-b3eb-12b69de83c06', 'vacunas', 'aplicar', 'Aplicar Vacunas', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('8bf3df24-cb39-4a1c-87c6-b7914e872da6', 'adopciones', 'evaluar', 'Evaluar Solicitudes', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('9b776dbe-7ccb-47b4-b8b8-9f26d088f515', 'animales', 'crear', 'Crear Animales', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('a0e3dfdd-34d7-4a97-b1f9-a4f2cbd155cd', 'denuncias', 'asignar', 'Asignar Denuncias', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('a3982dcb-d17f-4189-b021-4acf2881475b', 'visitas', 'programar', 'Programar Visitas', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('af961eb3-f013-4f77-9470-2ebaa0ffe73d', 'animales', 'eliminar', 'Eliminar Animales', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('b1c6cb95-4d39-424d-8967-c4f7381950f5', 'cirugias', 'registrar', 'Registrar Cirugias', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('b224d3a1-b243-4c17-8968-5e30b7e772b3', 'animales', 'editar', 'Editar Animales', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('c3cffc12-461e-4796-b005-4185cbd05042', 'historial', 'ver', 'Ver Historial Clinico', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('cd98a98f-bfaf-4888-a4d9-74c18462ea67', 'adopciones', 'crear', 'Crear Solicitudes', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('ef9880a5-0722-4dbc-8fcc-f637ba601c03', 'roles', 'gestionar', 'Gestionar Roles', '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('f33a7536-72c5-4607-94d2-d6838078e162', 'usuarios', 'editar', 'Editar Usuarios', '2025-12-10 21:45:10', '2025-12-10 21:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User\\Usuario', '1e0c7b95-2088-45ca-9539-9fc868c222f4', 'auth_token', '9c85f61248643847aa754bc72d0b7f4215b9086686586c2907f530e2327a238b', '[\"*\"]', '2025-12-10 21:46:01', '2025-12-10 22:45:23', '2025-12-10 21:45:23', '2025-12-10 21:46:01'),
(2, 'App\\Models\\User\\Usuario', '1e0c7b95-2088-45ca-9539-9fc868c222f4', 'auth_token', 'b7108a6955b71429fd6b718b281d7b7cd38d39f25342ce8777886a6fd64c4f79', '[\"*\"]', NULL, '2025-12-10 22:49:11', '2025-12-10 21:49:11', '2025-12-10 21:49:11'),
(3, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'fca4a694cdf07f6a393ec5b82db369fd168036af901c5612e1a647fdf577d6ef', '[\"*\"]', NULL, '2025-12-10 23:05:53', '2025-12-10 22:05:54', '2025-12-10 22:05:54'),
(4, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'd05031c5113bae49c1d3fb5480c508811fe28b7776b1d408cf7720ceb89aa5f0', '[\"*\"]', NULL, '2025-12-10 23:08:25', '2025-12-10 22:08:25', '2025-12-10 22:08:25'),
(5, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '335da2c094ee1a6a12080c4f15529bb6dc01a2f07bca2d5d246791af1fbb99b2', '[\"*\"]', '2025-12-10 22:43:31', '2025-12-10 23:11:46', '2025-12-10 22:11:46', '2025-12-10 22:43:31'),
(6, 'App\\Models\\User\\Usuario', '1e0c7b95-2088-45ca-9539-9fc868c222f4', 'auth_token', '123ee9bcf19b27cf4511b1f20ab1bd98fd18e32fecc766654f8e8c3c437fec89', '[\"*\"]', '2025-12-10 22:29:26', '2025-12-10 23:28:49', '2025-12-10 22:28:49', '2025-12-10 22:29:26'),
(7, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '212f895ddb07424bade37526f4048edabe1c9b04b909f2798a0c6f05180b5acd', '[\"*\"]', '2025-12-10 22:37:26', '2025-12-10 23:36:55', '2025-12-10 22:36:55', '2025-12-10 22:37:26'),
(8, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'e0dd0d5d9112d04bb60e8ee46d6887eb02acf0f206049bd44f3534208938d141', '[\"*\"]', '2025-12-10 22:45:11', '2025-12-10 23:43:39', '2025-12-10 22:43:39', '2025-12-10 22:45:11'),
(9, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'ecf070abb89240f7a550a527c48878cf56bbe1b18ba03515ae25c4e4e0205214', '[\"*\"]', '2025-12-10 22:57:01', '2025-12-10 23:45:26', '2025-12-10 22:45:26', '2025-12-10 22:57:01'),
(10, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'a17c3ef43020dc43b0bcae243b30964872bcd4a04c8c7bacc2ea83ce3839b043', '[\"*\"]', NULL, '2025-12-10 23:52:19', '2025-12-10 22:52:19', '2025-12-10 22:52:19'),
(11, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'bffaf040a00be08c5387e96e2c331a3b37673186a97d21df3c6d38cd129f83aa', '[\"*\"]', NULL, '2025-12-10 23:57:12', '2025-12-10 22:57:12', '2025-12-10 22:57:12'),
(12, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '84d60283e2a409dc00f0b25ad67e3afefc22decb1aff4daaf3c7c409434f0221', '[\"*\"]', '2025-12-10 22:59:09', '2025-12-10 23:57:51', '2025-12-10 22:57:51', '2025-12-10 22:59:09'),
(13, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '3c476bc97f19acb7c917f5ce8e8e47a83a6499f568611bc34fae461d97089732', '[\"*\"]', NULL, '2025-12-10 23:59:43', '2025-12-10 22:59:44', '2025-12-10 22:59:44'),
(14, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'ce8ee84331c634565b574bc5c80f8d49ee023cd4dff412d91b8beaf83ac7c326', '[\"*\"]', '2025-12-10 23:05:18', '2025-12-11 00:00:08', '2025-12-10 23:00:08', '2025-12-10 23:05:18'),
(15, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '97e2d95103ceecb8fe89fd1be20382bdd5a344b44f59202e71ff85d5682af908', '[\"*\"]', NULL, '2025-12-11 00:02:34', '2025-12-10 23:02:34', '2025-12-10 23:02:34'),
(16, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '435fa331289c19a0422c0a34d82b691483568dba689037ca8f578e14cac6b496', '[\"*\"]', '2025-12-10 23:58:19', '2025-12-11 00:06:43', '2025-12-10 23:06:43', '2025-12-10 23:58:19'),
(17, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '1e8c0955c8b841b2e1f7ce36cf7bea0c50f85a3c06624812ffae930e855829e7', '[\"*\"]', NULL, '2025-12-11 00:19:14', '2025-12-10 23:19:14', '2025-12-10 23:19:14'),
(18, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'd536179d7b96e5240958a0f0fb3592923f2a23f149a63db2d753ce4fe14da4e5', '[\"*\"]', NULL, '2025-12-11 00:34:02', '2025-12-10 23:34:03', '2025-12-10 23:34:03'),
(19, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '2e39a409d3c99ffcaa46d86c6548aaf5e53a6c31dff669c60b4f5804cea851dc', '[\"*\"]', '2025-12-11 00:17:29', '2025-12-11 01:13:26', '2025-12-11 00:13:26', '2025-12-11 00:17:29'),
(20, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'api_token', '3365906c96af11008da815df29edea7de9a676d092a235668ed2f01c62df8a4e', '[\"*\"]', '2025-12-11 01:37:46', NULL, '2025-12-11 01:32:13', '2025-12-11 01:37:46'),
(21, 'App\\Models\\User\\Usuario', 'd608a761-927c-490b-bebb-fad74bd1a67c', 'api-token', 'bc163d39f47cbefa8527d6b8d57f27acc6c1c4f978a16ea43f571c4c38526681', '[\"*\"]', '2025-12-11 01:50:50', NULL, '2025-12-11 01:45:20', '2025-12-11 01:50:50'),
(22, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'abcdb5ebd131f84f95e1bd71cf8074698e1a94ffcda938bbf1d09ae01a6f4c8b', '[\"*\"]', '2025-12-11 02:01:23', '2025-12-11 02:56:59', '2025-12-11 01:56:59', '2025-12-11 02:01:23'),
(23, 'App\\Models\\User\\Usuario', '16c18d3d-d4fb-4a1f-a638-e00477563aa6', 'auth_token', '163f1e18e3fbc032f25b0f1bf4412968ebbfd40b550aa4d7f7a8f06dc7d26413', '[\"*\"]', '2025-12-11 02:04:23', '2025-12-11 03:02:58', '2025-12-11 02:02:58', '2025-12-11 02:04:23'),
(24, 'App\\Models\\User\\Usuario', 'e66c7991-54da-4640-9fbb-14f245909f93', 'auth_token', 'af1fc7229b6d7a0c04dd9ee7ec56724ea363c021483ac62d63f1410ee2dd0ce3', '[\"*\"]', NULL, '2025-12-11 09:29:52', '2025-12-11 08:29:52', '2025-12-11 08:29:52'),
(25, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', 'c52784be2a8c2b232dee47364a6b22b5fc0dc14d1df32cc59178530b95a20b9f', '[\"*\"]', '2025-12-11 09:15:48', '2025-12-11 09:30:34', '2025-12-11 08:30:35', '2025-12-11 09:15:48'),
(26, 'App\\Models\\User\\Usuario', '0e49e9fc-e378-4ed3-9774-930696470497', 'auth_token', '84bc94ebae436dd43b88e0971e819a2e6e445d458eacc71b9a9b8bd80e7501ab', '[\"*\"]', '2025-12-11 09:52:42', '2025-12-11 10:34:14', '2025-12-11 09:34:15', '2025-12-11 09:52:42'),
(27, 'App\\Models\\User\\Usuario', '16c18d3d-d4fb-4a1f-a638-e00477563aa6', 'auth_token', '9ca9a8b27cb6d0556baa7ea40cf80ea09e8fb17cd77604845f09c5f13ec3cb9b', '[\"*\"]', '2025-12-11 10:40:22', '2025-12-11 10:52:58', '2025-12-11 09:52:58', '2025-12-11 10:40:22'),
(28, 'App\\Models\\User\\Usuario', '16c18d3d-d4fb-4a1f-a638-e00477563aa6', 'auth_token', '18af43c756b4b6af4aaa5c9082b6ec5dc5433769e418b5b8d15a53ae8bc570a2', '[\"*\"]', '2025-12-11 11:21:45', '2025-12-11 11:57:59', '2025-12-11 10:58:00', '2025-12-11 11:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `procedimientos`
--

DROP TABLE IF EXISTS `procedimientos`;
CREATE TABLE `procedimientos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tratamiento_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_procedimiento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_realizacion` datetime NOT NULL,
  `veterinario_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asistente_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracion` int DEFAULT NULL COMMENT 'Duración en minutos',
  `resultado` text COLLATE utf8mb4_unicode_ci,
  `complicaciones` text COLLATE utf8mb4_unicode_ci,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos_farmaceuticos`
--

DROP TABLE IF EXISTS `productos_farmaceuticos`;
CREATE TABLE `productos_farmaceuticos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_generico` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principio_activo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concentracion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laboratorio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_sanitario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `stock_actual` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '0',
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `requiere_receta` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `puntos_indicadores`
--

DROP TABLE IF EXISTS `puntos_indicadores`;
CREATE TABLE `puntos_indicadores` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicador_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `valor` decimal(15,4) NOT NULL,
  `dimensiones` json DEFAULT NULL,
  `calidad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recordatorios_vacunas`
--

DROP TABLE IF EXISTS `recordatorios_vacunas`;
CREATE TABLE `recordatorios_vacunas` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vacuna_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `animal_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_recordatorio` date NOT NULL,
  `tipo` enum('proxima_dosis','refuerzo','vencimiento') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','enviado','completado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_envio` datetime DEFAULT NULL,
  `canal` enum('email','sms','whatsapp','sistema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `destinatario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rescates`
--

DROP TABLE IF EXISTS `rescates`;
CREATE TABLE `rescates` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `denuncia_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_programada` date NOT NULL,
  `fecha_ejecucion` datetime DEFAULT NULL,
  `equipo_rescate` json DEFAULT NULL,
  `animal_rescatado_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_fallo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requiere_mfa` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `codigo`, `nombre`, `descripcion`, `modulo`, `requiere_mfa`, `activo`, `created_at`, `updated_at`) VALUES
('0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', 'EVALUADOR', 'Evaluador de Adopciones', 'Evaluacion de solicitudes de adopcion', 'adopciones', 0, 1, '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('2809ac0b-5dd8-4b79-97af-175e4beef1ef', 'VETERINARIO', 'Veterinario', 'Gestion de atencion veterinaria', 'veterinaria', 0, 1, '2025-12-10 21:45:09', '2025-12-10 21:45:09'),
('47fbe426-ee51-4d04-a145-233655d65570', 'ADMIN', 'Administrador', 'Acceso total al sistema', 'general', 1, 1, '2025-12-10 21:45:09', '2025-12-10 21:45:09'),
('8395d089-464f-4147-9ce3-c414265b21b0', 'OPERADOR', 'Operador de Rescate', 'Gestion de rescates y denuncias', 'denuncias', 0, 1, '2025-12-10 21:45:10', '2025-12-10 21:45:10'),
('99729af9-b8ec-475a-9f03-c6ea1680444a', 'COORDINADOR', 'Coordinador', 'Coordinador de operaciones', 'general', 0, 1, '2025-12-10 21:45:09', '2025-12-10 21:45:09'),
('9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', 'AUXILIAR_VET', 'Auxiliar Veterinario', 'Apoyo en atencion veterinaria', 'veterinaria', 0, 1, '2025-12-10 21:45:09', '2025-12-10 21:45:09'),
('ee42f23d-9e23-4728-88a2-fce011c7becd', 'DIRECTOR', 'Director', 'Director del programa de bienestar animal', 'general', 1, 1, '2025-12-10 21:45:09', '2025-12-10 21:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `rol_permiso`
--

DROP TABLE IF EXISTS `rol_permiso`;
CREATE TABLE `rol_permiso` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permiso_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asignado_por` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rol_permiso`
--

INSERT INTO `rol_permiso` (`id`, `rol_id`, `permiso_id`, `asignado_por`, `fecha_asignacion`) VALUES
('07ebc074-91d4-47a8-a57e-b0de06646128', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', 'cd98a98f-bfaf-4888-a4d9-74c18462ea67', NULL, '2025-12-10 21:45:11'),
('07fb1732-5afd-4961-92f8-db963ca762d6', '9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', '86391d3f-d78b-4ad9-b3eb-12b69de83c06', NULL, '2025-12-10 21:45:11'),
('0cc03730-e7f5-407c-850b-c166aa58dd28', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', '159f96d1-50e3-4b3e-ab12-9d8572afa86d', NULL, '2025-12-10 21:45:11'),
('0dc4a11e-501b-4953-aa62-6c54455bac06', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '8bf3df24-cb39-4a1c-87c6-b7914e872da6', NULL, '2025-12-10 21:45:11'),
('0e20068f-7683-4e74-a4ac-1c88f1d42af4', '47fbe426-ee51-4d04-a145-233655d65570', 'cd98a98f-bfaf-4888-a4d9-74c18462ea67', NULL, '2025-12-10 21:45:10'),
('13aba195-08cb-498c-8859-3b7781cc492f', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', '9b776dbe-7ccb-47b4-b8b8-9f26d088f515', NULL, '2025-12-10 21:45:11'),
('166ea22b-4f7e-44dc-a6df-aec564dc13e6', '99729af9-b8ec-475a-9f03-c6ea1680444a', 'b224d3a1-b243-4c17-8968-5e30b7e772b3', NULL, '2025-12-10 21:45:11'),
('1790868f-6be2-4b80-b6ba-d1956599db2a', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', '86391d3f-d78b-4ad9-b3eb-12b69de83c06', NULL, '2025-12-10 21:45:11'),
('1a495fb7-71d2-4f19-849d-f887a15b64e1', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', 'af961eb3-f013-4f77-9470-2ebaa0ffe73d', NULL, '2025-12-10 21:45:11'),
('2b0787c0-12b0-4ce5-8ac3-2f3c0b22b183', '47fbe426-ee51-4d04-a145-233655d65570', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:10'),
('2d09f575-d18c-44fd-ade7-9209e6e7476a', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('315b2bdf-d82e-4139-8f66-5ba1069f7985', '47fbe426-ee51-4d04-a145-233655d65570', '5a6a9227-943b-4be5-a5a4-70a6a9aa52de', NULL, '2025-12-10 21:45:10'),
('31e4f040-d73a-4efa-8ad3-9b398a01d5d8', '9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('37f59a33-9aca-48c5-a317-b7f122a8c44e', '47fbe426-ee51-4d04-a145-233655d65570', '86391d3f-d78b-4ad9-b3eb-12b69de83c06', NULL, '2025-12-10 21:45:10'),
('389d31e5-37f7-473c-9fee-194fda5716ba', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', 'a3982dcb-d17f-4189-b021-4acf2881475b', NULL, '2025-12-10 21:45:11'),
('3a69e373-eced-45c3-9232-0882294dee12', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'f33a7536-72c5-4607-94d2-d6838078e162', NULL, '2025-12-10 21:45:11'),
('3c33b4c1-eb55-44d7-8a0c-aa6f1fc2e838', '8395d089-464f-4147-9ce3-c414265b21b0', '5a6a9227-943b-4be5-a5a4-70a6a9aa52de', NULL, '2025-12-10 21:45:11'),
('41a0ae11-0aa1-4a99-9595-d2360f0de634', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '5a6a9227-943b-4be5-a5a4-70a6a9aa52de', NULL, '2025-12-10 21:45:11'),
('41f02370-3fe4-4822-93eb-9bfbbc50f453', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'a0e3dfdd-34d7-4a97-b1f9-a4f2cbd155cd', NULL, '2025-12-10 21:45:11'),
('49b399f7-69a8-4570-a29f-70b51dfc093b', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '5477d7ae-6a27-49cc-9c8f-0f844abd4c54', NULL, '2025-12-10 21:45:11'),
('4a94262e-8f2b-4336-a9cf-d0f9d08e7a95', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', 'b1c6cb95-4d39-424d-8967-c4f7381950f5', NULL, '2025-12-10 21:45:11'),
('4ae59b19-fc5e-4293-a390-0528c04eab8f', '99729af9-b8ec-475a-9f03-c6ea1680444a', '5a6a9227-943b-4be5-a5a4-70a6a9aa52de', NULL, '2025-12-10 21:45:11'),
('4bad10fd-5f36-4526-9fe8-5f7d2588c999', '47fbe426-ee51-4d04-a145-233655d65570', 'a3982dcb-d17f-4189-b021-4acf2881475b', NULL, '2025-12-10 21:45:10'),
('4bebc868-0eda-45ff-b233-ebc64c042efd', '47fbe426-ee51-4d04-a145-233655d65570', '24adffd6-b91e-4145-a958-e50a41104898', NULL, '2025-12-10 21:45:10'),
('4df85d52-f6ff-4887-ac15-27b2a804c46a', '99729af9-b8ec-475a-9f03-c6ea1680444a', '9b776dbe-7ccb-47b4-b8b8-9f26d088f515', NULL, '2025-12-10 21:45:11'),
('4e6ea71d-8c42-416a-b64f-6dc37308bafb', '47fbe426-ee51-4d04-a145-233655d65570', '9b776dbe-7ccb-47b4-b8b8-9f26d088f515', NULL, '2025-12-10 21:45:10'),
('510b1fe2-ad12-487e-ade7-d819a6a33632', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '63fcd37c-fca0-4c2a-b937-5c4ab43ac1eb', NULL, '2025-12-10 21:45:11'),
('53a714bd-ff82-4732-8b98-394024868a27', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '159f96d1-50e3-4b3e-ab12-9d8572afa86d', NULL, '2025-12-10 21:45:11'),
('549877f6-6750-4f70-98f6-647639a7fd50', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', '6cd57da5-de74-4c59-88f5-5e138e4928d7', NULL, '2025-12-10 21:45:11'),
('57b1a8a9-7814-48d7-8d01-c512e621e4fc', '99729af9-b8ec-475a-9f03-c6ea1680444a', '8bf3df24-cb39-4a1c-87c6-b7914e872da6', NULL, '2025-12-10 21:45:11'),
('57dd20b9-4839-4f28-be0f-b201e3e0e23c', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '9b776dbe-7ccb-47b4-b8b8-9f26d088f515', NULL, '2025-12-10 21:45:11'),
('586122ce-9e73-44ea-9476-dfde5fba21b9', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '7f9f1ad7-f376-45e3-915b-4c41c7a72fc1', NULL, '2025-12-10 21:45:11'),
('5a723dcf-1931-445b-9279-d284ec268e6d', '47fbe426-ee51-4d04-a145-233655d65570', '48939245-d642-49db-aed2-b8eea6fe16ef', NULL, '2025-12-10 21:45:10'),
('5b0a522d-73a9-4097-a678-991ad7fe705e', '47fbe426-ee51-4d04-a145-233655d65570', 'ef9880a5-0722-4dbc-8fcc-f637ba601c03', NULL, '2025-12-10 21:45:10'),
('63497d96-cd64-4884-b7ea-b68c67f6472e', '47fbe426-ee51-4d04-a145-233655d65570', '159f96d1-50e3-4b3e-ab12-9d8572afa86d', NULL, '2025-12-10 21:45:10'),
('68eb2f0f-d512-4242-8d6d-f646e6bad028', '47fbe426-ee51-4d04-a145-233655d65570', 'b1c6cb95-4d39-424d-8967-c4f7381950f5', NULL, '2025-12-10 21:45:10'),
('69a9869e-bc86-47d1-b730-8614b72b0ee9', '99729af9-b8ec-475a-9f03-c6ea1680444a', '5d29f387-db33-45ce-8712-f414964c64bc', NULL, '2025-12-10 21:45:11'),
('6d101d23-e7e8-49ea-9b72-ab2da9e6994c', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '10158e4a-b00f-4a61-864e-a363ee2bb74a', NULL, '2025-12-10 21:45:11'),
('6f0d0cff-e930-4d0f-a6ee-a77908b78e72', '8395d089-464f-4147-9ce3-c414265b21b0', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('70a833a5-c3c1-4f99-afb1-013eac83f1f7', '99729af9-b8ec-475a-9f03-c6ea1680444a', 'c3cffc12-461e-4796-b005-4185cbd05042', NULL, '2025-12-10 21:45:11'),
('741fa418-9397-4140-95c3-5ad76ef78f5f', '8395d089-464f-4147-9ce3-c414265b21b0', '9b776dbe-7ccb-47b4-b8b8-9f26d088f515', NULL, '2025-12-10 21:45:11'),
('770729a4-2079-4aa2-bff5-dbc1d8ce0091', '9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', 'c3cffc12-461e-4796-b005-4185cbd05042', NULL, '2025-12-10 21:45:11'),
('7825061c-838f-40b6-baa0-aa37848abaee', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '48939245-d642-49db-aed2-b8eea6fe16ef', NULL, '2025-12-10 21:45:11'),
('7caf0fb8-5726-4c07-80d3-a5e97607a3f2', '99729af9-b8ec-475a-9f03-c6ea1680444a', 'cd98a98f-bfaf-4888-a4d9-74c18462ea67', NULL, '2025-12-10 21:45:11'),
('7ea1bde2-d474-45d9-a696-e9dcc1122527', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('82425797-dd88-441a-b20d-115a01bb5bf4', '47fbe426-ee51-4d04-a145-233655d65570', '10158e4a-b00f-4a61-864e-a363ee2bb74a', NULL, '2025-12-10 21:45:10'),
('8715e679-89b8-4b5b-b187-9c29cfbfc487', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'b224d3a1-b243-4c17-8968-5e30b7e772b3', NULL, '2025-12-10 21:45:11'),
('87e399d5-11d2-4d35-81d8-e6a1d5eeb757', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '5d29f387-db33-45ce-8712-f414964c64bc', NULL, '2025-12-10 21:45:11'),
('92be8cec-b576-43c4-b04b-19a799a833e1', '47fbe426-ee51-4d04-a145-233655d65570', 'a0e3dfdd-34d7-4a97-b1f9-a4f2cbd155cd', NULL, '2025-12-10 21:45:10'),
('954b3e57-e569-4530-8d08-8319fc3a3786', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '3a3cbb65-7645-4ab4-aa92-36db16249d3b', NULL, '2025-12-10 21:45:11'),
('963a4eb7-53f5-4f21-840d-46e2ad4b6a4c', '9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', '5477d7ae-6a27-49cc-9c8f-0f844abd4c54', NULL, '2025-12-10 21:45:11'),
('9847e294-9182-47f0-b329-c4543d43453a', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('a0ca3e92-43eb-4fa0-a4f7-3a3218b52a36', '8395d089-464f-4147-9ce3-c414265b21b0', '10158e4a-b00f-4a61-864e-a363ee2bb74a', NULL, '2025-12-10 21:45:11'),
('a2e00025-eb08-4151-bf34-8758154d6c0d', '47fbe426-ee51-4d04-a145-233655d65570', '7f9f1ad7-f376-45e3-915b-4c41c7a72fc1', NULL, '2025-12-10 21:45:10'),
('a312ccc9-fb32-41e6-aae9-9a1c0c561a5b', '99729af9-b8ec-475a-9f03-c6ea1680444a', '7f9f1ad7-f376-45e3-915b-4c41c7a72fc1', NULL, '2025-12-10 21:45:11'),
('a44fc364-1331-4d4d-be83-099b0351daa5', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', 'b224d3a1-b243-4c17-8968-5e30b7e772b3', NULL, '2025-12-10 21:45:11'),
('a470b463-65e9-4048-9a48-7c1718cf1f9b', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', '7f9f1ad7-f376-45e3-915b-4c41c7a72fc1', NULL, '2025-12-10 21:45:11'),
('a518ad5a-1f6b-4287-8c9d-9f37409ca10d', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', '8bf3df24-cb39-4a1c-87c6-b7914e872da6', NULL, '2025-12-10 21:45:11'),
('b3c6997a-b17a-4b31-bac5-3bdcbbbdaf02', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', '5477d7ae-6a27-49cc-9c8f-0f844abd4c54', NULL, '2025-12-10 21:45:11'),
('b58f22b7-0ce8-42a9-aba3-e48e2fa94039', '47fbe426-ee51-4d04-a145-233655d65570', '3a3cbb65-7645-4ab4-aa92-36db16249d3b', NULL, '2025-12-10 21:45:10'),
('b6953b88-8dd9-4ed8-a7f6-de19a7ec321a', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', 'c3cffc12-461e-4796-b005-4185cbd05042', NULL, '2025-12-10 21:45:11'),
('b80c149d-e820-4c50-ace0-0f81fc12c74b', '47fbe426-ee51-4d04-a145-233655d65570', '63fcd37c-fca0-4c2a-b937-5c4ab43ac1eb', NULL, '2025-12-10 21:45:10'),
('b85b8887-2bf7-4657-beeb-db093e0b04af', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'c3cffc12-461e-4796-b005-4185cbd05042', NULL, '2025-12-10 21:45:11'),
('b9f3a3f8-7ac4-45ad-9916-db09838fcbdf', '47fbe426-ee51-4d04-a145-233655d65570', 'f33a7536-72c5-4607-94d2-d6838078e162', NULL, '2025-12-10 21:45:10'),
('bb622ce4-3ff3-4d01-9917-9886e9b4dd62', '47fbe426-ee51-4d04-a145-233655d65570', '05fff966-bb1b-4fb8-815b-14e8a42f4ab0', NULL, '2025-12-10 21:45:10'),
('be7b6336-624c-4bfd-8c2d-44c3533cbacb', '47fbe426-ee51-4d04-a145-233655d65570', '5477d7ae-6a27-49cc-9c8f-0f844abd4c54', NULL, '2025-12-10 21:45:10'),
('beb58fcd-4774-4da8-a2a7-6651ea064775', '99729af9-b8ec-475a-9f03-c6ea1680444a', '5477d7ae-6a27-49cc-9c8f-0f844abd4c54', NULL, '2025-12-10 21:45:11'),
('bfcaf3cd-7753-4384-aa64-9b81ef55a3d2', '47fbe426-ee51-4d04-a145-233655d65570', 'af961eb3-f013-4f77-9470-2ebaa0ffe73d', NULL, '2025-12-10 21:45:10'),
('c18bcc75-5429-42d6-9e25-dd9619e945ec', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'af961eb3-f013-4f77-9470-2ebaa0ffe73d', NULL, '2025-12-10 21:45:11'),
('c1ee4179-7b9c-4a48-babe-a7beb39fddb4', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'cd98a98f-bfaf-4888-a4d9-74c18462ea67', NULL, '2025-12-10 21:45:11'),
('c66f5c9a-281c-4529-bfe6-ac633e2dad00', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '24adffd6-b91e-4145-a958-e50a41104898', NULL, '2025-12-10 21:45:11'),
('c680b673-e10e-470f-bdc0-905c8cdb7a37', '47fbe426-ee51-4d04-a145-233655d65570', '5d29f387-db33-45ce-8712-f414964c64bc', NULL, '2025-12-10 21:45:10'),
('ca7f9844-cfc5-4bf6-b2f3-2ecba8c373af', '47fbe426-ee51-4d04-a145-233655d65570', 'b224d3a1-b243-4c17-8968-5e30b7e772b3', NULL, '2025-12-10 21:45:10'),
('cd85db5b-687c-4599-a52f-e9409870bc90', '99729af9-b8ec-475a-9f03-c6ea1680444a', '159f96d1-50e3-4b3e-ab12-9d8572afa86d', NULL, '2025-12-10 21:45:11'),
('cff29d29-4c1f-488f-872b-d8f2fae01b73', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'b1c6cb95-4d39-424d-8967-c4f7381950f5', NULL, '2025-12-10 21:45:11'),
('d1b2d24c-23e6-4170-9a4e-02b4a00d3fac', '47fbe426-ee51-4d04-a145-233655d65570', 'c3cffc12-461e-4796-b005-4185cbd05042', NULL, '2025-12-10 21:45:10'),
('d26ebf3a-39d8-441b-897f-bd7c599c1ac1', '47fbe426-ee51-4d04-a145-233655d65570', '8bf3df24-cb39-4a1c-87c6-b7914e872da6', NULL, '2025-12-10 21:45:10'),
('db0fd652-ac4f-469f-84d8-1a18d68b1d0e', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '6cd57da5-de74-4c59-88f5-5e138e4928d7', NULL, '2025-12-10 21:45:11'),
('e24c3148-2745-496f-bee6-288967504625', '99729af9-b8ec-475a-9f03-c6ea1680444a', '707b7922-d5bd-44a9-8b77-1b43f40cfd7e', NULL, '2025-12-10 21:45:11'),
('e87710b0-a4fb-4f78-8910-6cb970eec163', 'ee42f23d-9e23-4728-88a2-fce011c7becd', '86391d3f-d78b-4ad9-b3eb-12b69de83c06', NULL, '2025-12-10 21:45:11'),
('f2a0362a-cbbd-4c6c-adb9-899cdcd46ea5', '47fbe426-ee51-4d04-a145-233655d65570', '6cd57da5-de74-4c59-88f5-5e138e4928d7', NULL, '2025-12-10 21:45:10'),
('f787feb8-a69d-4cfc-85c3-dbda950f9db3', '99729af9-b8ec-475a-9f03-c6ea1680444a', 'a0e3dfdd-34d7-4a97-b1f9-a4f2cbd155cd', NULL, '2025-12-10 21:45:11'),
('f95b84d8-b8cf-4aa8-90ae-3e9119034bd8', '8395d089-464f-4147-9ce3-c414265b21b0', '63fcd37c-fca0-4c2a-b937-5c4ab43ac1eb', NULL, '2025-12-10 21:45:11'),
('ff217575-057a-4748-aa9f-99b3ac21539c', 'ee42f23d-9e23-4728-88a2-fce011c7becd', 'a3982dcb-d17f-4189-b021-4acf2881475b', NULL, '2025-12-10 21:45:11');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_vacunas`
--

DROP TABLE IF EXISTS `tipos_vacunas`;
CREATE TABLE `tipos_vacunas` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `especie_aplicable` enum('perro','gato','ambos','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `edad_minima` int DEFAULT NULL COMMENT 'Edad mínima en meses',
  `intervalo_dosis` int DEFAULT NULL COMMENT 'Días entre dosis',
  `numero_dosis` int NOT NULL DEFAULT '1',
  `es_obligatoria` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipos_vacunas`
--

INSERT INTO `tipos_vacunas` (`id`, `codigo`, `nombre`, `descripcion`, `especie_aplicable`, `edad_minima`, `intervalo_dosis`, `numero_dosis`, `es_obligatoria`, `activo`, `created_at`, `updated_at`) VALUES
('1b8ef666-3a93-4eb6-a272-536490781af1', 'VAC-FEL-PAN', 'Panleucopenia Felina', 'Vacuna contra la panleucopenia felina', 'gato', 2, 365, 2, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('36175940-8812-4aeb-b815-a55e59183b0f', 'VAC-CAN-LEP', 'Leptospirosis', 'Vacuna contra la leptospirosis', 'perro', 3, 180, 2, 0, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('3b28a42e-b7ce-403e-8a60-091a904a222a', 'VAC-CAN-HEP', 'Hepatitis Canina', 'Vacuna contra la hepatitis infecciosa canina', 'perro', 2, 365, 2, 0, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('58e11181-210b-47a9-86af-6960f1b559fc', 'VAC-CAN-POL', 'Polivalente Canina (Puppy)', 'Vacuna polivalente para cachorros', 'perro', 1, 21, 3, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('7dcc9b38-0c7c-4fe7-a26d-9c530be8f1e5', 'VAC-CAN-RAB', 'Rabia Canina', 'Vacuna antirabica obligatoria para caninos', 'perro', 3, 365, 1, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('87bf95f4-9dfe-43c6-947c-1822b79fa91c', 'VAC-FEL-LEU', 'Leucemia Felina', 'Vacuna contra el virus de leucemia felina', 'gato', 2, 365, 2, 0, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('cc34913b-0f5a-4fb4-b63a-095f4a78caf7', 'VAC-FEL-TRI', 'Triple Felina', 'Vacuna contra panleucopenia, calicivirus y rinotraqueitis', 'gato', 2, 365, 2, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('deaa98fe-d691-4de0-8227-e4af550b79da', 'VAC-CAN-PAR', 'Parvovirus Canino', 'Vacuna contra el parvovirus canino', 'perro', 2, 365, 3, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('dffbfc7d-417a-4db2-955f-4b89c3116696', 'VAC-CAN-MOQ', 'Moquillo Canino', 'Vacuna contra el distemper canino', 'perro', 2, 365, 3, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13'),
('f23d31f2-4500-4a2f-ab59-53436e891d9e', 'VAC-FEL-RAB', 'Rabia Felina', 'Vacuna antirabica obligatoria para felinos', 'gato', 3, 365, 1, 1, 1, '2025-12-10 21:45:13', '2025-12-10 21:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `tratamientos`
--

DROP TABLE IF EXISTS `tratamientos`;
CREATE TABLE `tratamientos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consulta_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `historial_clinico_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_tratamiento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `duracion_estimada` int DEFAULT NULL COMMENT 'Duración en días',
  `estado` enum('activo','completado','suspendido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `efectividad` enum('excelente','buena','regular','pobre','sin_evaluar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin_evaluar',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origen_autenticacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `mfa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password_hash`, `nombres`, `apellidos`, `origen_autenticacion`, `mfa_enabled`, `mfa_secret`, `activo`, `ultimo_acceso`, `created_at`, `updated_at`, `deleted_at`) VALUES
('0e49e9fc-e378-4ed3-9774-930696470497', 'op.torres', 'diego.torres@bienestaranimal.gov.co', '$2y$12$yzE.aRssKRz1ikAOFfhfMOZ50CX4W22oI4IdfVKdMrH/H/3l/HKjS', 'Diego', 'Torres Mendez', 'local', 0, NULL, 1, '2025-12-11 09:34:14', '2025-12-10 21:45:12', '2025-12-11 09:34:14', NULL),
('16c18d3d-d4fb-4a1f-a638-e00477563aa6', 'vet.garcia', 'ana.garcia@bienestaranimal.gov.co', '$2y$12$LumsD.1iqXX7HxUojHYS0eqLYRKKQEc9x41FeS/Tkhm0MoV/siy7W', 'Ana Maria', 'Garcia Sanchez', 'local', 0, NULL, 1, '2025-12-11 10:57:59', '2025-12-10 21:45:12', '2025-12-11 10:57:59', NULL),
('1e0c7b95-2088-45ca-9539-9fc868c222f4', 'coordinador', 'coordinador@bienestaranimal.gov.co', '$2y$12$sECgXSnWh8eBIK/1.gybNemCPiKtdJjyABz/NbrRDgTXpX9nQfypK', 'Juan Pablo', 'Martinez Lopez', 'local', 0, NULL, 1, '2025-12-10 22:28:49', '2025-12-10 21:45:12', '2025-12-10 22:28:49', NULL),
('67756e13-d42b-4d3c-a98b-8760aee8352e', 'eval.castro', 'patricia.castro@bienestaranimal.gov.co', '$2y$12$1coqBak4OVk8RDL6wLOAdeaNqf0o7VVlDTfbdI4LnCy1ayso2bOha', 'Patricia', 'Castro Herrera', 'local', 0, NULL, 1, NULL, '2025-12-10 21:45:13', '2025-12-10 21:45:13', NULL),
('a2c1abd4-8a4c-4d66-8a18-f066dedc6890', 'vet.ramirez', 'pedro.ramirez@bienestaranimal.gov.co', '$2y$12$HtcaMa90yBTUfOpg/o2VjOzLBSolxeq5tgD.NqudIjwIIVPmdYaZi', 'Pedro', 'Ramirez Castillo', 'local', 0, NULL, 1, NULL, '2025-12-10 21:45:12', '2025-12-10 21:45:12', NULL),
('b32f3dfd-5f56-4d95-956d-f9d2f8bb5297', 'op.moreno', 'sandra.moreno@bienestaranimal.gov.co', '$2y$12$evCiP2YVcy1nbBvPofKwFOYnhooyfehZHZqDR4UyL3HhP2G3cgyZS', 'Sandra', 'Moreno Rios', 'local', 0, NULL, 1, NULL, '2025-12-10 21:45:12', '2025-12-10 21:45:12', NULL),
('d608a761-927c-490b-bebb-fad74bd1a67c', 'admin', 'admin@bienestaranimal.gov.co', '$2y$12$IV82Qx2My4vF1WxyUWjxk.5moo.PkpsNZ/BzetdX3A/SbkhOt5.cG', 'Carlos', 'Rodriguez Gomez', 'local', 0, NULL, 1, NULL, '2025-12-10 21:45:11', '2025-12-10 21:45:11', NULL),
('e66c7991-54da-4640-9fbb-14f245909f93', 'aux.lopez', 'laura.lopez@bienestaranimal.gov.co', '$2y$12$0xR99TEWQr9IcJ6uOL5PYedsfM0X4/n3sYIT5ENRXwzmAJp6hzAEe', 'Laura', 'Lopez Vargas', 'local', 0, NULL, 1, '2025-12-11 08:29:52', '2025-12-10 21:45:12', '2025-12-11 08:29:52', NULL),
('f7f54604-e0e3-4aa4-a8b5-6f10e3068e71', 'director', 'director@bienestaranimal.gov.co', '$2y$12$d9xNnYc2tKhkHhg.cK4j1eOkqg8F4gHs8uCDk4Vm9/EZOVh1etlq6', 'Maria Elena', 'Ospina Restrepo', 'local', 0, NULL, 1, NULL, '2025-12-10 21:45:11', '2025-12-10 21:45:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuario_rol`
--

DROP TABLE IF EXISTS `usuario_rol`;
CREATE TABLE `usuario_rol` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asignado_por` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuario_rol`
--

INSERT INTO `usuario_rol` (`id`, `usuario_id`, `rol_id`, `asignado_por`, `fecha_asignacion`, `fecha_expiracion`, `activo`) VALUES
('4c3b4aaf-f4e1-410a-b1bc-8231dd4ab707', 'e66c7991-54da-4640-9fbb-14f245909f93', '9a0ec4c0-a4a5-4ee2-9a23-1bf0d41f45a4', NULL, '2025-12-10 21:45:12', NULL, 1),
('8da11312-2a81-484e-b76f-aa1a1702cb65', 'b32f3dfd-5f56-4d95-956d-f9d2f8bb5297', '8395d089-464f-4147-9ce3-c414265b21b0', NULL, '2025-12-10 21:45:12', NULL, 1),
('99d09328-66a0-4153-9987-5f4a155d3699', '67756e13-d42b-4d3c-a98b-8760aee8352e', '0ff88a9b-8a0c-4a42-8c80-da858c9bfbf3', NULL, '2025-12-10 21:45:13', NULL, 1),
('9d082640-8504-4235-baf9-8c73b7c4c397', 'f7f54604-e0e3-4aa4-a8b5-6f10e3068e71', 'ee42f23d-9e23-4728-88a2-fce011c7becd', NULL, '2025-12-10 21:45:11', NULL, 1),
('b047293f-8b66-4702-a180-67663341fc40', '1e0c7b95-2088-45ca-9539-9fc868c222f4', '99729af9-b8ec-475a-9f03-c6ea1680444a', NULL, '2025-12-10 21:45:12', NULL, 1),
('b3c26f34-1eae-4ac0-ae7e-f97ff5507791', 'a2c1abd4-8a4c-4d66-8a18-f066dedc6890', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', NULL, '2025-12-10 21:45:12', NULL, 1),
('e86bf098-8cb3-49ff-b91b-b9b1a02c683a', '0e49e9fc-e378-4ed3-9774-930696470497', '8395d089-464f-4147-9ce3-c414265b21b0', NULL, '2025-12-10 21:45:12', NULL, 1),
('eeebf35a-d6d1-4e00-9484-5606c6e3901f', '16c18d3d-d4fb-4a1f-a638-e00477563aa6', '2809ac0b-5dd8-4b79-97af-175e4beef1ef', NULL, '2025-12-10 21:45:12', NULL, 1),
('f9debff5-fdbd-474e-b2ef-4acd349fd695', 'd608a761-927c-490b-bebb-fad74bd1a67c', '47fbe426-ee51-4d04-a145-233655d65570', NULL, '2025-12-10 21:45:11', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vacunas`
--

DROP TABLE IF EXISTS `vacunas`;
CREATE TABLE `vacunas` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `historial_clinico_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_vacuna_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_aplicacion` date NOT NULL,
  `fecha_proxima_dosis` date DEFAULT NULL,
  `lote_vacuna` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabricante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `veterinario_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `reacciones_adversas` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('aplicada','programada','vencida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aplicada',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vacunas`
--

INSERT INTO `vacunas` (`id`, `historial_clinico_id`, `tipo_vacuna_id`, `fecha_aplicacion`, `fecha_proxima_dosis`, `lote_vacuna`, `fabricante`, `veterinario_id`, `observaciones`, `reacciones_adversas`, `estado`, `created_at`, `updated_at`) VALUES
('f0d0af40-5a17-4b73-a434-c8d880ab0a06', '06dfffee-c4d5-42c8-a20d-92d38b0109c4', '1b8ef666-3a93-4eb6-a272-536490781af1', '2025-12-11', NULL, NULL, 'Pfizer', '1333a2cc-a288-4f47-bcdf-daa600de6f8f', NULL, NULL, 'aplicada', '2025-12-11 01:36:37', '2025-12-11 01:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `veterinarios`
--

DROP TABLE IF EXISTS `veterinarios`;
CREATE TABLE `veterinarios` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_tarjeta_profesional` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `veterinarios`
--

INSERT INTO `veterinarios` (`id`, `usuario_id`, `nombres`, `apellidos`, `numero_tarjeta_profesional`, `especialidad`, `telefono`, `email`, `activo`, `created_at`, `updated_at`) VALUES
('1333a2cc-a288-4f47-bcdf-daa600de6f8f', 'a2c1abd4-8a4c-4d66-8a18-f066dedc6890', 'Pedro', 'Ramirez Castillo', 'TP52278', 'General', '3001234567', 'pedro.ramirez@bienestaranimal.gov.co', 1, '2025-12-11 01:34:47', '2025-12-11 01:34:47'),
('c5a20de1-274a-4092-a0cb-e378aeff010f', '16c18d3d-d4fb-4a1f-a638-e00477563aa6', 'Ana Maria', 'Garcia Sanchez', 'TP80038', 'General', '3001234567', 'ana.garcia@bienestaranimal.gov.co', 1, '2025-12-11 01:34:47', '2025-12-11 01:34:47');

-- --------------------------------------------------------

--
-- Table structure for table `visitas_domiciliarias`
--

DROP TABLE IF EXISTS `visitas_domiciliarias`;
CREATE TABLE `visitas_domiciliarias` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adopcion_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_programada` date NOT NULL,
  `fecha_realizada` datetime DEFAULT NULL,
  `visitador_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_visita` enum('pre_adopcion','seguimiento_1mes','seguimiento_3meses','seguimiento_6meses','extraordinaria') COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `condiciones_hogar` json DEFAULT NULL,
  `estado_animal` json DEFAULT NULL,
  `resultado` enum('satisfactoria','observaciones','critica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recomendaciones` text COLLATE utf8mb4_unicode_ci,
  `fotos_respaldo` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adopciones`
--
ALTER TABLE `adopciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adopciones_evaluador_id_foreign` (`evaluador_id`),
  ADD KEY `adopciones_animal_id_index` (`animal_id`),
  ADD KEY `adopciones_adoptante_id_index` (`adoptante_id`),
  ADD KEY `adopciones_estado_index` (`estado`),
  ADD KEY `adopciones_fecha_solicitud_index` (`fecha_solicitud`);

--
-- Indexes for table `adoptantes`
--
ALTER TABLE `adoptantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adoptantes_numero_documento_unique` (`numero_documento`),
  ADD KEY `adoptantes_numero_documento_index` (`numero_documento`),
  ADD KEY `adoptantes_email_index` (`email`),
  ADD KEY `adoptantes_estado_index` (`estado`);

--
-- Indexes for table `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `animals_codigo_unico_unique` (`codigo_unico`),
  ADD KEY `animals_created_by_foreign` (`created_by`),
  ADD KEY `animals_updated_by_foreign` (`updated_by`),
  ADD KEY `animals_codigo_unico_index` (`codigo_unico`),
  ADD KEY `animals_especie_index` (`especie`),
  ADD KEY `animals_estado_index` (`estado`),
  ADD KEY `animals_estado_salud_index` (`estado_salud`),
  ADD KEY `animals_fecha_rescate_index` (`fecha_rescate`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cirugias`
--
ALTER TABLE `cirugias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cirugias_anestesiologo_id_foreign` (`anestesiologo_id`),
  ADD KEY `cirugias_historial_clinico_id_index` (`historial_clinico_id`),
  ADD KEY `cirugias_cirujano_id_index` (`cirujano_id`),
  ADD KEY `cirugias_fecha_programada_index` (`fecha_programada`),
  ADD KEY `cirugias_estado_index` (`estado`);

--
-- Indexes for table `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consultas_historial_clinico_id_index` (`historial_clinico_id`),
  ADD KEY `consultas_veterinario_id_index` (`veterinario_id`),
  ADD KEY `consultas_fecha_consulta_index` (`fecha_consulta`),
  ADD KEY `consultas_tipo_consulta_index` (`tipo_consulta`);

--
-- Indexes for table `denunciantes`
--
ALTER TABLE `denunciantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `denunciantes_telefono_index` (`telefono`),
  ADD KEY `denunciantes_email_index` (`email`);

--
-- Indexes for table `denuncias`
--
ALTER TABLE `denuncias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `denuncias_numero_ticket_unique` (`numero_ticket`),
  ADD KEY `denuncias_responsable_id_foreign` (`responsable_id`),
  ADD KEY `denuncias_numero_ticket_index` (`numero_ticket`),
  ADD KEY `denuncias_denunciante_id_index` (`denunciante_id`),
  ADD KEY `denuncias_estado_index` (`estado`),
  ADD KEY `denuncias_prioridad_index` (`prioridad`),
  ADD KEY `denuncias_fecha_denuncia_index` (`fecha_denuncia`),
  ADD KEY `denuncias_tipo_denuncia_index` (`tipo_denuncia`);

--
-- Indexes for table `eventos_auditoria`
--
ALTER TABLE `eventos_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eventos_auditoria_trace_id_index` (`trace_id`),
  ADD KEY `eventos_auditoria_usuario_id_index` (`usuario_id`),
  ADD KEY `eventos_auditoria_timestamp_index` (`timestamp`),
  ADD KEY `eventos_auditoria_modulo_index` (`modulo`),
  ADD KEY `eventos_auditoria_accion_index` (`accion`);

--
-- Indexes for table `examenes_laboratorio`
--
ALTER TABLE `examenes_laboratorio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examenes_laboratorio_consulta_id_index` (`consulta_id`),
  ADD KEY `examenes_laboratorio_tipo_examen_index` (`tipo_examen`),
  ADD KEY `examenes_laboratorio_estado_index` (`estado`),
  ADD KEY `examenes_laboratorio_fecha_solicitud_index` (`fecha_solicitud`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `historiales_clinicos`
--
ALTER TABLE `historiales_clinicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `historiales_clinicos_animal_id_unique` (`animal_id`),
  ADD KEY `historiales_clinicos_animal_id_index` (`animal_id`),
  ADD KEY `historiales_clinicos_estado_index` (`estado`);

--
-- Indexes for table `indicadores`
--
ALTER TABLE `indicadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `indicadores_codigo_unique` (`codigo`),
  ADD KEY `indicadores_codigo_index` (`codigo`),
  ADD KEY `indicadores_tipo_index` (`tipo`),
  ADD KEY `indicadores_activo_index` (`activo`);

--
-- Indexes for table `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `insumos_codigo_unique` (`codigo`),
  ADD KEY `insumos_codigo_index` (`codigo`),
  ADD KEY `insumos_categoria_index` (`categoria`),
  ADD KEY `insumos_activo_index` (`activo`);

--
-- Indexes for table `inventarios`
--
ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventarios_codigo_unique` (`codigo`),
  ADD KEY `inventarios_codigo_index` (`codigo`),
  ADD KEY `inventarios_categoria_index` (`categoria`),
  ADD KEY `inventarios_tipo_index` (`tipo`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicamentos_tratamiento_id_index` (`tratamiento_id`),
  ADD KEY `medicamentos_producto_id_index` (`producto_id`),
  ADD KEY `medicamentos_fecha_inicio_index` (`fecha_inicio`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_recurso_accion` (`recurso`,`accion`),
  ADD KEY `permisos_recurso_index` (`recurso`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `procedimientos`
--
ALTER TABLE `procedimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedimientos_asistente_id_foreign` (`asistente_id`),
  ADD KEY `procedimientos_tratamiento_id_index` (`tratamiento_id`),
  ADD KEY `procedimientos_veterinario_id_index` (`veterinario_id`),
  ADD KEY `procedimientos_fecha_realizacion_index` (`fecha_realizacion`);

--
-- Indexes for table `productos_farmaceuticos`
--
ALTER TABLE `productos_farmaceuticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productos_farmaceuticos_codigo_unique` (`codigo`),
  ADD KEY `productos_farmaceuticos_codigo_index` (`codigo`),
  ADD KEY `productos_farmaceuticos_nombre_comercial_index` (`nombre_comercial`),
  ADD KEY `productos_farmaceuticos_activo_index` (`activo`),
  ADD KEY `productos_farmaceuticos_stock_actual_index` (`stock_actual`);

--
-- Indexes for table `puntos_indicadores`
--
ALTER TABLE `puntos_indicadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `puntos_indicadores_indicador_id_index` (`indicador_id`),
  ADD KEY `puntos_indicadores_fecha_index` (`fecha`);

--
-- Indexes for table `recordatorios_vacunas`
--
ALTER TABLE `recordatorios_vacunas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recordatorios_vacunas_vacuna_id_index` (`vacuna_id`),
  ADD KEY `recordatorios_vacunas_animal_id_index` (`animal_id`),
  ADD KEY `recordatorios_vacunas_fecha_recordatorio_index` (`fecha_recordatorio`),
  ADD KEY `recordatorios_vacunas_estado_index` (`estado`);

--
-- Indexes for table `rescates`
--
ALTER TABLE `rescates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rescates_denuncia_id_index` (`denuncia_id`),
  ADD KEY `rescates_animal_rescatado_id_index` (`animal_rescatado_id`),
  ADD KEY `rescates_fecha_programada_index` (`fecha_programada`),
  ADD KEY `rescates_exitoso_index` (`exitoso`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_codigo_unique` (`codigo`),
  ADD KEY `roles_codigo_index` (`codigo`),
  ADD KEY `roles_activo_index` (`activo`);

--
-- Indexes for table `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rol_permiso` (`rol_id`,`permiso_id`),
  ADD KEY `rol_permiso_asignado_por_foreign` (`asignado_por`),
  ADD KEY `rol_permiso_rol_id_index` (`rol_id`),
  ADD KEY `rol_permiso_permiso_id_index` (`permiso_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tipos_vacunas`
--
ALTER TABLE `tipos_vacunas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipos_vacunas_codigo_unique` (`codigo`),
  ADD KEY `tipos_vacunas_codigo_index` (`codigo`),
  ADD KEY `tipos_vacunas_especie_aplicable_index` (`especie_aplicable`),
  ADD KEY `tipos_vacunas_activo_index` (`activo`);

--
-- Indexes for table `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tratamientos_consulta_id_index` (`consulta_id`),
  ADD KEY `tratamientos_historial_clinico_id_index` (`historial_clinico_id`),
  ADD KEY `tratamientos_estado_index` (`estado`),
  ADD KEY `tratamientos_fecha_inicio_index` (`fecha_inicio`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuarios_username_unique` (`username`),
  ADD UNIQUE KEY `usuarios_email_unique` (`email`),
  ADD KEY `usuarios_username_index` (`username`),
  ADD KEY `usuarios_email_index` (`email`),
  ADD KEY `usuarios_activo_index` (`activo`);

--
-- Indexes for table `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_rol` (`usuario_id`,`rol_id`),
  ADD KEY `usuario_rol_asignado_por_foreign` (`asignado_por`),
  ADD KEY `usuario_rol_usuario_id_index` (`usuario_id`),
  ADD KEY `usuario_rol_rol_id_index` (`rol_id`);

--
-- Indexes for table `vacunas`
--
ALTER TABLE `vacunas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vacunas_historial_clinico_id_index` (`historial_clinico_id`),
  ADD KEY `vacunas_tipo_vacuna_id_index` (`tipo_vacuna_id`),
  ADD KEY `vacunas_veterinario_id_index` (`veterinario_id`),
  ADD KEY `vacunas_fecha_aplicacion_index` (`fecha_aplicacion`),
  ADD KEY `vacunas_estado_index` (`estado`);

--
-- Indexes for table `veterinarios`
--
ALTER TABLE `veterinarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `veterinarios_usuario_id_unique` (`usuario_id`),
  ADD UNIQUE KEY `veterinarios_numero_tarjeta_profesional_unique` (`numero_tarjeta_profesional`),
  ADD KEY `veterinarios_usuario_id_index` (`usuario_id`),
  ADD KEY `veterinarios_numero_tarjeta_profesional_index` (`numero_tarjeta_profesional`),
  ADD KEY `veterinarios_activo_index` (`activo`);

--
-- Indexes for table `visitas_domiciliarias`
--
ALTER TABLE `visitas_domiciliarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitas_domiciliarias_adopcion_id_index` (`adopcion_id`),
  ADD KEY `visitas_domiciliarias_visitador_id_index` (`visitador_id`),
  ADD KEY `visitas_domiciliarias_fecha_programada_index` (`fecha_programada`),
  ADD KEY `visitas_domiciliarias_tipo_visita_index` (`tipo_visita`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adopciones`
--
ALTER TABLE `adopciones`
  ADD CONSTRAINT `adopciones_adoptante_id_foreign` FOREIGN KEY (`adoptante_id`) REFERENCES `adoptantes` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `adopciones_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `adopciones_evaluador_id_foreign` FOREIGN KEY (`evaluador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `animals`
--
ALTER TABLE `animals`
  ADD CONSTRAINT `animals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `animals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cirugias`
--
ALTER TABLE `cirugias`
  ADD CONSTRAINT `cirugias_anestesiologo_id_foreign` FOREIGN KEY (`anestesiologo_id`) REFERENCES `veterinarios` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `cirugias_cirujano_id_foreign` FOREIGN KEY (`cirujano_id`) REFERENCES `veterinarios` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `cirugias_historial_clinico_id_foreign` FOREIGN KEY (`historial_clinico_id`) REFERENCES `historiales_clinicos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_historial_clinico_id_foreign` FOREIGN KEY (`historial_clinico_id`) REFERENCES `historiales_clinicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultas_veterinario_id_foreign` FOREIGN KEY (`veterinario_id`) REFERENCES `veterinarios` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `denuncias`
--
ALTER TABLE `denuncias`
  ADD CONSTRAINT `denuncias_denunciante_id_foreign` FOREIGN KEY (`denunciante_id`) REFERENCES `denunciantes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `denuncias_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `eventos_auditoria`
--
ALTER TABLE `eventos_auditoria`
  ADD CONSTRAINT `eventos_auditoria_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `examenes_laboratorio`
--
ALTER TABLE `examenes_laboratorio`
  ADD CONSTRAINT `examenes_laboratorio_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historiales_clinicos`
--
ALTER TABLE `historiales_clinicos`
  ADD CONSTRAINT `historiales_clinicos_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD CONSTRAINT `medicamentos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos_farmaceuticos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `medicamentos_tratamiento_id_foreign` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procedimientos`
--
ALTER TABLE `procedimientos`
  ADD CONSTRAINT `procedimientos_asistente_id_foreign` FOREIGN KEY (`asistente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `procedimientos_tratamiento_id_foreign` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `procedimientos_veterinario_id_foreign` FOREIGN KEY (`veterinario_id`) REFERENCES `veterinarios` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `puntos_indicadores`
--
ALTER TABLE `puntos_indicadores`
  ADD CONSTRAINT `puntos_indicadores_indicador_id_foreign` FOREIGN KEY (`indicador_id`) REFERENCES `indicadores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recordatorios_vacunas`
--
ALTER TABLE `recordatorios_vacunas`
  ADD CONSTRAINT `recordatorios_vacunas_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recordatorios_vacunas_vacuna_id_foreign` FOREIGN KEY (`vacuna_id`) REFERENCES `vacunas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rescates`
--
ALTER TABLE `rescates`
  ADD CONSTRAINT `rescates_animal_rescatado_id_foreign` FOREIGN KEY (`animal_rescatado_id`) REFERENCES `animals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rescates_denuncia_id_foreign` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncias` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `rol_permiso_asignado_por_foreign` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rol_permiso_permiso_id_foreign` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permiso_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD CONSTRAINT `tratamientos_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tratamientos_historial_clinico_id_foreign` FOREIGN KEY (`historial_clinico_id`) REFERENCES `historiales_clinicos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `usuario_rol_asignado_por_foreign` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuario_rol_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_rol_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vacunas`
--
ALTER TABLE `vacunas`
  ADD CONSTRAINT `vacunas_historial_clinico_id_foreign` FOREIGN KEY (`historial_clinico_id`) REFERENCES `historiales_clinicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacunas_tipo_vacuna_id_foreign` FOREIGN KEY (`tipo_vacuna_id`) REFERENCES `tipos_vacunas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `vacunas_veterinario_id_foreign` FOREIGN KEY (`veterinario_id`) REFERENCES `veterinarios` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `veterinarios`
--
ALTER TABLE `veterinarios`
  ADD CONSTRAINT `veterinarios_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visitas_domiciliarias`
--
ALTER TABLE `visitas_domiciliarias`
  ADD CONSTRAINT `visitas_domiciliarias_adopcion_id_foreign` FOREIGN KEY (`adopcion_id`) REFERENCES `adopciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visitas_domiciliarias_visitador_id_foreign` FOREIGN KEY (`visitador_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
