<?php
require_once "conexion.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug temporal
    error_log("POST recibido: " . print_r($_POST, true));

    $nombre = trim($_POST['nombre'] ?? '');
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validaciones
    $errores = [];

    if (empty($nombre)) {
        $errores[] = 'El nombre del producto es obligatorio';
    } elseif (strlen($nombre) > 100) {
        $errores[] = 'El nombre no puede exceder 100 caracteres';
    }

    if (empty($codigo_barras)) {
        $errores[] = 'El código de barras es obligatorio';
    } elseif (strlen($codigo_barras) > 50) {
        $errores[] = 'El código de barras no puede exceder 50 caracteres';
    }

    if (empty($categoria) || !is_numeric($categoria)) {
        $errores[] = 'Debe seleccionar una categoría válida';
    }

    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errores[] = 'El stock debe ser un número mayor o igual a 0';
    }

    if (empty($precio) || !is_numeric($precio) || $precio <= 0) {
        $errores[] = 'El precio debe ser un número mayor a 0';
    }

    if (strlen($descripcion) > 500) {
        $errores[] = 'La descripción no puede exceder 500 caracteres';
    }

    if (!empty($errores)) {
        $mensaje = implode(', ', $errores);
        error_log("Errores de validación: " . $mensaje);
        header("Location: inventario.php?error=" . urlencode($mensaje));
        exit;
    }

    error_log("Datos validados correctamente, intentando insertar...");

    try {
        // Obtener el primer ID de marca disponible o usar NULL
        $marcaStmt = $conn->query("SELECT id_marca FROM marcas LIMIT 1");
        $marca = $marcaStmt->fetch(PDO::FETCH_ASSOC);
        $id_marca = $marca ? $marca['id_marca'] : null;

        $sql = "INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock, stock_minimo, id_categoria, id_marca, activo) 
                VALUES (?, ?, ?, ?, ?, 5, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$codigo_barras, $nombre, $descripcion, $precio, $stock, $categoria, $id_marca]);

        // Reiniciar AUTO_INCREMENT para mantener IDs consecutivos
        $maxStmt = $conn->query("SELECT MAX(id_producto) as max_id FROM productos");
        $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);
        $nextId = ($maxResult['max_id'] ?? 0) + 1;
        $conn->exec("ALTER TABLE productos AUTO_INCREMENT = $nextId");

        header("Location: inventario.php?success=1");
        exit;
    } catch (PDOException $e) {
        error_log("Error al insertar producto: " . $e->getMessage());
        header("Location: inventario.php?error=" . urlencode("Error en la base de datos"));
        exit;
    }
}

header("Location: inventario.php");
exit;
