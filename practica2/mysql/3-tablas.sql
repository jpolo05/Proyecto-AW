/*
  Recuerda deshabilitar "Enable foreign key checks" para evitar problemas al importar.
*/
DROP TABLE IF EXISTS `linea_pedido`;
DROP TABLE IF EXISTS `pedidos`;
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

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `numeroPedido` INT NOT NULL,
  `estado` ENUM('Nuevo', 'Recibido', 'En preparación', 'Cocinando', 'Listo cocina', 'Terminado', 'Entregado', 'Cancelado') NOT NULL DEFAULT 'Nuevo',
  `tipo` ENUM('Local', 'Llevar') NOT NULL DEFAULT 'Local',
  `fecha` DATETIME NOT NULL,
  `cliente` VARCHAR(20) NOT NULL,
  `cocinero` VARCHAR(20) NULL,
  `imagenCocinero` VARCHAR(255) NOT NULL DEFAULT '/uploads/usuarios/default.jpg',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cliente_pedidos`
    FOREIGN KEY (`cliente`) REFERENCES `usuarios`(`user`) ON DELETE CASCADE,
  CONSTRAINT `fk_cocinero_pedidos`
    FOREIGN KEY (`cocinero`) REFERENCES `usuarios`(`user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `linea_pedido` (
  `numeroPedido` INT NOT NULL,
  `idProducto` INT NOT NULL,
  `cantidad` SMALLINT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`numeroPedido`, `idProducto`),
  CONSTRAINT `fk_idPedido_lineaPedido`
    FOREIGN KEY (`numeroPedido`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_idProducto_lineaPedido`
    FOREIGN KEY (`idProducto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

