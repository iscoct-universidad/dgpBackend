-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 21-11-2019 a las 11:53:11
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
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`id_actividad`, `id_voluntario`, `id_socio`, `nombre`, `fecha`, `localizacion`, `id_usuario_propone`, `descripcion`, `imagen`, `puntuacion`, `cerrada`) VALUES
(1, 3, 1, 'actividad de prueba', NULL, NULL, NULL, 'sadfadsf sadf asdf asdf dsf', NULL, NULL, 0),
(2, NULL, 1, 'actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actividad de prueba, esto es una actividad de prueba.', 'bb73e6fd43c9ae10.png', NULL, 0),
(20, 3, 1, 'esto es una actividad de prueba', '2019-11-24 18:00:00', 'La Plaza', 1, 'esta es la descripcion de la actvidad', NULL, NULL, 1),
(24, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(25, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(26, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(27, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(28, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(29, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(30, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(31, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(32, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(33, NULL, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(34, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(35, 3, 1, 'actividad ya realizada', '2019-11-14 21:35:00', 'Parque', 3, 'actividad que ya ha acabado', NULL, 4, 1),
(36, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(37, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(38, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(39, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(40, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(41, 3, NULL, 'esto es una actividad de prueba', NULL, NULL, NULL, 'esta es la descripcion de la actvidad', NULL, NULL, 0),
(42, 3, 1, 'esto es una actividad de prueba', NULL, NULL, NULL, 'actividad des prueba y ajfnenfmd', NULL, NULL, 0);

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
(2, 'etiqueta2'),
(20, 'etiqueta2'),
(28, 'etiqueta2'),
(29, 'etiqueta2'),
(30, 'etiqueta2'),
(31, 'etiqueta2'),
(32, 'etiqueta2'),
(33, 'etiqueta2'),
(34, 'etiqueta2'),
(36, 'etiqueta2'),
(37, 'etiqueta2'),
(38, 'etiqueta2'),
(39, 'etiqueta2'),
(40, 'etiqueta2'),
(41, 'etiqueta2'),
(2, 'gusto1'),
(20, 'gusto1'),
(28, 'gusto1'),
(29, 'gusto1'),
(30, 'gusto1'),
(31, 'gusto1'),
(32, 'gusto1'),
(33, 'gusto1'),
(34, 'gusto1'),
(36, 'gusto1'),
(37, 'gusto1'),
(38, 'gusto1'),
(39, 'gusto1'),
(40, 'gusto1'),
(41, 'gusto1');

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
('etiqueta2'),
('gusto1');

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
(4, 'etiqueta2'),
(4, 'gusto1'),
(28, 'etiqueta2'),
(28, 'gusto1');

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
(1, 'socio', 'asdf', 'asdf', 'asdf', '77448447H', '2019-10-10', 'granada', 'asdf@gmail.com', 123412345, 'asdfasdf', 'asdfasdfadsf', '1234', ''),
(3, 'voluntario', 'maria', 'anyApellido1', 'anyApellido2', 'dndddi', '2000-02-02', 'loc', 'superusuarioM@gmail.com', 123412345, 'asp dsf asdf', 'estas son mis observaciones', '12345', ''),
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
  ADD KEY `id_voluntario` (`id_voluntario`),
  ADD KEY `id_socio` (`id_socio`),
  ADD KEY `id_usuario_propone` (`id_usuario_propone`);

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
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
--
-- AUTO_INCREMENT de la tabla `gustos`
--
ALTER TABLE `gustos`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
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
  ADD CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`id_voluntario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `actividad_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `actividad_ibfk_3` FOREIGN KEY (`id_usuario_propone`) REFERENCES `usuario` (`id`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;