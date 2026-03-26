/*
  Recuerda deshabilitar "Enable foreign key checks" para evitar problemas al importar.
*/

TRUNCATE TABLE `linea_pedido`;
TRUNCATE TABLE `pedidos`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `usuarios`;
TRUNCATE TABLE `categorias`;
TRUNCATE TABLE `lineas_oferta`;
TRUNCATE TABLE `ofertas`;

-- Datos de prueba
INSERT INTO `categorias` (`nombre`, `descripcion`, `imagen`) VALUES
('Entrantes', 'Platos para empezar', 'img/uploads/categorias/entrantes.jpg'),
('Ensaladas', 'Opciones frescas y ligeras', 'img/uploads/categorias/ensaladas.jpg'),
('Principales', 'Platos principales', 'img/uploads/categorias/principales.jpg'),
('Postres', 'Dulces y cafes', 'img/uploads/categorias/postres.jpg'),
('Bebidas', 'Refrescos, cervezas y agua', 'img/uploads/categorias/bebidas.jpg');

INSERT INTO `productos` (`nombre`, `descripcion`, `id_categoria`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
('Croquetas caseras', 'Croquetas de jamón', 1, 6.50, 10, 1, 1, 'img/uploads/productos/croquetas.jpg'),
('Patatas bravas', 'Patatas con salsa brava casera', 1, 5.80, 10, 1, 1, 'img/uploads/productos/patatas_bravas.jpg'),
('Nachos con queso', 'Nachos gratinados con queso y jalapeños', 1, 7.20, 10, 1, 1, 'img/uploads/productos/nachos.jpg'),
('Hamburguesa completa', 'Hamburguesa con queso y patatas', 3, 12.00, 10, 1, 1, 'img/uploads/productos/hamburguesa.jpg'),
('Pizza barbacoa', 'Pizza mediana con salsa barbacoa y bacon', 3, 11.50, 10, 1, 1, 'img/uploads/productos/pizza_barbacoa.jpg'),
('Pasta carbonara', 'Pasta fresca con salsa carbonara', 3, 10.20, 10, 1, 1, 'img/uploads/productos/pasta_carbonara.jpg'),
('Tarta de queso', 'Tarta casera al horno', 4, 4.50, 10, 1, 1, 'img/uploads/productos/tarta_queso.jpg'),
('Brownie', 'Brownie templado con helado de vainilla', 4, 4.80, 10, 1, 1, 'img/uploads/productos/brownie.jpg'),
('Coca cola', 'Lata 33cl', 5, 2.10, 10, 1, 1, 'img/uploads/productos/cocacola.jpg'),
('Agua mineral', 'Botella 50cl', 5, 1.80, 10, 1, 1, 'img/uploads/productos/agua.jpg'),
('Ensalada cesar', 'Lechuga, pollo, parmesano y salsa cesar', 2, 8.90, 10, 1, 1, 'img/uploads/productos/ensalada_cesar.jpg');

INSERT INTO `usuarios` (`user`, `email`, `nombre`, `apellidos`, `contrasena`, `rol`, `imagen`) VALUES
('camarero', 'camarero@gmail.com', 'Luisa', 'Perez', '$2y$10$LnEzbAdQLlY/RryEvXmzLumCAFnNZX4ZkajoWDnovEIGP8fc5RRAS', 'Camarero', 'img/uploads/usuarios/img_69b14259473ea8.36904842.png'),
('cliente', 'cliente@gmail.com', 'Pablo', 'Galindo', '$2y$10$Q1FYw7v/1VXDDpPK0GoVuOLZSpCY4UFOfIVdmBYy/e3hAWVBli7DC', 'Cliente', 'img/uploads/usuarios/default.jpg'),
('cocinero', 'cocinero@gmail.com', 'Jorge', 'Garcia', '$2y$10$D07t97MRNseN16VXPwfUCu8ARLahI9csWUiK/UBbs7XXoL20rxBRy', 'Cocinero', 'img/uploads/usuarios/img_69b14288349b83.44261640.png'),
('gerente', 'gerente@gmail.com', 'Juan', 'Lopez', '$2y$10$zxIuzR7iyrlWarfISYkgu.FAECOvX5z5ToF126Kn3l7r9CjLNenWC', 'Gerente', 'img/uploads/usuarios/img_69b1422d2b95c4.04711911.png');