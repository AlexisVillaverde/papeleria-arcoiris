<?php
require_once "conexion.php";
session_start();

// Cargar empleados para el filtro
$stmt = $conn->query("SELECT id_empleado, nombre, apellidos FROM empleados WHERE activo = 1 ORDER BY nombre");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas básicas
$stats = [];

// Total ventas
$stmt = $conn->query("SELECT COUNT(*) as total FROM ventas");
$stats['total_ventas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Ingresos totales
$stmt = $conn->query("SELECT SUM(total) as ingresos FROM ventas");
$stats['ingresos_totales'] = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'] ?? 0;

// Venta promedio
$stats['venta_promedio'] = $stats['total_ventas'] > 0 ? $stats['ingresos_totales'] / $stats['total_ventas'] : 0;

// IVA recaudado (16%)
$stats['iva_recaudado'] = $stats['ingresos_totales'] * 0.16;

// Items vendidos
$stmt = $conn->query("SELECT SUM(cantidad) as items FROM detalle_ventas");
$stats['items_vendidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['items'] ?? 0;

// Ventas recientes
$stmt = $conn->query("SELECT v.*, e.nombre, e.apellidos FROM ventas v LEFT JOIN empleados e ON v.id_empleado = e.id_empleado ORDER BY v.fecha_venta DESC LIMIT 3");
$ventas_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papeleria Arcoiris - Reportes</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;500;700&display=swap" rel="stylesheet">
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

                <div class="nav-tabs-container">
                    <a href="productos.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/punto-venta.svg" class="icon-sm">
                        <span>Punto de Venta</span>
                    </a>
                    <a href="empleados.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/empleados.svg" class="icon-sm">
                        <span>Empleados</span>
                    </a>
                    <a href="inventario.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/box.svg" class="icon-sm">
                        <span>Inventario</span>
                    </a>
                    <a href="reporte.php" class="text-nav-item nav-tab-item nav-tab-item--active">
                        <img src="public/images/stadistic.svg" class="icon-sm">
                        <span>Reportes</span>
                    </a>
                </div>
            </div>

            <div class="nav-right">
                <div class="time-date-block">
                    <div class="text-time font-cousine">
                        <img src="public/images/clock.svg" class="icon-sm">
                        <span>10:16 p.m.</span>
                    </div>
                    <div class="text-date">
                        sábado, 25 de octubre de 2025
                    </div>
                </div>
                <div class="user-info-block">
                    <details class="perfil-container">
                        <summary class="user-info-block">
                            <div class="user-avatar-wrapper">
                                <div class="text-user-emoji">👤</div>
                                <span class="user-status-dot"></span>
                            </div>
                            <div>
                                <div class="text-user-name">Administrador Sistema</div>
                                <div class="text-user-role">Administrador</div>
                            </div>
                        </summary>
                        <div class="menu-logout">
                            <a href="logout.php">Cerrar Sesión</a>
                        </div>
                    </details>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="filter-section">
            <div class="filter-header">
                <div class="filter-title-group">
                    <div class="filter-icon-wrapper">
                        <img src="public/images/stadistic.svg" class="icon-lg">
                    </div>
                    <div>
                        <h1 class="filter-title">Reportes de Ventas</h1>
                        <p class="filter-subtitle">Análisis y estadísticas de ventas</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" class="filter-button-new" onclick="generarReporte()">
                        <img src="public/images/descarga.svg" class="icon-sm">
                        <span>Generar Reporte</span>
                    </button>
                    <button type="button" class="filter-button-new" onclick="exportarExcel()" style="background: #10b981;">
                        <img src="public/images/importar.svg" class="icon-sm">
                        <span>Exportar Excel</span>
                    </button>
                </div>
            </div>

            <div class="filter-controls">
                <div class="filter-search-wrapper">
                    <img src="public/images/filtro.svg" class="filter-search-icon icon-sm">
                    <span class="text-sm text-gray-600">Filtros:</span>
                </div>

                <input type="date" id="fecha-inicio" min="2020-01-01" max="2030-12-31" title="Seleccione fecha de inicio" class="select-dropdown">
                
                <input type="date" id="fecha-fin" min="2020-01-01" max="2030-12-31" title="Seleccione fecha fin" class="select-dropdown">
                
                <select id="filtro-empleado" class="filter-dropdown-select">
                    <option value="">Seleccionar empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>"><?= $emp['nombre'] . ' ' . $emp['apellidos'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Total Ventas</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_ventas'] ?></p>
                    </div>
                    <img src="public/images/punto-venta.svg" class="icon-xl">
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Ingresos Totales</p>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($stats['ingresos_totales'], 2) ?></p>
                    </div>
                    <img src="public/images/dinero.svg" class="icon-xl">
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Venta Promedio</p>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($stats['venta_promedio'], 0) ?></p>
                    </div>
                    <img src="public/images/flechaMo.svg" class="icon-xl">
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">IVA Recaudado</p>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($stats['iva_recaudado'], 0) ?></p>
                    </div>
                    <img src="public/images/calendario.svg" class="icon-xl">
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Items Vendidos</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['items_vendidos'] ?></p>
                    </div>
                    <img src="public/images/box.svg" class="icon-xl">
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventas por Día</h3>
                <div id="grafico-ventas-dia" class="h-48 flex items-center justify-center text-gray-500">
                    Gráfico de ventas por día
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Métodos de Pago</h3>
                <div id="grafico-metodos-pago" class="h-48 flex items-center justify-center text-gray-500">
                    Gráfico de métodos de pago
                </div>
            </div>
        </section>

        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Rendimiento por Empleado</h3>
            <div id="grafico-empleados" class="h-64 flex items-center justify-center text-gray-500">
                Gráfico de rendimiento por empleado
            </div>
        </section>

        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Ventas Recientes</h3>
                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-sm"><?= count($ventas_recientes) ?> registros</span>
            </div>
            <div class="space-y-3">
                <?php foreach ($ventas_recientes as $venta): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <img src="public/images/carroMo.svg" class="icon-lg">
                            <div>
                                <p class="font-semibold text-gray-900">FOLIO-<?= str_pad($venta['folio'], 4, '0', STR_PAD_LEFT) ?></p>
                                <p class="text-sm text-gray-600"><?= date('d/m/Y - g:i:s A', strtotime($venta['fecha_venta'])) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">$<?= number_format($venta['total'], 2) ?></p>
                                <div class="flex gap-2 mt-1">
                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs"><?= ucfirst($venta['metodo_pago']) ?></span>
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">EMP-<?= str_pad($venta['id_empleado'], 3, '0', STR_PAD_LEFT) ?></span>
                                </div>
                            </div>
                            <a href="ver_venta.php?folio=<?= $venta['folio'] ?>" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 flex items-center gap-1">
                                <img src="public/images/vista.svg" class="icon-sm">
                                Ver
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Área de Resultados de Reportes -->
        <section id="resultado-reportes" class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm min-h-48">
            <?php if (isset($_GET['reporte_generado']) && isset($_SESSION['reporte_resultados'])): ?>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resultados del Reporte</h3>
                <?php if (!empty($_SESSION['reporte_estadisticas'])): ?>
                    <div class="flex flex-wrap gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                        <div><strong>Total Ventas:</strong> <?= $_SESSION['reporte_estadisticas']['total_ventas'] ?></div>
                        <div><strong>Ingresos:</strong> $<?= number_format($_SESSION['reporte_estadisticas']['total_ingresos'], 2) ?></div>
                        <div><strong>Promedio:</strong> $<?= number_format($_SESSION['reporte_estadisticas']['promedio_venta'], 2) ?></div>
                    </div>
                <?php endif; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 border border-gray-300 text-left">Código</th>
                                <th class="p-3 border border-gray-300 text-left">Fecha</th>
                                <th class="p-3 border border-gray-300 text-left">Total</th>
                                <th class="p-3 border border-gray-300 text-left">Método Pago</th>
                                <th class="p-3 border border-gray-300 text-left">Empleado</th>
                                <th class="p-3 border border-gray-300 text-left">Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['reporte_resultados'] as $venta): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-2 border border-gray-300"><?= $venta['codigo_venta'] ?></td>
                                    <td class="p-2 border border-gray-300"><?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?></td>
                                    <td class="p-2 border border-gray-300">$<?= number_format($venta['total'], 2) ?></td>
                                    <td class="p-2 border border-gray-300"><?= ucfirst($venta['metodo_pago']) ?></td>
                                    <td class="p-2 border border-gray-300"><?= $venta['empleado'] ?></td>
                                    <td class="p-2 border border-gray-300"><?= $venta['items_vendidos'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="text-red-700 p-4 bg-red-100 border border-red-300 rounded-lg">
                    Error: <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">Seleccione filtros y haga clic en "Generar Reporte" para ver los resultados</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function generarReporte() {
            const fechaInicio = document.getElementById('fecha-inicio').value;
            const fechaFin = document.getElementById('fecha-fin').value;
            const empleadoId = document.getElementById('filtro-empleado').value;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'generar_reporte.php';
            
            const campos = {
                'tipo_reporte': 'ventas_detalle',
                'fecha_inicio': fechaInicio,
                'fecha_fin': fechaFin,
                'empleado_id': empleadoId
            };
            
            Object.keys(campos).forEach(key => {
                if (campos[key]) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = campos[key];
                    form.appendChild(input);
                }
            });
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function exportarExcel() {
            // Verificar si hay un reporte generado
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('reporte_generado')) {
                alert('Primero debe generar un reporte antes de exportar');
                return;
            }
            window.location.href = 'exportar_reporte.php?tipo=csv';
        }
    </script>
</body>

</html>