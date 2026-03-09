CREATE USER 'awp2'@'%' IDENTIFIED BY 'awp2';
GRANT ALL PRIVILEGES ON `awp2`.* TO 'awp2'@'%';

CREATE USER 'awp2'@'localhost' IDENTIFIED BY 'awp2';
GRANT ALL PRIVILEGES ON `awp2`.* TO 'awp2'@'localhost';