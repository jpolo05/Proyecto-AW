/* Deshabilitar comprobación de claves foráneas para facilitar la importación 
*/

DROP TABLE IF EXISTS `lineas_oferta`;
DROP TABLE IF EXISTS `ofertas`;
DROP TABLE IF EXISTS `linea_pedido`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `recompensas`;

-- 1. Tabla Categorías
CREATE TABLE `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `imagen` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabla Usuarios
CREATE TABLE `usuarios` (
  `user` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `nombre` VARCHAR(50) NOT NULL,
  `apellidos` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  `rol` ENUM('Gerente', 'Cliente', 'Cocinero', 'Camarero') NOT NULL DEFAULT 'Cliente',
  `imagen` VARCHAR(255) NULL,
  `bistroCoins` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabla Productos
CREATE TABLE `productos` (
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

-- 4. Tabla Pedidos
CREATE TABLE `pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `numeroPedido` INT(11) NOT NULL,
  `estado` ENUM('Nuevo','Recibido','En preparación','Cocinando','Listo cocina','Terminado','Entregado','Cancelado') NOT NULL DEFAULT 'Nuevo',
  `tipo` ENUM('Local','Llevar') NOT NULL DEFAULT 'Local',
  `fecha` DATETIME NOT NULL,
  `cliente` VARCHAR(20) NOT NULL,
  `cocinero` VARCHAR(20) DEFAULT NULL,
  `imagenCocinero` VARCHAR(255) NOT NULL DEFAULT 'img/uploads/usuarios/default.jpg',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `fk_cliente_pedidos_idx` (`cliente`),
  KEY `fk_cocinero_pedidos_idx` (`cocinero`),
  CONSTRAINT `fk_cliente_pedidos` 
    FOREIGN KEY (`cliente`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE,
  CONSTRAINT `fk_cocinero_pedidos` 
    FOREIGN KEY (`cocinero`) REFERENCES `usuarios` (`user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Tabla Líneas de Pedido
CREATE TABLE `linea_pedido` (
  `numeroPedido` INT(11) NOT NULL,
  `idProducto` INT(11) NOT NULL,
  `cantidad` SMALLINT(6) NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estado` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`numeroPedido`,`idProducto`),
  KEY `fk_idProducto_lineaPedido_idx` (`idProducto`),
  CONSTRAINT `fk_idPedido_lineaPedido` 
    FOREIGN KEY (`numeroPedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_idProducto_lineaPedido` 
    FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Tabla Ofertas
CREATE TABLE `ofertas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `comienzo` DATETIME NOT NULL,
  `fin` DATETIME NOT NULL,
  `descuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tabla Líneas de Oferta
CREATE TABLE `lineas_oferta` (
  `id_oferta` INT NOT NULL,
  `producto` INT NOT NULL,
  `cantidad` SMALLINT(6) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_oferta`, `producto`),
  KEY `fk_producto_lineasOferta_idx` (`producto`),
  CONSTRAINT `fk_idOferta_lineasOferta` 
    FOREIGN KEY (`id_oferta`) REFERENCES `ofertas`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_producto_lineasOferta` 
    FOREIGN KEY (`producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Tabla de Recompensas
CREATE TABLE `recompensas`(
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT NOT NULL,
  `bistroCoins` INT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_producto_idProducto` 
    FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

