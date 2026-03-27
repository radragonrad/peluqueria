<?php
// 1. Configuración central
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

// 2. Incluir PHPMailer
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);

/**
 * FUNCIÓN PARA AGENDAR EN GOOGLE Y DEVOLVER EL ID
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
        $end_time = date('Y-m-d\TH:i:s', strtotime($start_time . ' +45 minutes'));

        $event = new Google\Service\Calendar\Event([
            'summary'     => 'Cita Peluquería: ' . $datos['servicio_nombre'],
            'location'    => 'C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla',
            'description' => "Cliente: " . $datos['cliente_nombre'] . " (" . $datos['cliente_email'] . ") | Peluquero: " . $datos['peluquero_nombre'],
            'start'       => ['dateTime' => $start_time, 'timeZone' => 'Europe/Madrid'],
            'end'         => ['dateTime' => $end_time, 'timeZone' => 'Europe/Madrid'],
            'reminders'   => [
                'useDefault' => FALSE,
                'overrides'  => [['method' => 'popup', 'minutes' => 30]],
            ],
        ]);

        $calendarId = 'radragon.rad@gmail.com'; 
        
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

// --- PROCESO DE RESERVA ---

if (isset($data['user_id'], $data['servicio_id'], $data['peluquero_id'], $data['fecha'], $data['hora'])) {
    
    $user_id = intval($data['user_id']);
    $servicio_id = intval($data['servicio_id']);
    $peluquero_id = intval($data['peluquero_id']);
    $fecha = $data['fecha'];
    $hora = $data['hora'];

    try {
        // 1. Insertar reserva básica en DB
        $sql = "INSERT INTO reservas (user_id, servicio_id, peluquero_id, fecha, hora) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        
        if ($stmt_insert->execute([$user_id, $servicio_id, $peluquero_id, $fecha, $hora])) {
            $id_reserva_local = $pdo->lastInsertId(); // El ID que acaba de crear MySQL

            // 2. Obtener datos para Google y Email
            $stmt_info = $pdo->prepare("
                SELECT u_cliente.nombre AS cliente_nombre, u_cliente.email AS cliente_email, 
                       s.nombre AS servicio_nombre, u_peluquero.nombre AS peluquero_nombre
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
                    'fecha' => $fecha, 'hora' => $hora,
                    'cliente_nombre' => $info['cliente_nombre'],
                    'cliente_email' => $info['cliente_email'],
                    'servicio_nombre' => $info['servicio_nombre'],
                    'peluquero_nombre' => $info['peluquero_nombre']
                ];

                // 3. MANDAR A GOOGLE Y OBTENER EL ID DEL EVENTO
                $google_event_id = agendarEnGoogleCalendar($datos_cal, $pdo);

                if ($google_event_id) {
                    // 4. ACTUALIZAR LA RESERVA CON EL ID DE GOOGLE
                    $stmt_upd = $pdo->prepare("UPDATE reservas SET google_event_id = ? WHERE id = ?");
                    $stmt_upd->execute([$google_event_id, $id_reserva_local]);
                }

                enviarEmailConfirmacionCita($datos_cal);
                echo json_encode(["success" => true, "message" => "Cita agendada correctamente."]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}