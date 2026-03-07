<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php'; 

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';


try {
 
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['fecha'])) {
            echo json_encode(["has_appointments" => false, "error" => "Fecha no proporcionada"]);
            exit;
        }

        $fecha = $data['fecha'];
        $solo_tramo = intval($data['solo_tramo']); // 0 día completo, 1 tramo
        $h_inicio = $data['h_inicio'] ?? '00:00';
        $h_fin = $data['h_fin'] ?? '23:59';

        if ($solo_tramo === 0) {
            // --- VALIDACIÓN DÍA COMPLETO ---
            // Buscamos cualquier cita que no esté cancelada o rechazada para ese día
            $sql = "SELECT COUNT(*) as total FROM reservas 
                    WHERE fecha = ? 
                    AND estado = 'PENDIENTE'";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha]);
        } else {
            // --- VALIDACIÓN TRAMO ESPECÍFICO ---
            // Buscamos citas que se solapen con el tramo horario
            // Lógica: La cita empieza antes de que termine el cierre Y termina después de que empiece el cierre
            $sql = "SELECT COUNT(*) as total FROM reservas 
                    WHERE fecha = ? 
                    AND estado = 'PENDIENTE'
                    AND (hora < ? AND hora > ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha, $h_fin, $h_inicio]);
        }

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = intval($res['total']);

        echo json_encode([
            "has_appointments" => $count > 0,
            "count" => $count
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}