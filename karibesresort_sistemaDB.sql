-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-06-2026 a las 13:46:53
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `karibesresort_sistemaDB`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_hero`
--

CREATE TABLE `configuracion_hero` (
  `id` int(11) NOT NULL,
  `imagen_fondo` varchar(255) NOT NULL,
  `subtitulo` varchar(150) NOT NULL,
  `titulo_html` varchar(255) NOT NULL COMMENT 'Permite etiquetas HTML como <span>',
  `descripcion` text NOT NULL,
  `texto_boton` varchar(50) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_hero`
--

INSERT INTO `configuracion_hero` (`id`, `imagen_fondo`, `subtitulo`, `titulo_html`, `descripcion`, `texto_boton`, `actualizado_en`) VALUES
(1, '', 'El Primer Resort Climatizado del Perú', 'Oasis Tropical en <span>Huancayo</span>', 'Descubra el primer resort con ambientes 100% climatizados y un atrio encapsulado en el corazón de los Andes. Lujo, confort y calidez en un solo lugar.', 'Reserva tu Suite', '2026-06-10 18:23:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_nosotros`
--

CREATE TABLE `configuracion_nosotros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `parrafo_1` text NOT NULL,
  `parrafo_2` text NOT NULL,
  `imagen_principal` varchar(255) NOT NULL,
  `imagen_secundaria` varchar(255) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_nosotros`
--

INSERT INTO `configuracion_nosotros` (`id`, `titulo`, `parrafo_1`, `parrafo_2`, `imagen_principal`, `imagen_secundaria`, `actualizado_en`) VALUES
(1, 'El Primer Resort Climatizado', 'Karibe`S será el primer Resort climatizado en la ciudad de Huancayo y el Perú, con ambientes con calefacción y terrazas orientadas hacia un espectacular atrio encapsulado con techos traslúcidos y ventanales panorámicos.', 'Diseñado para ofrecer un oasis inalterable por el clima exterior, contamos con piscinas de agua caliente, un relajante spa, restaurante de alta gastronomía y exclusivas áreas de entretenimiento para un confort absoluto.', '', '', '2026-06-10 18:23:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `mensaje` text DEFAULT NULL,
  `estado_reserva` enum('Nueva','Atendida','Confirmada','Cancelada') DEFAULT 'Nueva',
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `nombre`, `email`, `telefono`, `checkin`, `checkout`, `mensaje`, `estado_reserva`, `fecha_solicitud`) VALUES
(1, 'Luciano Quiroz', 'lujesuqui@gmail.com', '961735024', '2026-06-09', '2026-06-17', '[Interesado en: Hotel prueba]\n\nHola', 'Nueva', '2026-06-09 23:57:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `icono_fontawesome` varchar(100) NOT NULL COMMENT 'Clase del icono ej: fa-solid fa-spa',
  `orden` int(11) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `titulo`, `descripcion`, `icono_fontawesome`, `orden`, `estado`) VALUES
(1, 'Restaurante de Alta Cocina', 'Desayunos premium incluidos en su estadía, además de exquisitos platos a la carta para almuerzos y cenas.', 'fa-solid fa-utensils', 1, 1),
(2, 'Oasis Termal y Spa', 'Relájese sin importar el clima exterior en nuestra piscina de agua caliente para niños y adultos, o renuévese en nuestra Sala de Spa.', 'fa-solid fa-water-ladder', 2, 1),
(3, 'Bar & Video Karaoke', 'El ambiente perfecto para su entretenimiento nocturno. Disfrute de nuestra coctelería exclusiva y cante sus temas favoritos.', 'fa-solid fa-martini-glass-citrus', 3, 1),
(4, 'Salón de Entretenimiento', 'Diversión garantizada para toda la familia con nuestra moderna sala de juegos equipada con mesas de Ping Pong, Fulbito de mesa y más.', 'fa-solid fa-table-tennis-paddle-ball', 4, 1),
(5, 'Confort Total', 'Relájese en nuestros lobbies confortables en cada piso. Contamos con servicio de lavandería y amplia playa de estacionamiento privada.', 'fa-solid fa-car-rear', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suites`
--

CREATE TABLE `suites` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion_corta` varchar(255) NOT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `amenidades_json` text DEFAULT NULL COMMENT 'JSON con las clases de los iconos ej: ["fa-wifi", "fa-tv"]',
  `orden` int(11) DEFAULT 0 COMMENT 'Para ordenar las tarjetas manualmente',
  `estado` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `suites`
--

INSERT INTO `suites` (`id`, `nombre`, `descripcion_corta`, `precio_noche`, `imagen`, `amenidades_json`, `orden`, `estado`, `creado_en`) VALUES
(1, 'Hotel prueba', 'Prueba', 145.00, '', '[\"fa-solid fa-temperature-half\",\"fa-solid fa-hot-tub-person\",\"fa-solid fa-couch\",\"fa-solid fa-wifi\",\"fa-solid fa-tv\",\"fa-solid fa-martini-glass\",\"fa-solid fa-mug-hot\",\"fa-solid fa-shirt\",\"fa-solid fa-vault\",\"fa-solid fa-bell-concierge\"]', 0, 1, '2026-06-09 23:22:40'),
(2, 'Hotel prueba 2', 'Prueba 2', 147.00, '', '[\"fa-solid fa-wifi\",\"fa-solid fa-bell-concierge\",\"fa-solid fa-vault\"]', 1, 1, '2026-06-09 23:23:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `testimonios`
--

CREATE TABLE `testimonios` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `origen` varchar(100) NOT NULL COMMENT 'Ej: Huésped Frecuente, Milán',
  `comentario` text NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_admin`
--

CREATE TABLE `usuarios_admin` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL COMMENT 'Usuario para el login',
  `password` varchar(255) NOT NULL COMMENT 'Hash de contraseña (BCRYPT)',
  `nombre_completo` varchar(100) NOT NULL,
  `rol` enum('SuperAdmin','Recepcion','Marketing') DEFAULT 'SuperAdmin',
  `ultimo_acceso` datetime DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1 COMMENT '1: Activo, 0: Suspendido'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_admin`
--

INSERT INTO `usuarios_admin` (`id`, `usuario`, `password`, `nombre_completo`, `rol`, `ultimo_acceso`, `estado`) VALUES
(1, 'admin', '$2a$12$GaSrjUDW7l45f96P7cqg7OiJcsXiWoyAsIbruG3JsNIKK07rvCyfO', 'Gerente General', 'SuperAdmin', '2026-06-10 13:28:13', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion_hero`
--
ALTER TABLE `configuracion_hero`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_nosotros`
--
ALTER TABLE `configuracion_nosotros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `suites`
--
ALTER TABLE `suites`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion_hero`
--
ALTER TABLE `configuracion_hero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion_nosotros`
--
ALTER TABLE `configuracion_nosotros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `suites`
--
ALTER TABLE `suites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
