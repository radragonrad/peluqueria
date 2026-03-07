<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php'; 



try {
   
    $fecha = $_GET['fecha'];
    $peluquero_id = intval($_GET['peluquero_id']);
    $servicio_id = intval($_GET['servicio_id']);

    // 1. Obtener duración del servicio que se quiere reservar ahora
    $stmt_s = $pdo->prepare("SELECT duracion_min FROM servicios WHERE id = ?");
    $stmt_s->execute([$servicio_id]);
    $duracion_nueva = $stmt_s->fetchColumn() ?: 30;

    // 2. Obtener horario base de la tienda
    $dia_semana = date('N', strtotime($fecha));
    $stmt_h = $pdo->prepare("SELECT * FROM horarios WHERE id_dia = ?");
    $stmt_h->execute([$dia_semana]);
    $horario = $stmt_h->fetch(PDO::FETCH_ASSOC);

    if (!$horario || $horario['abierto'] == 0) {
        echo json_encode([]); exit;
    }

    // 3. Buscar excepciones (días cerrados o tramos especiales)
    $stmt_ex = $pdo->prepare("SELECT * FROM horario_excepciones WHERE fecha = ?");
    $stmt_ex->execute([$fecha]);
    $excepcion = $stmt_ex->fetch(PDO::FETCH_ASSOC);

    if ($excepcion && $excepcion['solo_tramo'] == 0 && $excepcion['cerrado'] == 1) {
        echo json_encode([]); exit;
    }

    // 4. NUEVA LÓGICA: Obtener reservas con su duración para calcular bloqueos
    // Unimos con la tabla servicios para saber cuánto dura cada cita ya existente
  
    $stmt_r = $pdo->prepare("
        SELECT r.hora, s.duracion_min 
        FROM reservas r 
        JOIN servicios s ON r.servicio_id = s.id 
        WHERE r.peluquero_id = ? AND r.fecha = ?
    ");
    $stmt_r->execute([$peluquero_id, $fecha]);
    $reservas_existentes = $stmt_r->fetchAll(PDO::FETCH_ASSOC);

    // Convertimos las reservas en rangos de tiempo (timestamps)
    $bloqueos = [];
    foreach ($reservas_existentes as $res) {
        $hora_limpia = date('H:i', strtotime($res['hora']));
        $inicio = strtotime($fecha . ' ' . $hora_limpia);
        $fin = $inicio + ($res['duracion_min'] * 60);
    
        $bloqueos[] = ['inicio' => $inicio, 'fin' => $fin];
    }

    /**
     * Función generadora con detección de colisiones por tramos
     */
    function generarIntervalosDinamicos($inicio_turno, $fin_turno, $bloqueos, $duracion_nueva, $excepcion, $fecha_base) {
        $intervalos = [];
        $actual = strtotime($fecha_base . ' ' . $inicio_turno);
        $cierre_turno = strtotime($fecha_base . ' ' . $fin_turno);

        // Mientras el servicio quepa antes del cierre del turno
        while ($actual + ($duracion_nueva * 60) <= $cierre_turno) {
            $fin_propuesto = $actual + ($duracion_nueva * 60);
            
            $ocupado = false;

            // A. Comprobar colisión con Reservas Existentes (Lógica de intervalos)
            foreach ($bloqueos as $b) {
                // Si el inicio nuevo está antes del fin de la reserva Y el fin nuevo está después del inicio de la reserva -> COLISIÓN
                if ($actual < $b['fin'] && $fin_propuesto > $b['inicio']) {
                    $ocupado = true;
                    break;
                }
            }

            // B. Comprobar colisión con Excepciones de tramo (Pestaña amarilla)
            if (!$ocupado && $excepcion && $excepcion['solo_tramo'] == 1) {
                $inicio_ex = strtotime($fecha_base . ' ' . $excepcion['h_inicio']);
                $fin_ex = strtotime($fecha_base . ' ' . $excepcion['h_fin']);
                
                if ($actual < $fin_ex && $fin_propuesto > $inicio_ex) {
                    $ocupado = true;
                }
            }

            // Si el tramo está libre, lo añadimos
            if (!$ocupado) {
                $intervalos[] = date('H:i', $actual);
            }
            
            // Avanzamos 15 minutos o la duración del servicio. 
            // Sugerencia: avanzar 15 min da más flexibilidad al cliente.
            $actual = strtotime("+" . $duracion_nueva . " minutes", $actual);
        }
        return $intervalos;
    }

    // 5. Generar horas para ambos turnos
    $disponibles = generarIntervalosDinamicos($horario['h_apertura_1'], $horario['h_cierre_1'], $bloqueos, $duracion_nueva, $excepcion, $fecha);
    
    if (!empty($horario['h_apertura_2'])) {
        $segundo_turno = generarIntervalosDinamicos($horario['h_apertura_2'], $horario['h_cierre_2'], $bloqueos, $duracion_nueva, $excepcion, $fecha);
        $disponibles = array_merge($disponibles, $segundo_turno);
    }

    // Eliminar duplicados si los hay y ordenar
    $disponibles = array_values(array_unique($disponibles));
    sort($disponibles);

    echo json_encode($disponibles);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}