<?php
// test_reportes.php - Archivo para probar las funciones de reportes

require_once 'database/reportes_crud.php';

echo "<h1>Prueba del Sistema de Reportes</h1>";

$reportes = new ReportesCRUD();

// Probar resumen del día
echo "<h2>Resumen de Ventas Hoy</h2>";
$resumen = $reportes->resumenVentasHoy();
if ($resumen) {
    echo "<p>Total Ventas: " . $resumen['total_ventas'] . "</p>";
    echo "<p>Ingresos Totales: $" . number_format($resumen['ingresos_totales'], 2) . "</p>";
    echo "<p>Promedio por Venta: $" . number_format($resumen['promedio_venta'], 2) . "</p>";
} else {
    echo "<p>Error al obtener resumen</p>";
}

// Probar reporte de inventario bajo
echo "<h2>Productos con Stock Bajo</h2>";
$inventario = $reportes->reporteInventarioBajo();
if ($inventario && count($inventario) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Código</th><th>Producto</th><th>Stock</th><th>Stock Mínimo</th></tr>";
    foreach ($inventario as $producto) {
        echo "<tr>";
        echo "<td>" . $producto['codigo_barras'] . "</td>";
        echo "<td>" . $producto['nombre'] . "</td>";
        echo "<td>" . $producto['stock'] . "</td>";
        echo "<td>" . $producto['stock_minimo'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay productos con stock bajo</p>";
}

// Probar reporte de ventas (últimos 30 días)
echo "<h2>Reporte de Ventas (Últimos 30 días)</h2>";
$fecha_inicio = date('Y-m-d', strtotime('-30 days'));
$fecha_fin = date('Y-m-d');

$ventas = $reportes->generarReporteVentas($fecha_inicio, $fecha_fin);
if ($ventas && count($ventas) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Folio</th><th>Fecha</th><th>Empleado</th><th>Total</th><th>Método Pago</th></tr>";
    foreach ($ventas as $venta) {
        echo "<tr>";
        echo "<td>" . $venta['folio'] . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($venta['fecha_venta'])) . "</td>";
        echo "<td>" . $venta['empleado'] . "</td>";
        echo "<td>$" . number_format($venta['total'], 2) . "</td>";
        echo "<td>" . $venta['metodo_pago'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay ventas en el período</p>";
}

// Probar lista de empleados
echo "<h2>Lista de Empleados</h2>";
$empleados = $reportes->obtenerEmpleados();
if ($empleados) {
    echo "<ul>";
    foreach ($empleados as $empleado) {
        echo "<li>ID: " . $empleado['id_empleado'] . " - " . $empleado['nombre_completo'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Error al obtener empleados</p>";
}

echo "<br><a href='Reporte.html'>Ir al módulo de reportes</a>";
?>