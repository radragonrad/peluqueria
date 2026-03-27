<?php
// 1. Cargamos la configuración central y la librería de Google
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once __DIR__ . '/../../../vendor/autoload.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['reserva_id']) && isset($data['user_id'])) {
    try {
        // Usamos la conexión $pdo que ya viene de db.php (no hace falta el new PDO si ya está en db.php)
        
        // 1. Antes de borrar, obtenemos el ID del evento de Google
        $stmt_select = $pdo->prepare("SELECT google_event_id FROM reservas WHERE id = ? AND user_id = ?");
        $stmt_select->execute([$data['reserva_id'], $data['user_id']]);
        $reserva = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($reserva) {
            // 2. Si tiene un ID de Google, intentamos borrarlo del calendario
            if (!empty($reserva['google_event_id'])) {
                try {
                    $client = new Google\Client();
                    $client->setAuthConfig(__DIR__ . '/../../../private/credentials.json');
                    $client->addScope(Google\Service\Calendar::CALENDAR);
                    $service = new Google\Service\Calendar($client);

                    $calendarId = 'radragon.rad@gmail.com'; 
                    
                    // Borramos el evento en Google
                    $service->events->delete($calendarId, $reserva['google_event_id']);
                } catch (Exception $ge) {
                    // Si falla Google (por ejemplo si la cita ya se borró manualmente), 
                    // simplemente lo ignoramos para que la reserva sí se borre de la DB.
                    error_log("Error al borrar en Google: " . $ge->getMessage());
                }
            }

            // 3. Ahora borramos la reserva de la base de datos
            $stmt_delete = $pdo->prepare("UPDATE reservas SET estado = 'ANULADA WEB' WHERE id = ? AND user_id = ?");
            $stmt_delete->execute([$data['reserva_id'], $data['user_id']]);

            if ($stmt_delete->rowCount() > 0) {
                echo json_encode(["success" => true, "message" => "Reserva anulada correctamente en el sistema y en el calendario."]);
            } else {
                echo json_encode(["success" => false, "message" => "No se pudo eliminar la reserva de la base de datos."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "No se encontró la reserva o no tienes permiso."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error de base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos insuficientes."]);
}