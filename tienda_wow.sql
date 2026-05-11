-- Borramos la base de datos si ya existía para evitar errores de duplicidad
DROP DATABASE IF EXISTS tienda_wow;

-- Creamos la base de datos limpia
CREATE DATABASE tienda_wow;
USE tienda_wow;

-- ==========================================
-- TABLA 1: USUARIOS
-- ==========================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'user') DEFAULT 'user',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TABLA 2: PRODUCTOS
-- ==========================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_es VARCHAR(100) NOT NULL,
    nombre_en VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descuento INT DEFAULT 0,
    imagen VARCHAR(255) NOT NULL
);

-- ==========================================
-- TABLA 3: VOTOS 
-- ==========================================
CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    id_producto INT NOT NULL,
    valoracion DECIMAL(3, 1) NOT NULL, 
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario) REFERENCES usuarios(username) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- ==========================================
-- TABLA 4: PEDIDOS (Corregida y unificada)
-- ==========================================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario) REFERENCES usuarios(username) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ==========================================
-- TABLA 5: DETALLES DE CADA PEDIDO
-- ==========================================
CREATE TABLE pedidos_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    nombre_producto VARCHAR(255),
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE
);

-- ==========================================
-- TABLA 6: TICKETS DE SOPORTE
-- ==========================================
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('Abierto', 'Resuelto') DEFAULT 'Abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario) REFERENCES usuarios(username) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ==========================================
-- INSERCIÓN DE DATOS: USUARIOS
-- ==========================================
INSERT INTO usuarios (username, password, rol) VALUES 
('admin', 'admin123', 'admin'),
('user', 'user123', 'user');

-- ==========================================
-- INSERCIÓN DE DATOS: PRODUCTOS
-- ==========================================
INSERT INTO productos (id, nombre_es, nombre_en, precio, descuento, imagen) VALUES 
(1, 'World of Warcraft', 'World of Warcraft', 14.99, 0, 'img/World of Warcraft.png'),
(2, 'World of Warcraft The Burning Crusade', 'World of Warcraft The Burning Crusade', 19.99, 10, 'img/World of Warcraft The Burning Crusade.png'),
(3, 'World of Warcraft Wrath of the Lich King', 'World of Warcraft Wrath of the Lich King', 29.99, 15, 'img/World of Warcraft Wrath of the Lich King.jpg'),
(4, 'World of Warcraft Cataclysm', 'World of Warcraft Cataclysm', 39.99, 0, 'img/World of Warcraft Cataclysm.jpg'),
(5, 'World of Warcraft Mists of Pandaria', 'World of Warcraft Mists of Pandaria', 49.99, 0, 'img/World of Warcraft Mists of Pandaria.jpg'),
(6, 'World of Warcraft Warlords of Draenor', 'World of Warcraft Warlords of Draenor', 59.99, 0, 'img/World of Warcraft Warlords of Draenor.jpg'),
(7, 'World of Warcraft Legion', 'World of Warcraft Legion', 69.99, 10, 'img/World of Warcraft Legion.jpg'),
(8, 'World of Warcraft Battle for Azeroth', 'World of Warcraft Battle for Azeroth', 64.99, 0, 'img/World of Warcraft Battle for Azeroth.jpg'),
(9, 'World of Warcraft Shadowlands', 'World of Warcraft Shadowlands', 69.99, 15, 'img/World of Warcraft Shadowlands.jpg'),
(10, 'World of Warcraft DragonFlight', 'World of Warcraft DragonFlight', 74.99, 0, 'img/World of Warcraft DragonFlight.png'),
(11, 'World of Warcraft The War Within', 'World of Warcraft The War Within', 79.99, 0, 'img/World of Warcraft The War Within.jpeg'),
(12, 'World of Warcraft Midnight', 'World of Warcraft Midnight', 84.99, 0, 'img/World of Warcraft Midnight.png'),
(13, 'World of Warcraft The Last Titan', 'World of Warcraft The Last Titan', 99.99, 0, 'img/World of Warcraft The Last Titan.png'),
(100, '1 Mes Suscripción', '1 Month Subscription', 12.99, 0, 'img/Suscripcion 1 mes.png'),
(101, '6 Meses Suscripción', '6 Months Subscription', 64.99, 0, 'img/Suscripcion 6 mes.png'),
(102, '12 Meses Suscripción', '12 Months Subscription', 129.99, 25, 'img/Suscripcion 12 mes.png');