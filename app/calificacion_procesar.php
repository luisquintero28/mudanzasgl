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
$mudanza_id = (int)($data['mudanza_id'] ?? 0);
$estrellas  = (int)($data['estrellas']  ?? 0);
$comentario = trim($data['comentario'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if (!$mudanza_id || $estrellas < 1 || $estrellas > 5) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = $conexion->prepare(
        "SELECT id FROM mudanzas 
         WHERE id = :id AND usuario_id = :uid AND estado = 'completada'"
    );
    $stmt->execute([':id' => $mudanza_id, ':uid' => $usuario_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'No puedes calificar esta mudanza']);
        exit;
    }

    $stmt = $conexion->prepare(
        "SELECT id FROM calificaciones WHERE mudanza_id = :id AND usuario_id = :uid"
    );
    $stmt->execute([':id' => $mudanza_id, ':uid' => $usuario_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'Ya calificaste esta mudanza']);
        exit;
    }

    $stmt = $conexion->prepare(
        "INSERT INTO calificaciones (mudanza_id, usuario_id, estrellas, comentario)
         VALUES (:mid, :uid, :est, :com)"
    );
    $stmt->execute([
        ':mid' => $mudanza_id,
        ':uid' => $usuario_id,
        ':est' => $estrellas,
        ':com' => $comentario
    ]);

    // 🔔 Notificar al colaborador
    require_once 'notificacion_crear.php';
    $stmtColab = $conexion->prepare(
        "SELECT (SELECT id FROM usuarios WHERE cedula = c.cedula AND rol = 'colaborador' LIMIT 1) AS colab_usuario_id
         FROM mudanzas m
         LEFT JOIN colaboradores c ON m.colaborador_id = c.id
         WHERE m.id = :mid"
    );
    $stmtColab->execute([':mid' => $mudanza_id]);
    $colab = $stmtColab->fetch(PDO::FETCH_ASSOC);

    if (!empty($colab['colab_usuario_id'])) {
        $estrellasTxt = str_repeat('⭐', $estrellas);
        crearNotificacion($conexion, $colab['colab_usuario_id'], 
            "⭐ Recibiste una nueva calificación: $estrellasTxt");
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Calificación guardada']);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}