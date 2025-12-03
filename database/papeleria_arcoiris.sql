-- Base de datos para Papelería Arcoíris

CREATE DATABASE papeleria_arcoiris;
USE papeleria_arcoiris;

-- Tabla de Categorías
CREATE TABLE categorias (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
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
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

-- Tabla de Empleados
CREATE TABLE empleados (
    id_empleado INT PRIMARY KEY AUTO_INCREMENT,
    codigo_empleado VARCHAR(10) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(15),
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
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    numero_venta VARCHAR(20) UNIQUE NOT NULL,
    id_empleado INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

-- Tabla de Detalle de Ventas
CREATE TABLE detalle_ventas (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de Movimientos de Inventario
CREATE TABLE movimientos_inventario (
    id_movimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(100),
    id_empleado INT NOT NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

-- Insertar categorías iniciales
INSERT INTO categorias (nombre, descripcion) VALUES
('Escritura', 'Productos para escribir como bolígrafos, lápices, marcadores'),
('Cuadernos', 'Cuadernos, libretas y blocks de diferentes tamaños'),
('Oficina', 'Artículos de oficina como folders, clips, grapas'),
('Arte y Manualidades', 'Materiales para arte y manualidades'),
('Corte y Pegado', 'Tijeras, pegamentos, cintas adhesivas'),
('Calculadoras', 'Calculadoras básicas y científicas');

-- Insertar empleados iniciales
INSERT INTO empleados (codigo_empleado, nombre, apellidos, email, telefono, rol, salario, usuario, password, fecha_contratacion) VALUES
('EMP-001', 'Ana', 'García Martínez', 'ana.garcia@papeleriaarcoiris.com', '555-0123', 'administrador', 5000.00, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-01-14'),
('EMP-002', 'Carlos', 'Rodríguez López', 'carlos.rodriguez@papeleriaarcoiris.com', '555-0154', 'cajero', 3000.00, 'carlos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-02-28'),
('EMP-003', 'María Elena', 'Vázquez', 'maria.elena@papeleriaarcoiris.com', '555-2532', 'cajero', 3000.00, 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-03-20');

-- Insertar productos de ejemplo
INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock, stock_minimo, id_categoria) VALUES
('1234567890123', 'Bolígrafo BIC Azul', 'Bolígrafo de tinta azul marca BIC', 15.00, 32, 10, 1),
('1234567890124', 'Lápiz HB No. 2', 'Lápiz de grafito HB número 2', 8.00, 45, 15, 1),
('1234567890125', 'Cuaderno Profesional 100 hojas', 'Cuaderno profesional de 100 hojas rayado', 35.00, 25, 5, 2),
('1234567890126', 'Folder Tamaño Carta', 'Folder de plástico tamaño carta', 12.00, 50, 10, 3),
('1234567890127', 'Tijeras Escolares', 'Tijeras de punta roma para uso escolar', 25.00, 18, 5, 5),
('1234567890128', 'Calculadora Básica', 'Calculadora básica de 8 dígitos', 85.00, 10, 3, 6);

