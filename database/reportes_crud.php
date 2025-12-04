<?php
require_once 'conexion.php';

class ReportesCRUD {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    // CREAR - Generar reporte de ventas
    public function generarReporteVentas($fecha_inicio, $fecha_fin, $empleado_id = null) {
        try {
            $sql = "SELECT v.folio, v.fecha_venta, CONCAT(e.nombre, ' ', e.apellidos) as empleado,
                           v.subtotal, v.iva, v.total, v.metodo_pago
                    FROM ventas v 
                    INNER JOIN empleados e ON v.id_empleado = e.id_empleado
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin";
            
            if ($empleado_id) {
                $sql .= " AND v.id_empleado = :empleado_id";
            }
            
            $sql .= " ORDER BY v.fecha_venta DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            
            if ($empleado_id) {
                $stmt->bindParam(':empleado_id', $empleado_id);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // CREAR - Generar reporte de productos más vendidos
    public function reporteProductosMasVendidos($fecha_inicio, $fecha_fin, $limite = 10) {
        try {
            $sql = "SELECT p.nombre, p.codigo_barras, SUM(dv.cantidad) as total_vendido,
                           SUM(dv.subtotal) as ingresos_generados
                    FROM detalle_ventas dv
                    INNER JOIN productos p ON dv.id_producto = p.id_producto
                    INNER JOIN ventas v ON dv.folio = v.folio
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY p.id_producto
                    ORDER BY total_vendido DESC
                    LIMIT :limite";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // CREAR - Generar reporte de inventario bajo
    public function reporteInventarioBajo() {
        try {
            $sql = "SELECT p.codigo_barras, p.nombre, p.stock, p.stock_minimo,
                           c.nombre as categoria, m.nombre_marca as marca
                    FROM productos p
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                    LEFT JOIN marcas m ON p.id_marca = m.id_marca
                    WHERE p.stock <= p.stock_minimo AND p.activo = 1
                    ORDER BY p.stock ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // CREAR - Generar reporte de ventas por empleado
    public function reporteVentasPorEmpleado($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT CONCAT(e.nombre, ' ', e.apellidos) as empleado,
                           COUNT(v.folio) as total_ventas,
                           SUM(v.total) as ingresos_totales,
                           AVG(v.total) as promedio_venta
                    FROM empleados e
                    LEFT JOIN ventas v ON e.id_empleado = v.id_empleado 
                           AND DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    WHERE e.activo = 1
                    GROUP BY e.id_empleado
                    ORDER BY ingresos_totales DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // LEER - Obtener resumen de ventas del día
    public function resumenVentasHoy() {
        try {
            $sql = "SELECT COUNT(*) as total_ventas,
                           COALESCE(SUM(total), 0) as ingresos_totales,
                           COALESCE(AVG(total), 0) as promedio_venta
                    FROM ventas 
                    WHERE DATE(fecha_venta) = CURDATE()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // LEER - Obtener productos con stock crítico
    public function obtenerStockCritico() {
        try {
            $sql = "SELECT COUNT(*) as productos_criticos
                    FROM productos 
                    WHERE stock <= stock_minimo AND activo = 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // LEER - Obtener detalle de una venta específica
    public function obtenerDetalleVenta($folio) {
        try {
            $sql = "SELECT v.folio, v.fecha_venta, CONCAT(e.nombre, ' ', e.apellidos) as empleado,
                           v.subtotal, v.iva, v.total, v.metodo_pago, v.monto_recibido, v.cambio,
                           p.nombre as producto, dv.cantidad, dv.precio_unitario, dv.subtotal as subtotal_producto
                    FROM ventas v
                    INNER JOIN empleados e ON v.id_empleado = e.id_empleado
                    INNER JOIN detalle_ventas dv ON v.folio = dv.folio
                    INNER JOIN productos p ON dv.id_producto = p.id_producto
                    WHERE v.folio = :folio";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':folio', $folio);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // ACTUALIZAR - Marcar reporte como revisado (si tuviéramos tabla de reportes guardados)
    public function marcarReporteRevisado($reporte_id) {
        // Esta función sería útil si guardáramos reportes en una tabla
        // Por ahora retornamos true como placeholder
        return true;
    }
    
    // ELIMINAR - Eliminar venta (solo para administradores)
    public function eliminarVenta($folio) {
        try {
            $this->conn->beginTransaction();
            
            // Primero eliminar detalles
            $sql = "DELETE FROM detalle_ventas WHERE folio = :folio";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':folio', $folio);
            $stmt->execute();
            
            // Luego eliminar la venta
            $sql = "DELETE FROM ventas WHERE folio = :folio";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':folio', $folio);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // UTILIDAD - Obtener lista de empleados para filtros
    public function obtenerEmpleados() {
        try {
            $sql = "SELECT id_empleado, CONCAT(nombre, ' ', apellidos) as nombre_completo
                    FROM empleados 
                    WHERE activo = 1
                    ORDER BY nombre, apellidos";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // UTILIDAD - Exportar reporte a CSV
    public function exportarCSV($datos, $nombre_archivo) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($datos)) {
            // Escribir encabezados
            fputcsv($output, array_keys($datos[0]));
            
            // Escribir datos
            foreach ($datos as $fila) {
                fputcsv($output, $fila);
            }
        }
        
        fclose($output);
        exit();
    }
}

// Manejo de peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportes = new ReportesCRUD();
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'ventas':
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $empleado_id = $_POST['empleado_id'] ?? null;
            
            $resultado = $reportes->generarReporteVentas($fecha_inicio, $fecha_fin, $empleado_id);
            echo json_encode($resultado);
            break;
            
        case 'productos_vendidos':
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $limite = $_POST['limite'] ?? 10;
            
            $resultado = $reportes->reporteProductosMasVendidos($fecha_inicio, $fecha_fin, $limite);
            echo json_encode($resultado);
            break;
            
        case 'inventario_bajo':
            $resultado = $reportes->reporteInventarioBajo();
            echo json_encode($resultado);
            break;
            
        case 'ventas_empleado':
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            
            $resultado = $reportes->reporteVentasPorEmpleado($fecha_inicio, $fecha_fin);
            echo json_encode($resultado);
            break;
            
        case 'resumen_hoy':
            $resultado = $reportes->resumenVentasHoy();
            echo json_encode($resultado);
            break;
            
        case 'detalle_venta':
            $folio = $_POST['folio'];
            $resultado = $reportes->obtenerDetalleVenta($folio);
            echo json_encode($resultado);
            break;
            
        case 'eliminar_venta':
            $folio = $_POST['folio'];
            $resultado = $reportes->eliminarVenta($folio);
            echo json_encode(['success' => $resultado]);
            break;
            
        case 'obtener_empleados':
            $resultado = $reportes->obtenerEmpleados();
            echo json_encode($resultado);
            break;
            
        case 'exportar_csv':
            $tipo_reporte = $_POST['tipo_reporte'];
            $datos = json_decode($_POST['datos'], true);
            $reportes->exportarCSV($datos, $tipo_reporte . '_' . date('Y-m-d'));
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reportes = new ReportesCRUD();
    $accion = $_GET['accion'] ?? '';
    
    switch ($accion) {
        case 'stock_critico':
            $resultado = $reportes->obtenerStockCritico();
            echo json_encode($resultado);
            break;
            
        case 'empleados':
            $resultado = $reportes->obtenerEmpleados();
            echo json_encode($resultado);
            break;
    }
}
?>