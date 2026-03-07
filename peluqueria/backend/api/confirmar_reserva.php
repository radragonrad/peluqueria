<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
require_once __DIR__ . '/../../../private/config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. Verificamos que los datos lleguen (usamos isset para evitar problemas con tipos de datos)
if (
    isset($data['user_id']) &&
    isset($data['servicio_id']) &&
    isset($data['peluquero_id']) &&
    !empty($data['fecha']) &&
    !empty($data['hora'])
) {
    $user_id = intval($data['user_id']);
    $servicio_id = intval($data['servicio_id']);
    $peluquero_id = intval($data['peluquero_id']);
    $fecha = $data['fecha'];
    $hora = $data['hora'];

    try {


        // 2. VALIDACIÓN: ¿Está el peluquero ocupado? (Sentencia preparada)
        $stmt_check = $pdo->prepare("SELECT id FROM reservas WHERE peluquero_id = ? AND fecha = ? AND hora = ?");
        $stmt_check->execute([$peluquero_id, $fecha, $hora]);
       
        if ($stmt_check->rowCount() > 0) {
            echo json_encode(["success" => false, "message" => "Lo sentimos, este peluquero ya tiene una cita a esa hora."]);
            exit;
        }

        // 3. INSERTAR RESERVA (Sentencia preparada)
        $sql = "INSERT INTO reservas (user_id, servicio_id, peluquero_id, fecha, hora) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        
        
        if ($stmt_insert->execute([$user_id, $servicio_id, $peluquero_id, $fecha, $hora])) {
            echo json_encode(["success" => true, "message" => "¡Cita reservada correctamente!"]);
        } else {
            echo json_encode(["success" => false, "message" => "No se pudo completar la reserva."]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {

    if (!isset($data['user_id']) || empty($data['user_id'])) {
        $mensaje = "Sesión caducada o usuario no identificado. Por favor reingresa.";
    }

    
    echo json_encode([
        "success" => false, 
        "message" => $mensaje,
        "debug_info" => [
            "user_id_presente" => isset($data['user_id']),
            "data_recibida" => $data
        ]
    ]);
    
}