<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
   

    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        // Obtenemos los horarios usando tu estructura de id_dia
        $stmtH = $pdo->query("SELECT * FROM horarios ORDER BY id_dia ASC");
        $horarios = $stmtH->fetchAll();

        $stmtE = $pdo->query("SELECT * FROM horario_excepciones WHERE fecha >= CURDATE() ORDER BY fecha ASC");
        $excepciones = $stmtE->fetchAll();

        ob_clean(); 
        echo json_encode(["horarios" => $horarios, "excepciones" => $excepciones]);
        exit;
    }

    if ($metodo === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $sql = "UPDATE horarios SET 
                abierto = ?, 
                h_apertura_1 = ?, h_cierre_1 = ?, 
                h_apertura_2 = ?, h_cierre_2 = ? 
                WHERE id_dia = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['abierto'],
            $data['h_apertura_1'] ?: null, 
            $data['h_cierre_1'] ?: null,
            $data['h_apertura_2'] ?: null, 
            $data['h_cierre_2'] ?: null,
            $data['id_dia']
        ]);
        
        ob_clean();
        echo json_encode(["success" => true]);
        exit;
    }

    // POST y DELETE se mantienen igual para la tabla de excepciones...
    // (Añadir código de POST y DELETE del ejemplo anterior)

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}