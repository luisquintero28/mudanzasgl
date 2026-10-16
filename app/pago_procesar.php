<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$usuario_id  = $_SESSION['usuario_id'];
$mudanza_id  = (int)($data['mudanza_id'] ?? 0);
$monto       = (float)($data['monto'] ?? 0);
$metodo_pago = $data['metodo_pago'] ?? '';
$referencia  = trim($data['referencia'] ?? '');

$metodos_validos = ['efectivo', 'transferencia', 'tarjeta', 'nequi', 'daviplata'];

if (!$mudanza_id || $monto <= 0 || !in_array($metodo_pago, $metodos_validos)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = $conexion->prepare(
        "SELECT id FROM mudanzas WHERE id = :id AND usuario_id = :uid"
    );
    $stmt->execute([':id' => $mudanza_id, ':uid' => $usuario_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'Mudanza no encontrada']);
        exit;
    }

    $stmt = $conexion->prepare(
        "SELECT id FROM pagos WHERE mudanza_id = :id AND estado IN ('pendiente','pagado')"
    );
    $stmt->execute([':id' => $mudanza_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'Ya registraste un pago para esta mudanza']);
        exit;
    }

    $stmt = $conexion->prepare(
        "INSERT INTO pagos (mudanza_id, usuario_id, monto, metodo_pago, estado, referencia)
         VALUES (:mid, :uid, :monto, :metodo, 'pendiente', :ref)"
    );
    $stmt->execute([
        ':mid'    => $mudanza_id,
        ':uid'    => $usuario_id,
        ':monto'  => $monto,
        ':metodo' => $metodo_pago,
        ':ref'    => $referencia
    ]);

    $pago_id = $conexion->lastInsertId();

    // 🔔 Notificar al admin
    require_once 'notificacion_crear.php';
    $montoFmt = number_format($monto, 0, ',', '.');
    crearNotificacionAdmin($conexion, "💳 Nuevo pago pendiente de $montoFmt COP por método " . strtoupper($metodo_pago));

    // ✅ Respuesta de éxito (faltaba: el frontend espera data.ok)
    echo json_encode([
        'ok'      => true,
        'mensaje' => 'Pago registrado correctamente',
        'id'      => $pago_id
    ]);

} catch (PDOException $e) {
    error_log('pago_procesar: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo registrar el pago. Intenta de nuevo.']);
}