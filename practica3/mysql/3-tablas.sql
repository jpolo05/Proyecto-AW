/*
  Recuerda deshabilitar "Enable foreign key checks" para evitar problemas al importar.
*/
DROP TABLE IF EXISTS `lineas_oferta`;
DROP TABLE IF EXISTS `ofertas`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `categorias`;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `imagen` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `productos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `id_categoria` INT NULL,
  `precio_base` DECIMAL(10,2) NOT NULL,
  `iva` TINYINT NOT NULL,
  `disponible` TINYINT(1) NOT NULL DEFAULT 1,
  `ofertado` TINYINT(1) NOT NULL DEFAULT 1,
  `imagen` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_categoria_productos`
    FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `user` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `nombre` VARCHAR(50) NOT NULL,
  `apellidos` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  `rol` ENUM('Gerente', 'Cliente', 'Cocinero', 'Camarero') NOT NULL DEFAULT 'Cliente',
  `imagen` VARCHAR(255) NULL,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ofertas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `cantidad` SMALLINT NOT NULL DEFAULT 1,
  `comienzo` DATETIME NOT NULL,
  `fin` DATETIME NOT NULL,
  `descuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `lineas_oferta` (
  `id` INT NOT NULL,
  `producto` INT NOT NULL,
  PRIMARY KEY (`id`, `producto`),
  CONSTRAINT `fk_idOferta_lineasOferta`
    FOREIGN KEY (`id`) REFERENCES `ofertas`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_producto_lineasOferta`
    FOREIGN KEY (`producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

