<?php
session_start();
require '../app/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$comision_id = (int)($data['comision_id'] ?? 0);

if (!$comision_id) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = $conexion->prepare(
        "UPDATE comisiones SET estado = 'pagada' WHERE id = :id AND estado = 'pendiente'"
    );
    $stmt->execute([':id' => $comision_id]);

    echo json_encode(['ok' => true, 'mensaje' => 'Comisión marcada como pagada']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}