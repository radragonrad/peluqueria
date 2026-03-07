<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
require_once __DIR__ . '/../../../private/config/db.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['reserva_id']) && isset($data['user_id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Verificamos que la reserva pertenezca al usuario antes de borrar
        $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ? AND user_id = ?");
        $stmt->execute([$data['reserva_id'], $data['user_id']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Reserva anulada correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontró la reserva o no tienes permiso."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos insuficientes."]);
}