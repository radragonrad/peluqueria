<?php
// 1. Cargamos la configuración central
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador (tu archivo de seguridad)
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        // Obtenemos solo los usuarios con rol cliente
        $stmt = $pdo->query("SELECT id, nombre, telefono FROM usuarios WHERE rol = 'usuario' ORDER BY nombre ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}