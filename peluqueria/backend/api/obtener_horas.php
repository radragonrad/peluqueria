<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo !== 'GET') {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        exit;
    }

    $fecha = $_GET['fecha'] ?? null;
    $servicio_id = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : null;
    // En el admin, si no pasas peluquero_id, podemos asignar uno por defecto o recibirlo
    $peluquero_id = isset($_GET['peluquero_id']) ? intval($_GET['peluquero_id']) : 1; 

    if (!$fecha || !$servicio_id) {
        echo json_encode([]);
        exit;
    }

    // 1. Obtener duración del servicio solicitado
    $stmt_s = $pdo->prepare("SELECT duracion_min FROM servicios WHERE id = ?");
    $stmt_s->execute([$servicio_id]);
    $duracion_nueva = $stmt_s->fetchColumn() ?: 30;

    // 2. Obtener horario base según el día de la semana
    $dia_semana = date('N', strtotime($fecha)); // 1 (Lunes) a 7 (Domingo)
    $stmt_h = $pdo->prepare("SELECT * FROM horarios WHERE id_dia = ?");
    $stmt_h->execute([$dia_semana]);
    $horario = $stmt_h->fetch(PDO::FETCH_ASSOC);

    if (!$horario || $horario['abierto'] == 0) {
        echo json_encode([]); exit;
    }

    // 3. Buscar excepciones (Días cerrados o vacaciones)
    $stmt_ex = $pdo->prepare("SELECT * FROM horario_excepciones WHERE fecha = ?");
    $stmt_ex->execute([$fecha]);
    $excepcion = $stmt_ex->fetch(PDO::FETCH_ASSOC);

    if ($excepcion && $excepcion['solo_tramo'] == 0 && $excepcion['cerrado'] == 1) {
        echo json_encode([]); exit;
    }

    // 4. Obtener reservas existentes para calcular bloqueos
    // Importante: Usamos user_id según tu reservas.sql
    $stmt_r = $pdo->prepare("
        SELECT r.hora, s.duracion_min 
        FROM reservas r 
        JOIN servicios s ON r.servicio_id = s.id 
        WHERE r.fecha = ? AND r.estado NOT IN ('ANULADA LOCAL', 'ANULADA WEB')
    ");
    $stmt_r->execute([$fecha]);
    $reservas_existentes = $stmt_r->fetchAll(PDO::FETCH_ASSOC);

    $bloqueos = [];
    foreach ($reservas_existentes as $res) {
        $inicio = strtotime($fecha . ' ' . $res['hora']);
        $fin = $inicio + ($res['duracion_min'] * 60);
        $bloqueos[] = ['inicio' => $inicio, 'fin' => $fin];
    }

    // 5. Función generadora de intervalos (Idéntica a tu web)
    function generarIntervalosAdmin($inicio_turno, $fin_turno, $bloqueos, $duracion_nueva, $excepcion, $fecha_base) {
        $intervalos = [];
        $actual = strtotime($fecha_base . ' ' . $inicio_turno);
        $cierre_turno = strtotime($fecha_base . ' ' . $fin_turno);
        $ahora = time();

        while ($actual + ($duracion_nueva * 60) <= $cierre_turno) {
            $fin_propuesto = $actual + ($duracion_nueva * 60);
            $ocupado = false;

            // Validación A: No permitir horas pasadas si es hoy
            if ($fecha_base === date('Y-m-d') && $actual <= $ahora) {
                $ocupado = true;
            }

            // Validación B: Colisión con reservas
            if (!$ocupado) {
                foreach ($bloqueos as $b) {
                    if ($actual < $b['fin'] && $fin_propuesto > $b['inicio']) {
                        $ocupado = true;
                        break;
                    }
                }
            }

            // Validación C: Colisión con tramos de excepción (ej: descanso)
            if (!$ocupado && $excepcion && $excepcion['solo_tramo'] == 1) {
                $inicio_ex = strtotime($fecha_base . ' ' . $excepcion['h_inicio']);
                $fin_ex = strtotime($fecha_base . ' ' . $excepcion['h_fin']);
                if ($actual < $fin_ex && $fin_propuesto > $inicio_ex) {
                    $ocupado = true;
                }
            }

            if (!$ocupado) {
                $intervalos[] = date('H:i', $actual);
            }
            
            // Avanzamos según la duración para que no se solapen (o puedes poner 15/20 min)
            $actual = strtotime("+20 minutes", $actual);
        }
        return $intervalos;
    }

    // 6. Generar horas para Turno 1 y Turno 2
    $disponibles = generarIntervalosAdmin($horario['h_apertura_1'], $horario['h_cierre_1'], $bloqueos, $duracion_nueva, $excepcion, $fecha);
    
    if (!empty($horario['h_apertura_2']) && $horario['h_apertura_2'] !== '00:00:00') {
        $segundo_turno = generarIntervalosAdmin($horario['h_apertura_2'], $horario['h_cierre_2'], $bloqueos, $duracion_nueva, $excepcion, $fecha);
        $disponibles = array_merge($disponibles, $segundo_turno);
    }

    echo json_encode(array_values(array_unique($disponibles)));

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}