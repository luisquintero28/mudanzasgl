<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
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
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar usuario
    $sql = "INSERT INTO usuarios (cedula, nombre, contrasena) 
            VALUES (:cedula, :nombre, :contrasena)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':cedula' => $cedula,
        ':nombre' => $nombre,
        ':contrasena' => $hash
    ]);

    // Redirigir al login
    header("Location: ../public/loginGL.php");
    exit;
}
?>