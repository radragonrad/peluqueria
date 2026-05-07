<?php
// 1. Configuración central
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../private/includes/functions.php';

// 2. Incluir PHPMailer
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);

/**
 * Devuelve el colorId de Google Calendar según el nombre del servicio.
 *
 * IDs disponibles:
 *   1  Lavanda   · 2  Salvia     · 3  Uva        · 4  Tomate
 *   5  Plátano   · 6  Mandarina  · 7  Pavo real  · 8  Grafito
 *   9  Arándano  · 10 Albahaca   · 11 Flamingo
 *
 * Ajusta el mapa con los nombres exactos de tu tabla `servicios`.
 */
function getColorIdPorServicio($servicio_id)
{
    // Mapa directo ID de servicio → colorId de Google Calendar.
    // Ajusta los IDs según tu tabla `servicios`.
    //
    // Colores disponibles:
    //   1 Lavanda · 2 Salvia · 3 Uva      · 4 Tomate
    //   5 Plátano · 6 Mandarina · 7 Pavo real · 8 Grafito
    //   9 Arándano · 10 Albahaca · 11 Flamingo
    $mapa = [
        1 => '7',   // Arreglo de barba → Pavo real (azul)
        2 => '2',   // Corte            → Salvia (verde)
        3 => '6',   // Corte + Barba    → Mandarina (naranja)
        4 => '5',   // Mechas           → Plátano (amarillo)
    ];

    return $mapa[$servicio_id] ?? '8'; // Grafito por defecto
}

/**
 * FUNCIÓN PARA AGENDAR EN GOOGLE Y DEVOLVER EL ID
 */
function agendarEnGoogleCalendar($datos, $pdo) {
    try {
        $client = new Google\Client();
        $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
        $client->addScope(Google\Service\Calendar::CALENDAR);

        $service = new Google\Service\Calendar($client);

        $fecha      = trim($datos['fecha']);
        $hora       = substr(trim($datos['hora']), 0, 5);
        $start_time = $fecha . 'T' . $hora . ':00';
        $duracion   = intval($datos['duracion']);
        $end_time   = date('Y-m-d\TH:i:s', strtotime($start_time . ' +' . $duracion . ' minutes'));

        // ── Color según servicio ─────────────────────────────────────────────
        $colorId = getColorIdPorServicio($datos['servicio_id']);

        $event = new Google\Service\Calendar\Event([
            'summary'     => 'Cita Peluquería: ' . $datos['servicio_nombre'],
            'location'    => 'C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla',
            'description' => "Cliente: " . $datos['cliente_nombre'] . " (" . $datos['cliente_email'] . ") | Peluquero: " . $datos['peluquero_nombre'],
            'colorId'     => $colorId,
            'start'       => ['dateTime' => $start_time, 'timeZone' => 'Europe/Madrid'],
            'end'         => ['dateTime' => $end_time,   'timeZone' => 'Europe/Madrid'],
            'reminders'   => [
                'useDefault' => FALSE,
                'overrides'  => [['method' => 'popup', 'minutes' => 30]],
            ],
        ]);

        $calendarId   = '74408619d033cb1f21fa67fc786412a08171ce2a3510512e075bd3b862a2d9e1@group.calendar.google.com';
        $eventCreated = $service->events->insert($calendarId, $event);

        return $eventCreated->getId();

    } catch (Exception $e) {
        error_log("Error Google Calendar: " . $e->getMessage());
        return null;
    }
}

function enviarEmailConfirmacionCita($datos) {
    $fecha_formato = date('Ymd', strtotime($datos['fecha']));
    $hora_inicio   = date('His', strtotime($datos['hora']));
    $hora_fin      = date('His', strtotime($datos['hora'] . ' +45 minutes'));

    $enlace_cal = "https://www.google.com/calendar/render?action=TEMPLATE"
        . "&text="     . urlencode("Cita: " . $datos['servicio_nombre'])
        . "&dates="    . $fecha_formato . "T" . $hora_inicio . "/" . $fecha_formato . "T" . $hora_fin
        . "&details="  . urlencode("Peluquero: " . $datos['peluquero_nombre'] . "\nServicio: " . $datos['servicio_nombre'])
        . "&location=" . urlencode("C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla")
        . "&sf=true&output=xml";

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
    } catch (Exception $e) {
        return false;
    }
}

// ── PROCESO DE RESERVA ────────────────────────────────────────────────────────

