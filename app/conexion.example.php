<?php
$host = '127.0.0.1';
$dbname = 'mudanzasgl';
$username = 'root';
$password = 'TU_CONTRASENA_AQUI';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>