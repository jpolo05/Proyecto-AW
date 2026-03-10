/*
  Recuerda deshabilitar "Enable foreign key checks" para evitar problemas al importar.
*/
TRUNCATE TABLE `linea_pedido`;
TRUNCATE TABLE `pedidos`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `usuarios`;
TRUNCATE TABLE `categorias`;

-- Datos de prueba
INSERT INTO `categorias` (`nombre`, `descripcion`, `imagen`) VALUES
('Entrantes', 'Platos para empezar', 'img/uploads/categorias/entrantes.jpg'),
('Principales', 'Platos principales', 'img/uploads/categorias/principales.jpg'),
('Postres', 'Dulces y cafes', 'img/uploads/categorias/postres.jpg'),
('Bebidas', 'Refrescos, cervezas y agua', 'img/uploads/categorias/bebidas.jpg'),
('Ensaladas', 'Opciones frescas y ligeras', 'img/uploads/categorias/ensaladas.jpg');

INSERT INTO `productos` (`nombre`, `descripcion`, `id_categoria`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
('Croquetas caseras', 'Croquetas de jamón', 1, 6.50, 10, 1, 1, 'img/uploads/productos/croquetas.jpg'),
('Patatas bravas', 'Patatas con salsa brava casera', 1, 5.80, 10, 1, 1, 'img/uploads/productos/patatas_bravas.jpg'),
('Nachos con queso', 'Nachos gratinados con queso y jalapeños', 1, 7.20, 10, 1, 1, 'img/uploads/productos/nachos.jpg'),
('Hamburguesa completa', 'Hamburguesa con queso y patatas', 2, 12.00, 10, 1, 1, 'img/uploads/productos/hamburguesa.jpg'),
('Pizza barbacoa', 'Pizza mediana con salsa barbacoa y bacon', 2, 11.50, 10, 1, 1, 'img/uploads/productos/pizza_barbacoa.jpg'),
('Pasta carbonara', 'Pasta fresca con salsa carbonara', 2, 10.20, 10, 1, 1, 'img/uploads/productos/pasta_carbonara.jpg'),
('Tarta de queso', 'Tarta casera al horno', 3, 4.50, 10, 1, 1, 'img/uploads/productos/tarta_queso.jpg'),
('Brownie', 'Brownie templado con helado de vainilla', 3, 4.80, 10, 1, 1, 'img/uploads/productos/brownie.jpg'),
('Coca cola', 'Lata 33cl', 4, 2.10, 10, 1, 1, 'img/uploads/productos/cocacola.jpg'),
('Agua mineral', 'Botella 50cl', 4, 1.80, 10, 1, 1, 'img/uploads/productos/agua.jpg'),
('Ensalada cesar', 'Lechuga, pollo, parmesano y salsa cesar', 5, 8.90, 10, 1, 1, 'img/uploads/productos/ensalada_cesar.jpg');
