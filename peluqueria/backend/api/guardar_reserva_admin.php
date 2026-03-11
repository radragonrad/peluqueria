<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'POST') {
        $raw_data = file_get_contents("php://input");
        $data = json_decode($raw_data, true);

        if (!$data || empty($data['cliente_id']) || empty($data['servicio_id'])) {
            echo json_encode(["success" => false, "error" => "Datos incompletos"]);
            exit;
        }

        // 1. Obtenemos precio y duración real del servicio
        $stmtS = $pdo->prepare("SELECT precio, duracion_min FROM servicios WHERE id = ?");
        $stmtS->execute([$data['servicio_id']]);
        $serv = $stmtS->fetch(PDO::FETCH_ASSOC);

        // 2. Insertamos la reserva (Estado COMPLETADA o CONFIRMADA según prefieras)
        $sql = "INSERT INTO reservas (usuario_id, servicio_id, fecha, hora, precio, duracion, estado, notas) 
                VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE', 'Cita agendada por Admin')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['cliente_id'],
            $data['servicio_id'],
            $data['fecha'],
            $data['hora'],
            $serv['precio'],
            $serv['duracion_min']
        ]);

        echo json_encode([
            "success" => true,
            "reserva_id" => $pdo->lastInsertId()
        ]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}