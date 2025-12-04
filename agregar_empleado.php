<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $rfc = $_POST['rfc'] ?: null;
    $rol = $_POST['rol'];
    $salario = $_POST['salario'];
    $usuario = $_POST['usuario'];

    // HASH de la contraseña
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $fecha_contratacion = $_POST['fecha_contratacion'];

    try {

        $sql = "INSERT INTO empleados 
            (nombre, apellidos, email, telefono, domicilio, rfc, rol, salario, usuario, password, fecha_contratacion)
            VALUES 
            (:nombre, :apellidos, :email, :telefono, :domicilio, :rfc, :rol, :salario, :usuario, :password, :fecha)";

        // NOTA: AHORA USAMOS $conn, NO $conexion
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
            ':usuario' => $usuario,
            ':password' => $password_hash,
            ':fecha' => $fecha_contratacion
        ]);

        header("Location: empleados.php?success=1");
        exit();

    } catch (PDOException $e) {
        echo "Error al guardar: " . $e->getMessage();
    }
}
?>
