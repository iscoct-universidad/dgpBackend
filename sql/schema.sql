-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 29-11-2019 a las 13:42:23
-- Versión del servidor: 5.7.28-0ubuntu0.18.04.4
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dgp_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id_actividad` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `localizacion` varchar(30) COLLATE utf8_spanish_ci DEFAULT NULL,
  `id_creador` int(11) NOT NULL,
  `descripcion` varchar(280) COLLATE utf8_spanish_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `cerrada` tinyint(1) NOT NULL DEFAULT '0',
  `tipo` varchar(7) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`id_actividad`, `nombre`, `fecha`, `localizacion`, `id_creador`, `descripcion`, `imagen`, `cerrada`, `tipo`) VALUES
(49, 'actividad pareja', NULL, NULL, 1, 'esta es de pareja', NULL, 0, 'pareja'),
(50, 'actividad grupal', NULL, NULL, 3, 'esto es una actividad grupal', NULL, 0, 'grupal'),
(51, 'para no apuntarse', NULL, NULL, 3, 'en pareja vacia', NULL, 0, 'pareja'),
(52, 'en pareja de voluntario', NULL, NULL, 3, 'en pareja de voluntario', NULL, 0, 'pareja'),
(53, 'actividad grupal vacia', NULL, NULL, 3, 'actividad grupal para dejar vacia', NULL, 0, 'grupal'),
(54, 'esta de pareja de socio para dejar vacia', NULL, NULL, 1, 'esta de pareja de socio para dejar vacia', NULL, 0, 'pareja'),
(55, 'actividad de prueba 2', NULL, NULL, 1, 'esta es la descripcion de la actividad de prueba, esto es una actividad de prueba.', NULL, 0, 'pareja'),
(56, 'actividad grupal de prueba', NULL, NULL, 1, 'descripcion grupal', NULL, 0, 'grupal');

--
-- Disparadores `actividad`
--
DELIMITER $$
CREATE TRIGGER `dominio_tipo_actividad_insert` BEFORE INSERT ON `actividad` FOR EACH ROW IF !(new.tipo='pareja' || new.tipo='grupal') THEN 
signal sqlstate '45000';
end if
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `dominio_tipo_actividad_update` BEFORE UPDATE ON `actividad` FOR EACH ROW IF !(new.tipo='pareja' || new.tipo='grupal') THEN 
signal sqlstate '45000';
end if
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_etiquetas`
--

