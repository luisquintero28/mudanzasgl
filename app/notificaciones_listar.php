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
    // Traer últimas 15
    $stmt = $conexion->prepare(
        "SELECT id, mensaje, leida, fecha
         FROM notificaciones
         WHERE usuario_id = :uid
         ORDER BY fecha DESC
         LIMIT 15"
    );
    $stmt->execute([':uid' => $usuario_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar no leídas
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS total FROM notificaciones 
         WHERE usuario_id = :uid AND leida = 0"
    );
    $stmt->execute([':uid' => $usuario_id]);
    $sin_leer = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'ok' => true,
        'notificaciones' => $notificaciones,
        'sin_leer' => $sin_leer
    ]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
?>