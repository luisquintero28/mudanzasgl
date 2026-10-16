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
$pago_id = (int)($data['pago_id'] ?? 0);
$estado  = $data['estado'] ?? '';

$validos = ['pagado', 'rechazado', 'reembolsado'];

if (!$pago_id || !in_array($estado, $validos)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    // Traer datos del pago ANTES de actualizar
    $stmtInfo = $conexion->prepare(
        "SELECT p.usuario_id, p.monto, p.metodo_pago
         FROM pagos p WHERE p.id = :id"
    );
    $stmtInfo->execute([':id' => $pago_id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    // Actualizar el estado
    $stmt = $conexion->prepare(
        "UPDATE pagos SET estado = :estado WHERE id = :id"
    );
    $stmt->execute([':estado' => $estado, ':id' => $pago_id]);

    // 🔔 Notificar al cliente
    if ($info) {
        require_once '../app/notificacion_crear.php';
        $montoFmt = number_format($info['monto'], 0, ',', '.');
        if ($estado === 'pagado') {
            crearNotificacion($conexion, $info['usuario_id'], "✅ Tu pago de $montoFmt COP fue confirmado");
        } elseif ($estado === 'rechazado') {
            crearNotificacion($conexion, $info['usuario_id'], "❌ Tu pago de $montoFmt COP fue rechazado");
        } elseif ($estado === 'reembolsado') {
            crearNotificacion($conexion, $info['usuario_id'], "↩ Tu pago de $montoFmt COP fue reembolsado");
        }
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Pago actualizado']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}