CREATE TABLE `actividad_etiquetas` (
  `id_actividad` int(11) NOT NULL,
  `etiqueta` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `actividad_etiquetas`
--

INSERT INTO `actividad_etiquetas` (`id_actividad`, `etiqueta`) VALUES
(49, 'Cine'),
(50, 'Cine'),
(51, 'Cine'),
(49, 'Compras'),
(51, 'Compras'),
(54, 'Deportes'),
(55, 'etiqueta2'),
(55, 'gusto1'),
(54, 'Juegos de Mesa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `nombre` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`nombre`) VALUES
('Cine'),
('Compras'),
('Deportes'),
('etiqueta2'),
('gusto1'),
('Juegos de Mesa'),
('Ocio'),
('Otro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gustos`
--

CREATE TABLE `gustos` (
  `id_usuario` int(11) NOT NULL,
  `gusto` varchar(30) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `gustos`
--

INSERT INTO `gustos` (`id_usuario`, `gusto`) VALUES
(1, 'Cine'),
(3, 'Cine');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_chat`
--

CREATE TABLE `mensajes_chat` (
  `id_mensaje` int(10) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `id_participante` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(5) NOT NULL,
  `contenido` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `mensajes_chat`
--

INSERT INTO `mensajes_chat` (`id_mensaje`, `id_actividad`, `id_participante`, `fecha`, `tipo`, `contenido`) VALUES
(1, 53, 1, '2019-11-29 10:38:31', 'texto', 'mensaje del socio'),
(2, 51, 3, '2019-11-29 17:26:54', 'texto', 'mensaje del socio'),
(3, 50, 3, '2019-11-30 00:00:00', 'texto', 'mensaje por voluntario en la 50 para que lo vea el socio.'),
(4, 54, 1, '2019-11-30 00:00:00', 'texto', 'mensaje del socio que NO verá el voluntario'),
(6, 52, 3, '2019-11-29 12:52:05', 'texto', 'mensaje desde postman');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes_grupal`
--

CREATE TABLE `participantes_grupal` (
  `id_actividad` int(11) NOT NULL,
  `id_participante` int(11) NOT NULL,
  `puntuacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `participantes_grupal`
--

INSERT INTO `participantes_grupal` (`id_actividad`, `id_participante`, `puntuacion`) VALUES
(50, 1, NULL),
(50, 3, NULL),
(53, 3, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes_pareja`
--

CREATE TABLE `participantes_pareja` (
  `id_actividad` int(11) NOT NULL,
  `id_socio` int(11) DEFAULT NULL,
  `id_voluntario` int(11) DEFAULT NULL,
  `puntuacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `participantes_pareja`
--

INSERT INTO `participantes_pareja` (`id_actividad`, `id_socio`, `id_voluntario`, `puntuacion`) VALUES
(49, 1, 3, NULL),
(51, NULL, 3, NULL),
(52, 1, 3, NULL),
(54, 1, NULL, NULL),
(55, 1, NULL, NULL);

--
-- Disparadores `participantes_pareja`
--
DELIMITER $$
CREATE TRIGGER `insert_apuntar` BEFORE INSERT ON `participantes_pareja` FOR EACH ROW BEGIN
DECLARE voluntario int default 0;
DECLARE socio int default 0;
IF (new.id_socio IS NULL && new.id_voluntario IS NULL) THEN
	signal sqlstate '45000';
ELSEIF (new.id_socio IS NULL) THEN
    select count(*) into voluntario from usuario where usuario.id=new.id_voluntario and usuario.rol='voluntario';
    IF (voluntario <= 0) THEN 
    signal sqlstate '45000';
    end if;
ELSEIF (new.id_voluntario IS NULL) THEN
   select count(*) into socio from usuario where usuario.id=new.id_socio and usuario.rol='socio';
    IF (socio <= 0) THEN 
    signal sqlstate '45000';
    end if;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_apuntar` BEFORE UPDATE ON `participantes_pareja` FOR EACH ROW BEGIN
DECLARE voluntario int default 0;
DECLARE socio int default 0;
IF (old.id_socio IS NULL) THEN
    select count(*) into socio from usuario where usuario.id=new.id_socio and usuario.rol='socio';
    IF (socio <= 0) THEN 
    signal sqlstate '45000';
    end if;
ELSEIF (old.id_voluntario IS NULL) THEN
   select count(*) into voluntario from usuario where usuario.id=new.id_voluntario and usuario.rol='voluntario';
    IF (voluntario <= 0) THEN 
    signal sqlstate '45000';
    end if;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `rol` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `nombre` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `apellido1` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `apellido2` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `DNI` varchar(9) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `localidad` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `telefono` int(9) NOT NULL,
  `aspiraciones` varchar(140) COLLATE utf8_spanish_ci DEFAULT NULL,
  `observaciones` varchar(140) COLLATE utf8_spanish_ci DEFAULT NULL,
  `password` varchar(24) COLLATE utf8_spanish_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `rol`, `nombre`, `apellido1`, `apellido2`, `DNI`, `fecha_nacimiento`, `localidad`, `email`, `telefono`, `aspiraciones`, `observaciones`, `password`, `imagen`) VALUES
(1, 'socio', 'socio', 'socioapellido1', 'socioapellido2', '77348546H', '2019-10-10', 'granada', 'socio@socio.com', 123412345, 'asdfasdf', 'asdfasdfadsf', '1234', 'whoisthis.png'),
(3, 'voluntario', 'voluntario', 'voluntarioApellido1', 'voluntarioApellido2', 'dndddi', '2000-02-02', 'loc', 'voluntario@voluntario.com', 123412345, 'asp dsf asdf', 'estas son mis observaciones', '1234', 'modele.jpg'),
(4, 'administrador', 'admin', 'apellido1', 'apellido2', '77448467H', '2000-02-02', 'Granada', 'admin@admin.com', 958123123, 'asdf sdf asdfa sd', NULL, 'admin', '6f7f32bf4a8b2205.png'),
(5, 'voluntario', 'nombre', 'apellido1', 'apellido2', '77448447H', '2000-02-02', 'Granada', 'mail@mail.com', 958123123, 'asdf sdf asdfa sd', NULL, 'password1234', ''),
(28, 'socio', 'nombre', 'apellido1', 'apellido2', '77448467H', '2000-02-02', 'Granada', 'mail2@mail2.com', 958123123, 'asdf sdf asdfa sd', NULL, 'password1234', 'a00f025f530dbec9.png');

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `dominio_rol` BEFORE INSERT ON `usuario` FOR EACH ROW IF !(new.rol='socio' || new.rol='voluntario' || new.rol='administrador') THEN 
signal sqlstate '45000';
end if
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `dominio_rol_update` BEFORE UPDATE ON `usuario` FOR EACH ROW IF !(new.rol='socio' || new.rol='voluntario' || new.rol='administrador') THEN 
signal sqlstate '45000';
end if
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_usuario_propone` (`id_creador`);

--
-- Indices de la tabla `actividad_etiquetas`
--
ALTER TABLE `actividad_etiquetas`
  ADD PRIMARY KEY (`id_actividad`,`etiqueta`),
  ADD KEY `actividad-etiquetas_ibfk_2` (`etiqueta`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`nombre`);

--
-- Indices de la tabla `gustos`
--
ALTER TABLE `gustos`
  ADD PRIMARY KEY (`id_usuario`,`gusto`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `gusto` (`gusto`);

--
-- Indices de la tabla `mensajes_chat`
--
ALTER TABLE `mensajes_chat`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `id_participante` (`id_participante`);

--
-- Indices de la tabla `participantes_grupal`
--
ALTER TABLE `participantes_grupal`
  ADD PRIMARY KEY (`id_actividad`,`id_participante`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `id_participante` (`id_participante`);

--
-- Indices de la tabla `participantes_pareja`
--
ALTER TABLE `participantes_pareja`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `id_socio` (`id_socio`),
  ADD KEY `id_voluntario` (`id_voluntario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
--
-- AUTO_INCREMENT de la tabla `gustos`
--
ALTER TABLE `gustos`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `mensajes_chat`
--
ALTER TABLE `mensajes_chat`
  MODIFY `id_mensaje` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD CONSTRAINT `actividad_ibfk_3` FOREIGN KEY (`id_creador`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `actividad_etiquetas`
--
ALTER TABLE `actividad_etiquetas`
  ADD CONSTRAINT `actividad_etiquetas_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `actividad_etiquetas_ibfk_2` FOREIGN KEY (`etiqueta`) REFERENCES `etiquetas` (`nombre`);

--
-- Filtros para la tabla `gustos`
--
ALTER TABLE `gustos`
  ADD CONSTRAINT `gustos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `gustos_ibfk_2` FOREIGN KEY (`gusto`) REFERENCES `etiquetas` (`nombre`);

--
-- Filtros para la tabla `mensajes_chat`
--
ALTER TABLE `mensajes_chat`
  ADD CONSTRAINT `mensajes_chat_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `mensajes_chat_ibfk_2` FOREIGN KEY (`id_participante`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `participantes_grupal`
--
ALTER TABLE `participantes_grupal`
  ADD CONSTRAINT `participantes_grupal_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `participantes_grupal_ibfk_2` FOREIGN KEY (`id_participante`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `participantes_pareja`
--
ALTER TABLE `participantes_pareja`
  ADD CONSTRAINT `participantes_pareja_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `participantes_pareja_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `participantes_pareja_ibfk_3` FOREIGN KEY (`id_voluntario`) REFERENCES `usuario` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
