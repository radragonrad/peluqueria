<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once 'admin_check.php';
require_once __DIR__ . '/../../../private/includes/functions.php';

// Importamos PHPMailer (ajusta las rutas si es necesario)
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Client;
use Google\Service\Calendar;

/**
 * Devuelve el colorId de Google Calendar según el ID del servicio.
 * Ajusta los IDs según tu tabla `servicios`.
 *
 * Colores disponibles:
 *   1 Lavanda · 2 Salvia · 3 Uva      · 4 Tomate
 *   5 Plátano · 6 Mandarina · 7 Pavo real · 8 Grafito
 *   9 Arándano · 10 Albahaca · 11 Flamingo
 */
function getColorIdPorServicio($servicio_id)
{
    $mapa = [
        1 => '7',   // Arreglo de barba → Pavo real (azul)
        2 => '2',   // Corte            → Salvia (verde)
        3 => '6',   // Corte + Barba    → Mandarina (naranja)
        4 => '5',   // Mechas           → Plátano (amarillo)
    ];

    return isset($mapa[$servicio_id]) ? $mapa[$servicio_id] : '8'; // Grafito por defecto
}

/**
 * FUNCIÓN REUTILIZADA DE confirmar_reserva.php
 */
function agendarEnGoogleCalendar($datos, $pdo) {
    try {
        $client = new Google\Client();
        $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
        $client->addScope(Google\Service\Calendar::CALENDAR);

        $service = new Google\Service\Calendar($client);

        $fecha = trim($datos['fecha']);
        $hora = substr(trim($datos['hora']), 0, 5); 
        
        $start_time = $fecha . 'T' . $hora . ':00';
        $duracion = intval($datos['duracion']); // intval por seguridad
        $end_time = date('Y-m-d\TH:i:s', strtotime($start_time . ' +' . $duracion . ' minutes'));        

        $colorId = getColorIdPorServicio($datos['servicio_id']);

        $event = new Google\Service\Calendar\Event([
            'summary'     => 'Cita Peluquería: ' . $datos['servicio_nombre'],
            'location'    => 'C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla',
            'description' => "Cliente: " . $datos['cliente_nombre'] . " (" . $datos['cliente_email'] . ") | Peluquero: " . $datos['peluquero_nombre'],
            'colorId'     => $colorId,
            'start'       => ['dateTime' => $start_time, 'timeZone' => 'Europe/Madrid'],
            'end'         => ['dateTime' => $end_time, 'timeZone' => 'Europe/Madrid'],
            'reminders'   => [
                'useDefault' => FALSE,
                'overrides'  => [['method' => 'popup', 'minutes' => 30]],
            ],
        ]);

        $calendarId = '74408619d033cb1f21fa67fc786412a08171ce2a3510512e075bd3b862a2d9e1@group.calendar.google.com'; 
        
        // --- CAMBIO AQUÍ: Capturamos la respuesta de Google ---
        $eventCreated = $service->events->insert($calendarId, $event);
        
        // Devolvemos el ID del evento creado
        return $eventCreated->getId();
        
    } catch (Exception $e) {
        // Si falla Google, registramos el error pero no bloqueamos la app
        error_log("Error Google Calendar: " . $e->getMessage());
        return null;
    }
}

