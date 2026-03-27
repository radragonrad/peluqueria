<?php
require_once __DIR__ . '/../../../private/config/db.php'; 
require_once 'admin_check.php';

header('Content-Type: application/json');
$metodo = $_SERVER['REQUEST_METHOD'];

// LISTAR ETIQUETAS (GET)
if ($metodo === 'GET') {
    $stmt = $pdo->query("SELECT * FROM etiquetas ORDER BY nombre ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// CREAR ETIQUETA (POST)
if ($metodo === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['nombre'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nombre obligatorio']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO etiquetas (nombre, color) VALUES (?, ?)");
        $stmt->execute([$data['nombre'], $data['color'] ?? '#3498db']);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'La etiqueta ya existe']);
    }
    exit;
}

// ... después del bloque de POST ...

if ($metodo === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['nombre'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE etiquetas SET nombre = ?, color = ? WHERE id = ?");
    $stmt->execute([$data['nombre'], $data['color'], $data['id']]);
    echo json_encode(['status' => 'ok']);
    exit;
}

// BORRAR ETIQUETA (DELETE)
if ($metodo === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM etiquetas WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    }
    exit;
}