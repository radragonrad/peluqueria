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

   // --- Parámetros iniciales ---
    $fecha = $_GET['fecha'];
    $peluquero_id = intval($_GET['peluquero_id']);
    $servicio_id = intval($_GET['servicio_id']);

    // --- NUEVA RESTRICCIÓN: Solo a partir del 27 de Abril ---
    $fecha_apertura = '2026-04-27'; // Asegúrate de que el año sea el correcto
    if ($fecha < $fecha_apertura) {
        echo json_encode([]); 
        exit;
    }

    $ahora = time(); 
    $es_hoy = ($fecha === date('Y-m-d'));

    // 1. Obtener duración del servicio
    $stmt_s = $pdo->prepare("SELECT duracion_min FROM servicios WHERE id = ?");
    $stmt_s->execute([$servicio_id]);
    $duracion_nueva = $stmt_s->fetchColumn() ?: 30;

    // 2. Obtener horario base
    $dia_semana = date('N', strtotime($fecha));
    $stmt_h = $pdo->prepare("SELECT * FROM horarios WHERE id_dia = ?");
    $stmt_h->execute([$dia_semana]);
    $horario = $stmt_h->fetch(PDO::FETCH_ASSOC);

    if (!$horario || $horario['abierto'] == 0) {
        echo json_encode([]); exit;
    }

    // 3. Buscar excepciones
    $stmt_ex = $pdo->prepare("SELECT * FROM horario_excepciones WHERE fecha = ?");
    $stmt_ex->execute([$fecha]);
    $excepcion = $stmt_ex->fetch(PDO::FETCH_ASSOC);

    if ($excepcion && $excepcion['solo_tramo'] == 0 && $excepcion['cerrado'] == 1) {
        echo json_encode([]); exit;
    }

    // 4. Obtener reservas existentes (Bloqueos de otros usuarios/peluquero)
    $stmt_r = $pdo->prepare("
        SELECT r.hora, s.duracion_min 
        FROM reservas r 
        JOIN servicios s ON r.servicio_id = s.id 
        WHERE r.peluquero_id = ? AND r.fecha = ? AND r.estado IN ('PENDIENTE', 'COMPLETADA')
    ");
    $stmt_r->execute([$peluquero_id, $fecha]);
    $reservas_existentes = $stmt_r->fetchAll(PDO::FETCH_ASSOC);

    $bloqueos = [];
    foreach ($reservas_existentes as $res) {
        $hora_limpia = date('H:i', strtotime($res['hora']));
        $inicio = strtotime($fecha . ' ' . $hora_limpia);
        $fin = $inicio + ($res['duracion_min'] * 60);
        $bloqueos[] = ['inicio' => $inicio, 'fin' => $fin];
    }

    /**
     * Función generadora corregida
     */
    function generarIntervalosDinamicos($inicio_turno, $fin_turno, $bloqueos, $duracion_nueva, $excepcion, $fecha_base, $es_hoy, $ahora) {
        $intervalos = [];
        $actual = strtotime($fecha_base . ' ' . $inicio_turno);
        $cierre_turno = strtotime($fecha_base . ' ' . $fin_turno);
        $margen_cortesia = 5 * 60;

        while ($actual + ($duracion_nueva * 60) <= $cierre_turno) {
            
            if ($es_hoy && $actual <= ($ahora + $margen_cortesia)) {
                $actual = strtotime("+" . $duracion_nueva . " minutes", $actual);
                continue;
            }

            $fin_propuesto = $actual + ($duracion_nueva * 60);
            $ocupado = false;
            $fin_bloqueo_activo = null;

            foreach ($bloqueos as $b) {
                if ($actual < $b['fin'] && $fin_propuesto > $b['inicio']) {
                    $ocupado = true;
                    if ($fin_bloqueo_activo === null || $b['fin'] > $fin_bloqueo_activo) {
                        $fin_bloqueo_activo = $b['fin'];
                    }
                }
            }

            if (!$ocupado && $excepcion && $excepcion['solo_tramo'] == 1) {
                $inicio_ex = strtotime($fecha_base . ' ' . $excepcion['h_inicio']);
                $fin_ex    = strtotime($fecha_base . ' ' . $excepcion['h_fin']);
                if ($actual < $fin_ex && $fin_propuesto > $inicio_ex) {
                    $ocupado = true;
                    $fin_bloqueo_activo = $fin_ex;
                }
            }

            if (!$ocupado) {
                $intervalos[] = date('H:i', $actual);
                $actual = strtotime("+" . $duracion_nueva . " minutes", $actual);
            } else {
                $actual = $fin_bloqueo_activo;
            }
        }
        return $intervalos;
    }

    // 5. Generar horas
    $disponibles = generarIntervalosDinamicos($horario['h_apertura_1'], $horario['h_cierre_1'], $bloqueos, $duracion_nueva, $excepcion, $fecha, $es_hoy, $ahora);
    
    if (!empty($horario['h_apertura_2'])) {
        $segundo_turno = generarIntervalosDinamicos($horario['h_apertura_2'], $horario['h_cierre_2'], $bloqueos, $duracion_nueva, $excepcion, $fecha, $es_hoy, $ahora);
        $disponibles = array_merge($disponibles, $segundo_turno);
    }

    $disponibles = array_values(array_unique($disponibles));
    sort($disponibles);

    echo json_encode($disponibles);


} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}