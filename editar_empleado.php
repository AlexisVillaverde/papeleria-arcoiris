<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $rfc = $_POST['rfc'];
    $rol = $_POST['rol'];
    $salario = $_POST['salario'];
    $fecha_contratacion = $_POST['fecha_contratacion'];

    try {
        $sql = "UPDATE empleados SET 
                nombre = :nombre, 
                apellidos = :apellidos, 
                email = :email, 
                telefono = :telefono, 
                domicilio = :domicilio, 
                rfc = :rfc, 
                rol = :rol, 
                salario = :salario, 
                fecha_contratacion = :fecha
                WHERE id_empleado = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':email' => $email,
            ':telefono' => $telefono,
            ':domicilio' => $domicilio,
            ':rfc' => $rfc,
            ':rol' => $rol,
            ':salario' => $salario,
            ':fecha' => $fecha_contratacion,
            ':id' => $id
        ]);

        header("Location: empleados.php?success=1");
        exit();

    } catch (PDOException $e) {
        echo "Error al actualizar: " . $e->getMessage();
    }
}
?>