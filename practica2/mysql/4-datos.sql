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

INSERT INTO `usuarios` (`user`, `email`, `nombre`, `apellidos`, `contrasena`, `rol`, `imagen`) VALUES
('camarero', 'camarero@gmail.com', 'camarero', 'camarero', '$2y$10$pw0Ms551XxFrg4SmMOBz/O/8504T24xICATLSCb7Yn4LZoLoxRtwO', 'Camarero', 'img/uploads/usuarios/default.jpg'),
('cliente', 'cliente@gmail.com', 'cliente', 'cliente', '$2y$10$.YAs9G8w5.R0il57XaZrcuC582ZyN0sBTiJTS9bt/gXXqZtBSxsaK', 'Cliente', 'img/uploads/usuarios/default.jpg'),
('cocinero', 'cocinero@gmail.com', 'cocinero', 'cocinero', '$2y$10$kDvu6.r0F3ZuIkBJB3/6x.hoJ07OV5HOG05ws3tniErZK/bW.21A6', 'Cocinero', 'img/uploads/usuarios/default.jpg'),
('gerente', 'gerente@gmail.com', 'gerente', 'gerente', '$2y$10$.ZoCF3y/IuO5gUkdYAR.JuiVaLSs0k6oMmeiUMAToRe.H0ZJQZ5ae', 'Gerente', 'img/uploads/usuarios/default.jpg');

