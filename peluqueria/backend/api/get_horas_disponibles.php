<?php
require_once __DIR__ . '/../../../private/config/db.php'; 

try {
    $fecha = $_GET['fecha'];
    $peluquero_id = intval($_GET['peluquero_id']);
    $servicio_id = intval($_GET['servicio_id']);

    // --- NUEVO: Obtener timestamp actual para comparar ---
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

    // 4. Obtener reservas existentes
    $stmt_r = $pdo->prepare("
        SELECT r.hora, s.duracion_min 
        FROM reservas r 
        JOIN servicios s ON r.servicio_id = s.id 
        WHERE r.peluquero_id = ? AND r.fecha = ?
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
     * Función generadora corregida con MARGEN DE CORTESÍA
     */
    function generarIntervalosDinamicos($inicio_turno, $fin_turno, $bloqueos, $duracion_nueva, $excepcion, $fecha_base, $es_hoy, $ahora) {
        $intervalos = [];
        $actual = strtotime($fecha_base . ' ' . $inicio_turno);
        $cierre_turno = strtotime($fecha_base . ' ' . $fin_turno);

        // Definimos un margen de 30 minutos (30 * 60 segundos)
        // $margen_cortesia = 15 * 60;
        $margen_cortesia = 5 * 60;

        while ($actual + ($duracion_nueva * 60) <= $cierre_turno) {
            
            // Si es hoy, comparamos el intervalo contra (hora actual + 30 min)
            if ($es_hoy && $actual <= ($ahora + $margen_cortesia)) {
                // Si la hora ya pasó o falta menos de media hora, la saltamos
                $actual = strtotime("+" . $duracion_nueva . " minutes", $actual);
                continue;
            }

            $fin_propuesto = $actual + ($duracion_nueva * 60);
            $ocupado = false;

            // A. Colisión con Reservas
            foreach ($bloqueos as $b) {
                if ($actual < $b['fin'] && $fin_propuesto > $b['inicio']) {
                    $ocupado = true;
                    break;
                }
            }

            // B. Colisión con Excepciones
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
            
            $actual = strtotime("+" . $duracion_nueva . " minutes", $actual);
        }
        return $intervalos;
    }

    // 5. Generar horas pasando los nuevos parámetros $es_hoy y $ahora
    $disponibles = generarIntervalosDinamicos($horario['h_apertura_1'], $horario['h_cierre_1'], $bloqueos, $duracion_nueva, $excepcion, $fecha, $es_hoy, $ahora);
    
    if (!empty($horario['h_apertura_2'])) {
        $segundo_turno = generarIntervalosDinamicos($horario['h_apertura_2'], $horario['h_cierre_2'], $bloqueos, $duracion_nueva, $excepcion, $fecha, $es_hoy, $ahora);
        $disponibles = array_merge($disponibles, $segundo_turno);
    }

    $disponibles = array_values(array_unique($disponibles));
    sort($disponibles);

    echo json_encode($disponibles);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}