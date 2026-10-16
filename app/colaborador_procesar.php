<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre              = $_POST['nombre'];
    $cedula              = $_POST['cedula'];
    $telefono            = $_POST['telefono'];
    $correo              = $_POST['correo'];
    $tipo_vehiculo       = $_POST['tipo_vehiculo'];
    $placa_vehiculo      = $_POST['placa_vehiculo'];
    $capacidad_toneladas = $_POST['capacidad_toneladas'];
    $contrasena          = $_POST['contrasena'] ?? '';

    $stmt = $conexion->prepare("SELECT * FROM colaboradores WHERE cedula = :cedula");
    $stmt->execute([':cedula' => $cedula]);
    if ($stmt->rowCount() > 0) { echo "El colaborador ya existe"; exit; }

    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE cedula = :cedula");
    $stmt->execute([':cedula' => $cedula]);
    if ($stmt->rowCount() > 0) { echo "Ya existe un usuario con esa cédula"; exit; }

    if (empty($contrasena)) { echo "La contraseña es obligatoria"; exit; }

    try {
        $conexion->beginTransaction();

        $sql = "INSERT INTO colaboradores 
                (cedula, nombre, telefono, correo, tipo_vehiculo, placa_vehiculo, capacidad_toneladas) 
                VALUES (:cedula, :nombre, :telefono, :correo, :tipo_vehiculo, :placa_vehiculo, :capacidad_toneladas)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':cedula'              => $cedula,
            ':nombre'              => $nombre,
            ':telefono'            => $telefono,
            ':correo'              => $correo,
            ':tipo_vehiculo'       => $tipo_vehiculo,
            ':placa_vehiculo'      => $placa_vehiculo,
            ':capacidad_toneladas' => $capacidad_toneladas
        ]);

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (cedula, nombre, correo, contrasena, rol) 
                VALUES (:cedula, :nombre, :correo, :contrasena, 'colaborador')";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':cedula'     => $cedula,
            ':nombre'     => $nombre,
            ':correo'     => $correo,
            ':contrasena' => $hash
        ]);

        $conexion->commit();
        header("Location: ../public/loginGL.php");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>