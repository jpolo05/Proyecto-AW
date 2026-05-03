/* Deshabilitar comprobacion de claves foraneas para facilitar la importacion */

-- Borra tablas antiguas para poder recrear todo desde cero
-- Se espera ejecutar con comprobacion de claves foraneas deshabilitada
DROP TABLE IF EXISTS `lineas_oferta`;
DROP TABLE IF EXISTS `ofertas`;
DROP TABLE IF EXISTS `linea_pedido`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `recompensas`;

-- 1. Tabla Categorias
-- Guarda las categorias de productos de la carta
CREATE TABLE `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT, -- Identificador de la categoria
  `nombre` VARCHAR(80) NOT NULL, -- Nombre visible
  `descripcion` TEXT NOT NULL, -- Descripcion de la categoria
  `imagen` VARCHAR(255) NULL, -- Ruta o URL de la imagen
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabla Usuarios
-- Guarda clientes y empleados de la aplicacion
CREATE TABLE `usuarios` (
  `user` VARCHAR(20) NOT NULL, -- Nickname y clave primaria
  `email` VARCHAR(120) NOT NULL UNIQUE, -- Email unico
  `nombre` VARCHAR(50) NOT NULL, -- Nombre del usuario
  `apellidos` VARCHAR(50) NOT NULL, -- Apellidos del usuario
  `contrasena` VARCHAR(255) NOT NULL, -- Contraseña cifrada
  `rol` ENUM('Gerente', 'Cliente', 'Cocinero', 'Camarero') NOT NULL DEFAULT 'Cliente', -- Rol de acceso
  `imagen` VARCHAR(255) NULL, -- Ruta o URL de la imagen de perfil
  `bistroCoins` INT NOT NULL DEFAULT 0, -- Monedas acumuladas del cliente
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabla Productos
-- Guarda los productos de la carta
CREATE TABLE `productos` (
  `id` INT NOT NULL AUTO_INCREMENT, -- Identificador del producto
  `nombre` VARCHAR(120) NOT NULL, -- Nombre visible
  `descripcion` TEXT NOT NULL, -- Descripcion del producto
  `id_categoria` INT NULL, -- Categoria a la que pertenece
  `precio_base` DECIMAL(10,2) NOT NULL, -- Precio sin IVA
  `iva` TINYINT NOT NULL, -- Porcentaje de IVA
  `disponible` TINYINT(1) NOT NULL DEFAULT 1, -- Indica si se puede pedir
  `ofertado` TINYINT(1) NOT NULL DEFAULT 1, -- Indica si aparece en la carta
  `imagen` VARCHAR(255) NULL, -- Ruta o URL de la imagen
  PRIMARY KEY (`id`),
  -- Si se borra la categoria, el producto se queda sin categoria
  CONSTRAINT `fk_categoria_productos` 
    FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabla Pedidos
-- Guarda la cabecera de cada pedido
CREATE TABLE `pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, -- Identificador interno del pedido
  `numeroPedido` INT(11) NOT NULL, -- Numero mostrado al usuario
  `estado` ENUM('Nuevo','Recibido','En preparación','Cocinando','Listo cocina','Terminado','Entregado','Cancelado') NOT NULL DEFAULT 'Nuevo', -- Estado del pedido
  `tipo` ENUM('Local','Llevar') NOT NULL DEFAULT 'Local', -- Tipo de pedido
  `fecha` DATETIME NOT NULL, -- Fecha de creacion
  `cliente` VARCHAR(20) NOT NULL, -- Usuario cliente
  `cocinero` VARCHAR(20) DEFAULT NULL, -- Usuario cocinero asignado
  `imagenCocinero` VARCHAR(255) NOT NULL DEFAULT 'img/uploads/usuarios/default.jpg', -- Imagen mostrada del cocinero
  `bistroCoinsGastados` INT NOT NULL DEFAULT 0, -- BistroCoins usados en recompensas
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- Total final del pedido
  PRIMARY KEY (`id`),
  KEY `fk_cliente_pedidos_idx` (`cliente`), -- Indice para cliente
  KEY `fk_cocinero_pedidos_idx` (`cocinero`), -- Indice para cocinero
  -- Si se borra el cliente, se borran sus pedidos
  CONSTRAINT `fk_cliente_pedidos` 
    FOREIGN KEY (`cliente`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE,
  -- Si se borra el cocinero, el pedido queda sin cocinero
  CONSTRAINT `fk_cocinero_pedidos` 
    FOREIGN KEY (`cocinero`) REFERENCES `usuarios` (`user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Tabla Lineas de Pedido
-- Guarda los productos y recompensas dentro de cada pedido
CREATE TABLE `linea_pedido` (
  `numeroPedido` INT(11) NOT NULL, -- Id interno del pedido
  `idProducto` INT(11) NOT NULL, -- Producto de la linea
  `esRecompensa` TINYINT(1) NOT NULL DEFAULT 0, -- 1 si la linea viene de recompensa
  `cantidad` SMALLINT(6) NOT NULL DEFAULT 1, -- Unidades del producto
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- Subtotal en euros
  `bistroCoinsGastados` INT NOT NULL DEFAULT 0, -- Coins gastados en esta linea
  `estado` TINYINT(1) NOT NULL DEFAULT 0, -- 0 pendiente, 1 listo
  PRIMARY KEY (`numeroPedido`,`idProducto`,`esRecompensa`), -- Evita duplicados de producto/recompensa en pedido
  KEY `fk_idProducto_lineaPedido_idx` (`idProducto`), -- Indice para producto
  -- Si se borra el pedido, se borran sus lineas
  CONSTRAINT `fk_idPedido_lineaPedido` 
    FOREIGN KEY (`numeroPedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  -- Si se borra el producto, se borran sus lineas
  CONSTRAINT `fk_idProducto_lineaPedido` 
    FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Tabla Ofertas
-- Guarda ofertas y su porcentaje de descuento
CREATE TABLE `ofertas` (
  `id` INT NOT NULL AUTO_INCREMENT, -- Identificador de la oferta
  `nombre` VARCHAR(120) NOT NULL, -- Nombre visible
  `descripcion` TEXT NOT NULL, -- Descripcion de la oferta
  `comienzo` DATETIME NOT NULL, -- Fecha de inicio
  `fin` DATETIME NOT NULL, -- Fecha de fin
  `descuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- Porcentaje de descuento
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tabla Lineas de Oferta
-- Relaciona ofertas con productos y cantidades necesarias
CREATE TABLE `lineas_oferta` (
  `id_oferta` INT NOT NULL, -- Oferta a la que pertenece
  `producto` INT NOT NULL, -- Producto requerido
  `cantidad` SMALLINT(6) NOT NULL DEFAULT 1, -- Cantidad requerida
  PRIMARY KEY (`id_oferta`, `producto`), -- Evita repetir producto dentro de la misma oferta
  KEY `fk_producto_lineasOferta_idx` (`producto`), -- Indice para producto
  -- Si se borra la oferta, se borran sus lineas
  CONSTRAINT `fk_idOferta_lineasOferta` 
    FOREIGN KEY (`id_oferta`) REFERENCES `ofertas`(`id`) ON DELETE CASCADE,
  -- Si se borra el producto, se borran sus lineas de oferta
  CONSTRAINT `fk_producto_lineasOferta` 
    FOREIGN KEY (`producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Tabla de Recompensas
-- Relaciona productos con coste en BistroCoins
CREATE TABLE `recompensas`(
  `id` INT NOT NULL AUTO_INCREMENT, -- Identificador de la recompensa
  `id_producto` INT NOT NULL, -- Producto que se puede canjear
  `bistroCoins` INT NOT NULL, -- Coste en BistroCoins
  PRIMARY KEY (`id`),
  -- Si se borra el producto, se borra la recompensa
  CONSTRAINT `fk_producto_idProducto` 
    FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


