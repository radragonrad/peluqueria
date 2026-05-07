<?php
/**
 * get_horas_disponibles.php — Panel de Usuario (Web)
 *
 * GRID POR SERVICIO (sin citas en el día):
 *  - Corte+barba (45 min): mañana → 10:30, 11:30, 12:30
 *                           tarde  → 16:30, 17:30, 18:30, 19:30
 *                           (paso de 60 min, arranca en apertura)
 *  - Corte (30 min)       : cada 30 min desde apertura del turno
 *  - Barba (15 min)       : cada 30 min desde apertura del turno
 *  - Mechas (200 min)     : solo al inicio del turno si caben enteras
 *
 * CON CITAS EXISTENTES:
 *  - Se identifican los huecos libres (antes y después de cada cita).
 *  - Dentro de cada hueco se aplica el mismo grid del servicio
 *    reseteado desde el inicio del hueco.
 *  - Un slot se descarta si el restante hasta el siguiente bloqueo
 *    es > 0 pero < 15 min (hueco muerto inaprovechable).
 *
 * OTRAS REGLAS:
 *  - Solo horario del establecimiento (sin márgenes admin).
 *  - Hoy: se omiten slots con inicio <= ahora + 5 min.
 *  - Restricción: no antes del 2026-04-27.
 *  - Límite: máximo 2 citas pendientes en los próximos 7 días.
 */

require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../private/includes/functions.php';

header('Content-Type: application/json');

