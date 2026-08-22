<?php
/**
 * obtener_horas.php — Panel de Administrador
 *
 * LÓGICA DE SLOTS:
 *  1. Los slots nacen en la APERTURA del turno 1 (o 2h antes si es admin)
 *     y en el FIN de cada cita existente. Grid 100% dinámico.
 *  2. Un slot es válido si:
 *     a) Su ventana [inicio, inicio+duracion) no solapa ninguna cita existente.
 *     b) El servicio TERMINA dentro de los límites permitidos (ver punto 4).
 *  3. TURNO 1  → slots que terminan antes o en h_cierre_1.
 *     DESCANSO  → solo servicios >30 min, paso de 10 min, el servicio debe
 *                 terminar antes o en h_apertura_2 (si hay turno 2).
 *     TURNO 2   → slots que terminan antes o en h_cierre_2.
 *  4. MARGEN ADMIN (+2 h):
 *     - Antes del turno 1:  slots que EMPIEZAN desde (h_apertura_1 − 2h)
 *       pero TERMINAN dentro del turno 1 (≤ h_cierre_1).
 *     - Después del turno final: cualquier slot (incluyendo los que nacen
 *       del fin de citas existentes) cuyo FIN ≤ (h_cierre_final + 2h).
 *       Ej: cierre 21:00, cita termina 20:30, corte+barba 45 min → 21:15 ✅
 *  5. Hoy: se omiten slots cuyo inicio ≤ ahora + 5 min de cortesía.
 *  6. Restricción global: no antes del 2026-04-27.
 *  7. El horario base (turnos, aperturas y cierres) es el propio de cada
 *     peluquero (tabla horarios_peluquero), no el general de la tienda.
 */

