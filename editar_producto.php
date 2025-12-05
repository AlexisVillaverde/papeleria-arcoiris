<?php
require_once "conexion.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validaciones
    $errores = [];

    if (empty($id) || !is_numeric($id)) {
        $errores[] = 'ID de producto inválido';
    }

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
        header("Location: inventario.php?error=" . urlencode($mensaje));
        exit;
    }

    try {
        $sql = "UPDATE productos SET nombre = ?, codigo_barras = ?, id_categoria = ?, stock = ?, precio = ?, descripcion = ? WHERE id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre, $codigo_barras, $categoria, $stock, $precio, $descripcion, $id]);

        header("Location: inventario.php?updated=1");
        exit;
    } catch (PDOException $e) {
        error_log("Error al actualizar producto: " . $e->getMessage());
        header("Location: inventario.php?error=Error en la base de datos");
        exit;
    }
}

header("Location: inventario.php");
exit;