function enviarEmailConfirmacionCita($datos) {
    $fecha_formato = date('Ymd', strtotime($datos['fecha']));
    $hora_inicio = date('His', strtotime($datos['hora']));
    $hora_fin = date('His', strtotime($datos['hora'] . ' +45 minutes'));
    
    $enlace_cal = "https://www.google.com/calendar/render?action=TEMPLATE" .
        "&text=" . urlencode("Cita: " . $datos['servicio_nombre']) .
        "&dates=" . $fecha_formato . "T" . $hora_inicio . "/" . $fecha_formato . "T" . $hora_fin .
        "&details=" . urlencode("Peluquero: " . $datos['peluquero_nombre'] . "\nServicio: " . $datos['servicio_nombre']) .
        "&location=" . urlencode("C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla") .
        "&sf=true&output=xml";

    $mensaje_html = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>R. Gutiérrez Hair Studio</h1>
        </div>
        <div style='padding: 30px; line-height: 1.6; color: #333;'>
            <p style='font-size: 18px;'>Hola <strong>{$datos['cliente_nombre']}</strong>,</p>
            <p>Tu cita ha sido confirmada con éxito.</p>
            <div style='background-color: #fcf8f3; border-left: 4px solid #bc9667; padding: 15px; margin: 20px 0;'>
                <p><strong>Servicio:</strong> {$datos['servicio_nombre']}</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y', strtotime($datos['fecha'])) . "</p>
                <p><strong>Hora:</strong> " . substr($datos['hora'], 0, 5) . "h</p>
            </div>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$enlace_cal' style='background-color: #bc9667; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Añadir a mi Calendario</a>
            </div>
        </div>
    </div>";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.servidor-correo.net'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@rgutierrezhairstudio.com';
        $mail->Password   = '3K796Xy*8#5_s34@'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('no-reply@rgutierrezhairstudio.com', 'R. Gutiérrez Hair Studio');
        $mail->addAddress($datos['cliente_email'], $datos['cliente_nombre']);
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de tu cita - R. Gutiérrez Hair Studio';
        $mail->Body    = $mensaje_html;
        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'POST') {
        $raw_data = file_get_contents("php://input");
        $data = json_decode($raw_data, true);

        // ── ACCIÓN: MODIFICAR RESERVA EXISTENTE ──────────────────────────────
        if (!empty($data['accion']) && $data['accion'] === 'modificar') {

            if (empty($data['id']) || empty($data['peluquero_id']) || empty($data['servicio_id']) || empty($data['fecha']) || empty($data['hora'])) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos para modificar']);
                exit;
            }

            $id           = intval($data['id']);
            $peluquero_id = intval($data['peluquero_id']);
            $servicio_id  = intval($data['servicio_id']);
            $fecha        = $data['fecha'];
            $hora         = $data['hora'];

            $pdo->beginTransaction();

            // Datos actuales antes de modificar (para log y Google)
            $stmtOld = $pdo->prepare("
                SELECT r.google_event_id, r.fecha AS fecha_old, r.hora AS hora_old,
                       u.nombre AS cliente_nombre, u.email AS cliente_email,
                       s.nombre AS servicio_old, up.nombre AS peluquero_old
                FROM reservas r
                JOIN usuarios u   ON r.user_id      = u.id
                JOIN servicios s  ON r.servicio_id   = s.id
                JOIN peluqueros p ON r.peluquero_id  = p.id
                JOIN usuarios up  ON p.usuario_id    = up.id
                WHERE r.id = ?
            ");
            $stmtOld->execute([$id]);
            $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$old) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Reserva no encontrada']);
                exit;
            }

            // Datos del nuevo servicio y peluquero
            $stmtNew = $pdo->prepare("
                SELECT s.nombre AS servicio_nombre, s.duracion_min AS duracion,
                       up.nombre AS peluquero_nombre
                FROM servicios s, peluqueros p
                JOIN usuarios up ON p.usuario_id = up.id
                WHERE s.id = ? AND p.id = ?
            ");
            $stmtNew->execute([$servicio_id, $peluquero_id]);
            $new = $stmtNew->fetch(PDO::FETCH_ASSOC);

            // Actualizar reserva — solo si sigue PENDIENTE
            $stmtUpd = $pdo->prepare("UPDATE reservas SET peluquero_id = ?, servicio_id = ?, fecha = ?, hora = ? WHERE id = ? AND estado = 'PENDIENTE'");
            $stmtUpd->execute([$peluquero_id, $servicio_id, $fecha, $hora, $id]);

            if ($stmtUpd->rowCount() === 0) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'No se pudo modificar. La cita puede haber cambiado de estado.']);
                exit;
            }

            // Actualizar Google Calendar si existe evento
            if (!empty($old['google_event_id']) && $new) {
                try {
                    $client = new Google\Client();
                    $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
                    $client->addScope(Google\Service\Calendar::CALENDAR);
                    $service = new Google\Service\Calendar($client);
                    $calendarId = '74408619d033cb1f21fa67fc786412a08171ce2a3510512e075bd3b862a2d9e1@group.calendar.google.com';

                    $start_time = $fecha . 'T' . substr($hora, 0, 5) . ':00';
                    $end_time   = date('Y-m-d\TH:i:s', strtotime($start_time . ' +' . intval($new['duracion']) . ' minutes'));

                    $event = $service->events->get($calendarId, $old['google_event_id']);
                    $event->setSummary('Cita Peluquería: ' . $new['servicio_nombre']);
                    $event->setDescription("Cliente: {$old['cliente_nombre']} | Peluquero: {$new['peluquero_nombre']}");
                    $event->setColorId(getColorIdPorServicio($servicio_id));
                    $event->setStart(new Google\Service\Calendar\EventDateTime(['dateTime' => $start_time, 'timeZone' => 'Europe/Madrid']));
                    $event->setEnd(new Google\Service\Calendar\EventDateTime(['dateTime' => $end_time,   'timeZone' => 'Europe/Madrid']));
                    $service->events->update($calendarId, $old['google_event_id'], $event);
                } catch (Exception $ge) {
                    error_log("Error actualizando Google Calendar: " . $ge->getMessage());
                    // No bloqueamos si falla Google
                }
            }

            registrarLog($pdo, $_SESSION['user_id'] ?? null, 'MODIFICACION_RESERVA', [
                'reserva_id'      => $id,
                'cliente'         => $old['cliente_nombre'],
                'antes_fecha'     => $old['fecha_old'],
                'antes_hora'      => $old['hora_old'],
                'antes_servicio'  => $old['servicio_old'],
                'antes_peluquero' => $old['peluquero_old'],
                'nueva_fecha'     => $fecha,
                'nueva_hora'      => $hora,
                'nuevo_servicio'  => $new['servicio_nombre'] ?? null,
                'nuevo_peluquero' => $new['peluquero_nombre'] ?? null,
            ]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Cita modificada correctamente']);
            exit;
        }

        // ── ACCIÓN: CREAR RESERVA NUEVA (código original intacto) ─────────────
        if (!$data || empty($data['cliente_id']) || empty($data['servicio_id']) || empty($data['peluquero_id']) || empty($data['fecha']) || empty($data['hora'])) {        
            echo json_encode(["success" => false, "error" => "Datos incompletos"]);
            exit;
        }

        $user_id = intval($data['cliente_id']);
        $servicio_id = intval($data['servicio_id']);
        $peluquero_id = intval($data['peluquero_id']);
        $fecha = $data['fecha'];
        $hora = $data['hora'];

        try {
            $pdo->beginTransaction();

            // BLOQUEO PESIMISTA: verificamos que el hueco sigue libre
            $stmt_check = $pdo->prepare("
                SELECT COUNT(*) FROM reservas
                WHERE peluquero_id = ?
                  AND fecha = ?
                  AND hora = ?
                  AND estado IN ('PENDIENTE', 'COMPLETADA')
                FOR UPDATE
            ");
            $stmt_check->execute([$peluquero_id, $fecha, $hora]);
            $ocupado = $stmt_check->fetchColumn();

            if ($ocupado > 0) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Esa hora acaba de ser reservada. Por favor, elige otra hora.']);
                exit;
            }

            // 1. Insertar reserva básica en DB
            $sql = "INSERT INTO reservas (user_id, servicio_id, peluquero_id, fecha, hora) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql);
            
            if ($stmt_insert->execute([$user_id, $servicio_id, $peluquero_id, $fecha, $hora])) {
                $id_reserva_local = $pdo->lastInsertId(); // El ID que acaba de crear MySQL

                // 2. Obtener datos para Google y Email
                $stmt_info = $pdo->prepare("
                    SELECT u_cliente.nombre AS cliente_nombre, u_cliente.email AS cliente_email, 
                        s.nombre AS servicio_nombre, u_peluquero.nombre AS peluquero_nombre, s.duracion_min AS duracion
                    FROM usuarios u_cliente
                    CROSS JOIN servicios s
                    JOIN peluqueros p ON p.id = ?
                    JOIN usuarios u_peluquero ON p.usuario_id = u_peluquero.id 
                    WHERE u_cliente.id = ? AND s.id = ?
                ");
                $stmt_info->execute([$peluquero_id, $user_id, $servicio_id]);
                $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

                if ($info) {
                    $datos_cal = [
                        'fecha'            => $fecha,
                        'hora'             => $hora,
                        'cliente_nombre'   => $info['cliente_nombre'],
                        'cliente_email'    => $info['cliente_email'],
                        'servicio_nombre'  => $info['servicio_nombre'],
                        'peluquero_nombre' => $info['peluquero_nombre'],
                        'duracion'         => $info['duracion'],
                        'servicio_id'      => $servicio_id,
                    ];

                    // 3. MANDAR A GOOGLE Y OBTENER EL ID DEL EVENTO
                    $google_event_id = agendarEnGoogleCalendar($datos_cal, $pdo);

                    if ($google_event_id) {
                        // 4. ACTUALIZAR LA RESERVA CON EL ID DE GOOGLE
                        $stmt_upd = $pdo->prepare("UPDATE reservas SET google_event_id = ? WHERE id = ?");
                        $stmt_upd->execute([$google_event_id, $id_reserva_local]);
                    }

                    enviarEmailConfirmacionCita($datos_cal);
                    $pdo->commit();
                    echo json_encode(["success" => true, "message" => "Cita agendada correctamente."]);
                }
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}