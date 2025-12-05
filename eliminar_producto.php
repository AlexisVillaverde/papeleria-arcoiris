<?php
require_once "conexion.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'] ?? '';

    // Validaciones
    if (empty($id) || !is_numeric($id) || $id <= 0) {
        header("Location: inventario.php?error=ID de producto inválido");
        exit;
    }

    try {
        // Verificar si el producto existe
        $check = $conn->prepare("SELECT id_producto FROM productos WHERE id_producto = ?");
        $check->execute([(int)$id]);

        if (!$check->fetch()) {
            header("Location: inventario.php?error=El producto no existe");
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->execute([(int)$id]);

        // Reiniciar AUTO_INCREMENT después de eliminar
        $maxStmt = $conn->query("SELECT MAX(id_producto) as max_id FROM productos");
        $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);
        $nextId = ($maxResult['max_id'] ?? 0) + 1;
        $conn->exec("ALTER TABLE productos AUTO_INCREMENT = $nextId");

        header("Location: inventario.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        header("Location: inventario.php?error=Error al eliminar el producto");
        exit;
    }
}

header("Location: inventario.php");
exit;
