<?php
require_once "conexion.php";
session_start();

// Obtener estadísticas del inventario
$stats = [];

// Total productos
$stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
$stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Valor total del inventario
$stmt = $conn->query("SELECT SUM(precio * stock) as valor_total FROM productos");
$stats['valor_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

// Total items en stock
$stmt = $conn->query("SELECT SUM(stock) as total_items FROM productos");
$stats['total_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;

// Productos con stock bajo (≤5)
$stmt = $conn->query("SELECT COUNT(*) as stock_bajo FROM productos WHERE stock <= 5 AND stock > 0");
$stats['stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['stock_bajo'];

// Productos agotados
$stmt = $conn->query("SELECT COUNT(*) as agotados FROM productos WHERE stock = 0");
$stats['agotados'] = $stmt->fetch(PDO::FETCH_ASSOC)['agotados'];

// Cargar productos
$stmt = $conn->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar categorías para filtros
$stmt = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papeleria Arcoiris - Inventario</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./css/style.css">
    <style>
        .card__price {
            text-align: right;
        }

        .price-label {
            font-size: 12px;
            color: #6B7280;
            display: block;
        }

        .price-value {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
        }

        .card__button--stock {
            color: #059669;
        }

        .card__tag--success {
            background-color: #D1FAE5;
            color: #059669;
        }

        .card__tag--warning {
            background-color: #FEF3C7;
            color: #D97706;
        }

        .card__tag--danger {
            background-color: #FEE2E2;
            color: #DC2626;
        }

        .card__tag--info {
            background-color: #EFF6FF;
            color: #2563EB;
        }

        .employee-grid {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        @media (max-width: 1200px) {
            .employee-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        @media (max-width: 1024px) {
            .employee-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .employee-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
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

                    <a href="inventario.php" class="text-nav-item nav-tab-item nav-tab-item--active">
                        <img src="public/images/box.svg" class="icon-sm">
                        <span>Inventario</span>
                        <span class="text-nav-badge nav-notification">●</span>
                    </a>

                    <a href="reporte.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/stadistic.svg" class="icon-sm">
                        <span>Reportes</span>
                    </a>
                </div>
            </div>

            <div class="nav-right">
                <div class="time-date-block">
                    <div class="text-time font-cousine">
                        <img src="public/images/clock.svg" class="icon-sm">
                        <span><?= date('g:i a') ?></span>
                    </div>
                    <div class="text-date">
                        <?= date('l, j \d\e F \d\e Y') ?>
                    </div>
                </div>
                <div class="user-info-block">
                    <details class="perfil-container">

                        <summary class="user-info-block">

                            <div class="user-avatar-wrapper">
                                <div class="text-user-emoji">👤</div> <span class="user-status-dot"></span>
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
                        <img src="public/images/box.svg" class="icon-lg">
                    </div>
                    <div>
                        <h1 class="filter-title">Gestión de Inventario</h1>
                        <p class="filter-subtitle">Control de stock y productos</p>
                    </div>
                </div>

                <a href="#" id="btnNuevoProducto" class="filter-button-new">
                    <img src="public/images/cruz.svg" class="icon-sm">
                    <span>Nuevo Producto</span>
                </a>
            </div>

            <div class="filter-controls">
                <div class="filter-search-wrapper">
                    <input type="text" placeholder="Buscar productos..." class="filter-search-input"
                        id="searchProducto">
                    <img src="public/images/lupa.svg" class="filter-search-icon icon-sm">
                </div>

                <select id="filtroCategoria" class="select-dropdown">
                    <option value="todos">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="filtroEstado" class="filter-dropdown-select">
                    <option value="todos">Todos</option>
                    <option value="disponible">Disponible</option>
                    <option value="stock_bajo">Stock Bajo</option>
                    <option value="agotado">Agotado</option>
                </select>
            </div>
        </section>

        <section class="employee-grid">
            <?php foreach ($productos as $producto): ?>

                <?php
                // Estado del stock
                $estado_stock = $producto['stock'] == 0 ? 'agotado' : ($producto['stock'] <= 5 ? 'stock_bajo' : 'disponible');

                // Color del avatar según estado
                $avatarColor = $producto['stock'] == 0 ? "#FEE2E2" : ($producto['stock'] <= 5 ? "#FEF3C7" : "#D1FAE5");

                // Etiqueta de estado
                $tagEstado = $producto['stock'] == 0
                    ? "<span class='card__tag card__tag--danger'>Agotado</span>"
                    : ($producto['stock'] <= 5
                        ? "<span class='card__tag card__tag--warning'>Stock Bajo</span>"
                        : "<span class='card__tag card__tag--success'>Disponible</span>");

                // Etiqueta de categoría
                $tagCategoria = "<span class='card__tag card__tag--info'>" . ($producto['categoria_nombre'] ?? 'Sin categoría') . "</span>";
                ?>

                <article class="employee-card" data-categoria="<?= $producto['id_categoria'] ?? '' ?>" data-estado="<?= $estado_stock ?>">
                    <div class="card__header">
                        <div class="card__person">
                            <div class="card__avatar" style="background-color: <?= $avatarColor ?>;">
                                <img src="public/images/box.svg" alt="Producto" class="icon-xl">
                            </div>
                            <div class="card__info">
                                <h3 class="card__name"><?= htmlspecialchars($producto['nombre'] ?? 'Sin nombre') ?></h3>
                                <span class="card__id">ID: PROD-<?= str_pad($producto['id_producto'], 3, '0', STR_PAD_LEFT) ?></span>
                            </div>
                        </div>

                        <div class="card__price">
                            <span class="price-label">Precio</span>
                            <span class="price-value">$<?= number_format($producto['precio'] ?? 0, 2) ?></span>
                        </div>
                    </div>

                    <div class="card__body">
                        <div class="card__tags">
                            <?= $tagCategoria ?>
                            <?= $tagEstado ?>
                        </div>

                        <div class="card__details">
                            <div class="card__detail-item">
                                <img src="public/images/box.svg" class="icon-sm">
                                <span>Stock: <?= $producto['stock'] ?? 0 ?> unidades</span>
                            </div>
                            <div class="card__detail-item">
                                <img src="public/images/stadistic.svg" class="icon-sm">
                                <span>Valor: $<?= number_format(($producto['precio'] ?? 0) * ($producto['stock'] ?? 0), 2) ?></span>
                            </div>
                            <?php if (!empty($producto['descripcion'])): ?>
                                <div class="card__detail-item">
                                    <img src="public/images/edit.svg" class="icon-sm">
                                    <span><?= htmlspecialchars($producto['descripcion']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card__divider"></div>
                    <div class="card__footer">
                        <div class="card__actions">
                            <button class="card__button card__button--edit"
                                data-id="<?= $producto['id_producto'] ?>"
                                data-nombre="<?= htmlspecialchars($producto['nombre'] ?? '') ?>"
                                data-descripcion="<?= htmlspecialchars($producto['descripcion'] ?? '') ?>"
                                data-precio="<?= $producto['precio'] ?? 0 ?>"
                                data-stock="<?= $producto['stock'] ?? 0 ?>"
                                data-categoria="<?= $producto['id_categoria'] ?? '' ?>"
                                data-codigo="<?= htmlspecialchars($producto['codigo_barras'] ?? '') ?>">
                                <img src="public/images/edit.svg" class="icon-sm">
                                <span>Editar</span>
                            </button>

                            <button class="card__button card__button--stock" onclick="ajustarStock(<?= $producto['id_producto'] ?>, <?= $producto['stock'] ?>)">
                                <img src="public/images/box.svg" class="icon-sm">
                                <span>Stock</span>
                            </button>
                        </div>

                        <form method="POST" action="eliminar_producto.php" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                            <input type="hidden" name="id" value="<?= $producto['id_producto'] ?>">
                            <button type="submit" class="card__button card__button--delete">
                                <img src="public/images/eliminar.svg" class="icon-md">
                            </button>
                        </form>
                    </div>

                </article>
            <?php endforeach; ?>
        </section>

    </main>

    <script>
        const $ = s => document.querySelector(s),
            $$ = s => document.querySelectorAll(s);

        // Filtros
        const filtrar = () => {
            const txt = $('#searchProducto').value.toLowerCase();
            const cat = $('#filtroCategoria').value;
            const est = $('#filtroEstado').value;

            $$('.employee-card').forEach(c => {
                const content = c.innerText.toLowerCase();
                const categoria = c.dataset.categoria;
                const estado = c.dataset.estado;

                const show = content.includes(txt) &&
                    (cat === 'todos' || categoria === cat) &&
                    (est === 'todos' || estado === est);

                c.style.display = show ? 'flex' : 'none';
            });
        };

        $('#searchProducto').oninput = $('#filtroCategoria').onchange = $('#filtroEstado').onchange = filtrar;

        function ajustarStock(id, stockActual) {
            const nuevoStock = prompt('Ingrese el nuevo stock:', stockActual);
            if (nuevoStock === null) return;

            // Validaciones
            if (nuevoStock.trim() === '') {
                alert('El stock no puede estar vacío');
                return;
            }

            if (isNaN(nuevoStock) || nuevoStock < 0) {
                alert('El stock debe ser un número mayor o igual a 0');
                return;
            }

            if (nuevoStock > 999999) {
                alert('El stock no puede exceder 999,999 unidades');
                return;
            }

            fetch('ajustar_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&stock=${nuevoStock}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error al actualizar el stock');
                    }
                })
                .catch(() => {
                    alert('Error de conexión');
                });
        }

        // Modales
        $('#btnNuevoProducto').onclick = e => {
            e.preventDefault();
            $('#modalProducto').style.display = 'flex';
        };

        $$('.card__button--edit').forEach(b => b.onclick = () => {
            Object.keys(b.dataset).forEach(k => $('#edit_' + k) ? $('#edit_' + k).value = b.dataset[k] : null);
            $('#modalEditar').style.display = 'flex';
        });

        window.onclick = e => {
            if (e.target.matches('.modal-overlay, .btn-cerrar')) $$('.modal-overlay').forEach(m => m.style.display = 'none');
        };
    </script>



    <!-- Modal Nuevo Producto -->
    <div id="modalProducto" class="modal-overlay">
        <div class="modal">
            <h2>Nuevo Producto</h2>
            <form action="agregar_producto.php" method="POST">
                <label>Nombre del Producto:</label>
                <input type="text" name="nombre" required maxlength="100" pattern=".{1,100}" title="El nombre es obligatorio y no puede exceder 100 caracteres">

                <label>Código de Barras:</label>
                <input type="text" name="codigo_barras" required maxlength="50" pattern=".{1,50}" title="El código de barras es obligatorio y no puede exceder 50 caracteres">

                <label>Categoría:</label>
                <select name="categoria" required title="Debe seleccionar una categoría">
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Stock:</label>
                <input type="number" name="stock" min="0" max="999999" required title="El stock debe ser entre 0 y 999,999">

                <label>Precio:</label>
                <input type="number" name="precio" step="0.01" min="0.01" max="999999.99" required title="El precio debe ser mayor a 0">

                <label>Descripción:</label>
                <input type="text" name="descripcion" maxlength="500" title="Máximo 500 caracteres">

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cerrar">Cerrar</button>
                    <button type="submit" class="btn btn-guardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal">
            <h2>Editar Producto</h2>
            <form action="editar_producto.php" method="POST">
                <input type="hidden" name="id" id="edit_id">

                <label>Nombre del Producto:</label>
                <input type="text" name="nombre" id="edit_nombre" required maxlength="100" pattern=".{1,100}" title="El nombre es obligatorio y no puede exceder 100 caracteres">

                <label>Código de Barras:</label>
                <input type="text" name="codigo_barras" id="edit_codigo" required maxlength="50" pattern=".{1,50}" title="El código de barras es obligatorio y no puede exceder 50 caracteres">

                <label>Categoría:</label>
                <select name="categoria" id="edit_categoria" required title="Debe seleccionar una categoría">
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Stock:</label>
                <input type="number" name="stock" id="edit_stock" min="0" max="999999" required title="El stock debe ser entre 0 y 999,999">

                <label>Precio:</label>
                <input type="number" name="precio" id="edit_precio" step="0.01" min="0.01" max="999999.99" required title="El precio debe ser mayor a 0">

                <label>Descripción:</label>
                <input type="text" name="descripcion" id="edit_descripcion" maxlength="500" title="Máximo 500 caracteres">

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cerrar">Cerrar</button>
                    <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>