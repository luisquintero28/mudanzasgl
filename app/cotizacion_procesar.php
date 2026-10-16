<?php
session_start();              // ← PRIMERO SIEMPRE
require 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado - sesión no iniciada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'JSON inválido']);
    exit;
}

$usuario_id     = $_SESSION['usuario_id'];
$tipo           = trim($data['tipo']    ?? '');
$tamano         = trim($data['tamano']  ?? '');
$origen         = trim($data['origen']  ?? '');
$destino        = trim($data['destino'] ?? '');
$valor_estimado = (float) ($data['valor'] ?? 0);

if ($tipo === '' || $tamano === '' || $origen === '' || $destino === '' || $valor_estimado <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

try {
    $sql = "INSERT INTO cotizaciones 
            (usuario_id, tipo, tamano, origen, destino, valor_estimado, fecha) 
            VALUES (:usuario_id, :tipo, :tamano, :origen, :destino, :valor_estimado, NOW())";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':usuario_id'     => $usuario_id,
        ':tipo'           => $tipo,
        ':tamano'         => $tamano,
        ':origen'         => $origen,
        ':destino'        => $destino,
        ':valor_estimado' => $valor_estimado
    ]);

    echo json_encode([
        'ok'      => true,
        'mensaje' => 'Cotización guardada',
        'id'      => $conexion->lastInsertId()
    ]);
} catch (PDOException $e) {
    error_log('cotizacion_procesar: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No se pudo guardar la cotización. Intenta de nuevo.'
    ]);
}