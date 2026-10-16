<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$mudanza_id = (int)($_GET['mudanza_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];
$rol        = $_SESSION['usuario_rol'];

if (!$mudanza_id) {
    echo json_encode(['ok' => false, 'mensaje' => 'Mudanza inválida']);
    exit;
}

try {
    // Verificar permisos según el rol
    $permitido = false;

    if ($rol === 'admin') {
        $permitido = true;
    } elseif ($rol === 'cliente') {
        $stmt = $conexion->prepare("SELECT id FROM mudanzas WHERE id = :mid AND usuario_id = :uid");
        $stmt->execute([':mid' => $mudanza_id, ':uid' => $usuario_id]);
        $permitido = $stmt->rowCount() > 0;
    } elseif ($rol === 'colaborador') {
        $stmt = $conexion->prepare(
            "SELECT m.id FROM mudanzas m
             INNER JOIN colaboradores c ON m.colaborador_id = c.id
             INNER JOIN usuarios u ON c.cedula = u.cedula
             WHERE m.id = :mid AND u.id = :uid"
        );
        $stmt->execute([':mid' => $mudanza_id, ':uid' => $usuario_id]);
        $permitido = $stmt->rowCount() > 0;
    }

    if (!$permitido) {
        echo json_encode(['ok' => false, 'mensaje' => 'Sin permisos']);
        exit;
    }

    // Traer fotos agrupadas por categoría
    $stmt = $conexion->prepare(
        "SELECT id, categoria, archivo, fecha
         FROM mudanza_fotos
         WHERE mudanza_id = :mid
         ORDER BY fecha ASC"
    );
    $stmt->execute([':mid' => $mudanza_id]);
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar
    $agrupadas = [
        'antes' => [],
        'durante' => [],
        'despues' => []
    ];

    foreach ($fotos as $f) {
        $agrupadas[$f['categoria']][] = $f;
    }

    echo json_encode([
        'ok' => true,
        'fotos' => $fotos,
        'agrupadas' => $agrupadas,
        'total' => count($fotos)
    ]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}