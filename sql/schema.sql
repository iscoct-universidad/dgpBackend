-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 07-11-2019 a las 06:23:39
-- Versión del servidor: 5.7.27-0ubuntu0.18.04.1
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.1

USE dgp_db;
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
  `id_voluntario` int(11) DEFAULT NULL,
  `id_socio` int(11) DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `localizacion` varchar(30) COLLATE utf8_spanish_ci DEFAULT NULL,
  `id_usuario_propone` int(11) DEFAULT NULL,
  `descripcion` varchar(280) COLLATE utf8_spanish_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `puntuacion` int(11) DEFAULT NULL,
  `cerrada` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Disparadores `actividad`
--
DELIMITER $$
CREATE TRIGGER `insert_actividad` BEFORE INSERT ON `actividad` FOR EACH ROW BEGIN
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
CREATE TRIGGER `update_actividad` BEFORE UPDATE ON `actividad` FOR EACH ROW BEGIN
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
-- Estructura de tabla para la tabla `actividad-etiquetas`
--

CREATE TABLE `actividad-etiquetas` (
  `id_actividad` int(11) NOT NULL,
  `etiqueta` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `nombre` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gustos`
--

CREATE TABLE `gustos` (
  `id_usuario` int(11) NOT NULL,
  `gusto` varchar(30) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  ADD KEY `id_voluntario` (`id_voluntario`),
  ADD KEY `id_socio` (`id_socio`),
  ADD KEY `id_usuario_propone` (`id_usuario_propone`);

--
-- Indices de la tabla `actividad-etiquetas`
--
ALTER TABLE `actividad-etiquetas`
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
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT de la tabla `gustos`
--
ALTER TABLE `gustos`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`id_voluntario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `actividad_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `actividad_ibfk_3` FOREIGN KEY (`id_usuario_propone`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `actividad-etiquetas`
--
ALTER TABLE `actividad-etiquetas`
  ADD CONSTRAINT `actividad-etiquetas_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `actividad-etiquetas_ibfk_2` FOREIGN KEY (`etiqueta`) REFERENCES `etiquetas` (`nombre`);

--
-- Filtros para la tabla `gustos`
--
ALTER TABLE `gustos`
  ADD CONSTRAINT `gustos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `gustos_ibfk_2` FOREIGN KEY (`gusto`) REFERENCES `etiquetas` (`nombre`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

