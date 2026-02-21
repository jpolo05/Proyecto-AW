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
  FOREIGN KEY (id_categoria) REFERENCES categorias(id)
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