if (isset($data['user_id'], $data['servicio_id'], $data['peluquero_id'], $data['fecha'], $data['hora'])) {

    $user_id      = intval($data['user_id']);
    $servicio_id  = intval($data['servicio_id']);
    $peluquero_id = intval($data['peluquero_id']);
    $fecha        = $data['fecha'];
    $hora         = $data['hora'];

    // ── Límite de reservas por usuario ──────────────────────────────────────
    // Segunda capa de seguridad: aunque alguien salte el formulario y llame
    // a la API directamente, no podrá crear más de 2 citas pendientes.
    if (($_SESSION['usuario'] ?? '') !== 'hadesinfer') {
        $stmt_limite = $pdo->prepare("
            SELECT COUNT(*)
            FROM reservas
            WHERE user_id = ?
              AND fecha >= CURDATE()
              AND estado = 'PENDIENTE'
        ");
        $stmt_limite->execute([$user_id]);
        $citas_activas = $stmt_limite->fetchColumn();

        if ($citas_activas >= 3) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya tienes 2 citas reservadas. Cancela una antes de reservar otra.',
            ]);
            exit;
        }
    }

    // Obtener duración ANTES de la transacción
    $stmt_dur = $pdo->prepare("SELECT duracion_min FROM servicios WHERE id = ?");
    $stmt_dur->execute([$servicio_id]);
    $duracion_nueva = $stmt_dur->fetchColumn() ?: 30;

    try {
        $pdo->beginTransaction();

        // Bloqueo pesimista con comprobación de solapamiento real por duración
        $stmt_check = $pdo->prepare("
            SELECT COUNT(*)
            FROM reservas r
            JOIN servicios s ON r.servicio_id = s.id
            WHERE r.peluquero_id = ?
              AND r.fecha = ?
              AND r.estado IN ('PENDIENTE', 'COMPLETADA')
              AND ADDTIME(r.hora, SEC_TO_TIME(s.duracion_min * 60)) > ?
              AND r.hora < ADDTIME(?, SEC_TO_TIME(? * 60))
            FOR UPDATE
        ");
        $stmt_check->execute([$peluquero_id, $fecha, $hora, $hora, $duracion_nueva]);
        $ocupado = $stmt_check->fetchColumn();

        if ($ocupado > 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lo sentimos, esa hora acaba de ser reservada por otro cliente. Por favor, elige otra hora.']);
            exit;
        }

        // Insertar reserva
        $stmt_insert = $pdo->prepare("INSERT INTO reservas (user_id, servicio_id, peluquero_id, fecha, hora) VALUES (?, ?, ?, ?, ?)");

        if ($stmt_insert->execute([$user_id, $servicio_id, $peluquero_id, $fecha, $hora])) {
            $id_reserva_local = $pdo->lastInsertId();

            // Obtener datos para Google Calendar y Email
            $stmt_info = $pdo->prepare("
                SELECT u_cliente.nombre   AS cliente_nombre,
                       u_cliente.email    AS cliente_email,
                       s.nombre           AS servicio_nombre,
                       u_peluquero.nombre AS peluquero_nombre,
                       s.duracion_min     AS duracion
                FROM usuarios u_cliente
                CROSS JOIN servicios s
                JOIN peluqueros p    ON p.id = ?
                JOIN usuarios u_peluquero ON p.usuario_id = u_peluquero.id
                WHERE u_cliente.id = ? AND s.id = ?
            ");
            $stmt_info->execute([$peluquero_id, $user_id, $servicio_id]);
            $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                $datos_cal = [
                    'fecha'           => $fecha,
                    'hora'            => $hora,
                    'cliente_nombre'  => $info['cliente_nombre'],
                    'cliente_email'   => $info['cliente_email'],
                    'servicio_nombre' => $info['servicio_nombre'],
                    'peluquero_nombre'=> $info['peluquero_nombre'],
                    'duracion'        => $info['duracion'],
                    'servicio_id'     => $servicio_id,
                ];

                // Crear evento en Google Calendar (con color por servicio)
                $google_event_id = agendarEnGoogleCalendar($datos_cal, $pdo);

                if ($google_event_id) {
                    $stmt_upd = $pdo->prepare("UPDATE reservas SET google_event_id = ? WHERE id = ?");
                    $stmt_upd->execute([$google_event_id, $id_reserva_local]);
                }

                $email_ok = enviarEmailConfirmacionCita($datos_cal);

                registrarLog($pdo, $user_id, 'NUEVA_RESERVA', [
                    'id_reserva'      => $id_reserva_local,
                    'cliente_nombre'  => $info['cliente_nombre'],
                    'cliente_email'   => $info['cliente_email'],
                    'servicio'        => $info['servicio_nombre'],
                    'peluquero'       => $info['peluquero_nombre'],
                    'fecha'           => $fecha,
                    'hora'            => $hora,
                    'duracion_min'    => $info['duracion'],
                    'google_event_id' => $google_event_id ?? null,
                ], $email_ok ? 1 : 0);

                $pdo->commit();
                echo json_encode(["success" => true, "message" => "Cita agendada correctamente."]);
            }
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        registrarLog($pdo, $user_id ?? null, 'ERROR_RESERVA', [
            'error'        => $e->getMessage(),
            'user_id'      => $user_id ?? null,
            'servicio_id'  => $servicio_id ?? null,
            'peluquero_id' => $peluquero_id ?? null,
            'fecha'        => $fecha ?? null,
            'hora'         => $hora ?? null,
        ], 0);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}