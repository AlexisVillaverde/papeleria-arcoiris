<?php
require_once "conexion.php";

if (!isset($_GET['folio'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Folio requerido']);
    exit;
}

$folio = $_GET['folio'];

try {
    // Obtener datos de la venta
    $stmt = $conn->prepare("SELECT v.*, e.nombre, e.apellidos FROM ventas v LEFT JOIN empleados e ON v.id_empleado = e.id_empleado WHERE v.folio = ?");
    $stmt->execute([$folio]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        http_response_code(404);
        echo json_encode(['error' => 'Venta no encontrada']);
        exit;
    }

    // Obtener detalle de productos
    $stmt = $conn->prepare("SELECT dv.*, p.nombre as producto FROM detalle_ventas dv LEFT JOIN productos p ON dv.id_producto = p.id_producto WHERE dv.folio = ?");
    $stmt->execute([$folio]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'venta' => $venta,
        'detalles' => $detalles
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}
?>