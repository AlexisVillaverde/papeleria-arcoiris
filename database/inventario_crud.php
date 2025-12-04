<?php
require_once 'conexion.php';

class InventarioCRUD {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    // CREATE - Crear producto
    public function crearProducto($nombre, $sku, $categoria, $stock, $precio, $descripcion = '') {
        try {
            $sql = "INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock, stock_minimo, id_categoria, id_marca, activo) 
                    VALUES (:codigo_barras, :nombre, :descripcion, :precio, :stock, :stock_minimo, :id_categoria, :id_marca, 1)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codigo_barras', $sku);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':stock', $stock);
            $stock_minimo = 5;
            $stmt->bindParam(':stock_minimo', $stock_minimo);
            $stmt->bindParam(':id_categoria', $categoria);
            $id_marca = 1;
            $stmt->bindParam(':id_marca', $id_marca);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    // READ - Obtener todos los productos
    public function obtenerProductos() {
        try {
            $sql = "SELECT p.*, c.nombre as categoria, m.nombre_marca as marca 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                    LEFT JOIN marcas m ON p.id_marca = m.id_marca
                    WHERE p.activo = 1
                    ORDER BY p.fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // READ - Obtener producto por ID
    public function obtenerProductoPorId($id) {
        try {
            $sql = "SELECT * FROM productos WHERE id_producto = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // UPDATE - Actualizar producto
    public function actualizarProducto($id, $nombre, $sku, $categoria, $stock, $precio, $descripcion = '') {
        try {
            $sql = "UPDATE productos SET 
                    nombre = :nombre, 
                    codigo_barras = :codigo_barras, 
                    id_categoria = :id_categoria, 
                    stock = :stock, 
                    precio = :precio, 
                    descripcion = :descripcion,
                    fecha_actualizacion = NOW()
                    WHERE id_producto = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':codigo_barras', $sku);
            $stmt->bindParam(':id_categoria', $categoria);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':descripcion', $descripcion);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // DELETE - Eliminar producto
    public function eliminarProducto($id) {
        try {
            $sql = "DELETE FROM productos WHERE id_producto = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Buscar productos
    public function buscarProductos($termino) {
        try {
            $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                    WHERE p.nombre LIKE :termino 
                    OR p.codigo_barras LIKE :termino 
                    OR c.nombre LIKE :termino 
                    ORDER BY p.nombre";
            
            $stmt = $this->conn->prepare($sql);
            $termino = "%$termino%";
            $stmt->bindParam(':termino', $termino);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Obtener productos con stock bajo
    public function obtenerProductosStockBajo($limite = 5) {
        try {
            $sql = "SELECT * FROM productos WHERE stock <= :limite ORDER BY stock ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limite', $limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Buscar producto por código de barras
    public function buscarProductoPorCodigo($codigo) {
        try {
            $sql = "SELECT p.*, c.nombre as categoria, m.nombre_marca as marca 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                    LEFT JOIN marcas m ON p.id_marca = m.id_marca
                    WHERE p.codigo_barras = :codigo";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Obtener categorías
    public function obtenerCategorias() {
        try {
            $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Obtener marcas
    public function obtenerMarcas() {
        try {
            $sql = "SELECT * FROM marcas WHERE activo = 1 ORDER BY nombre_marca";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Obtener estadísticas del inventario
    public function obtenerEstadisticasInventario() {
        try {
            $stats = [];
            
            // Total productos
            $sql = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Valor total
            $sql = "SELECT SUM(precio * stock) as valor_total FROM productos WHERE activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['valor_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;
            
            // Stock bajo
            $sql = "SELECT COUNT(*) as productos_stock_bajo FROM productos WHERE stock <= stock_minimo AND activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['productos_stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['productos_stock_bajo'];
            
            // Sin stock
            $sql = "SELECT COUNT(*) as productos_sin_stock FROM productos WHERE stock = 0 AND activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['productos_sin_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['productos_sin_stock'];
            
            return $stats;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Obtener estadísticas del inventario (método original)
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // Total productos
            $sql = "SELECT COUNT(*) as total FROM productos";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Valor total
            $sql = "SELECT SUM(precio * stock) as valor_total FROM productos";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['valor_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;
            
            // Total items
            $sql = "SELECT SUM(stock) as total_items FROM productos";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['total_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;
            
            // Stock bajo
            $sql = "SELECT COUNT(*) as stock_bajo FROM productos WHERE stock <= 5";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['stock_bajo'];
            
            // Agotados
            $sql = "SELECT COUNT(*) as agotados FROM productos WHERE stock = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['agotados'] = $stmt->fetch(PDO::FETCH_ASSOC)['agotados'];
            
            return $stats;
        } catch(PDOException $e) {
            return [];
        }
    }
}

// API endpoints
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventario = new InventarioCRUD();
    $action = $_POST['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch($action) {
        case 'crear':
            $resultado = $inventario->crearProducto(
                $_POST['nombre'],
                $_POST['sku'],
                $_POST['categoria'],
                $_POST['stock'],
                $_POST['precio'],
                $_POST['descripcion'] ?? ''
            );
            echo json_encode(['success' => $resultado]);
            break;
            
        case 'actualizar':
            $resultado = $inventario->actualizarProducto(
                $_POST['id'],
                $_POST['nombre'],
                $_POST['sku'],
                $_POST['categoria'],
                $_POST['stock'],
                $_POST['precio'],
                $_POST['descripcion'] ?? ''
            );
            echo json_encode(['success' => $resultado]);
            break;
            
        case 'eliminar':
            $resultado = $inventario->eliminarProducto($_POST['id']);
            echo json_encode(['success' => $resultado]);
            break;
            
        case 'buscar':
            $productos = $inventario->buscarProductos($_POST['termino']);
            echo json_encode($productos);
            break;
            
        case 'obtener_todos':
            $productos = $inventario->obtenerProductos();
            echo json_encode($productos);
            break;
            
        case 'obtener_estadisticas':
            $stats = $inventario->obtenerEstadisticas();
            echo json_encode($stats);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $inventario = new InventarioCRUD();
    
    if (isset($_GET['id'])) {
        $producto = $inventario->obtenerProductoPorId($_GET['id']);
        header('Content-Type: application/json');
        echo json_encode($producto);
    }
}
?>