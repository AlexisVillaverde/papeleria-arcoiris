<?php
require_once "conexion.php";
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['stock'])) {
    $id = $_POST['id'] ?? '';
    $stock = $_POST['stock'] ?? '';

    // Validaciones
    if (empty($id) || !is_numeric($id) || $id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
        exit;
    }

    if (!is_numeric($stock) || $stock < 0) {
        echo json_encode(['success' => false, 'error' => 'El stock debe ser un número mayor o igual a 0']);
        exit;
    }

    if ($stock > 999999) {
        echo json_encode(['success' => false, 'error' => 'El stock no puede exceder 999,999 unidades']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE productos SET stock = ?, fecha_actualizacion = NOW() WHERE id_producto = ?");
        $stmt->execute([(int)$stock, (int)$id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
}
