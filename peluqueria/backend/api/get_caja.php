<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

header('Content-Type: application/json');

$periodo = $_GET['periodo'] ?? 'hoy';
$fecha_custom = $_GET['fecha'] ?? null;

// --- 1. CONFIGURACIÓN DE FILTROS DE FECHA (Lógica Mejorada) ---
$date_filter_reservas = "";
$date_filter_movimientos = "";

if (!empty($fecha_custom)) {
    // Filtro por día específico (Calendario)
    $date_filter_reservas = " AND r.fecha = '$fecha_custom'";
    $date_filter_movimientos = " AND DATE(fecha) = '$fecha_custom'";
} else {
    switch ($periodo) {
        case 'hoy':
            $date_filter_reservas = " AND r.fecha = CURDATE()";
            $date_filter_movimientos = " AND DATE(fecha) = CURDATE()";
            break;
        case 'semana':
            $date_filter_reservas = " AND YEARWEEK(r.fecha, 1) = YEARWEEK(CURDATE(), 1)";
            $date_filter_movimientos = " AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'mes':
            $date_filter_reservas = " AND MONTH(r.fecha) = MONTH(CURDATE()) AND YEAR(r.fecha) = YEAR(CURDATE())";
            $date_filter_movimientos = " AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
            break;
        case 'mes_pasado':
            // Lógica para el mes anterior
            $primer_dia_pasado = date('Y-m-01', strtotime('first day of last month'));
            $ultimo_dia_pasado = date('Y-m-t', strtotime('last day of last month'));
            
            $date_filter_reservas = " AND r.fecha BETWEEN '$primer_dia_pasado' AND '$ultimo_dia_pasado'";
            $date_filter_movimientos = " AND DATE(fecha) BETWEEN '$primer_dia_pasado' AND '$ultimo_dia_pasado'";
            break;
    }
}

// --- 2. INGRESOS REALES (RESERVAS COMPLETADAS) ---
$query_reservas = "SELECT SUM(s.precio) as total 
                   FROM reservas r 
                   JOIN servicios s ON r.servicio_id = s.id 
                   WHERE r.estado = 'COMPLETADA' $date_filter_reservas";
$ingresos_reservas = (float)($pdo->query($query_reservas)->fetch()['total'] ?? 0);

// --- 3. INGRESOS PREVISTOS (PRÓXIMAS CITAS) ---
$query_pendientes = "SELECT SUM(s.precio) as total 
                     FROM reservas r 
                     JOIN servicios s ON r.servicio_id = s.id 
                     WHERE r.estado = 'PENDIENTE' $date_filter_reservas";
$ingresos_pendientes = (float)($pdo->query($query_pendientes)->fetch()['total'] ?? 0);

// --- 4. MOVIMIENTOS MANUALES (LISTADO) ---
$query_movs = "SELECT * FROM movimientos_caja WHERE 1=1 $date_filter_movimientos ORDER BY fecha DESC";
$movimientos = $pdo->query($query_movs)->fetchAll(PDO::FETCH_ASSOC);

// --- 5. TOTALES DE MOVIMIENTOS MANUALES ---
$query_manuales = "SELECT 
    SUM(CASE WHEN tipo = 'INGRESO' THEN importe ELSE 0 END) as ingresos_manuales,
    SUM(CASE WHEN tipo = 'GASTO' THEN importe ELSE 0 END) as gastos_manuales
    FROM movimientos_caja WHERE 1=1 $date_filter_movimientos";
$totales_manuales = $pdo->query($query_manuales)->fetch();

$ingresos_manuales = (float)($totales_manuales['ingresos_manuales'] ?? 0);
$gastos_totales = (float)($totales_manuales['gastos_manuales'] ?? 0);

// --- 6. DESGLOSE POR MÉTODO DE PAGO ---
// Reservas
$query_metodos_res = "SELECT r.metodo_pago, SUM(s.precio) as total 
                      FROM reservas r JOIN servicios s ON r.servicio_id = s.id 
                      WHERE r.estado = 'COMPLETADA' $date_filter_reservas 
                      GROUP BY r.metodo_pago";
$res_reservas = $pdo->query($query_metodos_res)->fetchAll(PDO::FETCH_KEY_PAIR);

// Manuales
$query_metodos_man = "SELECT metodo_pago, SUM(importe) as total 
                      FROM movimientos_caja 
                      WHERE tipo = 'INGRESO' $date_filter_movimientos 
                      GROUP BY metodo_pago";
$res_manuales = $pdo->query($query_metodos_man)->fetchAll(PDO::FETCH_KEY_PAIR);

// Unificar métodos (aseguramos minúsculas para coincidir con el CSS)
$metodos_validos = ['efectivo', 'tarjeta', 'bizzum', 'deuda'];
$desglose_pagos = [];

foreach ($metodos_validos as $m) {
    // Sumamos lo de reservas + manuales buscando la clave en cualquier combinación de mayúsculas
    $suma = 0;
    foreach($res_reservas as $key => $val) { if(strtolower($key) == $m) $suma += $val; }
    foreach($res_manuales as $key => $val) { if(strtolower($key) == $m) $suma += $val; }
    
    $desglose_pagos[$m] = (float)$suma;
}

// --- 7. CÁLCULOS FINALES PARA EL FRONTEND ---
// El frontend espera recibir la deuda por separado para restarla del "Ingreso Real"
$monto_deuda = $desglose_pagos['deuda'] ?? 0;
$ingresos_reales_totales = ($ingresos_reservas + $ingresos_manuales) - $monto_deuda;

echo json_encode([
    'ingresos_reservas'   => $ingresos_reservas,
    'ingresos_pendientes' => $ingresos_pendientes,
    'ingresos_manuales'   => $ingresos_manuales,
    'gastos_totales'      => $gastos_totales,
    'desglose_pagos'      => $desglose_pagos,
    'movimientos'         => $movimientos,
    'balance_neto'        => $ingresos_reales_totales - $gastos_totales
]);