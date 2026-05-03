-- Crea el usuario para conexiones desde cualquier host
CREATE USER IF NOT EXISTS 'awp4'@'%' IDENTIFIED BY 'awp4';
-- Asegura que la contraseña sea la esperada
ALTER USER 'awp4'@'%' IDENTIFIED BY 'awp4';
-- Da permisos sobre la base de datos del proyecto
GRANT ALL PRIVILEGES ON `awp4`.* TO 'awp4'@'%';

-- Crea el usuario para conexiones locales
CREATE USER IF NOT EXISTS 'awp4'@'localhost' IDENTIFIED BY 'awp4';
-- Asegura que la contraseña local sea la esperada
ALTER USER 'awp4'@'localhost' IDENTIFIED BY 'awp4';
-- Da permisos locales sobre la base de datos del proyecto
GRANT ALL PRIVILEGES ON `awp4`.* TO 'awp4'@'localhost';
