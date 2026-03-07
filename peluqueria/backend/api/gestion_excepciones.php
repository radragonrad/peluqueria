<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
   
    $metodo = $_SERVER['REQUEST_METHOD'];

    // --- LISTAR EXCEPCIONES (GET) ---
    if ($metodo === 'GET') {
        // Solo mostramos de hoy en adelante para no saturar la vista
        $sql = "SELECT * FROM horario_excepciones WHERE 1 ORDER BY fecha DESC";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();
        
        ob_clean(); 
        echo json_encode($resultados);
        exit;
    }

    // --- CREAR EXCEPCIÓN (POST) ---
    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validamos si es tramo parcial para limpiar las horas si no lo es
        $esTramo = (int)$data['solo_tramo'];
        $h_inicio = ($esTramo === 1) ? $data['h_inicio'] : null;
        $h_fin = ($esTramo === 1) ? $data['h_fin'] : null;

        $sql = "INSERT INTO horario_excepciones 
                (fecha, descripcion, cerrado, solo_tramo, h_inicio, h_fin) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['fecha'], 
            $data['descripcion'] ?? 'Cierre excepcional', 
            $data['cerrado'] ?? 1,
            $esTramo,
            $h_inicio,
            $h_fin
        ]);
        
        ob_clean();
        echo json_encode(["success" => true]);
        exit;
    }

    // --- ELIMINAR EXCEPCIÓN (DELETE) ---
    if ($metodo === 'DELETE') {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $sql = "DELETE FROM horario_excepciones WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            ob_clean();
            echo json_encode(["success" => true]);
            exit;
        }
    }

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    // Manejo específico para el error de fecha duplicada (UNIQUE KEY idx_fecha)
    if ($e->getCode() == 23000) {
        echo json_encode(["error" => "Ya existe un registro para esta fecha"]);
    } else {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}