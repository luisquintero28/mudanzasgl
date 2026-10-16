<?php
require 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $contrasena = $_POST['contrasena'];

    // Buscar usuario
    $sql = "SELECT * FROM usuarios WHERE cedula = :cedula";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':cedula' => $cedula]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        // Evitar fijación de sesión: regenerar el ID al autenticar
        session_regenerate_id(true);

        // Iniciar sesión
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol']    = $usuario['rol'];

        // Redirigir según el rol
        if ($usuario['rol'] === 'admin') {
            header("Location: ../admin/panel.php");
        } elseif ($usuario['rol'] === 'colaborador') {
            header("Location: ../colaborador/portal.php");
        } else {
            header("Location: ../public/mudanzasGL.php");
        }
        exit;

    } else {
        header("Location: ../public/loginGL.php?error=1");
        exit;
    }
}
?>