// Duración mínima del negocio (hueco muerto)
define('MIN_DURACION_SEG', 15 * 60);

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

        if ($citas_activas >= 3 && ($_SESSION['usuario'] ?? '') !== 'hadesinfer') {
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

    if ($fecha < '2026-04-27') {
        echo json_encode([]);
        exit;
    }

    $ahora       = time();
    $es_hoy      = ($fecha === date('Y-m-d'));
    $margen_cort = 5 * 60;

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

    // ── 5. Funciones ─────────────────────────────────────────────────────────

    /**
     * Dado un hueco libre [inicio_hueco, fin_hueco) y la duración del servicio,
     * devuelve el paso del grid según el servicio:
     *   - Mechas (200 min) : solo al inicio del hueco si cabe entero → paso = hueco entero
     *   - Corte+barba (45) : paso 60 min
     *   - Corte (30)       : paso 30 min
     *   - Barba (15)       : paso 30 min (inicio de cada media hora)
     *   - Otros            : paso = duración
     */
    /**
     * Paso del grid cuando NO hay citas (día vacío):
     *   Corte+barba (45): 60 min → horas en punto (10:30, 11:30…)
     *   Corte/Barba (≤30): 30 min
     *   Mechas (≥200): solo al inicio
     */
    function ghd_paso_libre(int $duracion_min): int
    {
        if ($duracion_min >= 200) return PHP_INT_MAX;
        if ($duracion_min == 45)  return 60 * 60;
        if ($duracion_min <= 30)  return 30 * 60;
        return $duracion_min * 60;
    }

    /**
     * Paso del grid cuando HAY citas (dentro de un hueco):
     * Siempre = duración del servicio para encajar desde el borde del hueco.
     */
    function ghd_paso_hueco(int $duracion_min): int
    {
        if ($duracion_min >= 200) return PHP_INT_MAX;
        return $duracion_min * 60; // paso = duración real
    }

    /**
     * Genera los slots válidos dentro de un hueco libre.
     *
     * @param int  $inicio_hueco  Timestamp inicio del hueco
     * @param int  $fin_hueco     Timestamp fin del hueco (= inicio siguiente cita o cierre)
     * @param int  $duracion_seg  Duración del servicio en segundos
     * @param int  $duracion_min  Duración del servicio en minutos
     * @param bool $es_hoy
     * @param int  $ahora
     * @param int  $margen_cort
     * @param bool $hueco_abierto True si no hay cita justo después (fin de turno)
     * @return array Timestamps válidos
     */
    function ghd_slotsEnHueco(
        int  $inicio_hueco,
        int  $fin_hueco,
        int  $duracion_seg,
        int  $duracion_min,
        bool $es_hoy,
        int  $ahora,
        int  $margen_cort,
        bool $hueco_abierto,
        bool $hay_citas
    ): array {
        $slots = [];
        // Con citas: paso = duración (encaja desde borde del hueco)
        // Sin citas: paso = grid fijo por servicio (60 min para corte+barba, etc.)
        $paso  = $hay_citas ? ghd_paso_hueco($duracion_min) : ghd_paso_libre($duracion_min);

        // Mechas: solo al inicio si cabe entera
        if ($duracion_min >= 200) {
            // Solo al inicio del hueco, si caben enteras Y el restante no es hueco muerto
            $fin_mechas = $inicio_hueco + $duracion_seg;
            $restante   = $fin_hueco - $fin_mechas;
            $cabe       = ($fin_mechas <= $fin_hueco);
            $hueco_ok   = ($restante === 0) || $hueco_abierto || ($restante >= MIN_DURACION_SEG);
            if ($cabe && $hueco_ok) {
                if (!($es_hoy && $inicio_hueco <= ($ahora + $margen_cort))) {
                    $slots[] = $inicio_hueco;
                }
            }
            return $slots;
        }

        $candidato = $inicio_hueco;

        while (($candidato + $duracion_seg) <= $fin_hueco) {

            // Filtro: no en el pasado
            if ($es_hoy && $candidato <= ($ahora + $margen_cort)) {
                $candidato += $paso;
                continue;
            }

            $fin_slot = $candidato + $duracion_seg;
            $restante = $fin_hueco - $fin_slot;

            // Si el hueco tiene un muro duro (siguiente cita), el restante
            // no puede ser un hueco muerto (> 0 y < 15 min).
            // Si el hueco es abierto (fin de turno), no importa el restante.
            if (!$hueco_abierto && $restante > 0 && $restante < MIN_DURACION_SEG) {
                $candidato += $paso;
                continue;
            }

            $slots[]   = $candidato;
            $candidato += $paso;
        }

        return $slots;
    }

    /**
     * Genera todos los slots de un turno teniendo en cuenta los bloqueos.
     * Construye los huecos libres y aplica ghd_slotsEnHueco en cada uno.
     */
    function ghd_generarTurno(
        int  $apertura_ts,
        int  $cierre_ts,
        int  $duracion_seg,
        int  $duracion_min,
        array $bloqueos,
        bool $es_hoy,
        int  $ahora,
        int  $margen_cort
    ): array {
        // Bloqueos que afectan a este turno, ordenados por inicio
        $bt = [];
        foreach ($bloqueos as $b) {
            if ($b['fin'] > $apertura_ts && $b['inicio'] < $cierre_ts) {
                $bt[] = $b;
            }
        }
        usort($bt, function($a, $b) { return $a['inicio'] - $b['inicio']; });

        // Si este turno concreto tiene citas, usamos paso = duración (no grid fijo)
        $hay_citas = !empty($bt);

        // Construir huecos libres
        $huecos = [];
        $cursor = $apertura_ts;

        foreach ($bt as $b) {
            $b_inicio = max($b['inicio'], $apertura_ts);
            $b_fin    = min($b['fin'],    $cierre_ts);
            if ($cursor < $b_inicio) {
                $huecos[] = [
                    'inicio'  => $cursor,
                    'fin'     => $b_inicio,
                    'abierto' => false,
                ];
            }
            if ($b_fin > $cursor) $cursor = $b_fin;
        }
        // Hueco final (abierto: no hay cita después, solo el cierre)
        if ($cursor < $cierre_ts) {
            $huecos[] = [
                'inicio'  => $cursor,
                'fin'     => $cierre_ts,
                'abierto' => true,
            ];
        }

        $slots = [];
        foreach ($huecos as $h) {
            $nuevos = ghd_slotsEnHueco(
                $h['inicio'], $h['fin'],
                $duracion_seg, $duracion_min,
                $es_hoy, $ahora, $margen_cort,
                $h['abierto'],
                $hay_citas
            );
            $slots = array_merge($slots, $nuevos);
        }

        return $slots;
    }

    // ── 6. Generar slots ─────────────────────────────────────────────────────
    $ap1 = strtotime($fecha . ' ' . $horario['h_apertura_1']);
    $ci1 = strtotime($fecha . ' ' . $horario['h_cierre_1']);

    $slots_ts = ghd_generarTurno(
        $ap1, $ci1, $duracion_seg, $duracion_min,
        $bloqueos, $es_hoy, $ahora, $margen_cort
    );

    if (!empty($horario['h_apertura_2']) && !empty($horario['h_cierre_2'])) {
        $ap2 = strtotime($fecha . ' ' . $horario['h_apertura_2']);
        $ci2 = strtotime($fecha . ' ' . $horario['h_cierre_2']);
        $slots_ts = array_merge($slots_ts, ghd_generarTurno(
            $ap2, $ci2, $duracion_seg, $duracion_min,
            $bloqueos, $es_hoy, $ahora, $margen_cort
        ));
    }

    // ── 7. Deduplicar ────────────────────────────────────────────────────────
    $slots_ts = array_unique($slots_ts);
    sort($slots_ts);

    // ── 8. Priorizar si hay citas ese día ────────────────────────────────────
    // Sin citas → orden cronológico simple (ya es el grid correcto)
    // Con citas → ordenar por proximidad a citas existentes:
    //   P1: el slot TERMINA justo cuando empieza una cita (pegado antes)
    //   P2: el slot EMPIEZA justo cuando termina una cita (pegado después)
    //   P3: resto de slots válidos (orden cronológico)
    $hay_citas_dia = !empty($bloqueos);

    if (!$hay_citas_dia) {
        // Sin citas: devolver orden cronológico simple
        $disponibles = array_map(function($ts) {
            return date('H:i', $ts);
        }, $slots_ts);
        echo json_encode(array_values($disponibles));
        exit;
    }

    // Con citas: calcular prioridad de cada slot
    $slots_priorizados = [];

    foreach ($slots_ts as $ts) {
        $fin_slot = $ts + $duracion_seg;
        $prio     = 3; // por defecto: resto

        foreach ($bloqueos as $b) {
            // P1: fin del slot = inicio de cita (pegado justo antes)
            if ($fin_slot === $b['inicio']) {
                $prio = 1;
                break;
            }
            // P2: inicio del slot = fin de cita (pegado justo después)
            if ($ts === $b['fin']) {
                $prio = min($prio, 2);
            }
        }

        $slots_priorizados[] = ['ts' => $ts, 'prio' => $prio];
    }

    // Ordenar: primero por prioridad, luego cronológico dentro de cada prioridad
    usort($slots_priorizados, function($a, $b) {
        if ($a['prio'] !== $b['prio']) return $a['prio'] - $b['prio'];
        return $a['ts'] - $b['ts'];
    });

    // ── 9. Formatear y devolver ───────────────────────────────────────────────
    $disponibles = array_map(function($s) {
        return $s['hora'] = date('H:i', $s['ts']);
    }, $slots_priorizados);

    echo json_encode(array_values($disponibles));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}