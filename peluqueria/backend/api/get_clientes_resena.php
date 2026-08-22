<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    $stmt = $pdo->query("
        SELECT
            u.id,
            u.nombre,
            u.email,
            u.telefono,
            u.resena_email_enviado,
            MAX(r.fecha) AS ultima_visita,
            COUNT(r.id) AS visitas_completadas
        FROM usuarios u
        JOIN reservas r ON r.user_id = u.id AND r.estado = 'COMPLETADA'
        WHERE u.activo = 1 AND u.rol = 'usuario'
          AND u.email IS NOT NULL AND u.email != ''
        GROUP BY u.id
        ORDER BY u.resena_email_enviado ASC, ultima_visita DESC
    ");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($clientes as &$c) {
        $c['resena_email_enviado'] = (bool) $c['resena_email_enviado'];
    }
    unset($c);

    $pendientes = count(array_filter($clientes, function ($c) { return !$c['resena_email_enviado']; }));

    ob_clean();
    echo json_encode([
        'success'    => true,
        'clientes'   => $clientes,
        'pendientes' => $pendientes,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
