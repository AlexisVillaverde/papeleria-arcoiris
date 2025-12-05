<?php
require_once "conexion.php";
session_start();

if (!isset($_GET['tipo'])) {
    header("Location: reporte.php?error=tipo_no_especificado");
    exit;
}

if (!isset($_SESSION['reporte_resultados'])) {
    header("Location: reporte.php?error=no_hay_reporte");
    exit;
}

$tipo = $_GET['tipo'];
$resultados = $_SESSION['reporte_resultados'];
$tipo_reporte = $_SESSION['reporte_tipo'] ?? 'ventas';
$estadisticas = $_SESSION['reporte_estadisticas'] ?? [];

if ($tipo === 'csv') {
    // Configurar headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if (!empty($resultados)) {
        // Escribir encabezados en español
        $headers = [
            'ID Venta', 'Código Venta', 'Fecha', 'Total', 
            'Método Pago', 'Empleado', 'Items Vendidos'
        ];
        fputcsv($output, $headers);
        
        // Escribir datos
        foreach ($resultados as $row) {
            $data = [
                $row['id_venta'],
                $row['codigo_venta'],
                date('d/m/Y H:i', strtotime($row['fecha_venta'])),
                '$' . number_format($row['total'], 2),
                ucfirst($row['metodo_pago']),
                $row['empleado'],
                $row['items_vendidos']
            ];
            fputcsv($output, $data);
        }
        
        // Agregar estadísticas al final
        if (!empty($estadisticas)) {
            fputcsv($output, []);
            fputcsv($output, ['=== ESTADÍSTICAS ===']);
            fputcsv($output, ['Total Ventas:', $estadisticas['total_ventas']]);
            fputcsv($output, ['Total Ingresos:', '$' . number_format($estadisticas['total_ingresos'], 2)]);
            fputcsv($output, ['Promedio por Venta:', '$' . number_format($estadisticas['promedio_venta'], 2)]);
        }
    } else {
        fputcsv($output, ['No hay datos para exportar']);
    }
    
    fclose($output);
    exit;
    
} elseif ($tipo === 'pdf') {
    // Generar PDF simple
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.pdf"');
    
    // PDF básico (requiere librería externa para mejor formato)
    echo "PDF no disponible. Use CSV para exportar datos.";
    exit;
}

header("Location: reporte.php?error=tipo_invalido");
exit;
?>