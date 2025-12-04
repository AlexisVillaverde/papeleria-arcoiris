-- Base de datos para Papelería Arcoíris

CREATE DATABASE papeleria_arco;
USE papeleria_arco;

-- Tabla de Categorías
CREATE TABLE categorias (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Marcas
CREATE TABLE marcas (
    id_marca INT PRIMARY KEY AUTO_INCREMENT,
    nombre_marca VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Productos
CREATE TABLE productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    codigo_barras VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    id_categoria INT,
    id_marca INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    FOREIGN KEY (id_marca) REFERENCES marcas(id_marca)
);

-- Tabla de Empleados
CREATE TABLE empleados (
    id_empleado INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(15),
    domicilio VARCHAR(255),
    rfc VARCHAR(13) UNIQUE,
    rol ENUM('administrador', 'cajero') NOT NULL,
    salario DECIMAL(10,2),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_contratacion DATE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Ventas
CREATE TABLE ventas (
    folio INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    monto_recibido DECIMAL(10,2),
    cambio DECIMAL(10,2),
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

-- Tabla de Detalle de Ventas
CREATE TABLE detalle_ventas (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    folio INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (folio) REFERENCES ventas(folio),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de Almacenes
CREATE TABLE almacenes (
    id_almacen INT PRIMARY KEY AUTO_INCREMENT,
    nombre_almacen VARCHAR(100) NOT NULL
);

CREATE TABLE inventario_almacen (
    id_inventario INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT NOT NULL,
    id_almacen INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_almacen) REFERENCES almacenes(id_almacen)
);

-- Insertar categorías iniciales
INSERT INTO categorias (nombre, descripcion) VALUES
('Escritura', 'Productos para escribir como bolígrafos, lápices, marcadores'),
('Cuadernos', 'Cuadernos, libretas y blocks de diferentes tamaños'),
('Oficina', 'Artículos de oficina como folders, clips, grapas'),
('Arte y Manualidades', 'Materiales para arte y manualidades'),
('Corte y Pegado', 'Tijeras, pegamentos, cintas adhesivas'),
('Calculadoras', 'Calculadoras básicas y científicas');

-- Insertar marcas iniciales
INSERT INTO marcas (nombre_marca) VALUES
('BIC'),
('Faber-Castell'),
('Scribe'),
('Casio'),
('Pelikan'),
('Genérica');

-- Insertar empleados iniciales
INSERT INTO empleados (nombre, apellidos, email, telefono, domicilio, rfc, rol, salario, usuario, password, fecha_contratacion) VALUES
('Ana', 'García Martínez', 'ana.garcia@papeleriaarcoiris.com', '555-0123','Calle de las Bugambilias #123, Col. Reforma, Oaxaca de Juárez, OAX, C.P. 68050', 'GAM123456789', 'administrador', 5000.00, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-01-14'),
('Carlos', 'Rodríguez López', 'carlos.rodriguez@papeleriaarcoiris.com', '555-0154','Av. Independencia #456, Col. Centro, Oaxaca de Juárez, OAX, C.P. 68000', 'CRL987654321', 'cajero', 3000.00, 'carlos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-02-28'),
('María Elena', 'Vázquez', 'maria.elena@papeleriaarcoiris.com', '555-2532','Callejón del Sol #32, Col. Jalatlaco, Oaxaca de Juárez, OAX, C.P. 68080','MEV123456789', 'cajero', 3000.00, 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-03-20');

-- Insertar productos de ejemplo
INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock, stock_minimo, id_categoria, id_marca) VALUES
('1234567890123', 'Bolígrafo BIC Azul', 'Bolígrafo de tinta azul marca BIC', 15.00, 32, 10, 1, 1),
('1234567890124', 'Lápiz HB No. 2', 'Lápiz de grafito HB número 2', 8.00, 45, 15, 1, 2),
('1234567890125', 'Cuaderno Profesional 100 hojas', 'Cuaderno profesional de 100 hojas rayado', 35.00, 25, 5, 2, 3),
('1234567890126', 'Folder Tamaño Carta', 'Folder de plástico tamaño carta', 12.00, 50, 10, 3, 6),
('1234567890127', 'Tijeras Escolares', 'Tijeras de punta roma para uso escolar', 25.00, 18, 5, 5, 6),
('1234567890128', 'Calculadora Básica', 'Calculadora básica de 8 dígitos', 85.00, 10, 3, 6, 4);

-- Venta realizada por Carlos (id_empleado = 2)
INSERT INTO ventas (id_empleado, subtotal, iva, total, metodo_pago, monto_recibido, cambio)
VALUES (2, 70.00, 11.20, 81.20, 'efectivo', 100.00, 18.80);

-- Venta realizada por María (id_empleado = 3)
INSERT INTO ventas (id_empleado, subtotal, iva, total, metodo_pago, monto_recibido, cambio)
VALUES (3, 35.00, 5.60, 40.60, 'tarjeta', 40.60, 0.00);

-- Detalle de la primera venta (folio = 1)
INSERT INTO detalle_ventas (folio, id_producto, cantidad, precio_unitario, subtotal)
VALUES 
(1, 1, 2, 15.00, 30.00),   -- 2 bolígrafos BIC
(1, 2, 5, 8.00, 40.00);    -- 5 lápices HB

-- Detalle de la segunda venta (folio = 2)
INSERT INTO detalle_ventas (folio, id_producto, cantidad, precio_unitario, subtotal)
VALUES 
(2, 3, 1, 35.00, 35.00);   -- 1 cuaderno profesional

--Insertar almacenes iniciales
INSERT INTO almacenes (nombre_almacen) VALUES
('Almacén Principal');