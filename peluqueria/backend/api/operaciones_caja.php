<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

// Detectamos qué método se está usando (POST, DELETE, etc.)
$metodo = $_SERVER['REQUEST_METHOD'];

// Lógica para INSERTAR (POST)
if ($metodo === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO movimientos_caja (tipo, concepto, importe, metodo_pago, categoria, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['tipo'],
        $data['concepto'],
        $data['importe'],
        $data['metodo_pago'],
        $data['categoria'],
        $_SESSION['user_id']
    ]);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Lógica para BORRAR (DELETE)
if ($metodo === 'DELETE') {
    // El ID viene en la URL: operaciones_caja.php?id=XX
    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM movimientos_caja WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['status' => 'ok', 'message' => 'Movimiento eliminado']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
    }
    exit;
}