<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';
header('Content-Type: application/json');

try {
    // 1. Estadísticas Rápidas (Hoy y Total Clientes)
    // ---------------------------------------------------------
    $hoy_db = date('Y-m-d');
    
    // Citas para hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE fecha = ? AND estado != 'cancelado'");
    $stmt->execute([$hoy_db]);
    $stats_hoy = $stmt->fetchColumn();

    // Total clientes únicos
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'usuario' and activo = 1");
    $stats_clientes = $stmt->fetchColumn();

    // Usuarios activos reales (excluye emails sin cuenta y cuentas de sistema)
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM usuarios 
        WHERE activo = 1 
          AND rol = 'usuario' 
          AND email NOT LIKE '%sinemail@%'
    ");
    $stats_usuarios_activos = $stmt->fetchColumn();

    // 2. Próximas 5 Reservas (Para la tablita lateral)
    // ---------------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT r.id, r.fecha, r.hora, u.nombre as cliente, s.nombre as servicio 
        FROM reservas r
        JOIN usuarios u ON r.user_id = u.id
        JOIN servicios s ON r.servicio_id = s.id
        WHERE r.fecha >= ? AND r.estado != 'cancelado'
        ORDER BY r.fecha ASC, r.hora ASC
        LIMIT 5
    ");
    $stmt->execute([$hoy_db]);
    $proximas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Distribución de los próximos 7 días (Para el gráfico)
    // ---------------------------------------------------------
    $distribucion = [];
    $nombres_dias = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];

    for ($i = 0; $i < 7; $i++) {
        $fecha_iterada = date('Y-m-d', strtotime("+$i days"));
        $timestamp = strtotime($fecha_iterada);
        
        // Consultamos cuántas citas hay ese día concreto
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE fecha = ? AND estado != 'cancelado'");
        $stmt->execute([$fecha_iterada]);
        $cantidad = $stmt->fetchColumn();

        $distribucion[] = [
            "nombreDia" => $nombres_dias[date('w', $timestamp)],
            "soloFecha" => date('d M', $timestamp),
            "cantidad"  => (int)$cantidad,
            "esHoy"     => ($i === 0)
        ];
    }

    // 4. Servicio más solicitado (Ranking)
    // ---------------------------------------------------------
    $stmt = $pdo->query("
        SELECT s.nombre, COUNT(r.id) as total, s.precio
        FROM reservas r
        JOIN servicios s ON r.servicio_id = s.id
        WHERE r.estado != 'cancelado'
        GROUP BY r.servicio_id
        ORDER BY total DESC
        LIMIT 3
    ");
    $ranking_servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Servicio estrella (el primero del ranking)
    $servicio_estrella = $ranking_servicios[0]['nombre'] ?? 'Ninguno';

    // Ajustamos el json_encode final para incluir estos datos
    echo json_encode([
        "stats" => [
            "hoy"             => $stats_hoy,
            "clientes"        => $stats_clientes,
            "estrella"        => $servicio_estrella,
            "usuarios_activos" => $stats_usuarios_activos
        ],
        "proximas_reservas" => $proximas_reservas,
        "distribucion_semanal" => $distribucion,
        "ranking_servicios" => $ranking_servicios
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}