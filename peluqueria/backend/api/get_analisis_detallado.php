<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

// Configurar idioma para fechas (opcional según el servidor)
setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');

$inicio = $_GET['inicio'];
$fin = $_GET['fin'];

try {
    // 1. Obtener conteos diferenciados por estado
    // Usamos LIKE 'ANULADA%' para capturar 'ANULADA' y 'ANULADA WEB'
    $stmt = $pdo->prepare("
        SELECT 
            fecha, 
            SUM(CASE WHEN estado IN ('PENDIENTE', 'COMPLETADA') THEN 1 ELSE 0 END) as validas,
            SUM(CASE WHEN estado LIKE 'ANULADA%' THEN 1 ELSE 0 END) as anuladas
        FROM reservas 
        WHERE fecha BETWEEN ? AND ? 
        GROUP BY fecha
    ");
    $stmt->execute([$inicio, $fin]);
    $datosDB = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $resultado = [];
    $current = new DateTime($inicio);
    $end = new DateTime($fin);
    $end->modify('+1 day'); 

    while ($current < $end) {
        $fechaKey = $current->format('Y-m-d');
        
        $v = isset($datosDB[$fechaKey]) ? (int)$datosDB[$fechaKey]['validas'] : 0;
        $a = isset($datosDB[$fechaKey]) ? (int)$datosDB[$fechaKey]['anuladas'] : 0;
        
        $hPico = null;
        $hValle = null;

        // Horas pico/valle por día (Solo si hay citas válidas)
        if ($v > 0) {
            // Hora Pico del día
            $stmtH = $pdo->prepare("SELECT hora FROM reservas WHERE fecha = ? AND estado NOT LIKE 'ANULADA%' GROUP BY hora ORDER BY COUNT(*) DESC, hora ASC LIMIT 1");
            $stmtH->execute([$fechaKey]);
            $res = $stmtH->fetch();
            $hPico = $res ? substr($res['hora'], 0, 5) : null;

            // Hora Valle del día
            $stmtH = $pdo->prepare("SELECT hora FROM reservas WHERE fecha = ? AND estado NOT LIKE 'ANULADA%' GROUP BY hora ORDER BY COUNT(*) ASC, hora ASC LIMIT 1");
            $stmtH->execute([$fechaKey]);
            $res = $stmtH->fetch();
            $hValle = $res ? substr($res['hora'], 0, 5) : null;
        }

        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $resultado[] = [
            "fecha" => $fechaKey,
            "soloFecha" => $current->format('d M'),
            "nombreDia" => $diasSemana[$current->format('w')],
            "cantidad" => $v,
            "anuladas" => $a,
            "horaPico" => $hPico,
            "horaValle" => $hValle
        ];

        $current->modify('+1 day');
    }

    // --- CÁLCULOS DE RESUMEN GLOBAL (KPIs) ---
    $maxV = -1; 
    $minV = 999; 
    $diaP = '---'; 
    $diaV = '---';

    foreach ($resultado as $r) {
        // Día Pico
        if ($r['cantidad'] > $maxV) {
            $maxV = $r['cantidad'];
            $diaP = $r['nombreDia'] . ' ' . $r['soloFecha'];
        }
        // Día Valle (mínimo que tenga al menos 1 cita)
        if ($r['cantidad'] > 0 && $r['cantidad'] < $minV) {
            $minV = $r['cantidad'];
            $diaV = $r['nombreDia'] . ' ' . $r['soloFecha'];
        }
    }

    // Limpieza de etiquetas si no hay datos suficientes
    if ($minV == 999) $diaV = '---';
    if ($diaP === $diaV && $maxV > 0) $diaV = "Mismo que pico";

    // HORA PUESTA GENERAL (En todo el rango)
    $stmtG = $pdo->prepare("SELECT hora FROM reservas WHERE fecha BETWEEN ? AND ? AND estado NOT LIKE 'ANULADA%' GROUP BY hora ORDER BY COUNT(*) DESC, hora ASC LIMIT 1");
    $stmtG->execute([$inicio, $fin]);
    $gPico = $stmtG->fetch();

    $stmtG = $pdo->prepare("SELECT hora FROM reservas WHERE fecha BETWEEN ? AND ? AND estado NOT LIKE 'ANULADA%' GROUP BY hora ORDER BY COUNT(*) ASC, hora ASC LIMIT 1");
    $stmtG->execute([$inicio, $fin]);
    $gValle = $stmtG->fetch();

    header('Content-Type: application/json');
    echo json_encode([
        "porDias" => $resultado,
        "resumen" => [
            "diaPico" => $diaP,
            "diaValle" => $diaV,
            "horaPicoGral" => $gPico ? substr($gPico['hora'], 0, 5) : '---',
            "horaValleGral" => $gValle ? substr($gValle['hora'], 0, 5) : '---'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}