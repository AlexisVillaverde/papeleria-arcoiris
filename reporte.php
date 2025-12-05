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
    
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal {
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-cerrar {
            background: #6b7280;
            color: white;
        }
        
        .btn-guardar {
            background: #3b82f6;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
    </style>

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
                            <button onclick="verVenta(<?= $venta['folio'] ?>)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 flex items-center gap-1">
                                <img src="public/images/vista.svg" class="icon-sm">
                                Ver
                            </button>
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
        
        function verVenta(folio) {
            fetch(`obtener_venta.php?folio=${folio}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    mostrarModalVenta(data.venta, data.detalles);
                })
                .catch(error => {
                    alert('Error al cargar la venta');
                });
        }
        
        function mostrarModalVenta(venta, detalles) {
            let productosHtml = '';
            detalles.forEach(detalle => {
                productosHtml += `
                    <div class="mb-2">
                        <div class="flex justify-between">
                            <span class="truncate pr-2">${(detalle.producto || 'PRODUCTO ELIMINADO').toUpperCase()}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>${detalle.cantidad} x $${parseFloat(detalle.precio_unitario).toFixed(2)}</span>
                            <span>$${parseFloat(detalle.subtotal).toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });
            
            const modalHtml = `
                <div class="modal-overlay" onclick="cerrarModal(event)">
                    <div class="modal" onclick="event.stopPropagation()" style="max-width: 400px; font-family: monospace; font-size: 14px;">
                        <!-- Ticket de Venta -->
                        <div class="bg-white border-2 border-dashed border-gray-400 p-6">
                            <!-- Header del Ticket -->
                            <div class="text-center mb-4 border-b border-dashed border-gray-400 pb-4">
                                <h2 class="text-lg font-bold">PAPELERÍA ARCOÍRIS</h2>
                                <p class="text-xs">Sistema de Punto de Venta</p>
                                <p class="text-xs mt-2">TICKET DE VENTA</p>
                            </div>

                            <!-- Información de la Venta -->
                            <div class="mb-4 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span>FOLIO:</span>
                                    <span>${String(venta.folio).padStart(6, '0')}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>FECHA:</span>
                                    <span>${new Date(venta.fecha_venta).toLocaleDateString('es-MX')} ${new Date(venta.fecha_venta).toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'})}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>CAJERO:</span>
                                    <span>${(venta.nombre + ' ' + venta.apellidos).toUpperCase()}</span>
                                </div>
                            </div>

                            <!-- Línea separadora -->
                            <div class="border-b border-dashed border-gray-400 mb-4"></div>

                            <!-- Productos -->
                            <div class="mb-4">
                                <div class="text-xs font-bold mb-2">PRODUCTOS:</div>
                                ${productosHtml}
                            </div>

                            <!-- Línea separadora -->
                            <div class="border-b border-dashed border-gray-400 mb-4"></div>

                            <!-- Totales -->
                            <div class="text-xs space-y-1 mb-4">
                                <div class="flex justify-between">
                                    <span>SUBTOTAL:</span>
                                    <span>$${parseFloat(venta.subtotal).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>IVA (16%):</span>
                                    <span>$${parseFloat(venta.iva).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between font-bold text-sm border-t border-dashed border-gray-400 pt-2">
                                    <span>TOTAL:</span>
                                    <span>$${parseFloat(venta.total).toFixed(2)}</span>
                                </div>
                            </div>

                            <!-- Información de Pago -->
                            <div class="text-xs space-y-1 mb-4">
                                <div class="flex justify-between">
                                    <span>MÉTODO PAGO:</span>
                                    <span>${venta.metodo_pago.toUpperCase()}</span>
                                </div>
                                ${venta.monto_recibido ? `
                                    <div class="flex justify-between">
                                        <span>RECIBIDO:</span>
                                        <span>$${parseFloat(venta.monto_recibido).toFixed(2)}</span>
                                    </div>
                                ` : ''}
                                ${venta.cambio ? `
                                    <div class="flex justify-between">
                                        <span>CAMBIO:</span>
                                        <span>$${parseFloat(venta.cambio).toFixed(2)}</span>
                                    </div>
                                ` : ''}
                            </div>

                            <!-- Footer del Ticket -->
                            <div class="text-center text-xs border-t border-dashed border-gray-400 pt-4">
                                <p>¡GRACIAS POR SU COMPRA!</p>
                                <p class="mt-2">Conserve su ticket</p>
                                <p class="mt-1">www.papeleria-arcoiris.com</p>
                            </div>
                        </div>
                        
                        <div class="modal-buttons mt-4">
                            <button type="button" class="btn btn-cerrar" onclick="cerrarModal()">Cerrar</button>
                            <button type="button" class="btn btn-guardar" onclick="imprimirTicket()">Imprimir</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        function cerrarModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.querySelector('.modal-overlay')?.remove();
        }
        
        function imprimirTicket() {
            const ticketContent = document.querySelector('.modal .bg-white').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Ticket de Venta</title>
                        <style>
                            body { font-family: monospace; font-size: 12px; margin: 0; padding: 20px; }
                            .border-dashed { border-style: dashed; }
                            .border-b { border-bottom-width: 1px; }
                            .border-t { border-top-width: 1px; }
                            .border-gray-400 { border-color: #9ca3af; }
                            .text-center { text-align: center; }
                            .text-xs { font-size: 10px; }
                            .text-sm { font-size: 12px; }
                            .text-lg { font-size: 16px; }
                            .font-bold { font-weight: bold; }
                            .mb-2 { margin-bottom: 8px; }
                            .mb-4 { margin-bottom: 16px; }
                            .mt-1 { margin-top: 4px; }
                            .mt-2 { margin-top: 8px; }
                            .pb-4 { padding-bottom: 16px; }
                            .pt-2 { padding-top: 8px; }
                            .pt-4 { padding-top: 16px; }
                            .pr-2 { padding-right: 8px; }
                            .space-y-1 > * + * { margin-top: 4px; }
                            .flex { display: flex; }
                            .justify-between { justify-content: space-between; }
                            .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                        </style>
                    </head>
                    <body>${ticketContent}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        }
    </script>
</body>

</html>