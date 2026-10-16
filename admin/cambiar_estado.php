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
$mudanza_id = (int)($data['mudanza_id'] ?? 0);
$estado     = $data['estado'] ?? '';

$validos = ['programada','en_proceso','completada','cancelada'];
if (!$mudanza_id || !in_array($estado, $validos)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("UPDATE mudanzas SET estado = :estado WHERE id = :id");
    $stmt->execute([':estado' => $estado, ':id' => $mudanza_id]);

    if ($estado === 'completada') {
        $sqlInfo = "SELECT m.valor, m.colaborador_id, c.porcentaje_comision
                    FROM mudanzas m
                    INNER JOIN colaboradores c ON m.colaborador_id = c.id
                    WHERE m.id = :id";
        $stmtInfo = $conexion->prepare($sqlInfo);
        $stmtInfo->execute([':id' => $mudanza_id]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if (!$info) {
            throw new Exception('No se encontró la mudanza o el colaborador');
        }

        $valor_servicio    = (float)$info['valor'];
        $porcentaje        = (float)$info['porcentaje_comision'];
        $valor_comision    = round($valor_servicio * ($porcentaje / 100), 2);
        $valor_colaborador = round($valor_servicio - $valor_comision, 2);

        $stmtDup = $conexion->prepare("SELECT id FROM comisiones WHERE mudanza_id = :id");
        $stmtDup->execute([':id' => $mudanza_id]);

        if ($stmtDup->rowCount() === 0) {
            $stmtCom = $conexion->prepare(
                "INSERT INTO comisiones 
                 (mudanza_id, colaborador_id, valor_servicio, porcentaje, valor_comision, valor_colaborador, estado)
                 VALUES 
                 (:mudanza_id, :colaborador_id, :valor_servicio, :porcentaje, :valor_comision, :valor_colaborador, 'pendiente')"
            );
            $stmtCom->execute([
                ':mudanza_id'       => $mudanza_id,
                ':colaborador_id'   => $info['colaborador_id'],
                ':valor_servicio'   => $valor_servicio,
                ':porcentaje'       => $porcentaje,
                ':valor_comision'   => $valor_comision,
                ':valor_colaborador'=> $valor_colaborador
            ]);
        }
    }

    $conexion->commit();
    echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado']);

} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}