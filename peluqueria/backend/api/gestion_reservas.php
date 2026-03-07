<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
  
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        $sql = "SELECT r.*, u.usuario as cliente_nombre, u.telefono as cliente_telefono, 
                       s.nombre as servicio_nombre, s.icono as servicio_icono, s.precio as precio,
                        s.duracion_min as duracion
                FROM reservas r
                JOIN usuarios u ON r.user_id = u.id
                JOIN servicios s ON r.servicio_id = s.id
                ORDER BY r.fecha DESC, r.hora DESC";
        
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();
        
        // Limpiar cualquier salida previa y enviar
        ob_clean(); 
        echo json_encode($resultados);
        exit;
    }

    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $sql = "UPDATE reservas SET estado = ?, motivo_cancelacion = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['estado'], 
            $data['motivo'] ?? null, 
            $data['id']
        ]);
        
        ob_clean();
        echo json_encode(["success" => true]);
        exit;
    }

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}