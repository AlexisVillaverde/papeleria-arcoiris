<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_eliminar'])) {
    $id = $_POST['id_eliminar'];

    try {
        $stmt = $conn->prepare("DELETE FROM empleados WHERE id_empleado = ?");
        $stmt->execute([$id]);
        
        // Redirige avisando que se borró
        header("Location: empleados.php?deleted=1");
    } catch (PDOException $e) {
        // En caso de error (ej: si el empleado tiene ventas asociadas)
        header("Location: empleados.php?error=no_se_puede_borrar");
    }
    exit;
}
?>