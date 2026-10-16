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
    $sql = "SELECT m.id, m.tipo, m.tamano, m.origen, m.destino, 
                   m.fecha_servicio, m.valor, m.estado,
                   c.estrellas AS calificacion_estrellas,
                   p.estado AS pago_estado,
                   p.metodo_pago AS pago_metodo,
                   p.id AS pago_id
            FROM mudanzas m
            LEFT JOIN calificaciones c ON c.mudanza_id = m.id AND c.usuario_id = :uid
            LEFT JOIN pagos p ON p.mudanza_id = m.id AND p.usuario_id = :uid2
            WHERE m.usuario_id = :uid3
            ORDER BY m.fecha_servicio DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':uid' => $usuario_id, ':uid2' => $usuario_id, ':uid3' => $usuario_id]);
    $mudanzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'mudanzas' => $mudanzas]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}