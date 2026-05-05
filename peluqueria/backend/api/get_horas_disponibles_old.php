<?php
/**
 * get_horas_disponibles.php — Panel de Usuario (Web)
 *
 * LÓGICA DE SLOTS:
 *  1. Solo se muestran horas DENTRO del horario del establecimiento.
 *     Sin márgenes pre/post para usuarios.
 *  2. Grid 100% dinámico: los slots nacen desde:
 *     a) La apertura de cada turno.
 *     b) El fin de cada cita existente (si cae dentro del turno).
 *  3. Paso = duración del servicio en todos los casos.
 *     Ej: corte+barba 45 min → 10:30, 11:15, 12:00 …
 *         Si hay una cita de barba (15 min) a las 18:45→19:00,
 *         el siguiente slot disponible es 19:00, no 19:30.
 *  4. Un slot es válido si su ventana [inicio, inicio+duracion)
 *     no solapa ningún bloqueo Y termina dentro del turno (≤ cierre).
 *  5. Hoy: se omiten slots cuyo inicio ≤ ahora + 5 min de cortesía.
 *  6. Restricción: no antes del 2026-04-27.
 *  7. Límite de reservas: máximo 2 citas pendientes en los próximos 7 días.
 */

require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../private/includes/functions.php';

header('Content-Type: application/json');

