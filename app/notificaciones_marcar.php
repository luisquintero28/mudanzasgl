<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $conexion->prepare(
        "UPDATE notificaciones SET leida = 1 WHERE usuario_id = :uid AND leida = 0"
    );
    $stmt->execute([':uid' => $usuario_id]);

    echo json_encode(['ok' => true, 'mensaje' => 'Notificaciones marcadas como leídas']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
?>