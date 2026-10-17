<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'] ?? null;
    $contrasena = $_POST['contrasena'];

    // Verificar si el usuario ya existe
    $sql = "SELECT * FROM usuarios WHERE cedula = :cedula";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':cedula' => $cedula]);

    if ($stmt->rowCount() > 0) {
        echo "El usuario ya existe";
        exit;
    }

    // Encriptar contrasena
    $sql = "INSERT INTO usuarios (cedula, nombre, correo, contrasena) 
        VALUES (:cedula, :nombre, :correo, :contrasena)";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':cedula' => $cedula,
    ':nombre' => $nombre,
    ':correo' => $correo,
    ':contrasena' => $hash
]);

    // Redirigir al login
    header("Location: ../public/loginGL.php");
    exit;
}
?>