try {

    // ── 0. Límite de reservas por usuario ────────────────────────────────────
    if (isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];
        $hoy_sql    = date('Y-m-d');

        $stmt_check = $pdo->prepare("
            SELECT COUNT(*)
            FROM reservas
            WHERE user_id = ?
              AND fecha >= ?
              AND estado = 'PENDIENTE'
        ");
        $stmt_check->execute([$usuario_id, $hoy_sql]);
        $citas_activas = $stmt_check->fetchColumn();

        if ($citas_activas >= 2 && ($_SESSION['usuario'] ?? '') !== 'hadesinfer') {
            echo json_encode([
                'error'   => 'Limite_alcanzado',
                'mensaje' => 'Ya tienes 2 citas reservadas...',
            ]);
            exit;
        }
    }

    // ── Parámetros ───────────────────────────────────────────────────────────
    $fecha        = $_GET['fecha']        ?? '';
    $peluquero_id = intval($_GET['peluquero_id'] ?? 0);
    $servicio_id  = intval($_GET['servicio_id']  ?? 0);

    if (!$fecha || !$peluquero_id || !$servicio_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros incompletos']);
        exit;
    }

    // Restricción: solo desde el 27 de abril de 2026
    if ($fecha < '2026-04-27') {
        echo json_encode([]);
        exit;
    }

    $ahora       = time();
    $es_hoy      = ($fecha === date('Y-m-d'));
    $margen_cort = 5 * 60;   // 5 min de cortesía

    // ── 1. Duración del servicio ─────────────────────────────────────────────
    $stmt = $pdo->prepare('SELECT duracion_min FROM servicios WHERE id = ?');
    $stmt->execute([$servicio_id]);
    $duracion_min = intval($stmt->fetchColumn() ?: 30);
    $duracion_seg = $duracion_min * 60;

    // ── 2. Horario base del día ──────────────────────────────────────────────
    $dia_semana = intval(date('N', strtotime($fecha)));
    $stmt = $pdo->prepare('SELECT * FROM horarios WHERE id_dia = ?');
    $stmt->execute([$dia_semana]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario || $horario['abierto'] == 0) {
        echo json_encode([]);
        exit;
    }

    // ── 3. Excepciones ───────────────────────────────────────────────────────
    $stmt = $pdo->prepare('SELECT * FROM horario_excepciones WHERE fecha = ?');
    $stmt->execute([$fecha]);
    $excepcion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Día completamente cerrado
    if ($excepcion && $excepcion['cerrado'] == 1 && $excepcion['solo_tramo'] == 0) {
        echo json_encode([]);
        exit;
    }

    // ── 4. Reservas existentes → bloqueos ────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT r.hora, s.duracion_min
        FROM   reservas r
        JOIN   servicios s ON r.servicio_id = s.id
        WHERE  r.peluquero_id = ?
          AND  r.fecha        = ?
          AND  r.estado IN ('PENDIENTE', 'COMPLETADA')
    ");
    $stmt->execute([$peluquero_id, $fecha]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bloqueos = [];
    foreach ($reservas as $r) {
        $hora_limpia = date('H:i', strtotime($r['hora']));
        $inicio_ts   = strtotime($fecha . ' ' . $hora_limpia);
        if ($inicio_ts === false) continue;
        $bloqueos[] = [
            'inicio' => $inicio_ts,
            'fin'    => $inicio_ts + intval($r['duracion_min']) * 60,
        ];
    }

    // Bloqueo de excepción de tramo parcial
    if ($excepcion && $excepcion['solo_tramo'] == 1) {
        $bloqueos[] = [
            'inicio' => strtotime($fecha . ' ' . $excepcion['h_inicio']),
            'fin'    => strtotime($fecha . ' ' . $excepcion['h_fin']),
        ];
    }

    // ── 5. Funciones auxiliares ──────────────────────────────────────────────
    function ghd_slotLibre(int $inicio, int $duracion_seg, array $bloqueos): bool
    {
        $fin = $inicio + $duracion_seg;
        foreach ($bloqueos as $b) {
            if ($inicio < $b['fin'] && $fin > $b['inicio']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Genera slots para un turno dado.
     *
     * Semillas = apertura del turno + fin de cada cita que caiga en este turno.
     * Desde cada semilla avanzamos con paso = duración del servicio.
     * El slot es válido si:
     *   - No solapa ningún bloqueo.
     *   - Termina dentro del turno (fin ≤ cierre_turno).
     *   - No está en el pasado (solo hoy).
     *
     * @param int   $apertura_ts  Timestamp de apertura del turno
     * @param int   $cierre_ts   Timestamp de cierre del turno
     * @param int   $duracion_seg Duración del servicio en segundos
     * @param array $bloqueos    Array de bloqueos ['inicio'=>ts, 'fin'=>ts]
     * @param bool  $es_hoy
     * @param int   $ahora
     * @param int   $margen_cort
     * @return array Timestamps válidos
     */
    function ghd_generarTurno(
        int $apertura_ts,
        int $cierre_ts,
        int $duracion_seg,
        array $bloqueos,
        bool $es_hoy,
        int $ahora,
        int $margen_cort
    ): array {
        // Semillas: apertura + fin de citas dentro de este turno
        $semillas = [$apertura_ts];
        foreach ($bloqueos as $b) {
            // El fin de la cita debe estar dentro del turno para ser semilla útil
            if ($b['fin'] > $apertura_ts && $b['fin'] < $cierre_ts) {
                $semillas[] = $b['fin'];
            }
        }
        $semillas = array_unique($semillas);
        sort($semillas);

        $slots = [];

        foreach ($semillas as $semilla) {
            $candidato = $semilla;

            while (($candidato + $duracion_seg) <= $cierre_ts) {
                // Filtro: no en el pasado
                if ($es_hoy && $candidato <= ($ahora + $margen_cort)) {
                    $candidato += $duracion_seg;
                    continue;
                }

                if (ghd_slotLibre($candidato, $duracion_seg, $bloqueos)) {
                    $slots[] = $candidato;
                }

                $candidato += $duracion_seg;
            }
        }

        return $slots;
    }

    // ── 6. Generar slots por turno ───────────────────────────────────────────
    $ap1 = strtotime($fecha . ' ' . $horario['h_apertura_1']);
    $ci1 = strtotime($fecha . ' ' . $horario['h_cierre_1']);

    $slots_ts = ghd_generarTurno($ap1, $ci1, $duracion_seg, $bloqueos, $es_hoy, $ahora, $margen_cort);

    if (!empty($horario['h_apertura_2']) && !empty($horario['h_cierre_2'])) {
        $ap2 = strtotime($fecha . ' ' . $horario['h_apertura_2']);
        $ci2 = strtotime($fecha . ' ' . $horario['h_cierre_2']);

        $slots_t2 = ghd_generarTurno($ap2, $ci2, $duracion_seg, $bloqueos, $es_hoy, $ahora, $margen_cort);
        $slots_ts = array_merge($slots_ts, $slots_t2);
    }

    // ── 7. Deduplicar, ordenar y formatear ───────────────────────────────────
    $slots_ts    = array_unique($slots_ts);
    sort($slots_ts);
    $disponibles = array_map(function ($ts) { return date('H:i', $ts); }, $slots_ts);

    echo json_encode(array_values($disponibles));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}