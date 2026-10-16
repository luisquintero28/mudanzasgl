<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'colaborador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Validaciones
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'mensaje' => 'No se recibió la imagen']);
    exit;
}

$mudanza_id = (int)($_POST['mudanza_id'] ?? 0);
$categoria  = $_POST['categoria'] ?? '';

$categorias_validas = ['antes', 'durante', 'despues'];

if (!$mudanza_id || !in_array($categoria, $categorias_validas)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

// Validar extensión y tamaño
$archivo    = $_FILES['foto'];
$nombre     = $archivo['name'];
$tmp        = $archivo['tmp_name'];
$tamano     = $archivo['size'];
$ext        = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

$extensiones_validas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($ext, $extensiones_validas)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Formato no válido. Solo JPG, PNG, WEBP o GIF']);
    exit;
}

// Validar que el contenido REAL sea una imagen (no solo la extensión del nombre)
$info_imagen = @getimagesize($tmp);
$mime_validos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if ($info_imagen === false || !in_array($info_imagen['mime'], $mime_validos)) {
    echo json_encode(['ok' => false, 'mensaje' => 'El archivo no es una imagen válida']);
    exit;
}

if ($tamano > 5 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'mensaje' => 'La imagen es muy grande. Máximo 5MB']);
    exit;
}

// Verificar que la mudanza pertenece al colaborador logueado
try {
    $stmt = $conexion->prepare(
        "SELECT m.id FROM mudanzas m
         INNER JOIN colaboradores c ON m.colaborador_id = c.id
         INNER JOIN usuarios u ON c.cedula = u.cedula
         WHERE m.id = :mid AND u.id = :uid"
    );
    $stmt->execute([':mid' => $mudanza_id, ':uid' => $_SESSION['usuario_id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para subir fotos de esta mudanza']);
        exit;
    }

    // Crear nombre único para el archivo
    $nombreArchivo = 'mudanza_' . $mudanza_id . '_' . $categoria . '_' . uniqid() . '.' . $ext;
    $rutaDestino   = '../uploads/mudanzas/' . $nombreArchivo;

    // Mover el archivo
    if (!move_uploaded_file($tmp, $rutaDestino)) {
        echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar la imagen']);
        exit;
    }

    // Insertar en la BD
    $stmt = $conexion->prepare(
        "INSERT INTO mudanza_fotos (mudanza_id, categoria, archivo)
         VALUES (:mid, :cat, :arch)"
    );
    $stmt->execute([
        ':mid'  => $mudanza_id,
        ':cat'  => $categoria,
        ':arch' => $nombreArchivo
    ]);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Foto subida correctamente',
        'id' => $conexion->lastInsertId(),
        'archivo' => $nombreArchivo
    ]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}