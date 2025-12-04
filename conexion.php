<?php
// conexion.php

$host = 'localhost';
$dbname = 'papeleria_arco';
$username = 'root'; // Cambia esto si tienes otro usuario en tu servidor
$password = 'SaulGhost04';     // Cambia esto si tienes contraseña en tu servidor

try {
    // Creamos la conexión usando PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuramos PDO para que lance excepciones en caso de error (útil para depurar)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>