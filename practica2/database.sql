CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  descripcion TEXT NOT NULL,
  imagen VARCHAR(255) NULL
);

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT NOT NULL,
  id_categoria INT NOT NULL,
  precio_base DECIMAL(10,2) NOT NULL,
  iva TINYINT NOT NULL,
  disponible TINYINT(1) NOT NULL DEFAULT 1,
  ofertado TINYINT(1) NOT NULL DEFAULT 1,
  imagen VARCHAR(255) NULL,
  CONSTRAINT fk_categoria_productos FOREIGN KEY (id_categoria) REFERENCES categorias(id)
);

CREATE TABLE IF NOT EXISTS usuarios (
  user VARCHAR(20) PRIMARY KEY,
  email VARCHAR(120) NOT NULL UNIQUE,
  nombre VARCHAR(50) NOT NULL,
  apellidos VARCHAR(50) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol ENUM('Gerente', 'Cliente', 'Cocinero', 'Camarero') NOT NULL DEFAULT 'Cliente',
  imagen VARCHAR(255) NULL
);

CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numeroPedido INT NOT NULL,
  estado ENUM('Nuevo', 'Recibido', 'En preparación', 'Cocinando', 'Listo cocina', 'Terminado', 'Entregado', 'Cancelado') NOT NULL DEFAULT 'Nuevo',
  tipo ENUM('Local', 'Llevar') NOT NULL DEFAULT 'Local',
  fecha DATETIME NOT NULL,
  idCliente VARCHAR(20) NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_idUsuario_pedidos FOREIGN KEY (idCliente) REFERENCES usuarios(user)
);

CREATE TABLE IF NOT EXISTS linea_pedido (
  numeroPedido INT NOT NULL,
  idProducto INT NOT NULL,
  cantidad SMALLINT NOT NULL DEFAULT 1,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (numeroPedido, idProducto),
  CONSTRAINT fk_idPedido_lineaPedido FOREIGN KEY (numeroPedido) REFERENCES pedidos(id),
  CONSTRAINT fk_idProducto_lineaPedido FOREIGN KEY (idProducto) REFERENCES productos(id)
);

-- Datos de prueba
INSERT INTO categorias (nombre, descripcion) VALUES
('Entrantes', 'Platos para empezar'),
('Principales', 'Platos principales'),
('Postres', 'Dulces y cafés');

INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado, imagen) VALUES
('Croquetas caseras', 'Croquetas de jamón', 1, 6.50, 10, 1, 1, 'img/uploads/productos/croquetas.jpg'),
('Hamburguesa completa', 'Hamburguesa con queso y patatas', 2, 12.00, 10, 1, 1, 'img/uploads/productos/hamburguesa.jpg'),
('Tarta de queso', 'Tarta casera al horno', 3, 4.50, 10, 1, 1, 'img/uploads/productos/tarta_queso.jpg');