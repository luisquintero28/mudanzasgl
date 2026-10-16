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
$mudanza_id     = (int)($data['mudanza_id'] ?? 0);
$colaborador_id = (int)($data['colaborador_id'] ?? 0);

if (!$mudanza_id || !$colaborador_id) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos']);
    exit;
}

try {
    // Traer datos ANTES de actualizar (para notificar)
    $stmtInfo = $conexion->prepare(
        "SELECT m.usuario_id, m.origen, m.destino,
                c.nombre AS colaborador_nombre,
                (SELECT id FROM usuarios WHERE cedula = c.cedula AND rol = 'colaborador' LIMIT 1) AS colaborador_usuario_id
         FROM mudanzas m
         LEFT JOIN colaboradores c ON c.id = :colab
         WHERE m.id = :id"
    );
    $stmtInfo->execute([':colab' => $colaborador_id, ':id' => $mudanza_id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    // Actualizar la mudanza
    $stmt = $conexion->prepare(
        "UPDATE mudanzas 
         SET colaborador_id = :colab, estado = 'programada' 
         WHERE id = :id"
    );
    $stmt->execute([':colab' => $colaborador_id, ':id' => $mudanza_id]);

    // 🔔 Notificaciones
    if ($info) {
        require_once '../app/notificacion_crear.php';

        // Al cliente
        crearNotificacion($conexion, $info['usuario_id'], 
            "🚚 Tu mudanza {$info['origen']} → {$info['destino']} ya tiene colaborador asignado");

        // Al colaborador
        if (!empty($info['colaborador_usuario_id'])) {
            crearNotificacion($conexion, $info['colaborador_usuario_id'], 
                "📦 Nueva mudanza asignada: {$info['origen']} → {$info['destino']}");
        }
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Colaborador asignado']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}