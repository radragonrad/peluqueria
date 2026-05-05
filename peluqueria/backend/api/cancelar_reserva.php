<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once __DIR__ . '/../../../private/includes/functions.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['reserva_id']) && isset($data['user_id'])) {

    $reserva_id = intval($data['reserva_id']);
    $user_id    = intval($data['user_id']);

    try {
        // 1. Obtenemos los datos completos de la reserva antes de borrar
        $stmt_select = $pdo->prepare("
            SELECT r.google_event_id, r.fecha, r.hora, 
                   s.nombre AS servicio_nombre, u.nombre AS cliente_nombre
            FROM reservas r
            JOIN servicios s ON s.id = r.servicio_id
            JOIN usuarios u ON u.id = r.user_id
            WHERE r.id = ? AND r.user_id = ?
        ");
        $stmt_select->execute([$reserva_id, $user_id]);
        $reserva = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($reserva) {
            $google_borrado = false;

            // 2. Intentamos borrar de Google Calendar
            if (!empty($reserva['google_event_id'])) {
                try {
                    $client = new Google\Client();
                    $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
                    $client->addScope(Google\Service\Calendar::CALENDAR);
                    $service = new Google\Service\Calendar($client);

                    $calendarId = '74408619d033cb1f21fa67fc786412a08171ce2a3510512e075bd3b862a2d9e1@group.calendar.google.com';
                    $service->events->delete($calendarId, $reserva['google_event_id']);
                    $google_borrado = true;

                } catch (Exception $ge) {
                    error_log("Error al borrar en Google: " . $ge->getMessage());

                    // Log fallo Google
                    registrarLog($pdo, $user_id, 'ERROR_CANCELACION_GOOGLE', [
                        'reserva_id'     => $reserva_id,
                        'google_event_id'=> $reserva['google_event_id'],
                        'error'          => $ge->getMessage(),
                    ], 0);
                }
            }

            // 3. Anulamos la reserva en DB
            $stmt_delete = $pdo->prepare("UPDATE reservas SET estado = 'ANULADA WEB' WHERE id = ? AND user_id = ?");
            $stmt_delete->execute([$reserva_id, $user_id]);

            if ($stmt_delete->rowCount() > 0) {

                // Log cancelación exitosa
                registrarLog($pdo, $user_id, 'CANCELACION_RESERVA', [
                    'reserva_id'      => $reserva_id,
                    'cliente_nombre'  => $reserva['cliente_nombre'],
                    'servicio'        => $reserva['servicio_nombre'],
                    'fecha'           => $reserva['fecha'],
                    'hora'            => $reserva['hora'],
                    'google_event_id' => $reserva['google_event_id'] ?? null,
                    'google_borrado'  => $google_borrado,
                ], 0);

                echo json_encode(["success" => true, "message" => "Reserva anulada correctamente."]);

            } else {

                // Log fallo al actualizar DB
                registrarLog($pdo, $user_id, 'ERROR_CANCELACION_DB', [
                    'reserva_id' => $reserva_id,
                    'motivo'     => 'rowCount 0 al actualizar estado',
                ], 0);

                echo json_encode(["success" => false, "message" => "No se pudo eliminar la reserva de la base de datos."]);
            }

        } else {
            // Log intento sin permiso o reserva inexistente
            registrarLog($pdo, $user_id, 'ERROR_CANCELACION_PERMISO', [
                'reserva_id' => $reserva_id,
                'motivo'     => 'Reserva no encontrada o sin permiso',
            ], 0);

            echo json_encode(["success" => false, "message" => "No se encontró la reserva o no tienes permiso."]);
        }

    } catch (PDOException $e) {
        // Log error general de BD
        registrarLog($pdo, $user_id ?? null, 'ERROR_CANCELACION_RESERVA', [
            'reserva_id' => $reserva_id ?? null,
            'error'      => $e->getMessage(),
        ], 0);

        echo json_encode(["success" => false, "message" => "Error de base de datos: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Datos insuficientes."]);
}