<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

$anio    = (int) date('Y');
$mes_hoy = (int) date('n');
$dia_hoy = (int) date('j');

// Construir cláusulas WHERE para los próximos 7 días (excluye hoy)
$proximosClauses = [];
for ($i = 1; $i <= 7; $i++) {
    $d = new DateTime();
    $d->modify("+$i days");
    $mes = (int) $d->format('n');
    $dia = (int) $d->format('j');
    $proximosClauses[] = "(MONTH(fecha_nacimiento) = $mes AND DAY(fecha_nacimiento) = $dia)";
}
$proximosWhere = implode(' OR ', $proximosClauses);

try {
    // Cumpleaños de hoy
    $stmtHoy = $pdo->prepare("
        SELECT id, nombre, email, telefono, fecha_nacimiento, fecha_nacimiento,
               TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
        FROM usuarios
        WHERE MONTH(fecha_nacimiento) = ? AND DAY(fecha_nacimiento) = ?
          AND activo = 1 AND rol = 'usuario'
          AND email IS NOT NULL AND email != ''
        ORDER BY nombre
    ");
    $stmtHoy->execute([$mes_hoy, $dia_hoy]);
    $hoy = $stmtHoy->fetchAll();

    foreach ($hoy as &$u) {
        $u['email_enviado'] = ((int) $u['fecha_nacimiento'] === $anio);
    }
    unset($u);

    // Próximos 7 días
    $proximos = [];
    if (!empty($proximosWhere)) {
        $stmtProx = $pdo->query("
            SELECT id, nombre, email, telefono, fecha_nacimiento, fecha_nacimiento,
                   TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) + 1 AS edad,
                   DATE_FORMAT(fecha_nacimiento, '%d/%m') AS dia_mes
            FROM usuarios
            WHERE ($proximosWhere)
              AND activo = 1 AND rol = 'usuario'
              AND email IS NOT NULL AND email != ''
            ORDER BY MONTH(fecha_nacimiento), DAY(fecha_nacimiento)
        ");
        $proximos = $stmtProx->fetchAll();
        foreach ($proximos as &$u) {
            $u['email_enviado'] = ((int) $u['fecha_nacimiento'] === $anio);
        }
        unset($u);
    }

    $pendientes = count(array_filter($hoy, function($u) { return !$u['email_enviado']; }));

    ob_clean();
    echo json_encode([
        'success'      => true,
        'hoy'          => $hoy,
        'proximos'     => $proximos,
        'total_hoy'    => count($hoy),
        'pendientes_hoy' => $pendientes,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
