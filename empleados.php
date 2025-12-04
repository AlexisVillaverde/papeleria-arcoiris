<?php
require_once "conexion.php";
session_start();


// Cambiar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {

    $id = (int) $_POST['id'];
    $estadoActual = (int) $_POST['estado'];

    $nuevoEstado = ($estadoActual === 1) ? 0 : 1;

    $update = $conn->prepare("UPDATE empleados SET activo = ? WHERE id_empleado = ?");
    $update->execute([$nuevoEstado, $id]);

    // Evita reenvíos al recargar
    header("Location: empleados.php");
    exit;
}

// Cargar empleados 
$stmt = $conn->query("SELECT * FROM empleados ORDER BY id_empleado ASC");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papeleria Arcoiris</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;500;700&display=swap" rel="stylesheet">

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

                <div class="nav-tabs-container">
                    <a href="productos.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/punto-venta.svg" class="icon-sm">
                        <span>Punto de Venta</span>
                    </a>

                    <a href="empleados.php" class="text-nav-item nav-tab-item nav-tab-item--active">
                        <img src="public/images/empleados.svg" class="icon-sm">
                        <span>Empleados</span>
                        <span class="text-nav-badge nav-notification">●</span>
                    </a>

                    <a href="inventario.php" class="text-nav-item nav-tab-item">
                        <img src="public/images/box.svg" class="icon-sm">
                        <span>Inventario</span>
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
                        <img src="public/images/empleados.svg" class="icon-lg">
                    </div>
                    <div>
                        <h1 class="filter-title">Gestión de Empleados</h1>
                        <p class="filter-subtitle">Administra el personal de la papelería</p>
                    </div>
                </div>

                <a href="#" id="btnNuevoEmpleado" class="filter-button-new">
                    <img src="public/images/cruz.svg" class="icon-sm">
                    <span>Nuevo Empleado</span>
                </a>
            </div>

            <div class="filter-controls">
                <div class="filter-search-wrapper">
                    <input type="text" placeholder="Buscar empleados..." class="filter-search-input"
                        id="searchEmpleado">
                    <img src="public/images/lupa.svg" class="filter-search-icon icon-sm">
                </div>

                <select id="filtroRol" class="select-dropdown">
                    <option value="todos">Todos los roles</option>
                    <option value="administrador">Administrador</option>
                    <option value="cajero">Cajero</option>
                </select>

                <select id="filtroEstado" class="filter-dropdown-select">
                    <option value="todos">Todos</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>
        </section>

        <section class="employee-grid">
            <?php foreach ($empleados as $emp): ?>

                <?php
                // Avatar color según rol
                $avatarColor = ($emp['rol'] === "administrador")
                    ? "#FEE2E2"
                    : "#DBEAFE";

                // Etiqueta de rol
                $tagRol = ($emp['rol'] === "administrador")
                    ? "<span class='card__tag card__tag--admin'>Administrador</span>"
                    : "<span class='card__tag card__tag--cajero'>Cajero</span>";

                // Estado Dinámico
                $tagActivo = ($emp['activo'] == 1)
                    ? "<span class='card__tag card__tag--active'>Activo</span>"
                    : "<span class='card__tag card__tag--cajero'>Inactivo</span>";

                // Fecha
                $fecha = date("d/m/Y", strtotime($emp['fecha_contratacion']));
                ?>
                <article class="employee-card" data-estado="<?= $emp['activo'] == 1 ? 'activo' : 'inactivo' ?>">
                    <div class="card__header">
                        <div class="card__person">
                            <div class="card__avatar" style="background-color: <?= $avatarColor ?>;">
                                <img src="public/images/user.svg" alt="Avatar" class="icon-xl">
                            </div>
                            <div class="card__info">
                                <h3 class="card__name"><?= $emp['nombre'] . " " . $emp['apellidos'] ?></h3>
                                <span class="card__id">ID:
                                    EMP-<?= str_pad($emp['id_empleado'], 3, '0', STR_PAD_LEFT) ?></span>
                            </div>
                        </div>

                        <img src="public/images/<?= $emp['activo'] == 1 ? 'user_green.svg' : 'user_red.svg' ?>" alt="Status"
                            class="card__status-icon icon-xl">
                    </div>

                    <div class="card__body">
                        <div class="card__tags">
                            <?= $tagRol ?>
                            <?= $tagActivo ?>
                        </div>

                        <div class="card__details">
                            <div class="card__detail-item">
                                <img src="public/images/correo.svg" class="icon-sm">
                                <span><?= $emp['email'] ?></span>
                            </div>
                            <div class="card__detail-item">
                                <img src="public/images/telefono.svg" class="icon-sm">
                                <span><?= $emp['telefono'] ?></span>
                            </div>
                            <div class="card__detail-item">
                                <img src="public/images/calendario.svg" class="icon-sm">
                                <span><?= $fecha ?></span>
                            </div>
                            <div class="card__detail-item">
                                <img src="public/images/salario.svg" class="icon-sm">
                                <span>$<?= number_format($emp['salario'], 2) ?> MXN</span>
                            </div>
                        </div>
                    </div>

                    <div class="card__divider"></div>
                    <div class="card__footer">
                        <div class="card__actions">
                            <button class="card__button card__button--edit" data-id="<?= $emp['id_empleado'] ?>"
                                data-nombre="<?= $emp['nombre'] ?>" data-apellidos="<?= $emp['apellidos'] ?>"
                                data-email="<?= $emp['email'] ?>" data-telefono="<?= $emp['telefono'] ?>"
                                data-domicilio="<?= $emp['domicilio'] ?>" data-rfc="<?= $emp['rfc'] ?>"
                                data-rol="<?= $emp['rol'] ?>" data-salario="<?= $emp['salario'] ?>"
                                data-fecha="<?= $emp['fecha_contratacion'] ?>">
                                <img src="public/images/edit.svg" class="icon-sm">
                                <span>Editar</span>
                            </button>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $emp['id_empleado'] ?>">
                                <input type="hidden" name="estado" value="<?= $emp['activo'] ?>">

                                <button class="card__button card__button--deactivate">
                                    <img src="public/images/user_red.svg" class="icon-sm">
                                    <span><?= $emp['activo'] == 1 ? 'Desactivar' : 'Activar' ?></span>
                                </button>
                            </form>

                        </div>

                        <form action="eliminar_empleado.php" method="POST" style="display:inline;"
                            onsubmit="return confirm('¿Estás seguro de ELIMINAR permanentemente a este empleado?');">
                            <input type="hidden" name="id_eliminar" value="<?= $emp['id_empleado'] ?>">
                            <button type="submit" class="card__button card__button--delete">
                                <img src="public/images/eliminar.svg" class="icon-md">
                            </button>
                        </form>
                    </div>

                </article>
            <?php endforeach; ?>
        </section>

    </main>

    <!-- Modal empleado-->
    <div id="modalEmpleado" class="modal-overlay">
        <div class="modal">
            <h2>Nuevo Empleado</h2>

            <form action="agregar_empleado.php" method="POST">

                <label>Nombre:</label>
                <input type="text" name="nombre" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" required>

                <label>Correo Electrónico:</label>
                <input type="email" name="email" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono">

                <label>Domicilio:</label>
                <input type="text" name="domicilio">

                <label>RFC (opcional):</label>
                <input type="text" name="rfc">

                <label>Rol:</label>
                <select name="rol" required>
                    <option value="administrador">Administrador</option>
                    <option value="cajero">Cajero</option>
                </select>

                <label>Salario:</label>
                <input type="number" step="0.01" name="salario">

                <label>Usuario:</label>
                <input type="text" name="usuario" required>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <label>Fecha de contratación:</label>
                <input type="date" name="fecha_contratacion" required>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cerrar" id="btnCerrarModal">Cerrar</button>
                    <button type="submit" class="btn btn-guardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal-overlay">
        <div class="modal">
            <h2>Editar Empleado</h2>

            <form action="editar_empleado.php" method="POST">

                <input type="hidden" name="id" id="edit_id">

                <label>Nombre:</label>
                <input type="text" name="nombre" id="edit_nombre" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" id="edit_apellidos" required>

                <label>Correo:</label>
                <input type="email" name="email" id="edit_email" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" id="edit_telefono">

                <label>Domicilio:</label>
                <input type="text" name="domicilio" id="edit_domicilio">

                <label>RFC:</label>
                <input type="text" name="rfc" id="edit_rfc">

                <label>Rol:</label>
                <select name="rol" id="edit_rol">
                    <option value="administrador">Administrador</option>
                    <option value="cajero">Cajero</option>
                </select>

                <label>Salario:</label>
                <input type="number" step="0.01" name="salario" id="edit_salario">

                <label>Fecha de contratación:</label>
                <input type="date" name="fecha_contratacion" id="edit_fecha">

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cerrar" id="cerrarEditar">Cerrar</button>
                    <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        const $ = s => document.querySelector(s), $$ = s => document.querySelectorAll(s);
        // 1. MODALES
        $('#btnNuevoEmpleado').onclick = e => { e.preventDefault(); $('#modalEmpleado').style.display = 'flex'; };

        $$('.card__button--edit').forEach(b => b.onclick = () => {
            // Rellena inputs si existen (?. evita error si no encuentra el input)
            Object.keys(b.dataset).forEach(k => $('#edit_' + k) ? $('#edit_' + k).value = b.dataset[k] : null);
            $('#modalEditar').style.display = 'flex';
        });

        window.onclick = e => { // Cierra si click en overlay o botón cerrar
            if (e.target.matches('.modal-overlay, .btn-cerrar')) $$('.modal-overlay').forEach(m => m.style.display = 'none');
        };
        // 2. FILTROS
        const filtrar = () => {
            const txt = $('#searchEmpleado').value.toLowerCase(), rol = $('#filtroRol').value, est = $('#filtroEstado').value;
            $$('.employee-card').forEach(c => {
                const content = c.innerText.toLowerCase();
                const show = content.includes(txt) && (rol === 'todos' || content.includes(rol)) && (est === 'todos' || c.dataset.estado === est);
                c.style.display = show ? 'flex' : 'none';
            });
        };

        $('#searchEmpleado').oninput = $('#filtroRol').onchange = $('#filtroEstado').onchange = filtrar;
    </script>

    <?php if (isset($_GET['deleted'])): ?>
        <script>
            // 1. Muestra la alerta
            alert("Empleado eliminado correctamente");
            
            // 2. Limpia la URL
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        </script>
    <?php endif; ?>

</body>

</html>