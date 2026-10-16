<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
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

$usuario_id        = $_SESSION['usuario_id'];
$tipo              = $data['tipo']              ?? '';
$tamano            = $data['tamano']            ?? '';
$origen            = $data['origen']            ?? '';
$destino           = $data['destino']           ?? '';
$valor             = (float)($data['valor']     ?? 0);
$fecha_servicio    = $data['fecha_servicio']    ?? '';
$direccion_origen  = $data['direccion_origen']  ?? '';
$direccion_destino = $data['direccion_destino'] ?? '';
$contacto_nombre   = $data['contacto_nombre']   ?? '';
$contacto_telefono = $data['contacto_telefono'] ?? '';
$servicios         = $data['servicios']         ?? []; // array de {id, precio}

if ($tipo === '' || $tamano === '' || $origen === '' || $destino === '' || $fecha_servicio === '' || $valor <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

// Mapear tamaño numérico al enum de la tabla
$mapaTamano = [
    '1' => 'apto_pequeno',
    '2' => 'apto_grande',
    '3' => 'casa',
    '4' => 'oficina'
];
$tamanoEnum = $mapaTamano[$tamano] ?? 'apto_pequeno';

try {
    $conexion->beginTransaction();

    // Insertar en mudanzas
    $sql = "INSERT INTO mudanzas 
            (usuario_id, tipo, tamano, origen, destino, direccion_origen, direccion_destino,
             contacto_nombre, contacto_telefono, fecha_servicio, valor, estado)
            VALUES 
            (:usuario_id, :tipo, :tamano, :origen, :destino, :dir_origen, :dir_destino,
             :contacto_nombre, :contacto_telefono, :fecha_servicio, :valor, 'pendiente')";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':usuario_id'        => $usuario_id,
        ':tipo'              => $tipo,
        ':tamano'            => $tamanoEnum,
        ':origen'            => $origen,
        ':destino'           => $destino,
        ':dir_origen'        => $direccion_origen,
        ':dir_destino'       => $direccion_destino,
        ':contacto_nombre'   => $contacto_nombre,
        ':contacto_telefono' => $contacto_telefono,
        ':fecha_servicio'    => $fecha_servicio,
        ':valor'             => $valor
    ]);

    $mudanza_id = $conexion->lastInsertId();

    // Insertar los servicios adicionales elegidos
    if (!empty($servicios) && is_array($servicios)) {
        $stmtServ = $conexion->prepare(
            "INSERT INTO mudanza_servicios (mudanza_id, servicio_id, precio_aplicado)
             VALUES (:mudanza_id, :servicio_id, :precio)"
        );
        foreach ($servicios as $s) {
            $stmtServ->execute([
                ':mudanza_id'  => $mudanza_id,
                ':servicio_id' => (int)$s['id'],
                ':precio'      => (float)$s['precio']
            ]);
        }
    }

    $conexion->commit();

    echo json_encode([
        'ok'         => true,
        'mensaje'    => 'Mudanza agendada',
        'mudanza_id' => $mudanza_id
    ]);

} catch (PDOException $e) {
    $conexion->rollBack();
    error_log('mudanza_procesar: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo agendar la mudanza. Intenta de nuevo.']);
}