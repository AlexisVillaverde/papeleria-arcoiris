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

    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/Reporte.css">

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

    <div class="dhanshree-stationery-e-commerc">
        <div class="app">
            <div class="salesreports">
                <div class="container7">
                    <div class="container8">
                        <div class="container9">
                            <img src="/public/images/boxWhite.svg" alt="">
                            <div class="container10">
                                <h1 class="dhanshree-stationery-e-commerc-heading-1">
                                    <span class="reportes-de-ventas">Reportes de Ventas</span>
                                </h1>
                                <p class="paragraph4">
                                    <span class="administrador-sistema">Análisis y estadísticas de ventas</span>
                                </p>
                            </div>
                        </div>
                        <div class="container11">
                            <button type="button" class="button5" onclick="generarReporte()">
                                <img src="/public/images/descarga.svg" alt="">
                                <div class="exportar-pdf">Generar Reporte</div>
                            </button>
                            <button type="button" class="button6" onclick="exportarExcel()">
                                <img src="/public/images/importar.svg" alt="">
                                <div class="exportar-pdf">Exportar Excel</div>
                            </button>
                        </div>
                    </div>

                    <div class="container12">
                        <div class="container13">
                            <img src="/public/images/filtro.svg" alt="">
                            <div class="text2">
                                <div class="sistema-de-punto">Filtros:</div>
                            </div>
                        </div>
                        <div class="primitivebutton">
                            <div class="dhanshree-stationery-e-commerc-primitivespan">
                                <input type="date" id="fecha-inicio" min="2020-01-01" max="2030-12-31" title="Seleccione fecha de inicio" style="border: none; background: transparent; color: inherit;">
                            </div>
                            <img src="/public/images/despliegue.svg" alt="">
                        </div>
                        <div class="dhanshree-stationery-e-commerc-primitivebutton">
                            <div class="primitivespan2">
                                <input type="date" id="fecha-fin" min="2020-01-01" max="2030-12-31" title="Seleccione fecha fin" style="border: none; background: transparent; color: inherit;">
                            </div>
                            <img src="/public/images/despliegue.svg" alt="">
                        </div>
                        <div class="dhanshree-stationery-e-commerc-primitivebutton">
                            <div class="primitivespan2">
                                <select id="filtro-empleado" style="border: none; background: transparent; color: inherit; width: 100%;">
                                    <option value="">Seleccionar empleado</option>
                                    <?php foreach ($empleados as $emp): ?>
                                        <option value="<?= $emp['id_empleado'] ?>"><?= $emp['nombre'] . ' ' . $emp['apellidos'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <img src="/public/images/despliegue.svg" alt="">
                        </div>
                    </div>
                </div>

                <div class="container14">
                    <div class="container15">
                        <div class="card">
                            <div class="dhanshree-stationery-e-commerc-salesreports">
                                <div class="container16">
                                    <div class="paragraph5">
                                        <div class="sistema-de-punto">Total Ventas</div>
                                    </div>
                                    <div class="paragraph6">
                                        <b class="b"><?= $stats['total_ventas'] ?></b>
                                    </div>
                                </div>
                                <img src="/public/images/punto-venta.svg" alt="">
                            </div>
                        </div>

                        <div class="dhanshree-stationery-e-commerc-card">
                            <div class="dhanshree-stationery-e-commerc-salesreports">
                                <div class="container17">
                                    <div class="paragraph5">
                                        <div class="sistema-de-punto">Ingresos Totales</div>
                                    </div>
                                    <div class="paragraph8">
                                        <b class="dhanshree-stationery-e-commerc-b">$<?= number_format($stats['ingresos_totales'], 2) ?></b>
                                    </div>
                                </div>
                                <img src="/public/images/dinero.svg" alt="">
                            </div>
                        </div>

                        <div class="card2">
                            <div class="dhanshree-stationery-e-commerc-salesreports">
                                <div class="container18">
                                    <div class="paragraph5">
                                        <div class="sistema-de-punto">Venta Promedio</div>
                                    </div>
                                    <div class="paragraph8">
                                        <b class="b2">$<?= number_format($stats['venta_promedio'], 0) ?></b>
                                    </div>
                                </div>
                                <img src="/public/images/flechaMo.svg" alt="">
                            </div>
                        </div>

                        <div class="card3">
                            <div class="dhanshree-stationery-e-commerc-salesreports">
                                <div class="container19">
                                    <div class="paragraph5">
                                        <div class="sistema-de-punto">IVA Recaudado</div>
                                    </div>
                                    <div class="paragraph8">
                                        <b class="b2">$<?= number_format($stats['iva_recaudado'], 0) ?></b>
                                    </div>
                                </div>
                                <img src="/public/images/calendario.svg" alt="">
                            </div>
                        </div>

                        <div class="card4">
                            <div class="dhanshree-stationery-e-commerc-salesreports">
                                <div class="container20">
                                    <div class="paragraph5">
                                        <div class="sistema-de-punto">Items Vendidos</div>
                                    </div>
                                    <div class="paragraph6">
                                        <b class="b"><?= $stats['items_vendidos'] ?></b>
                                    </div>
                                </div>
                                <img src="/public/images/box.svg" alt="">
                            </div>
                        </div>
                    </div>

                    <div class="container21">
                        <div class="card5">
                            <div class="cardtitle">
                                <div class="ventas-por-da">Ventas por Día</div>
                            </div>
                            <div class="cardcontent">
                                <div id="grafico-ventas-dia" style="height: 200px; display: flex; align-items: center; justify-content: center; color: #666;">
                                    Gráfico de ventas por día
                                </div>
                            </div>
                        </div>

                        <div class="card6">
                            <div class="cardtitle">
                                <div class="ventas-por-da">Métodos de Pago</div>
                            </div>
                            <div class="dhanshree-stationery-e-commerc-cardcontent">
                                <div id="grafico-metodos-pago" style="height: 200px; display: flex; align-items: center; justify-content: center; color: #666;">
                                    Gráfico de métodos de pago
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card7">
                        <div class="cardtitle2">
                            <div class="ventas-por-da">Rendimiento por Empleado</div>
                        </div>
                        <div class="cardcontent2">
                            <div id="grafico-empleados" style="height: 200px; display: flex; align-items: center; justify-content: center; color: #666;">
                                Gráfico de rendimiento por empleado
                            </div>
                        </div>
                    </div>

                    <div class="card8">
                        <div class="cardtitle3">
                            <div class="salesreports6">
                                <div class="ventas-por-da">Ventas Recientes</div>
                            </div>
                            <div class="dhanshree-stationery-e-commerc-badge">
                                <div class="div"><?= count($ventas_recientes) ?> registros</div>
                            </div>
                        </div>
                        <div class="salesreports7">
                            <?php foreach ($ventas_recientes as $venta): ?>
                                <div class="container22">
                                    <div class="container23">
                                        <img src="/public/images/carroMo.svg" alt="">
                                        <div class="container24">
                                            <div class="paragraph2">
                                                <div class="administrador-sistema"><?= $venta['codigo_venta'] ?></div>
                                            </div>
                                            <div class="paragraph19">
                                                <div class="dhanshree-stationery-e-commerc-pm"><?= date('d/m/Y - g:i:s A', strtotime($venta['fecha_venta'])) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container25">
                                        <div class="container26">
                                            <div class="paragraph2">
                                                <b class="b5">$<?= number_format($venta['total'], 2) ?></b>
                                            </div>
                                            <div class="container27">
                                                <div class="badge2">
                                                    <div class="div"><?= ucfirst($venta['metodo_pago']) ?></div>
                                                </div>
                                                <div class="badge3">
                                                    <div class="div">EMP-<?= str_pad($venta['id_empleado'], 3, '0', STR_PAD_LEFT) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="button7">
                                            <img src="/public/images/vista.svg" alt="">
                                            <div class="ver">Ver</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Área de Resultados de Reportes -->
                    <div id="resultado-reportes" style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; min-height: 200px;">
                        <?php if (isset($_GET['reporte_generado']) && isset($_SESSION['reporte_resultados'])): ?>
                            <h3>Resultados del Reporte</h3>
                            <?php if (!empty($_SESSION['reporte_estadisticas'])): ?>
                                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                                    <div><strong>Total Ventas:</strong> <?= $_SESSION['reporte_estadisticas']['total_ventas'] ?></div>
                                    <div><strong>Ingresos:</strong> $<?= number_format($_SESSION['reporte_estadisticas']['total_ingresos'], 2) ?></div>
                                    <div><strong>Promedio:</strong> $<?= number_format($_SESSION['reporte_estadisticas']['promedio_venta'], 2) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th style="padding: 10px; border: 1px solid #ddd;">Código</th>
                                        <th style="padding: 10px; border: 1px solid #ddd;">Fecha</th>
                                        <th style="padding: 10px; border: 1px solid #ddd;">Total</th>
                                        <th style="padding: 10px; border: 1px solid #ddd;">Método Pago</th>
                                        <th style="padding: 10px; border: 1px solid #ddd;">Empleado</th>
                                        <th style="padding: 10px; border: 1px solid #ddd;">Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['reporte_resultados'] as $venta): ?>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #ddd;"><?= $venta['codigo_venta'] ?></td>
                                            <td style="padding: 8px; border: 1px solid #ddd;"><?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?></td>
                                            <td style="padding: 8px; border: 1px solid #ddd;">$<?= number_format($venta['total'], 2) ?></td>
                                            <td style="padding: 8px; border: 1px solid #ddd;"><?= ucfirst($venta['metodo_pago']) ?></td>
                                            <td style="padding: 8px; border: 1px solid #ddd;"><?= $venta['empleado'] ?></td>
                                            <td style="padding: 8px; border: 1px solid #ddd;"><?= $venta['items_vendidos'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php elseif (isset($_GET['error'])): ?>
                            <div style="color: red; padding: 10px; background: #ffe6e6; border-radius: 5px;">
                                Error: <?= htmlspecialchars($_GET['error']) ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: #666;">Seleccione filtros y haga clic en "Generar Reporte" para ver los resultados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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