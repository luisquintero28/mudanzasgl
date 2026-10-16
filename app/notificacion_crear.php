<?php
/**
 * Ayudante para crear notificaciones.
 * Uso: crearNotificacion($conexion, $usuario_id, "Mensaje aquí");
 */
function crearNotificacion($conexion, $usuario_id, $mensaje) {
    try {
        $stmt = $conexion->prepare(
            "INSERT INTO notificaciones (usuario_id, mensaje, leida)
             VALUES (:uid, :msg, 0)"
        );
        $stmt->execute([
            ':uid' => $usuario_id,
            ':msg' => $mensaje
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error creando notificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Ayudante para crear notificación a TODOS los admins.
 */
function crearNotificacionAdmin($conexion, $mensaje) {
    try {
        $stmt = $conexion->query("SELECT id FROM usuarios WHERE rol = 'admin'");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($admins as $admin) {
            crearNotificacion($conexion, $admin['id'], $mensaje);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error creando notificación admin: " . $e->getMessage());
        return false;
    }
}
?>