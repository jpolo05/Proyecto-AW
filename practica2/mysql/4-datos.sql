/*
  Recuerda deshabilitar "Enable foreign key checks" para evitar problemas al importar.
*/
TRUNCATE TABLE `linea_pedido`;
TRUNCATE TABLE `pedidos`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `usuarios`;
TRUNCATE TABLE `categorias`;

-- Datos de prueba
INSERT INTO `categorias` (`nombre`, `descripcion`) VALUES
('Entrantes', 'Platos para empezar'),
('Principales', 'Platos principales'),
('Postres', 'Dulces y cafés');

INSERT INTO `productos` (`nombre`, `descripcion`, `id_categoria`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
('Croquetas caseras', 'Croquetas de jamón', 1, 6.50, 10, 1, 1, 'img/uploads/productos/croquetas.jpg'),
('Hamburguesa completa', 'Hamburguesa con queso y patatas', 2, 12.00, 10, 1, 1, 'img/uploads/productos/hamburguesa.jpg'),
('Tarta de queso', 'Tarta casera al horno', 3, 4.50, 10, 1, 1, 'img/uploads/productos/tarta_queso.jpg');

INSERT INTO `pedidos` (`id`, `numeroPedido`, `estado`, `tipo`, `fecha`, `cliente`, `cocinero`, `imagenCocinero`, `total`) VALUES
(1, 1, 'En preparación', 'Local', '2026-03-09 19:59:54', 'cliente', NULL, 'img/uploads/usuarios/default.jpg', 0.00),
(2, 2, 'En preparación', 'Local', '2026-03-09 20:00:05', 'cliente', NULL, 'img/uploads/usuarios/default.jpg', 0.00),
(3, 3, 'Cocinando', 'Local', '2026-03-09 20:00:16', 'cliente', 'cocinero', 'img/uploads/usuarios/default.jpg', 0.00);

INSERT INTO `linea_pedido` (`numeroPedido`, `idProducto`, `cantidad`, `subtotal`, `estado`) VALUES
(3, 1, 1, 0.00,0),
(3, 2, 1, 0.00,0),
(3, 3, 1, 0.00,0);