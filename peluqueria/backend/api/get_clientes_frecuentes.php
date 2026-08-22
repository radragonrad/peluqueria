<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    $limitParam = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $sinLimite = $limitParam === 0;

    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.nombre,
            u.telefono,
            u.email,
            COUNT(CASE WHEN r.estado = 'COMPLETADA' THEN 1 END) AS completadas,
            COUNT(CASE WHEN r.estado IN ('ANULADA LOCAL', 'ANULADA WEB') THEN 1 END) AS anuladas,
            COUNT(r.id) AS total_reservas,
            ROUND(
                SUM(CASE WHEN r.estado = 'COMPLETADA' THEN s.precio ELSE 0 END),
                2
            ) AS gasto_total,
            ROUND(
                SUM(CASE WHEN r.estado = 'COMPLETADA' THEN s.precio ELSE 0 END)
                / NULLIF(COUNT(CASE WHEN r.estado = 'COMPLETADA' THEN 1 END), 0),
                2
            ) AS gasto_medio,
            MAX(CASE WHEN r.estado = 'COMPLETADA' THEN r.fecha END) AS ultima_visita,
            DATEDIFF(CURDATE(), MAX(CASE WHEN r.estado = 'COMPLETADA' THEN r.fecha END)) AS dias_sin_visitar,
            ROUND(
                DATEDIFF(
                    MAX(CASE WHEN r.estado = 'COMPLETADA' THEN r.fecha END),
                    MIN(CASE WHEN r.estado = 'COMPLETADA' THEN r.fecha END)
                ) / NULLIF(COUNT(CASE WHEN r.estado = 'COMPLETADA' THEN 1 END) - 1, 0),
                0
            ) AS frecuencia_dias,
            (
                SELECT s2.nombre
                FROM reservas r2
                JOIN servicios s2 ON r2.servicio_id = s2.id
                WHERE r2.user_id = u.id
                GROUP BY r2.servicio_id
                ORDER BY COUNT(*) DESC
                LIMIT 1
            ) AS servicio_favorito,
            (
                SELECT up.nombre
                FROM reservas r2
                JOIN peluqueros p ON r2.peluquero_id = p.id
                JOIN usuarios up ON p.usuario_id = up.id
                WHERE r2.user_id = u.id AND r2.estado = 'COMPLETADA'
                GROUP BY r2.peluquero_id
                ORDER BY COUNT(*) DESC
                LIMIT 1
            ) AS peluquero_favorito,
            (
                SELECT COALESCE(SUM(cu.cupones_actuales), 0)
                FROM cupones_usuario cu
                WHERE cu.usuario_id = u.id
            ) AS cupones_actuales
        FROM usuarios u
        LEFT JOIN reservas r ON r.user_id = u.id
        LEFT JOIN servicios s ON r.servicio_id = s.id
        WHERE u.rol = 'usuario'
        GROUP BY u.id        
       ORDER BY gasto_total DESC, dias_sin_visitar DESC
        " . ($sinLimite ? '' : 'LIMIT ?') . "
    ");

    $stmt->execute($sinLimite ? [] : [$limitParam]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'clientes' => $clientes]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
