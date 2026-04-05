<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

$fecha_inicio = $_GET['inicio'];
$fecha_fin = $_GET['fin'];
$intervalo_tramo = 30; // Minutos por tramo de la rejilla

try {
    // 1. CARGAR MAPA DE HORARIOS (Turnos partidos de la peluquería)
    $queryH = "SELECT * FROM horarios";
    $resH = $pdo->query($queryH)->fetchAll(PDO::FETCH_ASSOC);
    $horarios_semana = [];
    foreach ($resH as $h) {
        // Guardamos por nombre inglés (Monday, Tuesday...) para DateTime
        $key = traducirDiaIngles($h['dia_semana']);
        $horarios_semana[$key] = $h;
    }

    // 2. OBTENER CITAS CON DURACIÓN (Hacemos JOIN con servicios)
    $queryC = "SELECT r.fecha, r.hora, s.duracion_min 
               FROM reservas r
               JOIN servicios s ON r.servicio_id = s.id
               WHERE r.fecha BETWEEN ? AND ? 
               AND r.estado != 'ANULADA'
               ORDER BY r.fecha ASC, r.hora ASC";
    
    $stmtC = $pdo->prepare($queryC);
    $stmtC->execute([$fecha_inicio, $fecha_fin]);
    
    // Organizamos las reservas en un mapa detallado [fecha][] = [inicio, fin]
    $mapa_reservas_detallado = [];
    while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
        $f = $row['fecha'];
        
        // Calculamos hora exacta de inicio y fin en formato DateTime para comparar fácil
        $inicio_cita = new DateTime($f . ' ' . $row['hora']);
        $fin_cita = clone $inicio_cita;
        $fin_cita->modify('+' . $row['duracion_min'] . ' minutes');

        $mapa_reservas_detallado[$f][] = [
            'inicio' => $inicio_cita,
            'fin' => $fin_cita,
            'duracion' => $row['duracion_min']
        ];
    }

    // 3. GENERAR REJILLA CON LÓGICA DE BLOQUEO POR DURACIÓN
    $resultado = [];
    $fecha_actual = new DateTime($fecha_inicio);
    $fecha_limite = new DateTime($fecha_fin);
    $semana_index = 1;
    $dias_acumulados = [];

    while ($fecha_actual <= $fecha_limite) {
        $f_str = $fecha_actual->format('Y-m-d');
        $dia_ing = $fecha_actual->format('l'); // 'Monday', 'Tuesday'...
        $h_dia = $horarios_semana[$dia_ing] ?? null;
        
        $tramos = [];
        
        // Si el día está abierto, generamos los tramos oficiales
        if ($h_dia && $h_dia['abierto'] == 1) {
            
            // Función interna para generar tramos CORREGIDA con lógica de BLOQUEO
            $generarTramosBloqueados = function($inicio_turno, $fin_turno, $fecha, $mapa_detallado, $intervalo) {
                if (!$inicio_turno || !$fin_turno) return [];
                $t = [];
                
                $curr_tramo_inicio = new DateTime($fecha . ' ' . $inicio_turno);
                $end_turno  = new DateTime($fecha . ' ' . $fin_turno);
                
                while ($curr_tramo_inicio < $end_turno) {
                    $inicio_t = clone $curr_tramo_inicio;
                    $fin_t    = clone $curr_tramo_inicio;
                    $fin_t->modify("+$intervalo minutes");

                    $cantidad_citas = 0; // Cambiamos el booleano por un contador
                    if (isset($mapa_detallado[$fecha])) {
                        foreach ($mapa_detallado[$fecha] as $cita) {
                            // Si la cita se solapa con el tramo, sumamos 1
                            if ($cita['inicio'] < $fin_t && $cita['fin'] > $inicio_t) {
                                $cantidad_citas++;
                            }
                        }
                    }

                    $t[] = [
                        'hora' => $inicio_t->format('H:i'),
                        'ocupado' => $cantidad_citas > 0, // Mantenemos esto para el color
                        'cantidad' => $cantidad_citas    // Enviamos el número real
                    ];
                    
                    $curr_tramo_inicio->modify("+$intervalo minutes");
                }
                return $t;
            };

            // Tramo Mañana
            $tramos_m = $generarTramosBloqueados($h_dia['h_apertura_1'], $h_dia['h_cierre_1'], $f_str, $mapa_reservas_detallado, $intervalo_tramo);
            // Tramo Tarde
            $tramos_t = $generarTramosBloqueados($h_dia['h_apertura_2'], $h_dia['h_cierre_2'], $f_str, $mapa_reservas_detallado, $intervalo_tramo);
            
            $tramos = array_merge($tramos_m, $tramos_t);
        }

        $dias_acumulados[] = [
            'fecha' => $f_str,
            'fecha_f' => $fecha_actual->format('d M'),
            'dia_nombre' => traducirDiaEspanol($fecha_actual->format('D')),
            'abierto' => $h_dia ? (int)$h_dia['abierto'] : 0,
            // Contamos citas reales, no tramos bloqueados
            'total_citas' => isset($mapa_reservas_detallado[$f_str]) ? count($mapa_reservas_detallado[$f_str]) : 0,
            'tramos' => $tramos
        ];

        // Agrupar en semanas de 7 días
        if (count($dias_acumulados) == 7 || $fecha_actual == $fecha_limite) {
            $resultado[] = [
                'titulo' => "SEMANA $semana_index",
                'dias' => $dias_acumulados
            ];
            $dias_acumulados = [];
            $semana_index++;
        }
        $fecha_actual->modify('+1 day');
    }

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// FUNCIONES DE TRADUCCIÓN
function traducirDiaIngles($es) {
    $dict = ['Lunes'=>'Monday','Martes'=>'Tuesday','Miércoles'=>'Wednesday','Jueves'=>'Thursday','Viernes'=>'Friday','Sábado'=>'Saturday','Domingo'=>'Sunday'];
    return $dict[$es] ?? $es;
}

function traducirDiaEspanol($en) {
    $dict = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'];
    return $dict[$en] ?? $en;
}