require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../private/includes/functions.php';
require_once 'admin_check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }

    // ── Parámetros ──────────────────────────────────────────────────────────
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
    $margen_cort = 5 * 60;          // 5 min de cortesía
    $margen_adm  = 2 * 60 * 60;    // 2 h margen admin

    // ── 1. Duración del servicio ─────────────────────────────────────────────
    $stmt = $pdo->prepare('SELECT duracion_min FROM servicios WHERE id = ?');
    $stmt->execute([$servicio_id]);
    $duracion_min = intval($stmt->fetchColumn() ?: 30);
    $duracion_seg = $duracion_min * 60;

    // ── 2. Horario base del día (propio del peluquero) ───────────────────────
    // Si el peluquero no tiene fila para este día (o no la tiene configurada
    // en absoluto), se considera cerrado para él ese día.
    $dia_semana = intval(date('N', strtotime($fecha)));   // 1=lunes … 7=domingo
    $stmt = $pdo->prepare('SELECT * FROM horarios_peluquero WHERE peluquero_id = ? AND id_dia = ?');
    $stmt->execute([$peluquero_id, $dia_semana]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario || $horario['abierto'] == 0) {
        echo json_encode([]);
        exit;
    }

    // ── 3. Excepciones ──────────────────────────────────────────────────────
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

    // Bloqueos de reservas
    $bloqueos = [];
    foreach ($reservas as $r) {
        $raw        = trim($r['hora']);
        if (strlen($raw) > 8) $raw = substr($raw, -8);
        $hora_str   = substr($raw, 0, 5);                       // "HH:MM"
        $inicio_ts  = strtotime($fecha . ' ' . $hora_str);
        if ($inicio_ts === false) continue;
        $bloqueos[] = [
            'inicio' => $inicio_ts,
            'fin'    => $inicio_ts + intval($r['duracion_min']) * 60,
        ];
    }

    // Bloqueo de excepción de tramo (ej. cierre parcial por tarde)
    if ($excepcion && $excepcion['solo_tramo'] == 1) {
        $bloqueos[] = [
            'inicio' => strtotime($fecha . ' ' . $excepcion['h_inicio']),
            'fin'    => strtotime($fecha . ' ' . $excepcion['h_fin']),
        ];
    }

    // ── 5. Función auxiliar: ¿un candidato está libre? ───────────────────────
    /**
     * Devuelve true si el slot [inicio, inicio+duracion) no choca con ningún bloqueo.
     */
    function slotLibre(int $inicio, int $duracion_seg, array $bloqueos): bool
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
     * Filtra para no repetir horas ya pasadas (solo si es hoy).
     */
    function noEsPasado(int $ts, bool $es_hoy, int $ahora, int $margen_cort): bool
    {
        return !$es_hoy || $ts > ($ahora + $margen_cort);
    }

    // ── 6. Puntos de arranque ("semillas") ──────────────────────────────────
    /**
     * Los slots pueden arrancar desde:
     *  - La apertura de cada turno (y margen admin al principio del día).
     *  - El fin de cada cita existente.
     * Devolvemos un array de timestamps únicos ordenados.
     */
    function semillas(
        array  $bloqueos,
        int    $apertura_1_ts,
        int    $cierre_1_ts,
        ?int   $apertura_2_ts,
        ?int   $cierre_2_ts,
        int    $margen_adm
    ): array {
        $pts = [];

        // Apertura turno 1 y margen admin antes
        $pts[] = $apertura_1_ts;
        $pts[] = $apertura_1_ts - $margen_adm;

        // Apertura turno 2 (si existe)
        if ($apertura_2_ts !== null) {
            $pts[] = $apertura_2_ts;
        }

        // Fin de cada cita → posible nuevo arranque
        foreach ($bloqueos as $b) {
            $pts[] = $b['fin'];
        }

        $pts = array_unique($pts);
        sort($pts);
        return $pts;
    }

    // ── 7. Construir timestamps de horario ───────────────────────────────────
    $ap1 = strtotime($fecha . ' ' . $horario['h_apertura_1']);
    $ci1 = strtotime($fecha . ' ' . $horario['h_cierre_1']);

    $hay_turno2 = !empty($horario['h_apertura_2']) && !empty($horario['h_cierre_2']);
    $ap2 = $hay_turno2 ? strtotime($fecha . ' ' . $horario['h_apertura_2']) : null;
    $ci2 = $hay_turno2 ? strtotime($fecha . ' ' . $horario['h_cierre_2'])   : null;

    $cierre_final = $hay_turno2 ? $ci2 : $ci1;

    // ── 8. Generar slots ─────────────────────────────────────────────────────
    /**
     * Genera slots para un tramo horario dado, desde $arranque hasta $limite_fin.
     * $paso_normal  = duración del servicio (grid fijo dentro del tramo).
     * $paso_fino    = 10 min (para el descanso y para explorar tras citas).
     *
     * Desde cada semilla avanzamos con $paso hasta el límite.
     * Si la semilla viene del fin de una cita usamos $paso_fino para no
     * perder el primer hueco disponible.
     */

    $paso_normal = $duracion_seg;          // 45 min para corte+barba, etc.
    $paso_fino   = 10 * 60;               // 10 min (descanso / tras citas)
    $limite_max  = $cierre_final + $margen_adm;

    $slots_ts = [];

    // ── Función interna: añade slots válidos de un tramo ─────────────────────
    // $arranque   : timestamp desde donde empezar
    // $limite_fin : el servicio debe TERMINAR en o antes de este timestamp
    // $paso       : incremento entre candidatos
    $generarTramo = function(
        $arranque, $limite_fin, $paso
    ) use (
        &$slots_ts, $duracion_seg, $bloqueos,
        $es_hoy, $ahora, $margen_cort
    ) {
        $candidato = $arranque;
        while (($candidato + $duracion_seg) <= $limite_fin) {
            if (noEsPasado($candidato, $es_hoy, $ahora, $margen_cort)) {
                if (slotLibre($candidato, $duracion_seg, $bloqueos)) {
                    $slots_ts[] = $candidato;
                }
            }
            $candidato += $paso;
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // ZONA A: Margen admin PRE-apertura (ap1-2h … ap1)
    //         El servicio debe terminar dentro del turno 1 (≤ ci1)
    // ══════════════════════════════════════════════════════════════════════════
    $inicio_pre = $ap1 - $margen_adm;
    $paso_pre   = ($duracion_min > 30) ? $paso_fino : $paso_normal;
    $c = $inicio_pre;
    while (($c + $duracion_seg) <= $ci1 && $c < $ap1) {
        if (noEsPasado($c, $es_hoy, $ahora, $margen_cort)) {
            if (slotLibre($c, $duracion_seg, $bloqueos)) {
                $slots_ts[] = $c;
            }
        }
        $c += $paso_pre;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ZONA B: Turno 1 (ap1 … ci1)
    //         Paso = duración del servicio.
    //         Semillas: ap1 + fin de cada cita que caiga en turno 1.
    // ══════════════════════════════════════════════════════════════════════════
    $semillas_t1 = [$ap1];
    foreach ($bloqueos as $b) {
        if ($b['fin'] > $ap1 && $b['fin'] <= $ci1) {
            $semillas_t1[] = $b['fin'];
        }
    }
    $semillas_t1 = array_unique($semillas_t1);
    sort($semillas_t1);

    foreach ($semillas_t1 as $s) {
        $generarTramo($s, $ci1, $paso_normal);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ZONA C: Descanso (ci1 … ap2) — todos los servicios, paso 10 min
    //         El servicio debe terminar ≤ ap2 (tiene que caber antes de abrir)
    // ══════════════════════════════════════════════════════════════════════════
    if ($hay_turno2) {
        // Semillas: ci1 + fin de citas en el descanso
        $semillas_desc = [$ci1];
        foreach ($bloqueos as $b) {
            if ($b['fin'] > $ci1 && $b['fin'] < $ap2) {
                $semillas_desc[] = $b['fin'];
            }
        }
        $semillas_desc = array_unique($semillas_desc);
        sort($semillas_desc);

        $paso_desc = ($duracion_min > 30) ? $paso_fino : $paso_normal;
        foreach ($semillas_desc as $s) {
            $generarTramo($s, $ap2, $paso_desc);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ZONA D: Turno 2 (ap2 … ci2)
    //         Paso = duración del servicio.
    //         Semillas: ap2 + fin de citas en turno 2.
    // ══════════════════════════════════════════════════════════════════════════
    if ($hay_turno2) {
        $semillas_t2 = [$ap2];
        foreach ($bloqueos as $b) {
            if ($b['fin'] > $ap2 && $b['fin'] <= $ci2) {
                $semillas_t2[] = $b['fin'];
            }
        }
        $semillas_t2 = array_unique($semillas_t2);
        sort($semillas_t2);

        foreach ($semillas_t2 as $s) {
            $generarTramo($s, $limite_max, $paso_normal);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ZONA E: Margen admin POST-cierre (cierre_final … cierre_final+2h)
    //         Semillas: cierre_final + fin de citas post-cierre.
    //         El servicio termina ≤ cierre_final + 2h.
    // ══════════════════════════════════════════════════════════════════════════
    // Si no hay turno 2, también generamos la zona post desde ci1
    $paso_post = ($duracion_min > 30) ? $paso_fino : $paso_normal;
    if (!$hay_turno2) {
        $semillas_post = [$ci1];
        foreach ($bloqueos as $b) {
            if ($b['fin'] >= $ci1) {
                $semillas_post[] = $b['fin'];
            }
        }
        $semillas_post = array_unique($semillas_post);
        sort($semillas_post);

        foreach ($semillas_post as $s) {
            $generarTramo($s, $limite_max, $paso_post);
        }
    } else {
        // Con turno 2: semillas post-ci2 (fin de citas que terminan tras ci2)
        foreach ($bloqueos as $b) {
            if ($b['fin'] > $ci2) {
                $generarTramo($b['fin'], $limite_max, $paso_post);
            }
        }
        // También desde ci2 en sí (por si no hay citas post-cierre)
        $generarTramo($ci2, $limite_max, $paso_post);
    }

    // ── 9. Deduplicar, ordenar y formatear ───────────────────────────────────
    $slots_ts = array_unique($slots_ts);
    sort($slots_ts);

    $disponibles = array_map(function($ts) { return date('H:i', $ts); }, $slots_ts);

    echo json_encode(array_values($disponibles));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}