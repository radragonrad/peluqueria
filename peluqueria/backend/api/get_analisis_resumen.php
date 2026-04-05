<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

$fecha_inicio = $_GET['inicio'];
$fecha_fin = $_GET['fin'];
$intervalo = 30;

try {
    // 1. Obtener todas las citas con duración en el rango
    $query = "SELECT r.fecha, r.hora, s.duracion_min 
              FROM reservas r
              JOIN servicios s ON r.servicio_id = s.id
              WHERE r.fecha BETWEEN ? AND ? AND r.estado != 'ANULADA'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    
    $citas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $inicio = new DateTime($row['fecha'] . ' ' . $row['hora']);
        $fin = clone $inicio;
        $fin->modify('+' . $row['duracion_min'] . ' minutes');
        $citas[] = ['inicio' => $inicio, 'fin' => $fin];
    }

    // 2. Definir tramos estándar (mañana y tarde)
    // Usamos una fecha ficticia para generar la lista de tramos
    $tramos_maestros = [
        ['09:30', '14:00'],
        ['16:30', '20:30']
    ];

    $resumen = [];
    $total_reservas_reales = count($citas);
    foreach ($tramos_maestros as $rango) {
        $curr = new DateTime('2026-01-01 ' . $rango[0]);
        $end  = new DateTime('2026-01-01 ' . $rango[1]);

        while ($curr < $end) {
            $inicio_t = clone $curr;
            $fin_t = clone $curr;
            $fin_t->modify("+$intervalo minutes");
            
            $total_ocupaciones = 0;

            // Recorremos TODAS las citas para ver cuántas caen en este tramo horario (sin importar el día)
            foreach ($citas as $cita) {
                // Comparamos solo la parte de la HORA
                $c_ini = $cita['inicio']->format('H:i');
                $c_fin = $cita['fin']->format('H:i');
                $t_ini = $inicio_t->format('H:i');
                $t_fin = $fin_t->format('H:i');

                if ($c_ini < $t_fin && $c_fin > $t_ini) {
                    $total_ocupaciones++;
                }
            }

            $resumen[] = [
                'hora' => $t_ini,
                'cantidad' => $total_ocupaciones,
                'ocupado' => $total_ocupaciones > 0
            ];
            $curr->modify("+$intervalo minutes");
        }
    }

    echo json_encode([
        'total_reservas' => $total_reservas_reales,
        'tramos' => $resumen // El array que ya teníamos con las horas
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}