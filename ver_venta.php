<?php
require_once "conexion.php";
session_start();

if (!isset($_GET['folio'])) {
    header('Location: reporte.php');
    exit;
}

$folio = $_GET['folio'];

// Obtener datos de la venta
$stmt = $conn->prepare("SELECT v.*, e.nombre, e.apellidos FROM ventas v LEFT JOIN empleados e ON v.id_empleado = e.id_empleado WHERE v.folio = ?");
$stmt->execute([$folio]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header('Location: reporte.php?error=Venta no encontrada');
    exit;
}

// Obtener detalle de productos
$stmt = $conn->prepare("SELECT dv.*, p.nombre as producto FROM detalle_ventas dv LEFT JOIN productos p ON dv.id_producto = p.id_producto WHERE dv.folio = ?");
$stmt->execute([$folio]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Folio <?= $folio ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <header class="main-header header-gradient">
        <nav class="main-nav">
            <div class="nav-left">
                <a href="#" class="logo-link">
                    <div class="logo-icon-container">
                        <img src="public/images/logo.svg" class="logo-image">
                    </div>
                    <div>
                        <div class="text-logo-title">Papelería Arcoíris</div>
                        <div class="text-logo-subtitle">Sistema de Punto de Venta</div>
                    </div>
                </a>
            </div>

            <div class="nav-right">
                <a href="reporte.php" class="filter-button-new">
                    <img src="public/images/flecha-izquierda.svg" class="icon-sm">
                    <span>Volver a Reportes</span>
                </a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle de Venta</h1>
                    <p class="text-gray-600">Folio: <?= str_pad($folio, 4, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Fecha</p>
                    <p class="font-semibold"><?= date('d/m/Y g:i A', strtotime($venta['fecha_venta'])) ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-3">Información del Empleado</h3>
                    <p><strong>Nombre:</strong> <?= $venta['nombre'] . ' ' . $venta['apellidos'] ?></p>
                    <p><strong>ID:</strong> EMP-<?= str_pad($venta['id_empleado'], 3, '0', STR_PAD_LEFT) ?></p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-3">Información de Pago</h3>
                    <p><strong>Método:</strong> <?= ucfirst($venta['metodo_pago']) ?></p>
                    <?php if ($venta['monto_recibido']): ?>
                        <p><strong>Recibido:</strong> $<?= number_format($venta['monto_recibido'], 2) ?></p>
                    <?php endif; ?>
                    <?php if ($venta['cambio']): ?>
                        <p><strong>Cambio:</strong> $<?= number_format($venta['cambio'], 2) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Productos Vendidos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 border border-gray-300 text-left">Producto</th>
                                <th class="p-3 border border-gray-300 text-center">Cantidad</th>
                                <th class="p-3 border border-gray-300 text-right">Precio Unit.</th>
                                <th class="p-3 border border-gray-300 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 border border-gray-300"><?= $detalle['producto'] ?? 'Producto eliminado' ?></td>
                                    <td class="p-3 border border-gray-300 text-center"><?= $detalle['cantidad'] ?></td>
                                    <td class="p-3 border border-gray-300 text-right">$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                    <td class="p-3 border border-gray-300 text-right">$<?= number_format($detalle['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t pt-4">
                <div class="flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between py-2">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($venta['subtotal'], 2) ?></span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>IVA (16%):</span>
                            <span>$<?= number_format($venta['iva'], 2) ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-t font-bold text-lg">
                            <span>Total:</span>
                            <span>$<?= number_format($venta['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>