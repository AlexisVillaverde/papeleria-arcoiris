<?php
require_once "conexion.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_reporte = $_POST['tipo_reporte'] ?? 'ventas_detalle';
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $empleado_id = $_POST['empleado_id'] ?? null;

    $where_conditions = [];
    $params = [];

    // Filtros de fecha
    if ($fecha_inicio) {
        $where_conditions[] = "DATE(v.fecha_venta) >= ?";
        $params[] = $fecha_inicio;
    }

    if ($fecha_fin) {
        $where_conditions[] = "DATE(v.fecha_venta) <= ?";
        $params[] = $fecha_fin;
    }

    // Filtro de empleado
    if ($empleado_id) {
        $where_conditions[] = "v.id_empleado = ?";
        $params[] = $empleado_id;
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    try {
        // Generar reporte de ventas detallado
        $sql = "SELECT 
                    v.id_venta,
                    v.codigo_venta,
                    v.fecha_venta,
                    v.total,
                    v.metodo_pago,
                    CONCAT(e.nombre, ' ', e.apellidos) as empleado,
                    COUNT(dv.id_detalle) as items_vendidos
                FROM ventas v 
                LEFT JOIN empleados e ON v.id_empleado = e.id_empleado 
                LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
                $where_clause 
                GROUP BY v.id_venta
                ORDER BY v.fecha_venta DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular estadísticas del reporte
        $total_ventas = count($resultados);
        $total_ingresos = array_sum(array_column($resultados, 'total'));
        $promedio_venta = $total_ventas > 0 ? $total_ingresos / $total_ventas : 0;

        // Guardar en sesión
        $_SESSION['reporte_resultados'] = $resultados;
        $_SESSION['reporte_tipo'] = $tipo_reporte;
        $_SESSION['reporte_estadisticas'] = [
            'total_ventas' => $total_ventas,
            'total_ingresos' => $total_ingresos,
            'promedio_venta' => $promedio_venta,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'empleado_id' => $empleado_id
        ];

        header("Location: reporte.php?reporte_generado=1");
        exit;
    } catch (PDOException $e) {
        header("Location: reporte.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Si no es POST, generar reporte básico
header("Location: reporte.php");
exit;
