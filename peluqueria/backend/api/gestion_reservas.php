<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../private/includes/functions.php';
require_once 'admin_check.php';

use Google\Client;
use Google\Service\Calendar;

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    // ─────────────────────────────────────────
    // GET — Obtener reservas por rango de fechas
    // ─────────────────────────────────────────
    if ($metodo === 'GET') {
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('monday this week'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d', strtotime('sunday this week'));

        // Validación básica de formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de fecha inválido']);
            exit;
        }

        $peluqueroId = isset($_GET['peluquero_id']) && ctype_digit((string)$_GET['peluquero_id'])
                       ? (int)$_GET['peluquero_id'] : null;

        $sql = "SELECT
                    r.id, r.fecha, r.hora, r.estado, r.metodo_pago, r.google_event_id,
                    r.peluquero_id, r.servicio_id,
                    u.nombre  AS cliente_nombre,
                    u.email   AS cliente_email,
                    u.telefono AS cliente_telefono,
                    s.nombre       AS servicio_nombre,
                    s.duracion_min AS duracion,
                    s.precio,
                    up.nombre AS peluquero_nombre
                FROM reservas r
                JOIN usuarios  u  ON r.user_id      = u.id
                JOIN servicios s  ON r.servicio_id   = s.id
                JOIN peluqueros p ON r.peluquero_id  = p.id
                JOIN usuarios  up ON p.usuario_id    = up.id
                WHERE r.fecha BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($peluqueroId) {
            $sql .= " AND r.peluquero_id = ?";
            $params[] = $peluqueroId;
        }

        $sql .= " ORDER BY r.fecha ASC, r.hora ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Limpiamos etiquetas_raw si existiera en el futuro (por ahora la columna no está en el SELECT)
        foreach ($resultados as &$reserva) {
            $reserva['etiquetas'] = [];
            if (!empty($reserva['etiquetas_raw'])) {
                foreach (explode('|', $reserva['etiquetas_raw']) as $p) {
                    $subparts = explode(':', $p);
                    if (count($subparts) === 2) {
                        $reserva['etiquetas'][] = ['nombre' => $subparts[0], 'color' => $subparts[1]];
                    }
                }
            }
            unset($reserva['etiquetas_raw']);
        }
        unset($reserva);

        if (ob_get_level()) ob_clean();
        echo json_encode($resultados);
        exit;
    }

    // ─────────────────────────────────────────
    // POST — Gestión de estados y fidelidad
    // ─────────────────────────────────────────
    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $accion = $data['accion'] ?? 'actualizar_reserva';

        // --- COBRAR DEUDA ---
        if ($accion === 'pagar_deuda') {
            if (!isset($data['id'], $data['metodo_pago'])) {
                throw new Exception("Datos insuficientes para liquidar deuda.");
            }
            $stmt = $pdo->prepare("UPDATE reservas SET metodo_pago = ?, pagado_deuda = 1, fecha_pago_deuda = NOW() WHERE id = ?");
            $stmt->execute([$data['metodo_pago'], $data['id']]);

            registrarLog($pdo, $_SESSION['user_id'] ?? null, 'DEUDA_LIQUIDADA', [
                'reserva_id'  => $data['id'],
                'metodo_pago' => $data['metodo_pago']
            ]);

            if (ob_get_level()) ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Deuda liquidada correctamente']);
            exit;
        }

        // --- ACTUALIZAR ESTADO ---
        if (isset($data['id'], $data['estado'])) {
            $id          = intval($data['id']);
            $estado      = strtoupper($data['estado']);
            $motivo      = $data['motivo']       ?? null;
            $metodo_pago = $data['metodo_pago']  ?? null;
            $usuario_id  = $data['user_id']      ?? null;
            $promocion_id = $data['promocion_id'] ?? null;
            $tipo_promo  = $data['tipo_promo']   ?? null;
            $revertir    = isset($data['revertir_cupon']) && $data['revertir_cupon'] === true;

            $pdo->beginTransaction();

            // Obtenemos info de la reserva antes de modificar (para el log y Google)
            $stmtInfo = $pdo->prepare("
                SELECT r.google_event_id,
                       u.nombre AS cliente_nombre, u.email AS cliente_email,
                       s.nombre AS servicio_nombre,
                       r.fecha, r.hora
                FROM reservas r
                JOIN usuarios u ON r.user_id = u.id
                JOIN servicios s ON r.servicio_id = s.id
                WHERE r.id = ?
            ");
            $stmtInfo->execute([$id]);
            $infoReserva = $stmtInfo->fetch(PDO::FETCH_ASSOC);
            $google_event_id = $infoReserva['google_event_id'] ?? null;

            // Actualizamos estado
            $stmt = $pdo->prepare("UPDATE reservas SET estado = ?, metodo_pago = ?, motivo_cancelacion = ? WHERE id = ?");
            $stmt->execute([$estado, $metodo_pago, $motivo, $id]);

            // Eliminar de Google Calendar si se anula
            if ($google_event_id && in_array($estado, ['ANULADA LOCAL', 'ANULADA WEB'])) {
                try {
                    $client = new Google\Client();
                    $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
                    $client->addScope(Google\Service\Calendar::CALENDAR);
                    $service = new Google\Service\Calendar($client);
                    $calendarId = '74408619d033cb1f21fa67fc786412a08171ce2a3510512e075bd3b862a2d9e1@group.calendar.google.com';
                    $service->events->delete($calendarId, $google_event_id);
                    error_log("Google Calendar: Evento $google_event_id eliminado.");
                } catch (Exception $ge) {
                    error_log("Error Google Calendar: " . $ge->getMessage());
                }
            }

            // Log del cambio de estado
            registrarLog($pdo, $_SESSION['user_id'] ?? null, 'CAMBIO_ESTADO_RESERVA', [
                'reserva_id'     => $id,
                'nuevo_estado'   => $estado,
                'cliente'        => $infoReserva['cliente_nombre'] ?? null,
                'cliente_email'  => $infoReserva['cliente_email']  ?? null,
                'servicio'       => $infoReserva['servicio_nombre'] ?? null,
                'fecha'          => $infoReserva['fecha'] ?? null,
                'hora'           => $infoReserva['hora']  ?? null,
                'metodo_pago'    => $metodo_pago,
                'motivo'         => $motivo,
                'google_borrado' => ($google_event_id && in_array($estado, ['ANULADA LOCAL', 'ANULADA WEB'])) ? 'sí' : 'no'
            ], $metodo_pago ? 1 : 0);

            // Lógica de Fidelidad
            if ($usuario_id && $promocion_id) {
                if ($estado === 'COMPLETADA') {
                    if ($tipo_promo === 'ETIQUETA') {
                        $sql = "INSERT INTO cupones_usuario (usuario_id, promocion_id, cupones_actuales, total_historico, premios_canjeados) 
                                VALUES (?, ?, 0, 1, 1) 
                                ON DUPLICATE KEY UPDATE premios_canjeados = premios_canjeados + 1, total_historico = total_historico + 1";
                        $pdo->prepare($sql)->execute([$usuario_id, $promocion_id]);
                        $log_detalle = "Premio de etiqueta otorgado.";
                    } else {
                        $stmtP = $pdo->prepare("SELECT cupones_necesarios FROM promociones WHERE id = ?");
                        $stmtP->execute([$promocion_id]);
                        $necesarios = (int)$stmtP->fetchColumn();

                        $sql = "INSERT INTO cupones_usuario (usuario_id, promocion_id, cupones_actuales, total_historico, premios_canjeados) 
                                VALUES (?, ?, 1, 1, 0) 
                                ON DUPLICATE KEY UPDATE 
                                total_historico = total_historico + 1,
                                premios_canjeados = CASE WHEN cupones_actuales >= ? THEN premios_canjeados + 1 ELSE premios_canjeados END,
                                cupones_actuales  = CASE WHEN cupones_actuales >= ? THEN ? ELSE cupones_actuales + 1 END";
                        $pdo->prepare($sql)->execute([$usuario_id, $promocion_id, $necesarios, $necesarios, $necesarios]);
                        $log_detalle = "Sello de visita añadido o premio canjeado.";
                    }
                    $pdo->prepare("INSERT INTO logs_promociones (reserva_id, usuario_id, promocion_id, tipo_movimiento, tipo_promo, detalles) VALUES (?, ?, ?, 'SUMA', ?, ?)")
                        ->execute([$id, $usuario_id, $promocion_id, $tipo_promo, $log_detalle]);
                }

                elseif ($estado === 'PENDIENTE' && $revertir) {
                    $log_detalle = "Reversión de estado de reserva.";
                    if ($tipo_promo === 'ETIQUETA') {
                        $pdo->prepare("UPDATE cupones_usuario SET premios_canjeados = GREATEST(0, premios_canjeados - 1), total_historico = GREATEST(0, total_historico - 1) WHERE usuario_id = ? AND promocion_id = ?")
                            ->execute([$usuario_id, $promocion_id]);
                    } else {
                        $stmtP = $pdo->prepare("SELECT cupones_necesarios FROM promociones WHERE id = ?");
                        $stmtP->execute([$promocion_id]);
                        $necesarios = (int)$stmtP->fetchColumn();
                        $pdo->prepare("UPDATE cupones_usuario 
                                SET premios_canjeados = CASE WHEN cupones_actuales = ? AND premios_canjeados > 0 THEN premios_canjeados - 1 ELSE premios_canjeados END,
                                    cupones_actuales  = GREATEST(0, cupones_actuales - 1),
                                    total_historico   = GREATEST(0, total_historico - 1)
                                WHERE usuario_id = ? AND promocion_id = ?")
                            ->execute([$necesarios, $usuario_id, $promocion_id]);
                    }
                    $pdo->prepare("INSERT INTO logs_promociones (reserva_id, usuario_id, promocion_id, tipo_movimiento, tipo_promo, detalles) VALUES (?, ?, ?, 'REVERSION', ?, ?)")
                        ->execute([$id, $usuario_id, $promocion_id, $tipo_promo, $log_detalle]);
                }
            }

            $pdo->commit();
            if (ob_get_level()) ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}