<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
    // La variable $pdo ya está disponible gracias a db.php
    $metodo = $_SERVER['REQUEST_METHOD'];

    switch($metodo) {
        case 'GET':
            // Obtenemos todos los servicios ordenados alfabéticamente
            $stmt = $pdo->query("SELECT * FROM servicios WHERE 1 ORDER BY nombre ASC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'POST':
            // En servicios solemos enviar JSON desde el frontend (fetch body)
            $raw_data = file_get_contents("php://input");
            $data = json_decode($raw_data, true);

            if (!$data) {
                echo json_encode(["success" => false, "error" => "No se recibieron datos JSON válidos"]);
                exit;
            }

            $sql = "";
            $params = [];

            if(isset($data['id']) && !empty($data['id'])) {
                // --- MODO ACTUALIZAR ---
                $sql = "UPDATE servicios SET nombre=?, precio=?, duracion_min=?, descripcion=?, icono=?, activo=? WHERE id=?";
                $params = [
                    $data['nombre'], 
                    $data['precio'], 
                    $data['duracion_min'], 
                    $data['descripcion'], 
                    $data['icono'], 
                    $data['activo'], 
                    $data['id']
                ];
                $action = "update";
            } else {
                // --- MODO CREAR ---
                $sql = "INSERT INTO servicios (nombre, precio, duracion_min, descripcion, icono, activo) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [
                    $data['nombre'], 
                    $data['precio'], 
                    $data['duracion_min'], 
                    $data['descripcion'], 
                    $data['icono'], 
                    1 // Activo por defecto al crear
                ];
                $action = "insert";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                "success" => true,
                "action" => $action,
                "new_id" => ($action === "insert") ? $pdo->lastInsertId() : $data['id']
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }

} catch (PDOException $e) {
    // En caso de error, devolvemos un 500 con el detalle
    http_response_code(500); 
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}